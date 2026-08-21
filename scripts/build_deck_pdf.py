#!/usr/bin/env python3
"""
build_deck_pdf.py — arma el PDF completo de una baraja para que el líder la vea
de un tirón, sin recorrer 114 tarjetas de a una.

NO usa el PDF de imprenta: esos pesan entre 43 MB y 477 MB, viven fuera del
repo (`reference/` está en .gitignore) y son inservibles en el teléfono de un
líder con datos. Usa las láminas WebP que `render_cards.py` ya dejó en
`site/assets/img/cards/<deck>/`, que son las mismas caras impresas a un tamaño
que se lee en pantalla. Sale un PDF de ~3 MB.

    python3 scripts/build_deck_pdf.py lion
    python3 scripts/build_deck_pdf.py --todas

Una página por lámina, del tamaño exacto de la imagen: el PDF es un visor de
las tarjetas, no un documento maquetado. Requiere Pillow.
"""
import json, sys
from pathlib import Path
from PIL import Image

RAIZ = Path(__file__).resolve().parent.parent
CARDS = RAIZ / 'site/assets/img/cards'
DECKS = RAIZ / 'site/assets/data/decks'
DESTINO = RAIZ / 'site/assets/pdf'
# Las láminas están a ~900 px de ancho. A 150 dpi eso es una página de 6 in,
# parecida a la tarjeta impresa; suficiente para leer un requisito en pantalla.
DPI = 150


def build(deck_id):
    origen = CARDS / deck_id
    laminas = sorted(origen.glob('*.webp'))
    if not laminas:
        print(f'· {deck_id}: sin láminas todavía, se omite')
        return None

    # Las páginas se abren de a una y se convierten a RGB: un WebP con alfa no
    # entra en un PDF, y tener las 114 en memoria a la vez ahogaba el proceso.
    paginas = [Image.open(f).convert('RGB') for f in laminas]
    DESTINO.mkdir(parents=True, exist_ok=True)
    salida = DESTINO / f'{deck_id}-en.pdf'
    paginas[0].save(salida, 'PDF', save_all=True, append_images=paginas[1:],
                    resolution=DPI)
    for p in paginas:
        p.close()

    # Igual que el estuche: el JSON declara el PDF solo si el PDF existe, y lo
    # declara el script que lo genera. La vista no adivina rutas ni pesos —
    # el peso se muestra al líder antes de que toque, que puede estar con datos.
    ruta = DECKS / f'{deck_id}.json'
    if ruta.exists():
        deck = json.loads(ruta.read_text())
        deck['pdf'] = {'src': f'/site/assets/pdf/{salida.name}',
                       'pages': len(laminas),
                       'bytes': salida.stat().st_size,
                       'lang': 'en'}
        ruta.write_text(json.dumps(deck, ensure_ascii=False, indent=1) + '\n')

    print(f'{salida.relative_to(RAIZ)}  {len(laminas)} páginas  '
          f'({salida.stat().st_size / 1e6:.1f} MB)')
    return salida


def main(args):
    if args == ['--todas']:
        for d in sorted(p.name for p in CARDS.iterdir() if p.is_dir()):
            build(d)
    elif len(args) == 1:
        build(args[0])
    else:
        sys.exit(__doc__)


if __name__ == '__main__':
    main(sys.argv[1:])
