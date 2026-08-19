#!/usr/bin/env python3
"""
parse_deck.py — convierte el PDF de una baraja en el JSON completo del mazo.

Cada página impar es el frente de una tarjeta; las pares son el dorso (solo el
logo) y se descartan. Tres tipos de frente, cada uno con su marcador:

  · Adventure  → "NUMBER OF REQUIREMENTS"
  · Actividad  → "REQUIREMENT n OF m"
  · Recurso    → ninguno de los dos, en las primeras páginas

Uso:  python3 scripts/parse_deck.py bear reference/decks/bear-en.pdf
"""
import json, re, subprocess, sys, unicodedata
from collections import Counter
from pathlib import Path

RAIZ = Path(__file__).resolve().parent.parent
LUGARES = {'indoor':'indoor', 'outdoor':'outdoor', 'travel':'outing',
           'indoor/outdoor':'indoor-outdoor'}

def slug(s):
    s = unicodedata.normalize('NFD', s)
    s = ''.join(c for c in s if unicodedata.category(c) != 'Mn')
    return re.sub(r'[^a-z0-9]+', '-', s.lower()).strip('-')

def lineas(pagina):
    return [l.strip() for l in pagina.split('\n') if l.strip()]

def paginas_con_alturas(pdf):
    """Cada página como lista de (altura_de_fuente, x, texto).

    La sangría del texto plano no distingue el título del requisito: hay
    títulos más adentro y más afuera del bloque de requisito, y pdftotext
    redondea columnas. El cuerpo tipográfico sí es constante en toda la
    baraja —nombre de Adventure ~14, título ~12, texto ~9, etiquetas ~7—,
    así que la jerarquía se lee del PDF en vez de adivinarse.
    """
    xml = subprocess.run(['pdftotext', '-bbox-layout', str(pdf), '-'],
                         capture_output=True, text=True, check=True).stdout
    ent = {'&amp;': '&', '&lt;': '<', '&gt;': '>', '&quot;': '"', '&apos;': "'"}
    paginas = []
    for pg in re.findall(r'<page width=.*?</page>', xml, re.S):
        out = []
        for xmin, ymin, ymax, cuerpo in re.findall(
                r'<line xMin="([\d.]+)" yMin="([\d.]+)" xMax="[\d.]+" yMax="([\d.]+)">(.*?)</line>',
                pg, re.S):
            txt = ' '.join(re.findall(r'>([^<]*)</word>', cuerpo))
            for k, v in ent.items(): txt = txt.replace(k, v)
            txt = txt.strip()
            if txt: out.append((round(float(ymax) - float(ymin), 1), float(xmin), txt))
        paginas.append(out)
    return paginas

def nombre_adventure(lns, desde):
    """El nombre compuesto en el cuerpo grande, que puede ocupar dos renglones.

    "Running With the Pack" y "Safety in Numbers" envuelven; tomar solo el
    primer renglón dejaba ids truncados como `running-with`, y con ellos las
    actividades apuntando a una Adventure que no existe.
    """
    h = lns[desde][0]
    partes = []
    for alt, _, txt in lns[desde:]:
        if abs(alt - h) > 0.4: break
        partes.append(txt)
    return ' '.join(partes)


def parse_adventure(lns):
    """Nombre, categoría, obligatoria y resumen de una portada de Adventure."""
    ls = [t for _, _, t in lns]
    i = next(i for i, l in enumerate(ls) if 'NUMBER OF REQUIREMENTS' in l)
    resto = ls[i+1:]
    nombre = nombre_adventure(lns, i+1) if i+1 < len(lns) else ''
    # Las líneas en mayúsculas traen la categoría y la bandera, a veces juntas
    # y a veces en renglones distintos. Se separan, no se pisan.
    cat, obligatoria = '', False
    for l in resto[1:5]:
        if '©' in l or l.upper() != l or len(l) < 4: continue
        limpio = l.upper()
        if 'REQUIRED' in limpio:
            obligatoria = True
            limpio = limpio.replace('REQUIRED', '').strip(' ·-')
        elif 'ELECTIVE' in limpio:
            limpio = limpio.replace('ELECTIVE', '').strip(' ·-')
        if limpio and not cat: cat = limpio
    # El resumen es el texto corrido de la carta: la altura que más se repite.
    # Medirlo por longitud de línea no servía —el bloque envuelve en renglones
    # de ~45 caracteres— y dejaba el resumen vacío en los cinco mazos.
    # Se descartan los renglones del nombre: en una Adventure de título largo
    # y resumen corto empatan en cantidad, y el nombre ganaba el desempate.
    h_nombre = lns[i+1][0] if i+1 < len(lns) else 99
    cuerpo = [(alt, txt) for alt, _, txt in lns[i+1:]
              if '©' not in txt and abs(alt - h_nombre) > 0.4]
    conteo = Counter(alt for alt, _ in cuerpo)
    # A igualdad de renglones manda el cuerpo más chico: el texto corrido nunca
    # es la tipografía más grande de la carta.
    h_txt = min(conteo, key=lambda a: (-conteo[a], a)) if conteo else 9
    resumen = ' '.join(txt for alt, txt in cuerpo if abs(alt - h_txt) < 0.4)
    n = len(re.findall(r'\d+', ls[max(0, i-1)])) or 0
    return nombre, cat.title(), obligatoria, resumen.strip(), n

