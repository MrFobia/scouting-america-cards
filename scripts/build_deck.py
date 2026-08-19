#!/usr/bin/env python3
"""
build_deck.py — arma data/decks/<id>.json a partir del PDF de la baraja y del
volcado de la traducción, sin perder la cabecera curada del mazo.

    python3 scripts/build_deck.py bear reference/decks/bear-en.pdf \
        reference/decks/bear-es.txt

La traducción llega como texto plano con pipes (una línea por carta) porque es
lo que devuelve el documento del cliente; el cruce se hace por el título
original en inglés, que la traducción declara en cada carta.
"""
import json, sys
from pathlib import Path
import parse_deck

RAIZ = Path(__file__).resolve().parent.parent

def cargar_es(ruta):
    recursos, aventuras, acts = [], {}, {}
    if not ruta or not Path(ruta).exists():
        return recursos, aventuras, acts
    for l in Path(ruta).read_text().splitlines():
        if not l.strip(): continue
        p = l.split('|')
        if   p[0] == 'CARTA DE RECURSOS': recursos.append((p[1], p[2]))
        elif p[0] == 'AVENTURA':          aventuras[p[1]] = (p[2], p[3], p[4])
        else:                             acts[p[0]] = (p[1], p[2], p[3])
    return recursos, aventuras, acts

def main(deck_id, pdf, es_txt=None):
    ruta = RAIZ / 'site/assets/data/decks' / f'{deck_id}.json'
    viejo = json.loads(ruta.read_text()) if ruta.exists() else {
        'id': deck_id, 'audience': 'family', 'adventures': [], 'cards': []}
    # Los enlaces se curan a mano (el QR impreso se vuelve enlace en digital),
    # así que sobreviven a cada reconstrucción del contenido.
    enlaces = {c['id']: c['links'] for c in viejo['cards'] if c.get('links')}

    advs, cards = parse_deck.main(deck_id, Path(pdf))
    recursos, aventuras, acts = cargar_es(es_txt)

    n_adv = n_act = 0
    adventures = []
    for a in advs.values():
        tr = aventuras.get(a['name']['en'])
        if tr:
            a['name']['es'], a['category']['es'], a['summary']['es'] = tr
            n_adv += 1
        adventures.append(a)

    i_rec = 0
    for c in cards:
        pg = c.pop('_page'); c.pop('_aid', None); c.pop('_idx', None)
        c['links'] = enlaces.get(c['id'], [])
        c['image'] = {'src': f'/site/assets/img/cards/{deck_id}/p{pg:03d}.webp',
                      'width': 413, 'height': 563, 'page': pg, 'lang': 'en'}
        if c['skin'] == 'activity':
            tr = acts.get(c['originalTitle'])
            if tr:
                c['title']['es'], c['requirement']['text']['es'], c['description']['es'] = tr
                n_act += 1
        elif c['skin'] == 'resource' and i_rec < len(recursos):
            c['title']['es'], c['body']['es'] = recursos[i_rec]; i_rec += 1

    total_act = sum(1 for c in cards if c['skin'] == 'activity')
    # Un solo veredicto sobre la traducción, que se escribe en el mazo y en el
    # índice: mantenerlo a mano hacía que el índice dijera "traducción lista"
    # en tres mazos sin una sola carta en español.
    cobertura = n_act / total_act if total_act else 0
    estado_tr = ('lista' if cobertura >= 0.95
                 else 'parcial' if cobertura > 0 else 'pendiente')

    nuevo = {k: v for k, v in viejo.items() if k not in ('adventures', 'cards', 'estado')}
    # El mazo deja de estar "en preparación" en cuanto tiene tarjetas: la vista
    # lo lee de acá para decidir si muestra el estado vacío.
    nuevo['$status'] = (f'{len(cards)} tarjetas extraídas del PDF oficial. '
                        f'Español en {n_adv}/{len(adventures)} Adventures y '
                        f'{n_act}/{total_act} actividades; el resto cae a inglés con aviso.')
    nuevo['translation'] = estado_tr
    nuevo['adventures'], nuevo['cards'] = adventures, cards
    ruta.write_text(json.dumps(nuevo, ensure_ascii=False, indent=1) + '\n')

    idx_ruta = RAIZ / 'site/assets/data/decks/_index.json'
    idx = json.loads(idx_ruta.read_text())
    for d in idx['decks']:
        if d['id'] != deck_id: continue
        d['translation'] = estado_tr
        d['cardCount'] = len(cards)
        d['spanishRatio'] = round(cobertura, 2)
    idx_ruta.write_text(json.dumps(idx, ensure_ascii=False, indent=1) + '\n')

    faltan = sorted({c['adventureId'] for c in cards
                     if c['skin'] == 'activity' and not c['title']['es']})
    print(f'→ {ruta.relative_to(RAIZ)}  ·  {n_adv}/{len(adventures)} Adventures y '
          f'{n_act}/{total_act} actividades en español')
    if faltan: print('   sin traducir:', ', '.join(faltan))

if __name__ == '__main__':
    main(*sys.argv[1:])
