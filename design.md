# Design — Scouting America · Tarjetas de Reunión Cub Scout

Sistema de diseño bloqueado para esta app. Cada rediseño de página lee este archivo
antes de emitir código. No se regenera por página — se extiende o se enmienda cuando
el sistema necesita crecer.

Fuente de marca: `reference/scouting-america-brand-guidelines-2024.pdf`.
Donde este archivo y el manual de marca discrepen, **manda el manual**.

**Enmienda 17-ago-2026 — capa 2026.** Este archivo quedó atrás respecto al
código: los auras de marca, el bento, los chips, la nav en píldora y los radios
generosos ya estaban construidos y este documento todavía los prohibía. Se
reconcilia: esas decisiones quedan adoptadas acá, y se suma la **capa de motion
por niveles** (ver § Motion) y la **sombra de papel** (ver § Profundidad). Lo
que sigue prohibido sigue prohibido; lo que cambió tiene la razón escrita al
lado.

## Genre

**editorial.** El manual de Scouting America es un documento impreso: serif, titulares
en rojo, reglas finas, mucho blanco, fotografía real a sangre. No lo forzamos a
vocabulario SaaS. Las barajas también son un objeto impreso — el género coincide con
el producto, no solo con la marca.

## Familias de macroestructura

Tres familias, una por trabajo real de página. Las páginas de una familia comparten
forma; varían solo en archetipos de componente.

| Familia | Páginas | Macroestructura | Trabajo de la página |
|---|---|---|---|
| **Vitrina** | `site/index.html` · `site/carta.html` | **08 Photographic** | Mostrar la tarjeta. El texto anota, no titula. |
| **Índice** | `site/mazos.html` · `site/mazo.html` · `site/lider/index.html` | **13 Index-First** | Navegar rápido. La página *es* la lista. |
| **Documento** | `site/como-usar.html` · `site/guia-de-estilos.html` | **02 Long Document** | Leer de corrido. Prosa con encabezados inline. |

**Por qué Photographic manda en la Vitrina.** El objeto central del producto es una
tarjeta ilustrada. Una audiencia con inglés limitado y alfabetización variable entiende
antes viendo la lámina que leyendo un titular. Abrir con la tarjeta no es una decisión
estética: es la decisión de accesibilidad más grande de la app.

**Adaptación documentada del macro.** Photographic pide imagen a sangre de borde a
borde. Las láminas son verticales (proporción de carta, ~3:4); recortarlas a pantalla
completa las mutila. La adaptación: la banda ocupa el fold, el campo de color llega a
los bordes, y la tarjeta va **entera** y grande sobre ese campo. La imagen sigue
dominando el fold; no se recorta contenido oficial.

## Archetipos por familia

| Familia | Nav | Footer | Hero |
|---|---|---|---|
| **Vitrina** — `index` | **N9** Edge-aligned minimal | **Ft1** Mast-headed | Hero fotográfico a sangre |
| **Vitrina** — `carta` | **N9** Edge-aligned minimal | **Ft2** Inline single line | **H6** Photographic fold |
| **Índice** | **N9** Edge-aligned minimal | **Ft2** Inline single line | ninguno |
| **Documento** | **N9** Edge-aligned minimal | **Ft2** Inline single line | ninguno |

**Enmienda 13-ago-2026:** `index` pasó de N6 masthead a N9. Con el app shell ya
hay pestañas persistentes; masthead + pestañas + hero eran tres capas de cromo
sobre la primera pantalla. Manda la navegación de la app.

**Excepción documentada:** `carta.html` pertenece a Vitrina pero usa N9, no N6. Es la
página de llegada desde WhatsApp — un masthead de tres filas gastaría la primera
pantalla del papá antes de que vea su tarjeta. La familia define la forma del cuerpo;
la página de llegada define su propia cabecera.

Knobs elegidos:
- **N6** — issue line arriba del wordmark · wordmark 2xl · regla doble abajo
- **N9** — CTA texto + flecha · wordmark serif · padding-block default
- **H6** — campo a sangre, tarjeta entera encima · caption abajo-derecha · texto debajo
- **Ft1** — wordmark display xl · tagline serif · links en línea
- **Ft2** — orden credit/links · separador middot · densidad espaciada

## Tema

**No es OKLCH y es a propósito.** Los valores son los hex literales del Brand Guidelines
2024. Convertirlos a OKLCH los desplaza, y el manual prohíbe crear tintes o variantes de
los colores de marca. La convención de Hallmark cede ante la marca del cliente.

