#!/usr/bin/env python3
"""
extract_cards.py — convierte el PDF de un mazo en las imágenes de tarjeta que
muestra la app.

El brief del cliente es explícito (§12): «las tarjetas son contenido oficial del
programa y deben preservar su apariencia, terminología y marcas registradas».
Así que la app muestra **la tarjeta real**, no una recreación. El JSON sigue
existiendo al lado como metadata: es lo que permite buscar, filtrar, ordenar por
Adventure y darle texto a un lector de pantalla.

Cada página del PDF es una tarjeta. El emparejamiento página → tarjeta se hace
por texto: se lee el texto de cada página y se busca el título de la actividad.
Nada de números de página a mano.

Uso:
    python3 scripts/extract_cards.py bear reference/decks/bear-es.pdf
    python3 scripts/extract_cards.py bear reference/decks/bear-es.pdf --lang es
    python3 scripts/extract_cards.py bear ... --all      # exporta todas las páginas

Requiere: poppler (pdftoppm, pdftotext) y Pillow.
"""

from __future__ import annotations

import json
import re
import subprocess
import sys
import tempfile
import unicodedata
from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parent.parent
IMG_ROOT = ROOT / "site" / "assets" / "img" / "cards"
DPI = 150
MAX_W = 1000          # suficiente para un celular a 2x sin castigar los datos
QUALITY = 82


def norm(s: str) -> str:
    """Normaliza para comparar: sin tildes, sin puntuación, en minúsculas."""
    s = unicodedata.normalize("NFD", s)
    s = "".join(c for c in s if unicodedata.category(c) != "Mn")
    return re.sub(r"[^a-z0-9 ]+", " ", s.lower()).strip()


def page_texts(pdf: Path) -> list[str]:
    out = subprocess.run(
        ["pdftotext", "-layout", str(pdf), "-"],
        capture_output=True, text=True, check=True,
    ).stdout
    # pdftotext separa páginas con form feed
    return out.split("\f")


def render_page(pdf: Path, page: int, out_dir: Path) -> Path | None:
    """Renderiza UNA página. Los mazos completos pesan cientos de MB: rasterizar
    las 118 páginas para usar 10 sería tirar minutos a la basura."""
    prefix = out_dir / f"p{page}"
    subprocess.run(
        ["pdftoppm", "-png", "-r", str(DPI), "-f", str(page), "-l", str(page),
         str(pdf), str(prefix)],
        check=True,
    )
    hits = sorted(out_dir.glob(f"p{page}-*.png"))
    return hits[0] if hits else None


def trim_to_card(im: Image.Image, coverage: float = 0.30) -> Image.Image:
    """
    Recorta la página a la tarjeta.

    Las páginas del PDF de imprenta traen marcas de corte y sangrado: líneas
    finas en los bordes, sobre papel blanco. Un bounding box de "lo que no es
    blanco" las incluiría. Por eso medimos **cobertura**: nos quedamos con las
    filas y columnas donde al menos un 30% de los píxeles tiene tinta. Una marca
    de corte es una línea de un píxel y no llega; el borde de la tarjeta sí.
    """
    g = im.convert("L")
    w, h = g.size
    px = g.load()
    step = 2

    rows = [
        sum(1 for x in range(0, w, step) if px[x, y] < 245) / (w / step)
        for y in range(h)
    ]
    cols = [
        sum(1 for y in range(0, h, step) if px[x, y] < 245) / (h / step)
        for x in range(w)
    ]

    def span(series):
        hits = [i for i, v in enumerate(series) if v >= coverage]
        return (hits[0], hits[-1]) if hits else None

    ys, xs = span(rows), span(cols)
    if not ys or not xs:
        return im
    return im.crop((xs[0], ys[0], xs[1] + 1, ys[1] + 1))


