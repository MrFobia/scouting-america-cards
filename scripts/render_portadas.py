#!/usr/bin/env python3
"""
render_portadas.py — la portada del estuche, tal como la mandó el cliente,
lista para ir de fondo en la interna de la baraja.

Decisión de la revisión del 20-ago: el fondo de la interna es **el arte del
cliente**, no piezas recompuestas ni tramas dibujadas por nosotros. Se usa la
cara frontal del troquel entera —trama, "DEN MEETING DECK / FOR LEADERS",
wordmark del rank y mascota— quitando solo las dos franjas amarillas que el
cliente pidió sacar:

  · arriba  "Fun, Simple, Easy · planning resource for adult leaders."
  · abajo   "Plan a den meeting in minutes!" + el cronómetro

La de arriba se va recortando por debajo; la de abajo se **rellena clonando la
franja de arte que tiene justo encima**, que es fondo limpio con la misma
densidad de trama. Rellenar con color plano dejaba un rectángulo liso obvio
contra el degradado de puntos, y redibujar la trama habría sido inventar arte.

    python3 scripts/render_portadas.py lion "<...>/LION card deck carton_print ready.pdf"

Escribe `site/assets/img/decks/<id>-portada.webp` y `deck.art.portada`.
Requiere poppler (pdftoppm) y Pillow.
"""
import json, subprocess, sys, tempfile
from pathlib import Path
from PIL import Image

from render_cartons import cara_frontal, DPI
from render_deck_art import tintas, tinta_texto, hexa, near

RAIZ = Path(__file__).resolve().parent.parent
MAX_W = 820

# Geometría del troquel, en fracción del panel. Se midió detectando el amarillo
# de las franjas y da idéntica en cinco de las seis barajas. Lion no se puede
# medir así —su plano ES dorado, el test lo toma todo— pero el troquel es el
# mismo (las guías de corte caen en el mismo píxel en las seis), así que la
# geometría vale para todas.
# La franja de arriba es diagonal: baja más contra el borde izquierdo. Medida
# siguiendo el amarillo desde el borde superior (no por cobertura, que daba
# 0.169 y dejaba la punta asomando en la esquina) y sin confundirla con el
# "FOR LEADERS", que es amarillo pero no toca el borde y sí se conserva.
FRANJA_SUP = 0.205     # borde inferior de la franja de arriba, en su punto más bajo
FRANJA_INF = 0.698     # borde SUPERIOR de la franja de abajo, en su punto más alto
FRANJA_INF_X = 0.40    # hasta dónde llega hacia la derecha
PIE = 0.977            # encima del "Scouting America" impreso al pie
def es_fondo(p, bg):
    """¿El píxel es plano, punto de trama, o la mezcla de ambos?

    Comparar contra los dos colores exactos (`near(bg)` o `near(dot)`) no
    alcanzaba: en Bear y en Arrow of Light la trama tiene medios tonos y NINGUNA
    fila pasaba el filtro, así que esas dos barajas se quedaban con la franja
    amarilla puesta. Toda la familia de la trama es la misma tinta a distinta
    densidad, o sea escala pareja en los tres canales; el wordmark blanco, la
    mascota y el amarillo de la franja cambian de tono y quedan afuera."""
    razon = [a / max(b, 1) for a, b in zip(p[:3], bg[:3])]
    return (max(razon) - min(razon) < 0.18
            and 0.35 <= sum(p[:3]) / max(sum(bg[:3]), 1) <= 1.15)


def banda_limpia(panel, x1, tope, bg, dot):
    """La franja de filas más alta, dentro de x<x1 y por encima de `tope`, que
    sea SOLO fondo y trama — sin letras, sin mascota, sin franja amarilla.

    Se busca en vez de fijarla a ojo. Fijada a ojo se elegía la banda de justo
    encima de la franja, que tiene la pata de la última letra del wordmark: al
    repetirla en mosaico bajaba una escalera de cuñas blancas."""
    px = panel.load()
    limpias = []
    for y in range(tope):
        n = ok = 0
        for x in range(0, x1, 3):
            p = px[x, y]
            n += 1
            if es_fondo(p, bg):
                ok += 1
        limpias.append(ok >= n * 0.97)

    mejor = (0, 0)
    ini = None
    for y, val in enumerate(limpias + [False]):
        if val and ini is None:
            ini = y
        elif not val and ini is not None:
            if y - ini > mejor[1] - mejor[0]:
                mejor = (ini, y)
            ini = None
    return mejor


