# Modelo de datos

Fuente de verdad del prototipo: archivos JSON en `data/`. En producción los mismos objetos los sirve la API (Capa B) sin cambiar el frontend — ver `production/views/assets/js/api.js`.

## Principio de privacidad

Ningún objeto contiene datos de menores. El papá **no es una entidad**: no tiene registro, ni id persistente en servidor, ni perfil. Lo único que se guarda de su lado es un contador de apertura anónimo ligado al envío, no a él.

## Entidades

Jerarquía real, verificada contra los PDF: **mazo (rango) → aventura → requisito → carta de actividad.** Ver `docs/content-pipeline.md`.

### `deck`
Los 7 mazos son **6 rangos + la baraja de planeación del pack**: `lion`, `tiger`, `wolf`, `bear`, `webelos`, `arrow-of-light`, `pack-planning`. No hay mazo `bobcat` — Bobcat es la primera Aventura dentro de cada rango.

Índice en `data/decks/_index.json`; cada mazo en `data/decks/<id>.json` con `adventures[]` y `cards[]`. Ejemplo real cargado: `data/decks/bear.json`.

`translation: "lista" | "pendiente"` marca si ya llegó la versión en español. Un mazo `pendiente` se muestra en inglés con aviso, no se esconde.

### `adventure`
```json
{ "id": "bobcat",
  "name": { "es": "Bobcat (Gato Montés)", "en": "Bobcat" },
  "category": { "es": "Carácter y Liderazgo", "en": "Character and Leadership" },
  "required": true, "requirementCount": 8,
  "summary": { "es": "…", "en": "…" } }
```

### `card` (skin `activity`)
```json
{
  "id": "bear-a-bobcat-04",
  "skin": "activity",
  "adventureId": "bobcat",
  "requirement": { "index": 2, "of": 8, "text": { "es": "…", "en": "…" } },
  "title": { "es": "Lanzamiento de Bolsitas Bobcat", "en": "Bobcat Beanbag Toss" },
  "originalTitle": "Bobcat Beanbag Toss",
  "place": "indoor",
  "meters": { "energy": 4, "prep": 3, "duration": 3 },
  "description": { "es": "…", "en": "…" },
  "links": [ { "label": { "es": "Ver la actividad", "en": "See the activity" },
               "url": "https://…", "source": "qr" } ]
}
```
- `place`: `indoor` · `outdoor` · `outing` · `indoor-outdoor`.
- `meters`: los tres medidores 1–5 de la baraja impresa (Energía, Preparación, Duración).
- `originalTitle` es la **llave de emparejamiento ES↔EN**: la traducción ya declara "Actividad original: X" en cada carta.
- Los QR impresos se vuelven `links[]` (`source: "qr"`). Sin links la carta se ve completa igual.

### La lámina bilingüe — `images`

Decisión del 14-ago-2026: **la tarjeta bilingüe es otra lámina, no texto traducido.** El selector de idioma cambia la imagen; no se van a producir ochocientas tarjetas editables porque sale más barato una tarjeta nueva que editar en masa.

```json
"images": {
  "en": { "src": "/site/assets/img/cards/bear/p013.webp", "width": 413, "height": 563, "page": 13 },
  "es": { "src": "/site/assets/img/cards/bear/p013-es.webp", "width": 413, "height": 563, "page": 13 }
}
```

`Card.lamina(card, lang)` resuelve cuál toca. Acepta también la forma vieja —`image` suelta con su `lang`— que es la que hay hoy, todas en inglés. Mientras falte la versión en español devuelve la que exista con `esFallback: true`, y la tarjeta lo dice: «Esta tarjeta todavía no tiene versión en español». No se disimula con el texto de abajo, porque lo que la persona vino a ver es la tarjeta.

El texto plegado se queda, pero **ya no es la traducción**: es el contenido accesible. Una imagen no la lee un lector de pantalla, no se puede copiar y en una conexión lenta llega después.

Sobre las tarjetas: **no hay enlace de visualización individual**. Se miran en el visor a pantalla completa (`Card.ampliar`) y se pueden descargar como imagen.

### `skin`
Plantilla visual, no dato. **5 en total** para los 7 mazos: `cover`, `legend`, `resource`, `adventure`, `activity`. Viven en `production/views/partials/skins/`.

### Comunes heredados — `data/common.json`
Disclaimer, marcas del programa, iconografía de categoría, pie de tarjeta. Se editan una vez y aplican a todas las tarjetas.

### `leader`
```json
{ "id": "l-1042", "name": "Marta Rivas", "packId": "p-77", "councilId": "c-05", "lang": "es" }
```
Alimenta la firma automática de la tarjeta. El líder **nunca la escribe a mano**.

### `config` — `data/config.json`
Reglas de negocio que el cliente puede cambiar sin tocar código:
```json
{
  "activation": { "mode": "on-demand", "cardsPerRelease": 1 },
  "rewards": { "enabled": false }
}
```
- `activation.mode`: `on-demand` (hoy — el líder decide cuándo y qué manda) · `daily` (una carta por día) · `scheduled` (calendario). El frontend lee el modo; cambiar de uno a otro no reescribe vistas.
- `rewards.enabled`: hoy `false`. No hay premio digital al completar una baraja; el reconocimiento lo maneja el líder por fuera. La bandera existe para no rehacer la app si mañana lo piden.

