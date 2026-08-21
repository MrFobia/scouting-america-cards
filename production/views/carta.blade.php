<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>La actividad de tu hijo · Cub Scouts</title>
<meta name="description" content="La actividad Cub Scout de esta semana, explicada en español.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&display=swap" rel="stylesheet">
<link rel="manifest" href="/site/manifest.webmanifest">
<meta name="theme-color" content="#003F87">
<link rel="apple-touch-icon" href="/projects/scouting-america-cards/assets/img/app-icon-192.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<link rel="stylesheet" href="/projects/scouting-america-cards/assets/css/tokens.css?v=v4f83ebeb">
<link rel="stylesheet" href="/projects/scouting-america-cards/assets/css/app.css?v=v4f83ebeb">
</head>
<body>

<!--
  Familia Vitrina · macroestructura 08 Photographic · nav N9 · footer Ft2

  Excepción documentada en design.md: Vitrina usa N6 en la portada, pero acá va N9.
  Es la página de llegada desde WhatsApp — un masthead de tres filas gastaría la
  primera pantalla del papá antes de que vea su tarjeta.

  Reglas del brief (§5, §6, §13): abre sin app y sin cuenta, carga rápido en Android
  de gama baja, toma el idioma con el que se compartió, y muestra de quién viene.
  Con ?lider=1 aparece el panel de compartir. El papá nunca lo ve.
-->

<a class="skip-link" href="#main">Saltar al contenido</a>
<header id="pagehead"></header>

<main class="page" id="main" style="padding-top:var(--space-2)">
  <div id="card"><section class="fold" style="background:transparent; border:0"><div class="cardstack"><div class="skel skel--card"></div></div></section></div>

  <section style="margin-top:var(--space-5)">
    <div>
      <div id="share"></div>
      <div id="cta" class="actions"></div>
    </div>
  </section>
</main>

<footer id="pagefoot"></footer>

<script src="/projects/scouting-america-cards/assets/js/api.js?v=v4f83ebeb"></script>
<script src="/projects/scouting-america-cards/assets/js/card.js?v=v4f83ebeb"></script>
<script src="/projects/scouting-america-cards/assets/js/app.js?v=v4f83ebeb"></script>
<script src="/projects/scouting-america-cards/assets/js/shell.js?v=v4f83ebeb"></script>
<script>
(async () => {
  const q = App.params();
  const cardId = q.get('c');
  const shareId = q.get('s');
  // Un enlace compartido (?s=) nunca muestra el panel del líder.
  const asLeader = !q.has('s') && (q.get('lider') === '1' || API.getMode() === 'lider');
  // Pedido 20-ago: consola del líder, inglés fijo — ver mismo comentario en
  // baraja.html.
  const lang = asLeader ? 'en' : API.getLang();
  document.documentElement.lang = lang;
  const es = lang === 'es';

  if (!cardId) {
    document.getElementById('card').innerHTML = Shell.estado({
      titulo: es ? 'Falta un dato' : 'Missing info',
      texto: es ? 'Este enlace no trae el identificador de la tarjeta.' : 'This link is missing the card id.'
    });
    return;
  }

  try {
    const { card, deck } = await API.getCard(cardId);
    const profile = API.getProfile();

    // La personalización viaja en el enlace para sobrevivir a los reenvíos (§9).
    const person = {
      leader: q.get('l') || (asLeader ? profile.leader : ''),
      pack:   q.get('p') || (asLeader ? profile.pack : '')
    };

    document.title = `${Card.t(card.title, lang)} · ${asLeader
      ? (es ? 'Consola del líder' : 'Leader console')
      : (es ? 'La actividad de tu hijo' : 'Your child’s activity')}`;

    const adv = deck.adventures.find(a => a.id === card.adventureId);
    Shell.mountHeader(document.getElementById('pagehead'), {
      eyebrow: `${deck.name[lang] || deck.name.es}${adv ? ' · ' + Card.t(adv.name, lang) : ''}`,
      title: Card.t(card.title, lang),
      sub: card.requirement
        ? `${es ? 'Requisito' : 'Requirement'} ${card.requirement.index} ${es ? 'de' : 'of'} ${card.requirement.of}`
        : '',
      back: `/preview/947?d=${deck.id}&lang=${lang}${asLeader ? '&lider=1' : ''}`,
      crumbs: [
        { label: es ? 'Barajas' : 'Decks', href: `/preview/942?lang=${lang}` },
        { label: deck.name[lang] || deck.name.es, href: `/preview/947?d=${deck.id}&lang=${lang}` },
        ...(adv ? [{ label: Card.t(adv.name, lang), href: `/preview/947?d=${deck.id}&a=${adv.id}&lang=${lang}` }] : []),
        { label: Card.t(card.title, lang) }
      ],
      compact: true,
      soloIngles: asLeader
    });
    Shell.mountFooter(document.getElementById('pagefoot'));

    // La lámina va sobre el campo cálido, entera, sin recortar. Es el LCP.
    document.getElementById('card').innerHTML =
      `<section class="fold" style="background:transparent; border:0">${
        Card.render(card, { deck, lang, person })}</section>`;
    Card.conectarAmpliar(document.getElementById('card'));

    // La apertura se registra después de pintar: primero el papá ve su tarjeta.
    if (shareId && !asLeader) API.trackOpen(shareId, lang);

    /* ---------- panel del líder ----------
       Compartir dejó de vivir acá el 14-ago-2026. El líder ya no manda cartas
       sueltas: arma un mazo con las actividades de la semana y comparte ESE.
       Compartir de a una carta era justamente el error que la revisión marcó,
       así que en vez de dejar los dos caminos abiertos —y que el prototipo
       enseñe el flujo equivocado— esta pantalla lo manda a armar el mazo. */
    if (asLeader) {
      document.getElementById('share').innerHTML = Shell.notice({
        icono: 'info',
        html: `${es
          ? 'Para mandarles esto a los papás, agrégala a un mazo: lo que se comparte es la selección de la semana, no una tarjeta suelta.'
          : 'To send this to families, add it to a deck: what gets shared is the week’s selection, not a single card.'}
          <a href="/preview/953">${es ? 'Armar el mazo' : 'Build the deck'}</a>`
      });
    }

    /* El botón "Ya la hicimos" salió en la revisión del 14-ago-2026: sin login
       el progreso no se puede acumular de verdad, y el SOW solo pide que el
       papá VISUALICE el mazo. La API (markDone/isDone/getProgress) se queda:
       el MVP Plus, que sí identifica al papá, la va a volver a necesitar. */

    /* ---------- salidas · una primaria, el resto enlaces ---------- */
    document.getElementById('cta').innerHTML = asLeader ? `
      <a class="link-arrow" href="/preview/951">
        ${es ? 'Volver a mi consola' : 'Back to my console'} →</a>` : `
      <a class="btn btn--primary" href="/preview/947?d=${deck.id}&lang=${lang}">
        ${es ? 'Ver toda la baraja' : 'See the whole deck'}</a>
      <a class="link-arrow" href="/preview/946?lang=${lang}">
        ${es ? '¿Qué es esto?' : 'What is this?'} →</a>`;

  } catch (err) {
    App.fail(document.getElementById('card'), err);
  }
})();
</script>
</body>
</html>
