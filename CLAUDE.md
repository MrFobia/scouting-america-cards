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

## La interna de una baraja: estuche y PDF completo (20-ago-2026)

Pedido de la reunión: la interna se leía como un desplegable de Adventures, y
el líder no tenía forma de ver la baraja entera sin abrir las ~114 tarjetas de
a una. Dos piezas, dos scripts, y los dos escriben el JSON de la baraja para
que **el campo exista solo si el archivo existe**:

```bash
python3 scripts/render_cartons.py <id> "<...>/<RANK> card deck carton_print ready.pdf"
python3 scripts/build_deck_pdf.py --todas
```

- **`render_cartons.py`** → `site/assets/img/decks/<id>-carton.webp` + `deck.carton`.
  Recorta la cara frontal del troquel del estuche. El recorte **no** está
  hardcodeado: mide las guías de corte, que son rojo de registro exacto
  `(237,29,36)`. La tolerancia es corta a propósito — con un umbral ancho
  ("rojo alto, verde y azul bajos") el estuche del Wolf, que es rojo, contaba
  entero como guía. Hoy ninguna vista pinta el `-carton.webp`: quedó como el
  primitivo de recorte (`cara_frontal()`, que usa `render_deck_art.py`) y como
  foto de producto por si se quiere en el índice de barajas.
- **`render_portadas.py`** → `site/assets/img/decks/<id>-portada.webp` + `deck.art`.
  **Es el pipeline vigente del arte de la interna.** La portada del estuche
  entera —trama, tagline, wordmark y mascota— quitando solo las dos franjas
  amarillas que el cliente pidió sacar, más los colores (`ink`, `dot`) y el de
  texto **medido** contra el plano (`on`, `onRatio`, WCAG 2.2).

  La regla que manda acá: **el arte es del cliente y no se recompone.** Se
  probó antes armar el fondo con piezas (mascota recortada + trama dibujada en
  CSS) y se descartó: era arte nuestro imitando el suyo.

  Las dos franjas se quitan distinto y por buenas razones:
  - **La de arriba, recortando.** Es diagonal y baja más contra el borde
    izquierdo: medida por cobertura daba 0.169 y dejaba la punta asomando en la
    esquina. Se mide siguiendo el amarillo **desde el borde superior** → 0.205.
    Ojo: el "FOR LEADERS" también es amarillo y sí se conserva; se distingue
    porque no toca el borde.
  - **La de abajo, tapándola** con una banda de fondo del propio arte repetida
    en mosaico. Dos intentos fallidos antes, los dos por elegir la fuente por
    posición en vez de por contenido: clonar una banda del alto de la franja
    traía el wordmark y la propia franja (salía dos veces), y clonar la banda
    de justo encima traía la pata de la última letra, que en mosaico bajaba
    como una escalera de cuñas blancas. Ahora la banda se **busca**: la más
    alta que sea solo plano y trama.
  - En **Bear y Arrow of Light no existe** esa banda (su trama tiene medios
    tonos y ninguna fila pasa el filtro); ahí se tapa con el plano liso. Se ve
    bien porque en ese rincón las dos tienen poca trama.
- **`render_deck_art.py`** — ya no genera assets propios; quedó como el módulo
  de primitivas que usa `render_portadas.py` (`tintas()`, `tinta_texto()`,
  `near()`, `hexa()`).
  - **El destinte no contornea la mascota**: es line-art y su relleno ES el
    plano de color. Se vuelve transparente el fondo y se monta sobre un banner
    del mismo tono, donde se reconstruye sola.
  - **El punto de trama se detecta por TONO, no por oscuridad.** "El más oscuro
    y frecuente" elegía la tinta de la mascota (#45081B en Wolf) y el destinte
    la borraba junto con el fondo: banner sin lobo. El punto es una sombra
    pareja del plano (mismo ratio en los tres canales, 45–85 % del brillo).
  - El recorte final es **por cobertura, no `getbbox()`**: el borde de cada
    punto de trama deja motas que sobreviven al destinte, así que la caja daba
    la imagen entera.
- **`build_deck_pdf.py`** → `site/assets/pdf/<id>-en.pdf` + `deck.pdf`.
  **No usa el PDF de imprenta**: esos pesan 43–477 MB, viven en `reference/`
  (gitignored) y son inservibles en el teléfono de un líder con datos. Arma el
  PDF con las láminas WebP que ya generó `render_cards.py` → ~4 MB por baraja.

Reglas que quedaron fijadas:

- El **banner lo ve todo el mundo** (es la identidad de la baraja); el **PDF es
  del líder**, misma condición que las cartas de recursos. Al papá se le manda
  un mazo, no la baraja entera.
- **La descarga va DESPUÉS de las Adventures**, no arriba: el líder entra a
  elegir la actividad de la semana; bajarse la baraja entera es la salida para
  cuando eso no le alcanza, y una salida va al final del camino.