Por la misma razón `--color-paper` es `#FFFFFF` puro: el manual lo declara el quinto
color de la paleta. Es una excepción consciente a la regla de "nunca blanco puro".

```
--sa-red        #CE1126   PMS 186 · display, reglas de acento
--sa-blue       #003F87   PMS 294 · acción, enlaces
--sa-tan        #D6CEBD   reglas, bordes, campos cálidos
--sa-gray       #515354   texto
--sa-white      #FFFFFF   papel
--sa-pale-blue  #9AB3D5   texto secundario sobre azul oscuro
--sa-dark-blue  #003366   superficies oscuras
--sa-light-tan  #E9E9E4   superficie alterna
--sa-dark-tan   #AD9D7B   decorativo
--sa-pale-gray  #858787   texto secundario
--sa-dark-gray  #232528   tinta
```

Acento por viewport: el rojo no pasa del 5 %. Es display y regla, nunca texto corrido.

## Tipografía

- **Display y prosa** — Source Serif 4, 400 / 600, romana. Respaldo real: Times New Roman
  (aprobada por el manual).
- **Chrome** — Roboto Condensed 700. Cumple el rol que el manual le da a Helvetica Neue
  LT Std 77 Bold Condensed: etiquetas, botones, datos, encabezados de tabla.
- **Nada de itálicas en titulares.** Solo énfasis dentro de prosa.
- Medida de lectura: 60–65 ch en Documento, 46 ch en anotaciones de Vitrina.

## Espaciado

Escala de 4 pt con nombres semánticos, en `site/assets/css/tokens.css`. Las páginas usan
tokens (`var(--space-md)`), nunca valores crudos.

## App shell — la capa de aplicación

Añadido el 13-ago-2026. El sitio se usa como app, así que tiene armazón de app.
Vive en `site/assets/js/shell.js` y se monta solo en cada página (menos la guía
de estilos, que es documentación interna).

- **Navegación persistente de 4 destinos** — Inicio · Barajas · Guía · Líder.
  Solo primer nivel; una Adventure o una tarjeta viven en las migas, no acá.
  En el teléfono va fija abajo, al alcance del pulgar, con `env(safe-area-inset-bottom)`.
  Desde 64rem sube y se vuelve una fila horizontal bajo la cabecera — abajo no
  sirve cuando se navega con mouse.
- **Estado activo** marcado por color **y** peso **y** una barra de 3 px. Color
  solo no le sirve a quien no distingue azul de gris.
- **Iconos propios**, una sola familia, trazo 1.6, caja de 24. Sin librería y sin
  emoji: los emoji cambian de forma según el teléfono y no se tiñen con tokens.
- **Esqueletos** del alto real del contenido mientras carga, para que nada salte
  cuando llega el dato.
- **Vacío y error con salida** — título, explicación y una acción. Si no hay red
  lo dice y ofrece reintentar. Nunca una pantalla muerta.
- **Instalable** — `manifest.webmanifest`, iconos 192/512 + maskable, `standalone`.
  La barra de instalación se ofrece una vez; si la cierran, no vuelve.
- **Sin conexión** — `sw.js`. Navegación network-first con caída al caché,
  CSS/JS stale-while-revalidate, láminas cache-first (son inmutables), datos
  stale-while-revalidate. No es un badge de PWA: cada lámina que no se vuelve a
  bajar es plata del papá.

Solo un elemento pegado arriba por página: cabecera **o** pestañas, nunca las dos.

## Cabecera y pie únicos

Enmienda 13-ago-2026. **Toda la app usa el mismo componente de cabecera**,
`Shell.mountHeader()`. Antes el home tenía aura + titular grande y las internas
una barra blanca distinta: se leían como dos productos separados.

La cabecera es siempre: aura de marca · firma + selector de idioma · eyebrow ·
titular grande con una palabra en azul · subtítulo · fila de chips. Las migas de
pan son chips, no una fila de texto con barras.

El pie es `Shell.mountFooter()`, callado e igual en todas.

Ninguna página monta su propia cabecera. Si hace falta una variante, se agrega un
parámetro al componente, no un `<header>` a mano.

## Fotografía

Añadido el 13-ago-2026. **Toda la fotografía sale del Scouting America Brand
Guidelines 2024** — material licenciado del propio cliente, extraído de su
brandbook con `pdfimages`. Vive en `site/assets/img/photos/`.

