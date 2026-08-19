# Scouting America — Tarjetas Cub Scout

PWA bilingüe (ES/EN) para que líderes Cub Scout compartan por WhatsApp la tarjeta de actividad de la semana, y para que los papás hispanos tengan los 7 mazos en el teléfono. Plan completo en `docs/PLAN.md`.

## Stack y flujo de trabajo

Standalone + **Vanilla JS**, sin framework de CSS y sin build step.

**Local primero.** Se desarrolla en `site/`, que es una app estática que corre con `python3 -m http.server 8080` y no depende de la plataforma. `scripts/build_views.py` porta `site/` a `production/views/*.blade.php` cuando toca subir.

- **`production/views/` y `assets/` son generados. No se editan a mano** — el próximo build los pisa.
- Todo cambio va en `site/`.

## Glosario — los tres objetos, y por qué importa

Las palabras se fijaron en la revisión del 14-ago-2026 porque estaban colisionando:

- **Carta** — una actividad. `bear-a-bobcat-01`.
- **Baraja** (`deck`) — uno de los 7 conjuntos oficiales: los 6 rangos Cub Scout + la de planeación del pack. Es contenido de Scouting America, no lo arma nadie.
- **Mazo** (`mazo`) — la selección semanal que **crea el líder**: un nombre (p. ej. "Pack 77") y las cartas que eligió, que pueden venir de varias barajas. Es lo que se comparte por WhatsApp y lo que abre el papá.

Hasta el 14-ago-2026 el código llamaba "mazo" a la baraja (`mazo.html` mostraba un rango). Se renombró a `baraja.html` / `barajas.html` para dejar libre la palabra **mazo** para el objeto nuevo del líder. Si aparece "mazo" con el sentido viejo en algún lado, está desactualizado.

## Reglas del proyecto

- **Mobile-first, siempre.** El papá entra desde WhatsApp con datos limitados. Vista del papá < 150 KB sin imágenes, pintada en < 2 s en 3G.
- **El papá nunca crea cuenta.** Ninguna pantalla suya pide login, email ni teléfono.
- **Cero datos de menores.** No hay entidad "niño" en el sistema. Ver `docs/data-model.md`.
- **Las tarjetas no se rediseñan.** Se replica el layout impreso; nombres y marcas del programa se respetan literal.
- **Tarjeta = skin + JSON**, nunca imagen ni PDF embebido. ~6–8 skins para los 7 mazos.
- **Los JSON de los mazos viven en `site/assets/data/`**, no sueltos en la raíz: fuera del scope del service worker (`/site/`) no se cachean —la app no servía nada sin red— y viajan con la reescritura de rutas que el preview necesita.
- **Todo el acceso a datos pasa por `assets/js/api.js`.** Hoy `localStorage`; mañana la API real. Ninguna vista lee JSON directo.
- Contrastes WCAG 2.2 AA **medidos con script**, no afirmados. Área táctil ≥ 44 px.
- Nada de CSS ni JS inline: archivos propios en `assets/`.
- Marca: `Scouting-America-Brand-Guidelines-2024-BC.pdf` es la fuente. No se inventan colores ni tipografías.

## Layout en disco

```
site/                 ← LA APP. Acá se trabaja.
├── index.html · mazo.html · carta.html · guia-de-estilos.html
└── assets/css|js|img
    └── data/         ← mazos JSON + config.json
docs/                 ← plan, marca, modelo de datos, pipeline de contenido
scripts/              ← build_views.py, contrast.py, preview_ids.json
reference/            ← PDF del manual (fuera de git)

production/views/     ← GENERADO por build_views.py (.blade.php)
assets/               ← GENERADO, espejo público que publica la plataforma
```
`assets/` y `production/views/assets/` van **siempre en par** en cada commit; el build los escribe juntos.

`build_views.py` sella además el service worker y las URLs de CSS/JS con un hash
del contenido de `site/assets` (`?v=…`). Sin eso el navegador y el SW seguían
sirviendo la versión vieja y cada corrección parecía "no aplicar".

## Cargar un mazo desde su PDF

Los PDFs de imprenta viven en el Drive del cliente (carpeta `1c16XOh7…`) y pesan
entre 44 MB y 500 MB, así que no entran por el MCP de Drive: se bajan a mano a
`reference/decks/<id>-en.pdf`. Después son dos comandos:

```bash
python3 scripts/render_cards.py <id> reference/decks/<id>-en.pdf     # láminas WebP
python3 scripts/build_deck.py  <id> reference/decks/<id>-en.pdf \
                                    reference/decks/<id>-es.txt      # JSON + español
```

`build_deck.py` preserva la cabecera curada del mazo y los enlaces cargados a
mano (el QR impreso se vuelve enlace en digital), y saca el mazo del estado
"en preparación" solo cuando ya tiene tarjetas.

El título de la actividad se detecta por **cuerpo tipográfico** leído del PDF
(`pdftotext -bbox-layout`): Adventure ~14 pt, título ~12 pt, texto ~9 pt,
etiquetas ~7 pt. La sangría del texto plano NO sirve — hay títulos más adentro
y más afuera del bloque de requisito, y pdftotext redondea columnas.

La traducción llega como un volcado con pipes, una línea por carta, y cruza por
el título original en inglés que el documento declara en cada tarjeta. Lo que
no venga traducido cae a inglés con un aviso propio en la tarjeta.

## Quirks de la plataforma (verificados en otros proyectos, aplican acá)

1. **`@context` es una directiva de Blade.** Cualquier JSON-LD con `"@context"` revienta el preview con 500. Escribir `@@context`.
2. **Cada vista se sirve en `/preview/{id}`**, no en la ruta del sitio → un link root-relative (`/mazos`) da **404 garantizado** en el preview. Para navegar entre vistas hay que apuntar a `/preview/{id}` del destino.
3. Los assets root-relative **sí** resuelven en el preview.
4. Los archivos nuevos **no aparecen solos** en Generated Views: hay que darle a **Scan for new files**.
5. Los runs largos **mueren por timeout** entre pasos. Trabajar en pasos numerados con **commit obligatorio después de cada uno**, y nunca borrar un archivo para reescribirlo — sobreescribir.
6. El preview puede servir HTML compilado viejo: limpiar con **Regenerate preview** (`?v=` no sirve).
7. El PAT del Profile no lee GitHub. Para el pull: repo público un rato → Pull → privado otra vez.
8. **Nunca commitear el scaffold post-clone** (`CLAUDE.md` + `production/memory/` que deja la plataforma): la rama diverge y el proyecto queda muerto.

## Correr local

```bash
python3 -m http.server 8080 --directory .
```