- El arte no es un bloque debajo del texto: es el **fondo de la cabecera**
  (`Shell.mountHeader({ tinte })`, modificador `.pagehead--tinte`). Un
  modificador más del único componente de cabecera, como `sobreFoto`.
- **El nombre no se escribe dos veces.** Con la portada de fondo, el wordmark
  impreso ES el nombre; el `<h1>` va oculto (existe para el lector de pantalla
  y el índice del documento). Hubo una vuelta intermedia, sin portada, en que
  el titular iba visible — si algún día se saca la portada, vuelve a mostrarse.
- **Sobre el arte va SOLO la miga de pan.** El eyebrow y la descripción viven
  en el cuerpo de la página. Con la descripción encima había que taparle hasta
  el 94 % del ancho con velo para que fuera legible sobre la mascota —el oso
  del Bear llega al 37 % del ancho— y eso lavaba la portada entera. Si alguien
  quiere devolver texto al hero, ese es el costo.
- El velo que queda es el mínimo para la miga, más una franja arriba porque la
  firma reversada en blanco caía sobre las letras blancas del wordmark en Wolf
  y se borraba.
- El encuadre es `cover` con `background-position: center 24%`: la portada es
  vertical y la cabecera apaisada, así que recorta sí o sí. Ojo: con `cover` la
  imagen calza justo en el ancho y **no sobra nada a los lados**, así que el
  porcentaje horizontal no mueve nada — solo sirve el vertical.
- **La portada también es la ficha del índice** (`.tile--deck`, en
  `barajas.html` y en `lider/index.html`). Reemplazó una foto del brandbook por
  baraja: eran intercambiables y la grilla se veía toda azul. Detalles:
  - Encuadre sobre la **mascota** (`object-position: 50% 58%`), que es lo que
    se reconoce a ese tamaño; más arriba entraba el wordmark recortado y Wolf
    y Bear quedaban de color plano.
  - Velo del color del rank, **no** el navy de `.tile--foto`: encima de una
    portada dorada o celeste, un velo azul las igualaba a todas otra vez.
  - Empieza recién al 42 % de alto: parejo lavaba el arte. Firme abajo porque
    en Webelos y Arrow of Light el nombre cruza el parche bordado.
  - El índice lee `_index.json`, así que `render_portadas.py` escribe `art`
    **también ahí** — si no, la grilla tendría que abrir los siete mazos
    enteros (100 KB cada uno) para dibujarse.
- El **peso y el idioma van en el botón**, antes de tocarlo.
- El SW **no intercepta `/assets/pdf/`**: es una descarga, no un recurso de la
  app. Sin esa rama caía en la genérica y copiaba 20 MB de PDFs al caché del
  shell. Los estuches sí son cache-first, como las láminas: arte inmutable.
- `deck.grade` pasó de string suelto a `{es, en}` — la consola del líder es
  inglés fijo y mostraba "3.ER GRADO". **Las vistas aceptan las dos formas**: el
  SW sirve el JSON cacheado mientras revalida, así que la primera carga después
  de publicar todavía trae la forma vieja, y sin tolerancia pintaba `undefined`.

## El mazo de la semana, del lado de la familia (20-ago-2026)

El tile de "Esta semana" en el home del papá era un bloque azul liso, igual
para todos los mazos y todas las semanas. Ahora se viste con **la baraja que
más aporta al mazo** (`API.getMazoBaraja()`): su portada, su color y su tinta
de texto, los mismos que la interna de la baraja y las fichas del índice.

- La baraja mayoritaria se cuenta por el **prefijo del id de carta**
  (`bear-a-bobcat-01`), que es como `resolveDeckId()` ya ubica una carta, y se
  lee del **índice**, no de los mazos completos: el home del papá tiene que
  pintar en menos de 2 s en 3G y no puede bajar 100 KB de JSON para elegir un
  color. Empate → gana el rank más chico (`order`), para que sea estable.
- Si el mazo mezcla barajas, el tile dice **cuántas cartas son de la
  mayoritaria** ("9 actividades · 7 de Bear"). Decir solo "Bear" sería mentir
  sobre las otras dos.
- El tile ancho lleva **velo lateral**, no vertical: su texto no vive solo
  abajo (remitente arriba, nombre al medio, conteo abajo) y con el velo de las
  fichas normales el nombre caía sobre la cabeza de la mascota, tinta oscura
  sobre tinta oscura. Además el nombre se limita al 68 % del ancho: aunque el
  velo lo respalde, un titular cruzando la cara de la mascota se lee peor.

## El usuario ADMIN — Scouting America (20-ago-2026)

Salió de la alineación interna: toda la analítica que existía era del LÍDER
("qué mandé yo, cuántos lo abrieron"), y el brief pide otra cosa — la analítica
de la APLICACIÓN, para la organización. Eduardo no es un líder: no puede entrar
por la puerta del líder ni quedarse sin tablero.

