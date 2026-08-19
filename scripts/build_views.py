#!/usr/bin/env python3
"""
build_views.py — porta el sitio local (site/) a las vistas Blade de ai.backbone.

El desarrollo ocurre en local: site/ corre con `python3 -m http.server` y no
depende de la plataforma. Este script traduce ese sitio a lo que la plataforma
espera, y aplica las tres correcciones que el preview exige:

1. `@algo` es una directiva de Blade. Un JSON-LD con "@context" revienta el
   preview con 500. Se escapa a `@@`.
2. Cada vista se sirve en /preview/{id}, no en su ruta. Un link a
   "/site/baraja.html" da 404 garantizado. Se reescribe a /preview/{id} usando
   preview_ids.json, que solo existe DESPUÉS de registrar las vistas.
   Por eso el build es de dos pasadas: la primera sube las vistas, se corre
   "Scan for new files", se llena preview_ids.json, y se vuelve a correr.
3. Los assets root-relative sí resuelven, pero cuelgan del slug del proyecto.

Uso:
    python3 scripts/build_views.py            # build normal
    python3 scripts/build_views.py --check    # no escribe, solo reporta
"""

from __future__ import annotations

import hashlib
import json
import re
import shutil
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SITE = ROOT / "site"
VIEWS = ROOT / "production" / "views"
MIRROR = ROOT / "assets"
PREVIEW_MAP = ROOT / "scripts" / "preview_ids.json"

PROJECT_SLUG = "scouting-america"
ASSET_BASE = f"/projects/{PROJECT_SLUG}/assets"

# Directivas de Blade que pueden aparecer en texto o en JSON-LD y hay que escapar.
BLADE_DIRECTIVE = re.compile(r"@(?=[a-zA-Z])")


def load_preview_ids() -> dict[str, int]:
    if PREVIEW_MAP.exists():
        return json.loads(PREVIEW_MAP.read_text())
    return {}


def escape_blade(html: str) -> str:
    """Escapa los `@` para que Blade no los interprete como directivas."""
    return BLADE_DIRECTIVE.sub("@@", html)


def rewrite_assets(html: str) -> str:
    return html.replace("/site/assets", ASSET_BASE)


def rewrite_links(html: str, preview_ids: dict[str, int]) -> tuple[str, list[str]]:
    """
    Reescribe los links internos a /preview/{id}. Devuelve el HTML y la lista de
    destinos que todavía no tienen id — esos quedan como están y darán 404 hasta
    la segunda pasada.
    """
    missing: list[str] = []

    def repl(match: re.Match) -> str:
        page, query = match.group(1), match.group(2) or ""
        pid = preview_ids.get(page)
        if pid is None:
            missing.append(page)
            return match.group(0)
        return f"/preview/{pid}{query}"

    html = re.sub(r"/site/([\w./-]+\.html)(\?[^\"'\s]*)?", repl, html)
    return html, missing


def sellar_sw(check: bool = False) -> str:
    """Sella el service worker con un hash del contenido de los assets.

    Con la versión escrita a mano, el SW seguía sirviendo el CSS y el JS
    viejos después de cada despliegue y el cambio "no aplicaba". Derivarla
    del contenido hace que cada cambio real invalide el caché, y que un
    build sin cambios no lo invalide.
    """
    h = hashlib.sha256()
    for f in sorted((SITE / "assets").rglob("*")):
        if f.is_file():
            h.update(f.name.encode()); h.update(f.read_bytes())
    # El propio SW entra al hash sin su línea de versión: si no, cambiarle una
    # regla de caché no invalidaba nada y el navegador seguía con el SW viejo.
    sw_txt = (SITE / "sw.js").read_text()
    h.update(re.sub(r"const VERSION = '[^']*';", "", sw_txt).encode())
    version = "v" + h.hexdigest()[:8]

    sw = SITE / "sw.js"
    txt = sw.read_text()
    nuevo = re.sub(r"const VERSION = '[^']*';", f"const VERSION = '{version}';", txt, count=1)
    if nuevo != txt and not check:
        sw.write_text(nuevo)
    return version


def sellar_assets(version: str, check: bool = False) -> None:
    """Estampa ?v=<version> en cada CSS y JS que referencian las páginas.

    Sin esto el navegador sirve su copia vieja de api.js y card.js después de
    cada cambio —el servidor local no manda Cache-Control— y el arreglo
    "no aplica" aunque el archivo en disco ya esté corregido.
    """
    patron = re.compile(r'(/site/assets/(?:css|js)/[\w.-]+\.(?:css|js))(\?v=[\w]+)?')
    for page in sorted(SITE.rglob("*.html")):
        txt = page.read_text()
        nuevo = patron.sub(lambda m: f"{m.group(1)}?v={version}", txt)
        if nuevo != txt and not check:
            page.write_text(nuevo)


def build(check: bool = False) -> int:
    preview_ids = load_preview_ids()
    version = sellar_sw(check)
    sellar_assets(version, check)
    pages = sorted(SITE.rglob("*.html"))
    if not pages:
        print("No hay páginas en site/", file=sys.stderr)
        return 1

    all_missing: set[str] = set()

    for page in pages:
        rel = page.relative_to(SITE)
        html = page.read_text()
        html = rewrite_assets(html)
        html, missing = rewrite_links(html, preview_ids)
        all_missing.update(missing)
        html = escape_blade(html)

        target = VIEWS / rel.with_suffix(".blade.php")
        if not check:
            target.parent.mkdir(parents=True, exist_ok=True)
            target.write_text(html)
        print(f"  {rel} → {target.relative_to(ROOT)}")

    # La plataforma publica una copia de los assets en la raíz: van siempre en par.
    if not check:
        for dest in (VIEWS / "assets", MIRROR):
            if dest.exists():
                shutil.rmtree(dest)
            shutil.copytree(SITE / "assets", dest)
            # api.js lleva la ruta de los JSON escrita adentro; el reemplazo
            # que se le hace al HTML tiene que alcanzarla también.
            for js in dest.rglob("*.js"):
                txt = js.read_text()
                if "/site/assets" in txt:
                    js.write_text(txt.replace("/site/assets", ASSET_BASE))
        print(f"  assets → production/views/assets y assets/")
    print(f"  service worker sellado: {version}")

    if all_missing:
        print("\nSin preview id todavía (quedan como link local, 404 en el preview):")
        for m in sorted(all_missing):
            print(f"  - {m}")
        print(
            "\nPrimera pasada: subí las vistas, corré 'Scan for new files',\n"
            "llená scripts/preview_ids.json con {\"baraja.html\": 123, ...} y volvé a correr."
        )

    return 0


if __name__ == "__main__":
    raise SystemExit(build(check="--check" in sys.argv))