### `leader` — la cuenta, y de dónde sale la firma

El nombre y el número de pack que van en cada envío son datos de la **cuenta**, no algo que se le pregunte al líder al entrar. Entrar es identificarse; presentarse ya lo hizo cuando le dieron la cuenta.

En la Capa A viven en `assets/data/lideres.json` y `entrarComoLider(correo)` los resuelve al autenticar. En la Capa B los devolverá el servidor en la misma respuesta del login, y el archivo desaparece.

Un correo que no está en el archivo entra igual —es un prototipo, no un muro— con un nombre provisional derivado del correo y `cuentaConocida: false`. Solo en ese caso la consola ofrece corregir la firma, porque ese nombre es lo que verán los papás.

### `pack` / `council`
```json
{ "id": "p-77", "number": "Pack 77", "councilId": "c-05", "city": "Houston, TX" }
{ "id": "c-05", "name": "Sam Houston Area Council" }
```

### `mazo` — la selección semanal del líder
```json
{
  "id": "mz-9f2a",
  "nombre": "Pack 77",
  "leaderId": "l-1042",
  "packId": "p-77",
  "cardIds": ["lion-04", "bear-a-bobcat-01", "wolf-12"],
  "lang": "es",
  "createdAt": "2026-08-14T09:12:00Z"
}
```
**No confundir con `deck`.** La baraja es contenido oficial de Scouting America (los 6 rangos + la de planeación) y no la arma nadie. El mazo lo crea el líder cada semana: le pone un nombre y elige cartas que pueden venir de **varias barajas**. Es lo que se comparte y lo que abre el papá. Ver el glosario en `CLAUDE.md`.

Sigue sin haber ningún dato del papá ni del niño: un mazo guarda quién lo creó y qué cartas tiene, nada más.

### `share` — un envío
```json
{
  "id": "sh-9f2a",
  "mazoId": "mz-9f2a",
  "leaderId": "l-1042",
  "packId": "p-77",
  "lang": "es",
  "channel": "whatsapp",
  "sentAt": "2026-08-13T14:02:00Z"
}
```
El link que se pega en WhatsApp es `/site/mazo.html?m={mazoId}&s={shareId}&lang=es`. El `shareId` es lo que permite medir sin identificar a nadie.

**Un envío por semana, un link por semana.** Cada mazo tiene su propio id, así que el enlace cambia cada vez que el líder arma uno nuevo — y cambia también cuando al papá le toca otro líder. Consecuencia conocida y sin resolver: un acceso guardado en la pantalla de inicio del teléfono apunta al mazo de esa semana, no "al último que me llegó". Resolverlo requiere identificar al papá, que es MVP Plus.

Los envíos creados antes del 14-ago-2026 llevan `cardId` en vez de `mazoId` y apuntan a una carta suelta. `createShare()` y `shareUrl()` siguen aceptándolos para no invalidar lo que ya está guardado en el teléfono de quien probó el prototipo.

### Qué ve el papá como "recibido" (Capa A)

`getMazosRecibidos()` une dos fuentes:

1. Los mazos que **abrió por enlace** en este teléfono (`mazosRecibidos`).
2. Los mazos que **tienen un envío** guardado en este dispositivo (`shares`).

La segunda existe solo por la Capa A: sin backend, el líder y la familia comparten el mismo `localStorage`, así que un mazo ya enviado está presente aunque nadie haya tocado el enlace. Sin ella, mandar algo y entrar por el ícono mostraba «todavía no le ha llegado nada», que es falso — le llegó, solo que no lo abrió.

**En la Capa B esto se elimina**: el servidor sabrá qué envíos corresponden a este papá y la fuente será una sola. Si se deja, un papá vería los envíos de cualquier líder que haya usado el mismo dispositivo.

### `open` — una apertura
```json
{
  "shareId": "sh-9f2a",
  "at": "2026-08-13T14:07:31Z",
  "lang": "es",
  "device": "mobile",
  "isFirst": true
}
```
Sin IP cruda, sin nombre, sin teléfono, sin id del niño. `isFirst` se resuelve con una marca en el `localStorage` del propio dispositivo.

## Métricas derivadas

- **Tablero del líder:** por `share` → aperturas totales, dispositivos únicos, % en español, curva de las primeras 48 h.
- **Tablero Scouting America:** agregado por `packId` y `councilId` → envíos/semana, tasa de apertura, mazos más usados, packs activos vs. dormidos.

## Frontera con la Capa B

`api.js` expone y es lo único que se reescribe cuando exista backend:

```js
getDecks() · getDeck(id) · getCard(id) · drawCard(deckId, { seen })
getProgress(deckId) · markSeen(cardId)
createShare({ cardId, leaderId, lang }) · trackOpen(shareId)
getLeaderStats(leaderId) · getOrgStats({ packId, councilId })
```