**Tres usuarios, no dos:** familia (sin cuenta) · líder (`/site/lider/`) ·
admin (`/site/admin/`). Cada uno con su navegación en `shell.js`.

```
site/admin/entrar.html    puerta (correo contra assets/data/admins.json)
site/admin/index.html     tablero del programa
site/admin/lideres.html   líderes + alta/baja
```

Cuentas de demo en `assets/data/admins.json`: `eduardo@scouting.org`,
`admin@scouting.org` (contraseña: cualquiera de 6+, no se guarda).

Criterios que vinieron de la reunión y no hay que perder:

- **Manda el ÍNDICE de apertura, no el conteo crudo** — "más importante que
  decir abrieron nueve es el índice de apertura, lo calculamos".
- **Sin serie de tiempo es una foto**, y una foto no deja comparar. Por eso
  `getSerieMensual()` y las barras de seis meses.
- Un mazo cuenta como **abierto una sola vez**, así lo toquen catorce familias
  del grupo. Contar aperturas crudas premia al grupo grande, no al líder que
  comunica bien.
- El **idioma se mide sobre aperturas, no sobre envíos**: el envío lleva el
  idioma que eligió el líder; la apertura, el que usó la familia. Lo que el
  producto quiere saber es en qué idioma LEEN.
- **La puerta del admin sí tiene muro** (el correo debe estar en el archivo),
  a diferencia de la del líder, que deja pasar cualquier correo: el tablero
  muestra a TODOS los líderes.

Dos cosas que se rompieron y quedaron arregladas:

- **`profile.id` era por NAVEGADOR, no por líder.** Alcanzaba mientras cada
  líder miraba solo lo suyo; con el tablero, los cuatro líderes de la demo
  compartían id y el admin veía el total correcto y CERO en cada fila. Ahora
  `entrarComoLider()` deriva el id del correo. `getLideres()` empareja por id,
  correo y nombre para no perder los envíos guardados con las llaves viejas.
- **La baja no cerraba nada.** La pantalla decía "closes their access" y el
  líder seguía entrando. `entrarComoLider()` ahora devuelve `false` si está en
  `lideresBaja`, y la puerta lo dice.

**Fuera de alcance a propósito:** el ALTA de líderes (registro, validación
documental, aprobación). Se marcó en la reunión que no está estimada y que hay
que definirla con el cliente. La pantalla lo dice en vez de dejar un botón que
no hace nada.

**El tablero es la única pantalla que NO es mobile-first**, por pedido
explícito: quien lee analítica está en un escritorio. Responde igual en móvil,
donde las métricas van a dos columnas, la clave ocupa el ancho y las notas de
las secundarias se ocultan — en dos columnas estorbaban más de lo que aclaran.

**Las tres pantallas tienen layout de escritorio**, no solo el tablero:
- *Overview* — métricas en fila, serie ancha, dos columnas abajo (`.panel-cols`).
- *Leaders* — resumen de cuatro métricas + tabla, con barra lateral que trae el
  ranking por índice de apertura (la tabla ordena por volumen, así que el mejor
  por apertura había que sacarlo leyendo la columna a ojo).
- *Profile* — cuenta a la izquierda, lo que se puede *hacer* con ella a la
  derecha (contraseña, sesión).

**La barra lateral de Leaders solo se abre a partir de 80rem**, no a las 56rem
del resto. La tabla trae siete columnas con `nowrap`: a 56rem con una columna al
lado quedaban detrás del scroll el índice de apertura y el botón de baja, que es
justo lo que el admin viene a usar. Y cuando la lateral está, la página se
ensancha a 88rem (`:has(.panel-cols--aside)`), porque a 78rem tampoco cabían.

**Los gráficos van en `.panel`.** Flotaban sueltos sobre el lienzo mientras las
métricas de arriba sí eran tarjetas, y el tablero se leía como dos sistemas
apilados. Mismo borde, radio y sombra que `.metric`.

**Tres paradas en la barra del admin**, no cinco: Tablero · Líderes · Perfil.
"Barajas" y "Guía" salieron porque el catálogo y el instructivo son material
del LÍDER —quien arma y manda mazos— y el admin no hace ninguna de las dos.

**Perfil y recuperación de contraseña** (`admin/perfil.html`,
`admin/recuperar.html`). Las pantallas existen; el flujo real no, porque no hay
backend y la contraseña no se guarda en ninguna parte. Se dice en pantalla en
vez de fingir que algo cambió o que salió un correo. Dos decisiones del flujo
que SÍ son de verdad y hay que conservar en la Capa B:
- la respuesta de "recuperar" es **la misma exista o no la cuenta** — decir
  "ese correo no está registrado" es enumeración de usuarios, y en una
  plataforma de una organización que atiende menores no se regala;
- **el correo no se edita** desde el perfil: es la llave de la cuenta.

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
