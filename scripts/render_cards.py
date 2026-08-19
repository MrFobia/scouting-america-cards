#!/usr/bin/env python3
"""
render_cards.py — exporta las láminas de un mazo como WebP, una por página impar.

Las páginas pares son el dorso (solo el logo) y no se exportan. Los PDFs vienen
listos para imprenta, así que traen marcas de corte alrededor de la tarjeta:
`trim_to_card` las recorta midiendo cobertura de tinta por fila y por columna.

    python3 scripts/render_cards.py bear reference/decks/bear-en.pdf

Requiere poppler (pdftoppm) y Pillow.
"""
import subprocess, sys, tempfile
from pathlib import Path
from PIL import Image
from extract_cards import trim_to_card, DPI, MAX_W

RAIZ = Path(__file__).resolve().parent.parent

def main(deck_id, pdf):
    destino = RAIZ / 'site/assets/img/cards' / deck_id
    destino.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory() as tmp:
        # Una sola pasada de pdftoppm para todo el PDF: página por página tarda
        # minutos en mazos de 200+ páginas.
        subprocess.run(['pdftoppm', '-png', '-r', str(DPI),
                        str(pdf), str(Path(tmp) / 'p')], check=True)
        paginas = sorted(Path(tmp).glob('p*.png'))
        n = 0
        for src in paginas:
            num = int(''.join(c for c in src.stem if c.isdigit()))
            if num % 2 == 0:      # dorso
                continue
            im = trim_to_card(Image.open(src).convert('RGB'))
            if im.width > MAX_W:
                im = im.resize((MAX_W, round(im.height * MAX_W / im.width)), Image.LANCZOS)
            im.save(destino / f'p{num:03d}.webp', 'WEBP', quality=82, method=6)
            n += 1
    peso = sum(f.stat().st_size for f in destino.glob('*.webp')) / 1e6
    print(f'{n} láminas → {destino.relative_to(RAIZ)}  ({peso:.1f} MB)')

if __name__ == '__main__':
    main(*sys.argv[1:])