**Prohibido traer fotos de bancos de imágenes o de búsquedas web.** Esto es un
producto de una organización que atiende menores: fotos de chicos de terceros
son un problema legal y de protección, no una cuestión de gusto. Si hace falta
material nuevo, se le pide al cliente.

- La foto va **a sangre**, nunca dentro de una caja con borde.
- Siempre lleva **velo** (`--scrim-*`) para garantizar contraste del texto encima
  sobre cualquier zona de la imagen. El velo no es un efecto: es accesibilidad.
- Las fotos decorativas van con `alt=""` y `aria-hidden="true"`; las que aportan
  información llevan alt descriptivo.
- Hoy hay cuatro fotos para siete ranks. Antes que repetir una foto dos veces en
  la misma pantalla, las fichas sin foto van en campo navy liso.
- **Ampliado 17-ago-2026:** se extrajo una segunda tanda de fotos del brandbook
  con `pdfimages` (misma fuente, misma licencia). Cada foto nueva pasa por el
  mismo filtro: actividad real de chicos, buena luz, recortable a horizontal
  sin cortar caras. El listado vigente está en `site/assets/img/photos/`.

## El banner de la baraja — el color del rank, y hasta dónde llega

Añadido el 20-ago-2026. **Enmienda explícita a la regla de paleta.** Hasta acá
este documento decía que solo entran los cinco colores Scouting America y que
el dorado de Cub Scouts vive únicamente dentro del trademark. La interna de una
baraja es la excepción, decidida en la revisión del 20-ago: se probó primero
con el estuche como foto de producto sobre campo neutro y **no alcanzaba** —
la pantalla seguía siendo la app con una cajita adentro, no la baraja.

La interna abre con la **cabecera tintada**: el fondo de `.pagehead` es **la
portada del estuche tal como la mandó el cliente**. No es un bloque de imagen
debajo del texto — eso se probó y se descartó: la pantalla seguía leyéndose
como la app con una estampa pegada.

**Sobre el arte va solo la miga de pan.** El eyebrow y la descripción bajan al
cuerpo de la página. El hero se lee como la portada del mazo, no como una
cabecera con fondo, y de paso desaparece el problema que lo ensuciaba todo: con
la descripción encima había que taparle hasta el 94 % del ancho con velo para
que fuera legible sobre la mascota, y eso lavaba justo el arte que el cliente
quiere mostrar. Con una línea corta arriba a la izquierda, el velo es mínimo.