def to_webp(src: Path, dst: Path) -> tuple[int, int]:
    im = Image.open(src).convert("RGB")
    im = trim_to_card(im)
    if im.width > MAX_W:
        im = im.resize((MAX_W, round(im.height * MAX_W / im.width)), Image.LANCZOS)
    dst.parent.mkdir(parents=True, exist_ok=True)
    im.save(dst, "WEBP", quality=QUALITY, method=6)
    return im.size


def match_pages(deck: dict, texts: list[str], lang: str) -> dict[str, int]:
    """Devuelve {cardId: índice de página}. Empareja por título y, si hace falta,
    por el título original en inglés."""
    found: dict[str, int] = {}
    normalized = [norm(t) for t in texts]

    for card in deck["cards"]:
        candidates = []
        title = (card.get("title") or {}).get(lang) or (card.get("title") or {}).get("es")
        if title:
            candidates.append(title)
        if card.get("originalTitle"):
            candidates.append(card["originalTitle"])
        if card.get("skin") == "adventure":
            adv = next((a for a in deck["adventures"] if a["id"] == card["adventureId"]), None)
            if adv:
                candidates.append(adv["name"].get(lang) or adv["name"]["es"])

        for cand in candidates:
            needle = norm(cand)
            if len(needle) < 4:
                continue
            hits = [i for i, t in enumerate(normalized) if needle in t]
            if len(hits) == 1:
                found[card["id"]] = hits[0]
                break
            if len(hits) > 1:
                # varias páginas contienen el título (la actividad y su Adventure):
                # nos quedamos con la primera que además tenga el marcador de requisito
                req = card.get("requirement")
                if req:
                    marker = norm(f"requisito {req['index']} de {req['of']}")
                    strict = [i for i in hits if marker in normalized[i]]
                    if len(strict) == 1:
                        found[card["id"]] = strict[0]
                        break
                found[card["id"]] = hits[0]
                break
    return found


def main(argv: list[str]) -> int:
    if len(argv) < 3:
        print(__doc__)
        return 1

    deck_id, pdf_path = argv[1], Path(argv[2])
    lang = "es"
    if "--lang" in argv:
        lang = argv[argv.index("--lang") + 1]
    export_all = "--all" in argv

    if not pdf_path.exists():
        print(f"No existe {pdf_path}", file=sys.stderr)
        return 1

    deck_file = ROOT / "site" / "assets" / "data" / "decks" / f"{deck_id}.json"
    deck = json.loads(deck_file.read_text())

    texts = page_texts(pdf_path)
    print(f"{len(texts)} páginas en el PDF")

    matches = match_pages(deck, texts, lang)
    missing = [c["id"] for c in deck["cards"] if c["id"] not in matches]

    with tempfile.TemporaryDirectory() as tmp:
        wanted = range(len(texts)) if export_all else sorted(set(matches.values()))
        sizes: dict[int, tuple[int, int]] = {}
        for idx in wanted:
            src = render_page(pdf_path, idx + 1, Path(tmp))
            if src is None:
                print(f"  no se pudo rasterizar la página {idx + 1}", file=sys.stderr)
                continue
            dst = IMG_ROOT / deck_id / f"p{idx + 1:03d}.webp"
            sizes[idx] = to_webp(src, dst)
            src.unlink()

    for card in deck["cards"]:
        idx = matches.get(card["id"])
        if idx is None:
            card.pop("image", None)
            continue
        w, h = sizes.get(idx, (MAX_W, 0))
        card["image"] = {
            "src": f"/site/assets/img/cards/{deck_id}/p{idx + 1:03d}.webp",
            "width": w,
            "height": h,
            "page": idx + 1,
            "lang": lang,
        }

    deck_file.write_text(json.dumps(deck, ensure_ascii=False, indent=2) + "\n")

    print(f"{len(matches)} tarjetas emparejadas · {len(sizes)} imágenes exportadas")
    if missing:
        print("Sin emparejar (quedan sin imagen, se muestran con la plantilla):")
        for m in missing:
            print(f"  - {m}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main(sys.argv))
