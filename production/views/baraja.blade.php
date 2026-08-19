<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Baraja · Cub Scouts</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&display=swap" rel="stylesheet">
<link rel="manifest" href="/site/manifest.webmanifest">
<meta name="theme-color" content="#003F87">
<link rel="apple-touch-icon" href="/projects/scouting-america-cards/assets/img/app-icon-192.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<link rel="stylesheet" href="/projects/scouting-america-cards/assets/css/tokens.css?v=vffaf126a">
<link rel="stylesheet" href="/projects/scouting-america-cards/assets/css/app.css?v=vffaf126a">
</head>
<body>

<!--
  Familia Índice · macroestructura 13 Index-First · nav N9 · footer Ft2
  Dos niveles en un archivo: sin ?a= muestra las Adventures del rank;
  con ?a= muestra las actividades de esa Adventure, agrupadas por requisito.
-->

<a class="skip-link" href="#main">Saltar al contenido</a>
<header id="pagehead"></header>

<main class="page page--glow" id="main">
  <div id="head"></div>
  <div id="content"><div class="skel skel--row" style="margin-bottom:var(--space-3)"></div><div class="skel skel--row"></div></div>
</main>

<footer id="pagefoot"></footer>

<script src="/projects/scouting-america-cards/assets/js/api.js?v=vffaf126a"></script>
<script src="/projects/scouting-america-cards/assets/js/card.js?v=vffaf126a"></script>
<script src="/projects/scouting-america-cards/assets/js/app.js?v=vffaf126a"></script>
<script src="/projects/scouting-america-cards/assets/js/shell.js?v=vffaf126a"></script>
<script>
(async () => {
  const q = App.params();
  const deckId = q.get('d') || 'bear';
  const advId = q.get('a');
  const asLeader = q.get('lider') === '1';
  // Pedido 20-ago: esta parada llega desde el tab bar del líder tanto como de
  // un enlace de familia — la ruta sola no dice quién mira. `?lider=1` sí.
  const lang = asLeader ? 'en' : API.getLang();
  document.documentElement.lang = lang;
  const es = lang === 'es';
  const qs = extra => new URLSearchParams({ lang, ...(asLeader ? { lider: '1' } : {}), ...extra });

  const T = {
    adventures: 'Adventures',
    resources:  es ? 'Cartas de recursos' : 'Resource cards',
    required:   es ? 'Obligatoria' : 'Required',
    elective:   es ? 'Electiva' : 'Elective',
    cards:      es ? 'tarjetas' : 'cards',
    req:        es ? 'Requisito' : 'Requirement',
    of:         es ? 'de' : 'of',
    pending:    es ? 'Esta baraja todavía no está traducida. Se muestra en inglés.'
                   : 'This deck is not translated yet.',
    home:       es ? 'Inicio' : 'Home',
    decks:      es ? 'Barajas' : 'Decks'
  };

  try {
    const deck = await API.getDeck(deckId);
    const deckName = deck.name[lang] || deck.name.es;
    const adv = advId && deck.adventures.find(a => a.id === advId);

    Shell.mountHeader(document.getElementById('pagehead'), {
      eyebrow: adv ? `${deckName} · ${Card.categoria(adv, lang)}` : `Cub Scouts · ${T.decks}`,
      title: adv ? Card.t(adv.name, lang) : deckName,
      sub: adv ? Card.t(adv.summary, lang) : Card.t(deck.intro, lang),
      back: adv ? `/preview/947?${qs({ d: deckId })}` : `/preview/942?${qs()}`,
      crumbs: adv
        ? [{ label: T.decks, href: `/preview/942?${qs()}` },
           { label: deckName, href: `/preview/947?${qs({ d: deckId })}` },
           { label: Card.t(adv.name, lang) }]
        : [{ label: T.decks, href: `/preview/942?${qs()}` },
           { label: deckName }],
      compact: true,
      soloIngles: asLeader
    });
    Shell.mountFooter(document.getElementById('pagefoot'));



    /* ---------- nivel 2 · una Adventure ---------- */
    if (adv) {
      document.getElementById('head').innerHTML = '';

      const cards = deck.cards.filter(c => c.adventureId === adv.id && c.skin === 'activity');
      const byReq = new Map();
      cards.forEach(c => {
        if (!byReq.has(c.requirement.index)) byReq.set(c.requirement.index, []);
        byReq.get(c.requirement.index).push(c);
      });

      document.getElementById('content').innerHTML = [...byReq.entries()]
        .sort((a, b) => a[0] - b[0])
        .map(([idx, list], si) => `
          <section class="rise" style="--i:${Math.min(si, 4)}; margin-top:var(--space-6)">
            <p class="tile__pill" style="margin:0 0 var(--space-3)">${T.req} ${idx} ${T.of} ${list[0].requirement.of}</p>
            <p style="margin:0 0 var(--space-4); max-width:var(--measure)">${Card.t(list[0].requirement.text, lang)}</p>
            <div class="cardgrid">
              ${list.map(c => `
                <a class="deckcard rise-io" href="/preview/954?${qs({ c: c.id })}">
                  ${c.image
                    ? `<img src="${c.image.src}" alt="" aria-hidden="true"
                           width="${c.image.width}" height="${c.image.height}"
                           loading="lazy" decoding="async">`
                    : '<span class="deckcard__ph" aria-hidden="true"></span>'}
                  <span class="deckcard__name">${Card.t(c.title, lang)}</span>
                  <span class="deckcard__meta">${Card.PLACE[c.place]?.[lang] ?? c.place}</span>
                </a>`).join('')}
            </div>
          </section>`).join('');
      // Reveal one-shot de la retícula: entra una vez, no se repite al scrollear.
      Shell.reveals(document.getElementById('content'));
      return;
    }

    /* ---------- nivel 1 · el rank ---------- */
    // El título y la intro ya los pinta Shell.mountHeader(): acá solo el aviso.
    // El aviso de idioma solo aplica si hay contenido que mostrar.
    document.getElementById('head').innerHTML =
      (deck.translation === 'pendiente' && deck.cards.length) ? Shell.notice({ icono: 'traducir', html: T.pending }) : '';

    const activities = deck.cards.filter(c => c.skin === 'activity');
    const resources = deck.cards.filter(c => ['cover', 'legend', 'resource'].includes(c.skin));

    // Baraja sin contenido todavía: estado explicado, no pantalla de error.
    if (!deck.cards.length) {
      document.getElementById('content').innerHTML = Shell.estado({
        titulo: es ? 'Esta baraja está en preparación' : 'This deck is being prepared',
        texto: es
          ? 'Las tarjetas se cargan cuando llega el material del programa. La baraja Bear ya está disponible y funciona igual que esta.'
          : 'Cards load once the program material arrives. The Bear deck is already available and works just like this one.',
        accion: `<a class="btn btn--primary" href="/preview/947?${qs({ d: 'bear' })}">${
          es ? 'Ver la baraja Bear' : 'See the Bear deck'}</a>`
      });
      return;
    }

    document.getElementById('content').innerHTML = `
      ${API.getMode() !== 'lider' ? `
      <section class="rise" style="--i:0; margin-top:var(--space-5)">
        <button class="btn btn--accent btn--block" id="sortear" type="button">
          ${es ? '¿Qué hacemos hoy? Sacar una carta' : 'What today? Draw a card'}
        </button>
        <p style="margin:var(--space-2) 0 0; font-size:var(--step--1); color:var(--text-muted)">
          ${es ? 'Saca una actividad al azar. No repite hasta que las vean todas.'
               : 'Draws a random activity. No repeats until you have seen them all.'}
        </p>
        <div id="sorteada" aria-live="polite" style="margin-top:var(--space-4)"></div>
      </section>` : ''}

      <section class="rise" style="--i:1; margin-top:var(--space-7)">
        <p class="page__label">${T.adventures}</p>
        <ul class="bento">
          ${deck.adventures.map((a, i) => {
            const n = activities.filter(c => c.adventureId === a.id).length;
            const tono = ['tile--navy', 'tile--tan', 'tile--blue', 'tile--pale'][i % 4];
            return `<li>
              <a class="tile ${tono}" href="/preview/947?${qs({ d: deckId, a: a.id })}">
                <span class="tile__label">${Card.categoria(a, lang)}</span>
                <span>
                  <span class="tile__name" style="display:block">${Card.t(a.name, lang)}</span>
                  <span class="tile__label">${n} ${T.cards}</span>
                </span>
              </a></li>`;
          }).join('')}
        </ul>
      </section>

      ${API.getMode() === 'lider' && resources.length ? `
      <section class="rise" style="--i:2; margin-top:var(--space-7)">
        <p class="page__label">${T.resources}</p>
        <div class="cardgrid">
          ${resources.map(c => `
            <a class="deckcard rise-io" href="/preview/954?${qs({ c: c.id })}">
              ${c.image
                ? `<img src="${c.image.src}" alt="" aria-hidden="true"
                       width="${c.image.width}" height="${c.image.height}"
                       loading="lazy" decoding="async">`
                : '<span class="deckcard__ph" aria-hidden="true"></span>'}
              <span class="deckcard__name">${Card.t(c.title, lang)}</span>
            </a>`).join('')}
        </div>
      </section>` : ''}`;
    Shell.reveals(document.getElementById('content'));

    // Sorteo sin repetición — lo que se pidió en la reunión como "I'm feeling lucky".
    const bs = document.getElementById('sortear');
    if (bs) bs.addEventListener('click', async () => {
      const { card, cycled, remaining, total } = await API.drawCard(deckId);
      document.getElementById('sorteada').innerHTML = `
        ${cycled ? Shell.notice({ icono: 'reciclar', html: es
          ? 'Ya vieron todas las actividades de esta baraja. Empezamos otra vuelta.'
          : 'You have seen every activity in this deck. Starting over.' }) : ''}
        <a class="tile tile--navy rise" href="/preview/954?${qs({ c: card.id })}" style="min-height:8rem">
          <span class="tile__label">${es ? 'Les tocó' : 'You got'}</span>
          <span>
            <span class="tile__name" style="display:block">${Card.t(card.title, lang)}</span>
            <span class="tile__label">${Card.PLACE[card.place]?.[lang] ?? ''} ·
              ${es ? 'quedan' : 'left'} ${remaining} ${es ? 'de' : 'of'} ${total}</span>
          </span>
        </a>`;
    });

  } catch (err) {
    App.fail(document.getElementById('main'), err);
  }
})();
</script>
</body>
</html>
