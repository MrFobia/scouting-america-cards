#!/usr/bin/env python3
"""
render_cartons.py — recorta la cara frontal del estuche impreso de cada baraja.

Los PDFs `<RANK> card deck carton_print ready.pdf` son el troquel plano de la
caja: cinco paneles, solapas y marcas de corte. Lo que sirve en pantalla es UN
panel — la cara frontal, la que tiene el wordmark del rank, la mascota y el
fondo de puntos. Es arte oficial del programa, así que se recorta, no se
redibuja (design.md § Prohibido: "Las láminas y fotos oficiales son la única
imagen").

El recorte NO está hardcodeado por baraja: el troquel trae las guías de corte
en rojo puro, y la cara frontal es el rectángulo que queda entre las dos guías
verticales de la izquierda y entre las dos horizontales más separadas. Medir
las guías aguanta que un rank venga con la caja armada distinta; una tabla de
coordenadas por baraja, no.

    python3 scripts/render_cartons.py lion "<ruta>/LION card deck carton_print ready.pdf"

Requiere poppler (pdftoppm) y Pillow — lo mismo que render_cards.py.
"""
import json, subprocess, sys, tempfile
from pathlib import Path
from PIL import Image

RAIZ = Path(__file__).resolve().parent.parent
DPI = 200          # el panel sale ~930 px de ancho: alcanza para una banda hero
MAX_W = 900        # y se baja a esto, que es el ancho útil real en un teléfono


# Rojo de registro del troquel, medido en los seis PDFs: exactamente este.
# La tolerancia es corta a propósito — el arte del rank Wolf es rojo (229,22,53)
# y con un umbral ancho ("r alto, g y b bajos") la caja entera contaba como guía.
GUIA_RGB = (237, 29, 36)
TOL = 6


def es_guia(p):
    return all(abs(c - g) <= TOL for c, g in zip(p[:3], GUIA_RGB))


def lineas(cobertura, minimo):
    """Índices con cobertura suficiente, colapsando cada línea a su centro:
    a 200 dpi una guía son 3 px, y sin esto cada una contaba como tres."""
    hit = [i for i, v in enumerate(cobertura) if v > minimo]
    out, run = [], []
    for i in hit:
        if run and i - run[-1] > 2:
            out.append(sum(run) // len(run))
            run = []
        run.append(i)
    if run:
        out.append(sum(run) // len(run))
    return out


def guias(im):
    """Columnas y filas que son una guía de corte, medidas por cobertura."""
    w, h = im.size
    px = im.load()
    cols, rows = [0] * w, [0] * h
    for y in range(h):
        for x in range(w):
            if es_guia(px[x, y]):
                cols[x] += 1
                rows[y] += 1
    # 10 % de cobertura: una guía cruza el troquel entero, una mancha de arte no.
    return lineas(cols, h * 0.10), lineas(rows, w * 0.10)


def cara_frontal(im):
    """Caja de la cara frontal, en píxeles."""
    cols, rows = guias(im)
    if len(cols) < 2 or len(rows) < 2:
        raise SystemExit('No se encontraron las guías de corte: ¿es un troquel?')
    # Vertical: el primer par de guías por la izquierda es la cara frontal.
    x0, x1 = cols[0], cols[1]
    # Horizontal: el cuerpo impreso es el hueco más alto entre guías seguidas;
    # los huecos chicos de arriba y abajo son las solapas de la caja.
    y0, y1 = max(zip(rows, rows[1:]), key=lambda p: p[1] - p[0])
    # Hacia adentro de la guía, para no dejar la línea roja en el recorte.
    return (x0 + 2, y0 + 2, x1 - 1, y1 - 1)


def main(deck_id, pdf):
    destino = RAIZ / 'site/assets/img/decks'
    destino.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory() as tmp:
        subprocess.run(['pdftoppm', '-png', '-r', str(DPI), '-f', '1', '-l', '1',
                        str(pdf), str(Path(tmp) / 'c')], check=True)
        src = next(Path(tmp).glob('c*.png'))
        im = Image.open(src).convert('RGB').crop(cara_frontal(Image.open(src).convert('RGB')))
    if im.width > MAX_W:
        im = im.resize((MAX_W, round(im.height * MAX_W / im.width)), Image.LANCZOS)
    salida = destino / f'{deck_id}-carton.webp'
    im.save(salida, 'WEBP', quality=84, method=6)

    # El JSON de la baraja se entera acá y no a mano: el campo existe cuando el
    # archivo existe. build_deck.py conserva las claves de cabecera, así que
    # esto sobrevive a la próxima reconstrucción del contenido.
    ruta = RAIZ / 'site/assets/data/decks' / f'{deck_id}.json'
    if ruta.exists():
        deck = json.loads(ruta.read_text())
        deck['carton'] = {'src': f'/site/assets/img/decks/{salida.name}',
                          'width': im.width, 'height': im.height}
        ruta.write_text(json.dumps(deck, ensure_ascii=False, indent=1) + '\n')

    print(f'{salida.relative_to(RAIZ)}  {im.width}×{im.height}  '
          f'({salida.stat().st_size / 1024:.0f} KB)')


if __name__ == '__main__':
    if len(sys.argv) != 3:
        sys.exit(__doc__)
    main(sys.argv[1], sys.argv[2])