def parse_actividad(lns):
    """`lns` son (altura, x, texto) de una carta de actividad.

    El título es la única línea con cuerpo mayor al del texto corrido y menor
    al del nombre de la Adventure. Todo el texto corrido anterior es el
    requisito; el posterior, la descripción.
    """
    lns = [t for t in lns if '©' not in t[2]]
    k = next(k for k, t in enumerate(lns) if 'REQUIREMENT' in t[2])
    m = re.search(r'REQUIREMENT (\d+) OF (\d+)', lns[k][2])
    idx, tot = int(m.group(1)), int(m.group(2))

    resto = lns[k+1:]
    adventure = nombre_adventure(lns, k+1) if resto else ''
    h_adv = resto[0][0] if resto else 99
    # Cuántos renglones ocupó el nombre, para saber dónde empieza la categoría.
    n_nombre = next((j for j, (alt, _, _) in enumerate(resto) if abs(alt - h_adv) > 0.4), len(resto))
    cat = next((t for _, _, t in resto[n_nombre:n_nombre+2] if t.upper() == t and len(t) > 3), '')

    cuerpo = [t for t in resto[n_nombre:] if t[2] != cat]
    # El cuerpo del texto corrido es la altura que más se repite: la
    # descripción y el requisito juntos siempre superan en líneas al título.
    h_txt = Counter(h for h, _, _ in cuerpo).most_common(1)[0][0] if cuerpo else 9

    lugar, medidas, texto, titulo = None, [], [], ''
    en_titulo = False
    for h, x, l in cuerpo:
        # Solo en el texto corrido: hay títulos que empiezan por "Outdoor".
        mm = re.match(r'(Indoor/Outdoor|Indoor|Outdoor|Travel)\s*(.*)$', l) \
             if abs(h - h_txt) < 0.6 else None
        if mm:
            lugar = LUGARES[mm.group(1).lower()]
            l = mm.group(2).strip()
            if not l: continue
        if re.fullmatch(r'\d', l): medidas.append(int(l)); continue
        # Un título largo se compone en dos renglones del mismo cuerpo; se
        # unen mientras la altura siga siendo la del título.
        if h_txt + 1 < h < h_adv - 0.5 and (not titulo or en_titulo):
            titulo = f'{titulo} {l}'.strip()
            if not en_titulo: texto.append(None)          # marca el corte
            en_titulo = True
            continue
        en_titulo = False
        texto.append(l)

    corte = texto.index(None) if None in texto else len(texto)
    requisito = ' '.join(t for t in texto[:corte] if t)
    desc = ' '.join(t for t in texto[corte+1:] if t)

    while len(medidas) < 3: medidas.append(0)
    return dict(idx=idx, tot=tot, adventure=adventure, cat=cat.title(),
                requisito=requisito.strip(), titulo=titulo.strip(),
                desc=desc.strip(), lugar=lugar or 'indoor', medidas=medidas[:3])

def main(deck_id, pdf):
    paginas = paginas_con_alturas(pdf)

    adventures, cards = {}, []
    for i, pg in enumerate(paginas):
        ls = [t for _, _, t in pg]
        plano = ' '.join(ls)
        # Dorso: solo la marca, a veces con el nombre del rank al lado
        # ("TIGER ™"). Filtrar únicamente por '™' pelado colaba cinco dorsos
        # de Tiger como si fueran cartas de recurso.
        if not ls or ('™' in plano and len(ls) <= 3):
            continue
        pagina = i + 1
        try:
            if 'NUMBER OF REQUIREMENTS' in plano:
                nombre, cat, oblig, resumen, _ = parse_adventure(pg)
                aid = slug(nombre)
                adventures[aid] = dict(id=aid, name={'es':'','en':nombre},
                    category={'es':'','en':cat}, required=oblig,
                    requirementCount=0, summary={'es':'','en':resumen})
                cards.append(dict(id=f'{deck_id}-adv-{aid}', skin='adventure',
                    adventureId=aid, links=[], _page=pagina))
            elif re.search(r'REQUIREMENT \d+ OF \d+', plano):
                a = parse_actividad(pg)
                aid = slug(a['adventure'])
                cards.append(dict(
                    id=f"{deck_id}-a-{aid}-{a['idx']:02d}-{len([c for c in cards if c.get('_aid')==aid and c.get('_idx')==a['idx']])+1}",
                    skin='activity', adventureId=aid,
                    requirement={'index':a['idx'],'of':a['tot'],
                                 'text':{'es':'','en':a['requisito']}},
                    title={'es':'','en':a['titulo']}, originalTitle=a['titulo'],
                    place=a['lugar'],
                    meters={'energy':a['medidas'][0],'prep':a['medidas'][1],'duration':a['medidas'][2]},
                    description={'es':'','en':a['desc']}, links=[],
                    _page=pagina, _aid=aid, _idx=a['idx']))
                if aid in adventures:
                    adventures[aid]['requirementCount'] = max(adventures[aid]['requirementCount'], a['tot'])
            elif pagina < 12:
                titulo = ls[0]
                cuerpo = ' '.join(l for l in ls[1:] if '©' not in l)
                cards.append(dict(id=f'{deck_id}-r-{slug(titulo)}', skin='resource',
                    title={'es':'','en':titulo}, body={'es':'','en':cuerpo},
                    steps={'es':[], 'en':[]}, links=[], _page=pagina))
        except Exception as e:
            print(f'  p{pagina}: {type(e).__name__} {e}', file=sys.stderr)

    print(f'{len(adventures)} Adventures · {len(cards)} tarjetas')
    return adventures, cards

if __name__ == '__main__':
    a, c = main(sys.argv[1], Path(sys.argv[2]))
    Path('/tmp/parsed.json').write_text(json.dumps({'adventures':a,'cards':c}, ensure_ascii=False, indent=1))
    print('→ /tmp/parsed.json')