def sin_franja_inferior(panel, bg, dot):
    """Tapa la franja de abajo repitiendo una banda de fondo limpio del arte.

    Dos intentos fallidos antes de esto, los dos por la misma razón —elegir la
    fuente por posición en vez de por contenido:
      1. Clonar una banda tan alta como la franja traía el wordmark y, peor, el
         pedazo diagonal de la propia franja (arranca más arriba contra el borde
         izquierdo): el "Plan a den meeting" salía dos veces.
      2. Clonar la banda inmediatamente superior traía la pata de la última
         letra, que en mosaico bajaba como una escalera de cuñas blancas."""
    w, h = panel.size
    x1 = round(w * FRANJA_INF_X)
    y0, y1 = round(h * FRANJA_INF), round(h * PIE)
    sy0, sy1 = banda_limpia(panel, x1, y0, bg, dot)
    alto = sy1 - sy0
    if alto < 4:
        # Bear y Arrow of Light no tienen ninguna banda que sea fondo puro a
        # esa altura y a esa izquierda. Antes que dejarles la franja amarilla
        # puesta, se tapa con el plano del rank: pierde la trama en ese rincón,
        # pero es el color del propio arte y no una invención.
        panel.paste(bg[:3], (0, y0, x1, y1))
        return panel
    parche = panel.crop((0, sy0, x1, sy1))
    y = y0
    while y < y1:
        panel.paste(parche, (0, min(y, y1 - alto)))
        y += alto
    return panel


def main(deck_id, pdf):
    destino = RAIZ / 'site/assets/img/decks'
    destino.mkdir(parents=True, exist_ok=True)

    with tempfile.TemporaryDirectory() as tmp:
        subprocess.run(['pdftoppm', '-png', '-r', str(DPI), '-f', '1', '-l', '1',
                        str(pdf), str(Path(tmp) / 'c')], check=True)
        hoja = Image.open(next(Path(tmp).glob('c*.png'))).convert('RGB')
        panel = hoja.crop(cara_frontal(hoja))

    bg, dot = tintas(panel)
    panel = sin_franja_inferior(panel, bg, dot)

    w, h = panel.size
    portada = panel.crop((0, round(h * FRANJA_SUP), w, round(h * PIE)))
    if portada.width > MAX_W:
        portada = portada.resize(
            (MAX_W, round(portada.height * MAX_W / portada.width)), Image.LANCZOS)

    salida = destino / f'{deck_id}-portada.webp'
    portada.save(salida, 'WEBP', quality=86, method=6)

    on, ratio = tinta_texto(bg)
    art = {'portada': {'src': f'/site/assets/img/decks/{salida.name}',
                       'width': portada.width, 'height': portada.height},
           'ink': hexa(bg), 'dot': hexa(dot),
           'on': on, 'onRatio': round(ratio, 2)}

    ruta = RAIZ / 'site/assets/data/decks' / f'{deck_id}.json'
    if ruta.exists():
        deck = json.loads(ruta.read_text())
        deck['art'] = {**deck.get('art', {}), **art}
        ruta.write_text(json.dumps(deck, ensure_ascii=False, indent=1) + '\n')

    # También en el índice: el listado de barajas pinta la portada como ficha y
    # lee SOLO _index.json — sin esto tendría que abrir los siete mazos enteros
    # (100 KB cada uno) para dibujar una grilla.
    idx = RAIZ / 'site/assets/data/decks/_index.json'
    if idx.exists():
        datos = json.loads(idx.read_text())
        for d in datos['decks']:
            if d['id'] == deck_id:
                d['art'] = art
        idx.write_text(json.dumps(datos, ensure_ascii=False, indent=1) + '\n')

    print(f'{salida.relative_to(RAIZ)}  {portada.width}×{portada.height}  '
          f'({salida.stat().st_size / 1024:.0f} KB)  ink {hexa(bg)} · texto {on} ({ratio:.2f}:1)')


if __name__ == '__main__':
    if len(sys.argv) != 3:
        sys.exit(__doc__)
    main(sys.argv[1], sys.argv[2])
