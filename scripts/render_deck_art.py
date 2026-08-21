#!/usr/bin/env python3
"""
render_deck_art.py — saca del estuche las piezas sueltas que arman el banner
de la interna: el lockup (wordmark del rank + mascota) y los dos colores de
la trama.

Por qué no alcanza con la foto del estuche: el estuche entero es un objeto
pequeño en la página. El pedido de la reunión (20-ago) es que la interna se
sienta del rank —fondo del color de la caja, los círculos de la trama, el
nombre grande y la mascota—, y para eso hacen falta las piezas separadas del
fondo, no una foto.

Truco central: **la mascota es line-art, su interior ES el fondo rojo.** Por
eso el recorte no la contornea: vuelve transparente el rojo (el plano y el del
punto de trama) y deja la tinta. Al montarla sobre el banner del mismo color,
la mascota se reconstruye sola, con su relleno y todo.

    python3 scripts/render_deck_art.py wolf "<...>/WOLF card deck carton_print ready.pdf"

Escribe `site/assets/img/decks/<id>-lockup.webp` y el campo `art` del JSON de
la baraja. Requiere poppler (pdftoppm) y Pillow.
"""
import json, subprocess, sys, tempfile
from collections import Counter
from pathlib import Path
from PIL import Image

from render_cartons import cara_frontal, DPI

RAIZ = Path(__file__).resolve().parent.parent
MAX_W = 760

# Recortes en fracción del panel, no en píxeles: los seis estuches salen del
# mismo troquel (mismas guías de corte, verificado), así que lo que sirve para
# Wolf sirve para los demás aunque el arte cambie de tamaño de render.
TOP = 0.334        # debajo de "DEN MEETING DECK / FOR LEADERS"
BOTTOM = 0.977     # encima del pie "Scouting America"
# La franja amarilla de "Plan a den meeting in minutes!" y su cronómetro viven
# siempre en la esquina inferior izquierda. No se puede quitar por color (el
# texto es negro y la mascota también tiene tinta oscura): se borra por zona.
FRANJA_X = 0.415
FRANJA_Y = 0.497

TOL = 34           # tolerancia del destinte, medida contra los seis estuches

# La mascota sola, sin el wordmark. Desde que el nombre del rank volvió a ser
# texto (pedido 20-ago, 3.ª pasada), el wordmark impreso al lado del titular
# decía lo mismo dos veces con dos tipografías. La caja es fracción del panel:
# mismo troquel en los seis, la mascota siempre cae abajo a la derecha.
MASCOTA = (0.39, 0.42, 1.0, 0.97)

# El wordmark se saca por COLOR, no bajando el recorte: su base cae entre el
# 43 % y el 63 % del panel según el rank (Webelos arriba, Arrow of Light abajo
# porque va en dos líneas), y cortar por debajo de la más baja le comía las
# orejas a la mascota, que se monta sobre las letras. Es blanco puro en los
# seis; lo único blanco de las mascotas es una astilla del pañuelo, que se
# pierde sin que se note.
BLANCO = 210

# Texto sobre el plano del rank. No se elige a ojo: se mide (WCAG 2.2) y se
# usa el que pasa AA. Wolf y Webelos piden blanco; Lion y Bear, tinta oscura.
TINTA_CLARA = '#FFFFFF'
TINTA_OSCURA = '#232528'      # --sa-dark-gray, la misma del cuerpo de texto


def luminancia(rgb):
    f = lambda c: (c / 255 / 12.92 if c / 255 <= 0.03928
                   else ((c / 255 + 0.055) / 1.055) ** 2.4)
    r, g, b = rgb[:3]
    return 0.2126 * f(r) + 0.7152 * f(g) + 0.0722 * f(b)


def contraste(a, b):
    la, lb = luminancia(a), luminancia(b)
    hi, lo = max(la, lb), min(la, lb)
    return (hi + 0.05) / (lo + 0.05)


def tinta_texto(bg):
    """Blanco o gris oscuro, el que más contraste dé sobre el plano."""
    blanco = contraste(bg, (255, 255, 255))
    oscuro = contraste(bg, (35, 37, 40))
    return (TINTA_CLARA, blanco) if blanco >= oscuro else (TINTA_OSCURA, oscuro)


def near(p, c, t=TOL):
    return all(abs(a - b) <= t for a, b in zip(p[:3], c))


def es_tinte(p, bg):
    """¿Es un píxel del borde antialiaseado entre el wordmark blanco y el plano?

    Quitar solo el blanco puro dejaba un fantasma de las letras flotando sobre
    el banner: el borde de cada letra es una mezcla de blanco y plano, y no es
    ninguno de los dos. Un píxel de esa mezcla cumple `p = bg + t·(blanco−bg)`
    con el MISMO t en los tres canales; la tinta del dibujo, no."""
    ts = []
    for canal, base in zip(p[:3], bg[:3]):
        margen = 255 - base
        if margen < 8:            # canal ya saturado: no dice nada del mezclado
            continue
        ts.append((canal - base) / margen)
    if not ts or min(ts) < 0.12:  # 0 = es el plano, y de eso ya se ocupa near()
        return False
    return max(ts) - min(ts) < 0.18


