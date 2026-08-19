# Scouting America — Tarjetas Cub Scout

PWA bilingüe para líderes Cub Scout y papás hispanoparlantes. Los mazos de tarjetas de actividad, en español, compartibles por WhatsApp y con medición de aperturas.

**El desarrollo es local primero.** `site/` es una app estática que corre con cualquier servidor de archivos y no depende de ai.backbone. Cuando está lista, `scripts/build_views.py` la porta a las vistas Blade de la plataforma.

## Correr

```bash
python3 -m http.server 8080
```

| Pantalla | URL |
|---|---|
| **Home** | http://localhost:8080/site/index.html |
| Elegir baraja | http://localhost:8080/site/mazos.html |
| Una baraja + sorteo | http://localhost:8080/site/mazo.html?d=bear |
| Vista del papá (la que llega por WhatsApp) | http://localhost:8080/site/carta.html?c=bear-a-habitat-05&l=Marta%20Rivas&p=Pack%2077 |
| Guía de estilos | http://localhost:8080/site/guia-de-estilos.html |

## Estado

| Fase | Estado |
|---|---|
| F0 fundaciones — marca del manual 2024, logos extraídos, tokens, guía de estilos renderizada, **home** | ✅ |
| F1 sistema de tarjetas — los 5 skins + esquema JSON | ✅ con 10 cartas reales de Bear |
| F2 vista del papá | 🟡 carta y firma del líder listas; falta ping real y PWA |
| F3 PWA de mazos | 🟡 sorteo sin repetición y progreso listos; falta offline e instalación |
| F4 consola del líder | ⚪ |
| F5 tableros | ⚪ |
| F6 contenido (7 mazos) | ⚪ bloqueado: faltan LION y AOL en español |
| F7 handoff de API | ⚪ |

Plan completo en [`docs/PLAN.md`](docs/PLAN.md).

## Estructura

| Ruta | Qué es |
|---|---|
| `site/` | **la app** — corre en local, sin plataforma |
| `site/assets/js/api.js` | frontera de datos: hoy `localStorage`, mañana la API real |
| `site/assets/js/card.js` | los 5 skins de carta |
| `data/` | mazos en JSON + `config.json` |
| `docs/` | plan, marca, modelo de datos, pipeline de contenido |
| `scripts/` | `build_views.py` (port a Blade), `contrast.py` (WCAG) |
| `production/views/` | **generado** — no editar a mano |
| `assets/` | **generado** — espejo público que publica la plataforma |
| `reference/` | PDF del manual de marca (fuera de git por peso) |

## Subir a ai.backbone

Proyecto **standalone + Tailwind + Vanilla JS**, transporte por git.

```bash
python3 scripts/build_views.py      # site/ → production/views/*.blade.php
```

Primera pasada: subir, correr **Scan for new files**, anotar los preview ids en `scripts/preview_ids.json`, volver a correr el build y subir otra vez — ahí los links internos quedan apuntando a `/preview/{id}`. Trampas de la plataforma en [`CLAUDE.md`](CLAUDE.md).