**El arte es del cliente, no se recompone.** Se llegó acá después de armar el
fondo con piezas sueltas (mascota recortada + trama dibujada en CSS) y
descartarlo: era arte nuestro imitando el suyo. `scripts/render_portadas.py`
usa la cara frontal del troquel entera —trama, "DEN MEETING DECK / FOR
LEADERS", wordmark y mascota— y solo quita las dos franjas amarillas que el
cliente pidió sacar ("Fun, Simple, Easy" arriba y "Plan a den meeting in
minutes!" abajo).

Del PDF salen además el color plano, el de la trama y el de texto, que viajan
en `deck.art` como variables (`--deck-ink`, `--deck-dot`, `--deck-on`).
**Ningún color de rank está escrito a mano en el CSS**: una baraja nueva trae
el suyo desde su estuche.

**El nombre del rank no se escribe dos veces.** Con la portada de fondo, el
wordmark impreso ES el nombre; el `<h1>` existe igual pero oculto, porque es el
encabezado del documento y lo único que lee un lector de pantalla. (Antes de
tener la portada, cuando el fondo era solo color, el titular iba visible.)

La misma portada es la **ficha de la baraja en el índice** (`.tile--deck`, en
`barajas.html` y en la consola del líder). Antes iba una foto del brandbook por
baraja: correctas, pero intercambiables — ninguna decía cuál baraja era y la
grilla entera se veía igual de azul. El encuadre de la ficha va sobre la
mascota, que es lo que se reconoce de un vistazo; el wordmark a ese tamaño sale
recortado e ilegible, así que el nombre sigue yendo como texto. El velo de la
ficha es del color del rank, no el navy de `.tile--foto`: un velo azul sobre
una portada dorada o celeste las volvía a igualar a todas.

La foto sigue en pie para la baraja de planeación del pack, que no tiene
estuche impreso, y para las bandas de las demás pantallas.

Tres reglas que no se negocian acá:

1. **El color del rank vive en la cabecera y no sale de ahí.** Botones, tiles,
   pills y texto del resto de la interna siguen en paleta Scouting America.
2. **El color del texto se mide, no se elige.** `--deck-on` viene calculado
   (WCAG 2.2) contra el plano: blanco sobre Wolf y Webelos, tinta oscura sobre
   Lion y Bear. Y **sin opacidad encima** — bajar el eyebrow al 72 % dejaba a
   Lion, el rank de más contraste, en 4.50:1 clavado y al resto por debajo.
3. **Sobre el arte, solo la miga.** Todo lo demás va debajo. El velo que queda
   es el mínimo para esa línea, más una franja arriba porque la firma reversada
   en blanco caía sobre las letras blancas del wordmark en Wolf y se borraba.

## La firma no se deforma. Nunca.

Añadido el 20-ago-2026 tras encontrarla comprimida un 19 % en la cabecera de
las internas (relación 8.18 → 6.60, medida en pantalla). El manual lo prohíbe
explícitamente, así que no es cuestión de que "casi no se note".

La causa fue una combinación inocente: `.pagehead__logo` fija `height: 20px` y
una regla posterior le puso `max-width: 100%`. En una cabecera angosta el ancho
se recortaba y el alto no, o sea estirón horizontal. **La imagen de la firma
lleva `object-fit: contain`**: si la caja no le da, se escala; no se estira.
Cualquier regla nueva que toque su ancho o su alto tiene que dejar eso en pie.

## Tablas en el teléfono: fichas, no scroll lateral

Añadido el 20-ago-2026 con la tabla de líderes del admin. Siete columnas no
entran en 430 px. Con `overflow-x` la tabla scrolleaba de costado y las dos
últimas columnas —el índice de apertura y el botón de baja, o sea lo que el
admin viene a usar— quedaban fuera de cuadro sin nada que avisara que había
más. **Un scroll horizontal escondido dentro de una página que ya scrollea en
vertical es de las cosas que menos se descubren en un teléfono.**

El patrón: por debajo de 40rem cada fila se apila como una ficha, con el
nombre de título y los datos como pares etiqueta-valor. La etiqueta sale del
`data-label` de cada celda, así que no se escribe dos veces ni se puede
desincronizar del `<thead>`, que pasa a estar oculto.

**Y la trampa que hay que recordar:** cambiar el `display` de una tabla le
borra al navegador los roles implícitos (table / row / cell), y un lector de
pantalla deja de poder recorrerla por filas y columnas — queda una pila de
divs con pinta de tabla. Por eso el marcado lleva `role="table"`, `role="row"`,
`role="rowheader"` y `role="cell"` explícitos. Si se replica este patrón en
otra tabla, esos roles van sí o sí.

## Un solo eje por página

Añadido el 20-ago-2026. `.page` y `.pagefoot` tenían cada uno su `max-width`
escrito a mano (60rem los dos), y funcionó mientras todas las páginas midieran
lo mismo. Con el tablero del admin a 78rem —88 con barra lateral— el copyright
arrancaba ~180 px más adentro que las tarjetas: dos ejes distintos en la misma
pantalla.

Ahora el ancho se declara **una vez**, en `--page-max` sobre el `<body>`, y lo
leen el cuerpo y el pie. Va en el body y no en `.page` porque **el pie es
hermano del `<main>`, no hijo**: declarado más adentro no hay forma de que
suba hasta él. Cualquier pantalla que cambie de ancho cambia esa variable, no
el `max-width` de un componente suelto.

## Microinteracciones — qué se anima y qué no

Ampliación del § Motion, 20-ago-2026. El sistema (entrada `.rise`, reveal
`.rise-io`, tokens `--dur-*` / `--ease-*`) ya existía; faltaba aplicarlo a los
componentes de datos, que entraban en seco. La capa nueva usa **los mismos
tokens** — no hay una segunda escala de tiempos.

- **Las barras crecen desde la base** con `transform: scaleY()`, no animando
  el alto: el alto sale del dato en porcentaje y animarlo obliga al navegador
  a rehacer el layout en cada fotograma.
- **Los números cuentan** hasta su valor (`Shell.tick`, que estaba escrito
  para esto y no lo usaba nadie).
- **Lo que no es cliqueable no se levanta.** La fila de tabla solo cambia de
  fondo: levantarla prometería un clic que no existe.
- **El punto de estado late una vez** al entrar, no en bucle: un parpadeo
  permanente en una tabla es ruido, no información.

**Trampa a recordar con los contadores:** `requestAnimationFrame` no corre en
una pestaña en segundo plano, y abrir un enlace en pestaña nueva para mirarlo
después es de lo más común. El marcado tiene que traer **el valor final**, no
un cero de arranque, y `Shell.tick` sale por lo corto si
`document.visibilityState !== 'visible'`. Sin eso el tablero mostraba "0 %" de
apertura hasta que alguien recargara.

## Profundidad

**Por capas, con una sombra de papel fina.** El manual prohíbe sombras sobre
la marca (el logo, la firma), no sobre las tarjetas de interfaz. La jerarquía
se arma con:

- **Solape** — la tarjeta del hero se sale de su sección y se monta sobre la
  siguiente (`margin-bottom` negativo).
- **Rotación mínima** — 2–3° en la carta del hero y en las capas del mazo apilado.
- **Campos de color alternados** — navy · tan · blanco dan el ritmo vertical.
- **Borde de 1 px** como base de todo contenedor.
- **Sombra de papel** (añadida 17-ago-2026) — una sola sombra corta y oscura
  (`--shadow-paper`, alpha ≤ 0.10, sin color) en elementos interactivos que se
  levantan: tiles, deckcards al hover, barras flotantes. Prohibido el glow
  coloreado y prohibido sobre superficies oscuras — ahí la elevación es por
  luminosidad. En Android de gama baja una sombra chica cuesta lo mismo que
  un borde; una grande y difusa, no. Por eso una sola, corta.

## Motion

**Por capas, decidido con el equipo el 17-ago-2026.** Antes este proyecto era
motion-cut en todo. La decisión nueva: la riqueza de movimiento va donde el
dispositivo y los datos lo bancan, y se corta donde no.

- **Capa A — la vista del papá (`carta.html` y `mazo.html`, las páginas que
  llegan por WhatsApp).** Sin reveals, sin observers, sin animación de entrada.
  Solo microtransiciones de estado en controles (≤ 160 ms, `--ease-out`) y foco
  instantáneo. El presupuesto manda: < 150 KB sin imágenes, pintada en < 2 s en
  3G. Es la promesa del brief para Scout Reach y no se negocia.

  **Excepción, pedida y aceptada el 20-ago-2026:** el paso de una carta a la
  siguiente en `mazo.html` SÍ anima — la que sale se une visualmente al mazo
  (las capas rotadas de `.cardstack::before/::after`), la que entra se
  despega de ahí. Es lo único que rompe la regla de "sin animación de
  contenido" en Capa A, y se aceptó a propósito: transform + opacity nomás
  (Web Animations API, nunca layout), 180 ms de salida / 240 ms de entrada,
  `prefers-reduced-motion` la apaga entera y el cambio queda instantáneo. No
  agrega peso de red (cero libraries, cero imágenes nuevas) — el compromiso
  de 3G/Android de gama baja sigue en pie, lo que cede es solo el "sin
  movimiento", no el presupuesto de bytes ni de pintado.
- **Capa B — el resto de la app** (home, barajas, consola del líder, envíos,
  progreso, guía). Motion-on con disciplina, máximo **tres primitivas por
  página**, elegidas de este menú:
  - **Entrada orquestada** — una sola por página, stagger por `--i` en el DOM,
    techo 500 ms. Después de esa entrada la página *está ahí*; nada de
    fade-up sección por sección.
  - **Number tick** — los contadores (tarjetas del catálogo, aperturas,
    progreso) cuentan de 0 al valor en ≤ 600 ms cuando entran al viewport,
    una sola vez. Reduced-motion: valor final directo.
  - **Press físico** — `scale(0.98)` / `translateY(1px)` en 100 ms al
    presionar, vuelta en 150 ms. En todo botón, tile y pestaña.
  - **Hover lift** — `translateY(-2px)` + sombra de papel, solo bajo
    `@media (hover: hover) and (pointer: fine)`. En táctil no existe.
  - **Apertura animada** — `<details>` y acordeones con
    `grid-template-rows: 0fr → 1fr`, `--ease-in-out`, 250 ms.

Reglas que valen para las dos capas:

- Solo `transform` y `opacity`. Nunca `width`, `height`, `top`, `margin`.
- Tres easings con nombre (`--ease-out`, `--ease-in`, `--ease-in-out`) y tres
  duraciones (`--dur-micro` 120 ms · `--dur-short` 160 ms · `--dur-long` 420 ms).
  Prohibido `ease`, `linear` fuera de barras de progreso, y cualquier overshoot
  con rebote en UI.
- `prefers-reduced-motion: reduce` colapsa todo a crossfade de opacidad ≤ 150 ms.
  Lo funcional (esqueletos, barras) sigue, más lento.
- El foco aparece **instantáneo**, nunca animado.
- View Transitions entre páginas solo como mejora progresiva
  (`@view-transition` + detección); sin ella la navegación es la de siempre.

## Microinteracciones

- Éxito silencioso. Nada de toasts de celebración. Los toasts son para errores
  con reintento y para acciones cuyo efecto no se ve.
- El estado se muestra donde ocurrió la acción, no en una esquina. Copiar un
  enlace cambia el propio botón a "Copiado ✓" por 2.5 s — no hay toast.
- Optimista con rollback: marcar una actividad como hecha actualiza al instante;
  si falla, vuelve atrás con aviso y reintento.
- Tooltips: 0 ms al foco, 800 ms en hover. En táctil no hay hover — nada
  crítico vive en hover.
- Área táctil mínima 44 px en todo control, sin excepción.
- Spinners solo con demora de 150 ms o mínimo visible de 300 ms; si el layout
  es conocido, esqueleto, nunca spinner.
- Todo control interactivo tiene sus ocho estados resueltos (default, hover,
  foco, presionado, deshabilitado, cargando, error, éxito) — el detalle vive
  en `app.css`; lo que no tiene estado no se publica.

## Voz de los CTA

- **Primario** — bloque azul lleno, esquinas rectas, verbo en condensada. Uno por
  pantalla. `Enviar por WhatsApp`, `Ver las barajas`.
- **Secundario** — enlace tipográfico con subrayado de 1 px y flecha. Sin caja.
  `Cómo se usa →`
- Prohibido: tres botones apilados a ancho completo. Si hay tres acciones, una es
  primaria y las otras dos son enlaces.

## Permisos por familia

- **Vitrina** puede usar la lámina oficial a tamaño grande. No puede inventar imágenes.
- **Índice** lleva fotografía oficial solo en las fichas (`.tile--foto`, con velo)
  y en bandas; la lista sigue siendo la página. Enmendado el 17-ago-2026: antes
  decía "sin imagen", pero las fichas de color liso eran el punto más pobre de la
  app y ya hay doce fotos licenciadas para siete barajas.
- **Documento** es solo tipografía, más una banda fotográfica de apertura.

## Lo que TODAS las páginas comparten

- La firma Scouting America y la tagline `Preparados para el futuro.®`
- La paleta y el reparto tipográfico serif / condensada.
- La voz de los CTA.
- El foco visible azul de 3 px, offset 2.
- Español latino neutro de EE. UU. Términos de programa en inglés con glosa.

## Lo que las páginas PUEDEN variar

- La macroestructura, dentro de su familia.
- El archetipo de nav entre N6 y N9, según sea página de destino o de llegada.
- La densidad de la lista en Índice.

## Prohibido en todo el proyecto

Estas no son preferencias — cada una tiene una razón de audiencia o de marca:

| Prohibido | Por qué |
|---|---|
| Glassmorphism, backdrop-blur | Cuesta GPU en Android de gama baja |
| Dark mode | El manual prohíbe el logo a color sobre fondo oscuro |
| Mallas de gradiente multicolor, blobs de IA | El manual prohíbe efectos sobre la marca; las auras usan SOLO colores de la paleta |
| Fade-up sección por sección al hacer scroll | La página nunca se asienta; una sola entrada orquestada por página |
| Tres círculos numerados en fila | El tell generado más reconocible que hay |
| Eyebrow en versalitas en cada sección | Máximo uno por página, y solo si es ordinal |
| Sombra glow de color, sombra sobre superficie oscura, sombra sobre el logo | Lo primero es slop, lo segundo lo prohíbe el manual |
| Métricas inventadas | No tenemos datos de uso todavía |
| Fotos de stock | Las láminas y fotos oficiales son la única imagen |

## Exports

### tokens.css
La fuente vive en `site/assets/css/tokens.css` y se copia a `assets/css/tokens.css` en
el build. Los valores de marca son hex por la razón explicada arriba.
