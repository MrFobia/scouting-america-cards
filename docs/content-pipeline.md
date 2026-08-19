# Anatomía de las barajas y pipeline de contenido

Basado en la lectura real de `Bear_Mazo_Espanol.pdf` (Drive, carpeta *Español*) el 13-ago-2026.

## Qué hay en Drive

Carpeta: `1MvMlfoIfEZKzXO_WM_So3lBdh6_eUt2Y`

**Inglés (originales, 7 archivos):** LION · TIGER · WOLF · BEAR · WEBELOS · AOL · *Pack Program Planning Deck for Leaders*.
**Español (traducidos, 5 archivos):** Bear · Wolf · Tiger · Webelos · Pack_Planificación.

> **Los "7 mazos" son 6 rangos + la baraja de planeación del pack (para líderes).** No hay mazo Bobcat: Bobcat es la primera Aventura *dentro* de cada rango.
>
> **Faltan en español: LION y ARROW OF LIGHT.** Es una dependencia dura para F6. Ya no es problema nuestro traducirlos (hay persona encargada), pero sí lo es que lleguen.

## Anatomía de un mazo

Jerarquía real: **Mazo (rango) → Aventura → Requisito → Carta de actividad.**

Un mazo Bear contiene:

1. **Carta portada** — rango, subtítulo, propósito de la baraja, copyright.
2. **Cómo leer las cartas** — guía de íconos: los tres medidores 1–5 y los tipos de lugar.
3. **Cartas de recurso** (borde oscuro) — Instrucciones / Cómo usar esta baraja · Para Empezar · Bienvenido Nuevo Líder de Den · Protección de los Jóvenes · Capacitación del Líder de Den.
4. **Cartas de Aventura** — una por Aventura: nombre ES + EN, categoría, `OBLIGATORIA`/electiva, número de requisitos, resumen.
5. **Cartas de actividad** — el grueso. Varias por requisito, el líder elige una.

Aventuras del mazo Bear: Bobcat · Bear Habitat · Bear Strong · Paws for Action · Standing Tall · Fellowship (…).
Categorías vistas: Carácter y Liderazgo · Aire Libre · Condición Física Personal · Ciudadanía · Seguridad Personal · Familia y Reverencia.

## Anatomía de una carta de actividad

Campos constantes, verificados en decenas de cartas:

| Campo | Ejemplo |
|---|---|
| Paginador de requisitos | `1 2 3 4 5 6 7 8` con el activo resaltado |
| Etiqueta | `REQUISITO 2 DE 8` |
| Aventura | `Bobcat (Gato Montés)` |
| Categoría | `CARÁCTER Y LIDERAZGO` |
| Texto del requisito | "Recita el Scout Oath (Juramento Scout) y la Scout Law…" |
| Título de la actividad (ES) | `Lanzamiento de Bolsitas Bobcat` |
| Título original (EN) | `Actividad original: Bobcat Beanbag Toss` |
| Lugar | Bajo techo · Al aire libre · Excursión · Interior/Exterior |
| Medidores 1–5 | Energía · Preparación · Duración |
| Descripción | "Mientras lanzan bolsitas de frijol, los Cub Scouts se mueven…" |
| QR → link | página oficial de la actividad |
| Pie | `©2025 Scouting America · Traducción al español` |

**Esto confirma la tesis del plan**: la carta es plantilla + datos. Ninguna carta necesita ser imagen.

## Skins (plantillas)

**5, no 8.** Cubren los 7 mazos completos:

| Skin | Uso |
|---|---|
| `cover` | portada del mazo |
| `legend` | cómo leer las cartas / guía de íconos |
| `resource` | cartas de recurso, borde oscuro |
| `adventure` | portada de Aventura + resumen |
| `activity` | carta de actividad — la que más se ve |

## Bilingüe: cómo se resuelve

La traducción ya viene bilingüe por diseño: conserva los términos de marca en inglés con la glosa en español entre paréntesis, y cada actividad declara su `Actividad original` en inglés. Entonces:

- `content.es` sale del PDF español.
- `content.en` sale del PDF inglés, emparejado por el título original.
- El emparejamiento ES↔EN se hace por `Actividad original` — es la llave natural y ya está en el texto.

**La traducción no es alcance nuestro.** Nosotros tomamos lo que entregan. Si un mazo llega solo en inglés, la app lo muestra en inglés con aviso, no lo esconde.

## Extracción

`pdftotext -layout` sobre los PDF de Drive devuelve todo el contenido en orden y con la estructura intacta. El pipeline es:

```
PDF (Drive) → pdftotext -layout → parser por marcadores → data/decks/<id>.json → revisión humana
```

Marcadores de corte fiables: `REQUISITO n DE N`, `NÚMERO DE REQUISITOS`, `CARTA DE RECURSOS`, `©2025 Scouting America`.

Los QR **no** salen en el texto: hay que extraer los enlaces embebidos del PDF, o pedir la matriz de links que el equipo ya está armando (mencionada en la reunión). Mientras no lleguen, `links: []` y la carta se ve completa igual.

Esto reemplaza el armado a mano en Illustrator (4–5 días por mazo). El costo se va a **revisar** el JSON, no a maquetarlo.