def tintas(panel):
    """Los dos colores de la trama: el plano del rank y el punto del halftone.

    El plano es el color más repetido del panel. El punto es una SOMBRA del
    plano: mismo tono, 45–85 % del brillo. Ese test de tono es el que importa
    —"el más oscuro y frecuente" elegía la tinta de la mascota (#45081B en
    Wolf), que después el destinte borraba junto con el fondo y dejaba el
    banner sin lobo."""
    cuenta = Counter(panel.getdata())
    bg = cuenta.most_common(1)[0][0]
    sombras = []
    for c, n in cuenta.items():
        if c == bg or n < 500:
            continue
        razon = [a / max(b, 1) for a, b in zip(c, bg)]
        # Escalar parejo en los tres canales = misma tinta, más clara o más
        # oscura. La tinta del dibujo cambia de tono, no solo de brillo.
        if 0.45 <= sum(c) / sum(bg) <= 0.85 and max(razon) - min(razon) < 0.15:
            sombras.append((n, c))
    dot = max(sombras)[1] if sombras else bg
    return bg, dot


def hexa(c):
    return '#%02X%02X%02X' % c[:3]


def lockup(panel, bg, dot, caja=None):
    """La mascota (o el trozo que se pida) sobre transparente."""
    w, h = panel.size
    if caja:
        x0, y0, x1, y1 = caja
        recorte = panel.crop((round(w * x0), round(h * y0),
                              round(w * x1), round(h * y1)))
    else:
        recorte = panel.crop((0, round(h * TOP), w, round(h * BOTTOM)))
    rw, rh = recorte.size
    # La franja amarilla del "Plan a den meeting in minutes!" se borra por zona.
    # En el recorte completo ocupa el cuarto inferior izquierdo. En el de la
    # mascota apenas asoma la PUNTA —dos píxeles contra el borde izquierdo, en
    # Wolf una astilla amarilla bien visible sobre el rojo—, así que ahí la
    # zona es una tira fina: borrarla entera se comía media cara.
    fx, fy = ((round(rw * 0.012), round(rh * 0.40)) if caja
              else (round(rw * FRANJA_X), round(rh * FRANJA_Y)))

    out = Image.new('RGBA', (rw, rh))
    sp, dp = recorte.load(), out.load()
    for y in range(rh):
        for x in range(rw):
            p = sp[x, y]
            blanco = caja and (min(p[:3]) >= BLANCO or es_tinte(p, bg))
            if near(p, bg) or near(p, dot) or blanco or (x < fx and y > fy):
                dp[x, y] = (0, 0, 0, 0)
            else:
                dp[x, y] = p + (255,)

    # Al destintar queda un margen transparente grande donde estaba el fondo
    # (sobre todo abajo, de la franja borrada). Sin recortarlo, el banner
    # reservaba alto para aire vacío y la mascota quedaba chica.
    #
    # getbbox() no sirve acá: el borde de cada punto de trama deja motas
    # antialiaseadas que sobreviven al destinte, así que la caja daba la imagen
    # entera. Se recorta por COBERTURA — una fila con cuatro motas no es
    # contenido, una con el trazo del lobo sí.
    return out.crop(margen(out))


def margen(im, minimo=0.004):
    w, h = im.size
    a = im.split()[3].load()
    filas = [sum(1 for x in range(0, w, 2) if a[x, y] > 128) for y in range(h)]
    cols = [sum(1 for y in range(0, h, 2) if a[x, y] > 128) for x in range(w)]
    vivas = [i for i, v in enumerate(filas) if v > (w / 2) * minimo]
    vivos = [i for i, v in enumerate(cols) if v > (h / 2) * minimo]
    if not vivas or not vivos:
        return (0, 0, w, h)
    return (vivos[0], vivas[0], vivos[-1] + 1, vivas[-1] + 1)


def main(deck_id, pdf):
    destino = RAIZ / 'site/assets/img/decks'
    destino.mkdir(parents=True, exist_ok=True)

    with tempfile.TemporaryDirectory() as tmp:
        subprocess.run(['pdftoppm', '-png', '-r', str(DPI), '-f', '1', '-l', '1',
                        str(pdf), str(Path(tmp) / 'c')], check=True)
        hoja = Image.open(next(Path(tmp).glob('c*.png'))).convert('RGB')
        panel = hoja.crop(cara_frontal(hoja))

    bg, dot = tintas(panel)
    art = lockup(panel, bg, dot, MASCOTA)
    if art.width > MAX_W:
        art = art.resize((MAX_W, round(art.height * MAX_W / art.width)), Image.LANCZOS)

    salida = destino / f'{deck_id}-mascota.webp'
    art.save(salida, 'WEBP', quality=88, method=6)

    on, ratio = tinta_texto(bg)

    ruta = RAIZ / 'site/assets/data/decks' / f'{deck_id}.json'
    if ruta.exists():
        deck = json.loads(ruta.read_text())
        deck['art'] = {
            'mascota': {'src': f'/site/assets/img/decks/{salida.name}',
                        'width': art.width, 'height': art.height},
            'ink': hexa(bg),
            'dot': hexa(dot),
            'on': on,
            'onRatio': round(ratio, 2),
        }
        ruta.write_text(json.dumps(deck, ensure_ascii=False, indent=1) + '\n')

    aviso = '' if ratio >= 4.5 else '  ¡OJO: no llega a AA!'
    print(f'{salida.relative_to(RAIZ)}  {art.width}×{art.height}  '
          f'({salida.stat().st_size / 1024:.0f} KB)  ink {hexa(bg)} · dot {hexa(dot)} · '
          f'texto {on} ({ratio:.2f}:1){aviso}')


if __name__ == '__main__':
    if len(sys.argv) != 3:
        sys.exit(__doc__)
    main(sys.argv[1], sys.argv[2])
