# Scouting America — Tarjetas Cub Scout · Plan de producción

**Cliente:** Scouting America (Eduardo Casas · contacto comercial Charlie)
**Proyecto ClickUp:** MKT - SCOUTING AMERICA · [tarea Brief](https://app.clickup.com/t/90121598611/869egcnqx)
**Presupuesto producción:** USD 4.000 · **Ventana comprometida:** 2–3 semanas (no "la otra semana")
**Plan v2 — 13-ago-2026** (v1 corregida tras leer las barajas reales y el manual de marca)

---

## 1. El problema real

Los papás hispanos no se involucran en Scouting porque el programa comunica en inglés. No entienden qué hace su hijo semana a semana → no ven el valor → no se quedan ni se ofrecen como voluntarios.

El contenido ya existe y ya se está traduciendo (equipo aparte, **fuera de nuestro alcance**). Lo que falta es **cómo le llega al papá y cómo sabemos si lo vio**.

No estamos construyendo una app de contenido. Estamos construyendo un **canal de distribución con medición**.

## 2. Las tres personas

| Persona | Contexto | Éxito |
|---|---|---|
| **Líder / Den leader** | Voluntario, sin tiempo, celular en mano. Hoy reenvía por WhatsApp en 10 s. | Entra, encuentra la tarjeta, la manda. **< 60 s, sin fricción.** |
| **Papá** | Celular sencillo, datos limitados, español, poca familiaridad con Scouting. | Toca un link del grupo de WhatsApp → tarjeta en español **al instante**. Sin descargar, sin cuenta, sin contraseña. |
| **Scouting America** | Eduardo + dirección. | Ver adopción agregada por pack y por council. |

Regla de oro: **el papá nunca crea cuenta.** Toda autenticación vive del lado del líder.

## 3. Lo que descubrimos al abrir el material *(nuevo en v2)*

### Los "7 mazos" no son 7 rangos
Son **6 rangos** (Lion, Tiger, Wolf, Bear, Webelos, Arrow of Light) **+ la baraja de planeación del pack** para líderes. No existe mazo Bobcat: Bobcat es la primera Aventura *dentro* de cada rango.

### Falta contenido en español
Drive tiene los 6 rangos en inglés + la de planeación. En español hay **Bear, Wolf, Tiger, Webelos y Planificación**. **Faltan LION y ARROW OF LIGHT.** Dependencia dura para la fase de contenido — no la resolvemos nosotros, pero bloquea.

### La estructura de la carta es perfectamente regular
Jerarquía: **mazo → Aventura → requisito → carta de actividad**. Cada carta de actividad tiene siempre los mismos campos: paginador de requisitos, `REQUISITO n DE N`, Aventura, categoría, texto del requisito, título ES, `Actividad original` en inglés, lugar (bajo techo / al aire libre / excursión), tres medidores 1–5 (Energía, Preparación, Duración), descripción y QR.

Esto confirma y **mejora** la tesis: no son 6–8 skins, son **5**: `cover`, `legend`, `resource`, `adventure`, `activity`. Cinco plantillas cubren los 7 mazos completos.

### El bilingüe ya viene resuelto en el origen
La traducción conserva los términos de marca en inglés con glosa en español, y cada carta declara su `Actividad original`. Ese campo es la **llave natural de emparejamiento ES↔EN**. No hay que inventar mapeo.

### La marca está medida, no supuesta
Del manual 2024: **Cub Scouts Blue `#003F87` + Cub Scouts Gold `#FDC116`** son la base obligatoria ("should be used heavily"). Tipografía: la guía nombra **Montserrat** como sustituto aprobado de Proxima Nova.

Contraste crítico medido: **dorado sobre blanco da 1.64:1** — no sirve ni para texto grande. El dorado es superficie, no tinta. Azul sobre blanco da 10.19; tinta sobre dorado, 9.38. Detalle completo en `docs/brand.md`.

## 3b. El brief oficial del cliente *(13-ago, `brief_scouting_america_cub_scout_cards_es.docx`)*

Lo firma Eduardo Casas (Director de Multicultural Engagement). Confirma casi todo el plan y agrega cosas que no estaban en el brief interno:

| Tema | Qué dice el brief oficial |
|---|---|
| **Presupuesto** | **USD 10.000** — no 4.000. Incluye diseño, MVP, analítica, QA bilingüe, lanzamiento y habilitación. Estimar por fase. |
| **Cronograma** | Fase 0 discovery y diseño 4–6 semanas · Fase 1 MVP y piloto 8–12 semanas. Muy lejos de "la otra semana". |
| **Volumen real** | ~115–118 tarjetas por mazo, no 232 láminas. Siete mazos ≈ 820 tarjetas. |
| **Audiencias** | Cuatro, no dos: líder tradicional, **líder de Scout Reach** (personal remunerado, listas grandes), papá tradicional y **papá de Scout Reach** (Android viejo o compartido, datos limitados, poca familiaridad con Scouting). |
| **Personalización** | Nombre del líder + número de pack en una **banda** de encabezado o pie, configurada **una sola vez** en el perfil. |
| **Formatos de envío** | Enlace rastreable **+ imagen + PDF**, de una tarjeta o del mazo completo. |
| **Tracking** | Enlaces alojados únicos por envío. Eventos mínimos: `card_shared`, `link_delivered`, `card_opened`, `view_time`, `qr_or_link_tapped`, `language_toggled`. |
| **KPIs** | Unidades activas semanales, rachas del líder, tasa de apertura, **distribución por idioma**, mediana de tiempo hasta la apertura, y **código postal** del líder y del padre. Metas año 1: 50% unidades activas, 40%+ apertura, 45%+ aperturas en español. |
| **Dashboard** | Vista del líder **y vista nacional** por unidad y council. |
| **Idioma** | Español **latino neutro de EE. UU.** |
| **Plataforma** | No la prescriben: piden que la agencia recomiende nativa vs PWA vs ambas, con sesgo explícito a la de menor fricción y menor consumo de datos. Nuestra recomendación (PWA) queda alineada. |
| **Entregables** | Incluyen prototipo interactivo, sistema de diseño bilingüe y **una guía corta de "cómo compartir la tarjeta de la semana"** en ES/EN → implementada en `site/como-usar.html`. |

**Lo que esto cambia en el producto, ya aplicado:** navegación rank → Adventure → tarjeta; perfil del líder con firma automática; panel de compartir con WhatsApp y enlace rastreable; banda de personalización en la tarjeta; guía de uso.

**Lo que falta y no estaba dimensionado:** exportar imagen y PDF por tarjeta y por mazo, dashboard nacional, captura de código postal, y los eventos de analítica completos.

## 4. Alcance

### Sí
1. **Vista del papá** (link público): tarjeta bilingüe, ES por defecto, toggle EN/ES, links de actividad (ex-QR), peso mínimo.
2. **PWA de mazos para el papá**: los 7 mazos, "sacar carta" aleatoria sin repetir, progreso, historial.
3. **Consola del líder**: catálogo, elegir carta, firma automática (nombre + pack), **botón enviar a WhatsApp**.
4. **Tablero del líder**: cuántos papás abrieron lo que mandó.
5. **Tablero Scouting America**: agregado por pack y council.
6. Instalable como PWA, sin tiendas de apps.

### No
- **No traducimos.** Hay una persona encargada; nosotros tomamos lo que entregan.
- No rediseñamos las tarjetas: layout, nombres y marcas del programa se respetan.
- No hay app nativa en esta etapa.
- **No manejamos listas de niños ni datos de menores. Nunca.**
- **No hay premio ni gamificación al completar un mazo** — decisión del cliente. El reconocimiento lo maneja el líder por fuera.

### Decisiones cerradas *(nuevo en v2)*
- **Activación: por demanda.** El líder decide qué carta manda y cuándo. Queda en `data/config.json` como `activation.mode`, con `daily` y `scheduled` ya previstos: cambiar de modo es editar un valor, no reescribir la app.
- **Premio: apagado**, con bandera `rewards.enabled` lista por si lo piden después.

### Pendiente de cliente
- Nº de usuarios activos esperados (líderes y papás) → define el plan de infraestructura.
- Los links de los QR: hace falta la matriz que el equipo está armando. Sin ella la carta se ve completa igual, pero pierde el "ver la actividad".
- LION y ARROW OF LIGHT en español.

## 5. La decisión que sostiene el proyecto: tarjeta = plantilla + datos

Laura reportó **4–5 días por mazo** editando los PDF en Illustrator. Siete mazos ≈ 5–6 semanas solo de armado. No cabe en el presupuesto ni en la ventana.

En vez de digitalizar cada lámina como imagen: **5 skins HTML/CSS + contenido en JSON**. `pdftotext -layout` sobre los PDF de Drive devuelve toda la estructura intacta; el pipeline es `PDF → parser → JSON → revisión humana`. El costo se va a **revisar** el contenido, no a maquetarlo.

Ganancias: responsive real, texto seleccionable, accesible, editable sin Illustrator, y un mazo nuevo es copiar un JSON. El PDF queda como descarga opcional, nunca como visor.

## 6. Realidad técnica (dicho por Fabián, y lo sostenemos)

Backbone AI cubre **frontend y prototipo**. **No cubre backend**: usuarios, sesiones, APIs, persistencia. Es lo que pasó con Redeemers. El plan parte en dos capas:

- **Capa A — Producto navegable (Backbone AI).** Todo el frontend + data layer mockeado (JSON + `localStorage`) que se comporta como la app real: sortea cartas, guarda progreso, cuenta aperturas, pinta tableros con datos sembrados. Demo-able y testeable con usuarios reales.
- **Capa B — Backend real.** Auth de líderes, registro de aperturas, agregación por pack/council. Va en my.business / API propia, **fuera de Backbone AI**, con estimación aparte.

Todo el front habla con `assets/js/api.js`. Hoy responde desde `localStorage`; mañana apunta al endpoint real cambiando `MODE`. Sin reescribir vistas. Ya está escrito.

**Riesgo declarado:** comprometer la Capa B "para la otra semana" es un no. Rango honesto: 2–3 semanas para la Capa A completa.

## 7. Arquitectura de la experiencia

```
WhatsApp del pack
   │  el líder pega un link
   ▼
/c/{cardId}?s={shareId}&lang=es                  ← el papá, sin cuenta
   │  ping de apertura (anónimo, sin PII)
   ├── Tarjeta: qué va a hacer mi hijo, en español
   ├── "Ver la actividad"  (ex-QR → link)
   └── "Tener los 7 mazos en mi teléfono" → instalar PWA
          │
          ▼
   /mazos   → elegir mazo → "Sacar carta" (aleatorio sin repetir) o ver la lista
   /mi-progreso → cuántas hice de este mazo

/lider  (login)  → catálogo · elegir carta · firma automática · Enviar por WhatsApp
/lider/tablero   → aperturas de lo que mandé
/scouting/tablero → agregado por pack y council
```

**Sin JS, la tarjeta del papá se ve igual.** El link de WhatsApp abre HTML servido; JS solo agrega sorteo, progreso e instalación.

## 8. Fases

| Fase | Qué entrega | Estado |
|---|---|---|
| **F0 — Fundaciones** | Paleta medida, tipografía, tokens, guía de estilos **renderizada** | 🟡 tokens y marca listos; falta la guía renderizada |
| **F1 — Sistema de tarjetas** | Los 5 skins + esquema JSON + parser de PDF + un mazo completo | ⚪ esquema validado con cartas reales de Bear |
| **F2 — Vista del papá** | Tarjeta pública, EN/ES, links, ping de apertura, install prompt | ⚪ |
| **F3 — PWA de mazos** | 7 mazos, sorteo sin repetición, progreso, offline | ⚪ |
| **F4 — Consola del líder** | Catálogo, firma automática, enviar a WhatsApp | ⚪ |
| **F5 — Tableros** | Tablero del líder + tablero Scouting America | ⚪ |
| **F6 — Contenido** | Los 7 mazos parseados y revisados | ⚪ bloqueado por LION y AOL en español |
| **F7 — Handoff** | Contrato de API para Capa B, QA a11y/rendimiento, doc | ⚪ |

F0–F5 son Backbone AI. F7 abre la conversación de backend.

## 9. Criterios de aceptación

- Líder: de abrir la app a mensaje enviado en **< 60 s**, ≤ 3 toques.
- Papá: tarjeta pintada en **< 2 s** en 3G simulado; vista < 150 KB sin imágenes.
- Cero pantallas que le pidan cuenta o contraseña al papá.
- WCAG 2.2 AA con contrastes **medidos por script**. Área táctil ≥ 44 px.
- ES por defecto; el toggle persiste.
- El sorteo no repite carta dentro de un mazo hasta agotarlo.
- Ningún dato de menores en ninguna parte del sistema.
- Cambiar `activation.mode` no exige tocar vistas.

## 10. Riesgos

| Riesgo | Mitigación |
|---|---|
| Expectativa de "la otra semana" | Rango 2–3 semanas comunicado; Capa B estimada aparte |
| Backend fuera de Backbone AI | `api.js` como frontera; Capa A funciona sola para demo |
| Armado a mano en Illustrator (4–5 días/mazo) | Parser PDF → JSON; 5 skins |
| **LION y AOL sin traducir** | La app muestra el mazo en inglés con aviso; no bloquea F0–F5 |
| Links de QR sin llegar | `links: []`, la carta se ve completa igual |
| Quirks de la plataforma | Ver `CLAUDE.md`: `@@context`, links a `/preview/{id}`, commit por paso |

## 11. Próximo paso

Arrancar **F0 → F1** en ai.backbone: proyecto standalone + Tailwind + Vanilla JS, subida por git, guía de estilos renderizada y los 5 skins con las cartas reales de Bear ya cargadas.

## Documentos

- `docs/brand.md` — paleta y tipografía extraídas del manual, contrastes medidos
- `docs/content-pipeline.md` — anatomía de las barajas y extracción de PDF
- `docs/data-model.md` — entidades y frontera con la Capa B
- `CLAUDE.md` — reglas del proyecto y quirks de la plataforma
