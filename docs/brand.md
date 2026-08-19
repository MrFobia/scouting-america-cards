# Marca — Scouting America Brand Guidelines 2024

Fuente: `reference/scouting-america-brand-guidelines-2024.pdf` (31 MB, bajado de ClickUp).
Leído **página por página**, no solo extraído por texto. Contrastes **medidos** (`scripts/contrast.py`).

## El sistema visual (lo que hace que se lea como Scouting America)

No alcanza con los hex. La marca se reconoce por cómo arma la página:

1. **Página blanca con mucho aire.** El blanco es, en palabras del manual, el quinto color.
2. **Titular display en serif y en rojo `#CE1126`.** Es la firma. Todos los títulos del manual son así.
3. **Subtítulos en azul, bold, chicos** (`Brand Positioning`, `Identity`, `Brand Platform`).
4. **Regla vertical fina en rojo** que separa contenido de imagen. En el manual la de Cub Scouts es dorada, pero acá no se usa dorado.
5. **Paneles neutros** en Light Tan `#E9E9E4` para zonas de especificación.
6. **Fotografía real** de chicos en actividad, a sangre, ocupando media página.
7. **La firma del logo siempre presente**, arriba a la izquierda.

Implementado en `site/assets/css/tokens.css` (`.display`, `.subhead`, `.ruled`, `.section--warm`) y visible en `site/guia-de-estilos.html`.

## Logos

Extraídos del PDF a 300 dpi y recortados con fondo transparente:

| Archivo | Qué es |
|---|---|
| `site/assets/img/scouting-america-signature.png` | Firma horizontal Scouting America, a color |
| `site/assets/img/cub-scouts-trademark.png` | Trademark Cub Scouts (rombo, lobo, flor de lis) |

**Reglas del manual:**
- Espacio libre ≥ altura de la flor de lis (firma) o alto del rombo (trademark Cub Scouts).
- El ® / ™ siempre presente.
- Prohibido: tintas o tramas, sombra/bisel/glow, alterar la firma o su tipografía, usar el lobo fuera del trademark, usar la flor de lis suelta (está **retirada**).
- Sobre fondo oscuro **debe** usarse la versión en blanco.

> **Pendiente:** no tenemos la versión en blanco ni los vectores oficiales. Por eso el pie de página usa el nombre en texto, no el PNG a color. Pedirlos al cliente.

## Taglines

- Inglés: **Prepared. For Life.®**
- Español: **Preparados para el futuro.®**

El manual es explícito: la versión en español *«se debe colocar en todas las comunicaciones, literatura y productos en español»*. Este producto es en español por definición → **es la tagline por defecto**; la inglesa queda para la vista en inglés.

Nunca aparece sola: siempre junto al trademark o la firma, aunque estén en distintos lugares. El ® siempre.

## Paleta

### Decisión: solo la paleta Scouting America
El azul y dorado de la pág. 23 son de la **sub-marca Cub Scouts**, no de la paleta Scouting America. **Por decisión del cliente (13-ago) la interfaz no usa dorado.** Aparece únicamente dentro del trademark Cub Scouts, que no se altera.

### Primarios (pág. 14)
`#CE1126` Red (PMS 186) · `#003F87` Blue (PMS 294) · `#D6CEBD` Tan · `#515354` Gray · `#FFFFFF` White

### Secundarios (pág. 15)
`#9AB3D5` Pale Blue · `#003366` Dark Blue · `#E9E9E4` Light Tan · `#AD9D7B` Dark Tan · `#858787` Pale Gray · `#232528` Dark Gray

El manual aclara: **no crear tintes ni sombras del rojo.**

## Contrastes medidos (WCAG 2.2)

| Par | Ratio | AA normal | AA grande |
|---|---|---|---|
| Dark Gray `#232528` / blanco | **15.37** | ✅ | ✅ |
| Dark Blue `#003366` / blanco | **12.61** | ✅ | ✅ |
| Blue `#003F87` / blanco | **10.19** | ✅ | ✅ |
| Dark Gray / Light Tan | **12.61** | ✅ | ✅ |
| Blue / Light Tan | **8.37** | ✅ | ✅ |
| Gray `#515354` / blanco | **7.73** | ✅ | ✅ |
| Blue / Tan | **6.51** | ✅ | ✅ |
| Pale Blue / Dark Blue | **5.87** | ✅ | ✅ |
| Red `#CE1126` / blanco | **5.63** | ✅ | ✅ |
| **Red / Tan** | **3.60** | ❌ | ✅ |
| **Pale Gray `#858787` / blanco** | **3.61** | ❌ | ✅ |
| **Dark Tan `#AD9D7B` / blanco** | **2.66** | ❌ | ❌ |

### Reglas que salen de esos números

1. **El rojo es display y acento**, no texto corrido. Sobre Tan cae a 3.60: solo grande.
2. **Azul sobre blanco es el par de lectura** (10.19) y aguanta las dos superficies cálidas.
3. **Sobre Dark Blue, Pale Blue** para el texto secundario (5.87).
4. **Dark Tan y Pale Gray son decorativos.**
5. **No se crean tintes ni sombras del rojo** — lo prohíbe el manual.
6. Focus ring: azul sobre claro, blanco sobre azul.

## Tipografía

Aprobadas: **Times New Roman**, **Arial**, **Helvetica Neue LT Std 77 Bold Condensed**, **Proxima Nova Extra Bold**.
Proxima Nova es licencia Adobe y el manual nombra el sustituto: *«An approved alternate from Google fonts is Montserrat»*.

Lo que importa no es solo la lista, es **cómo las usa el manual**: todo el documento está compuesto en **serif** —titulares en rojo, prosa en gris— y reserva la **condensada bold** para micro-etiquetas (`HEX:`, `RGB:`, encabezados de tabla). Ese reparto es el sistema.

**Decisión:**
- `--font-display` y `--font-text`: **Source Serif 4**, con **Times New Roman** (aprobada) de respaldo real. Titulares y prosa.
- `--font-ui`: **Roboto Condensed**, con Arial Narrow detrás. Cumple el rol de Helvetica Neue 77 Bold Condensed: etiquetas, botones, datos, tablas.
- Medidores numéricos con `font-variant-numeric: tabular-nums`.
- Esquinas rectas: el manual no usa nada redondeado.

> **Pendiente:** el serif y la condensada exactos del manual son licenciados. Si el cliente los tiene, se cambian dos tokens y no se toca nada más.

## Nombres del programa

Cub Scouts®, Bear, Bobcat, den, pack, Pinewood Derby® se **conservan en inglés**, con glosa en español la primera vez — igual que hace la traducción de las barajas.
