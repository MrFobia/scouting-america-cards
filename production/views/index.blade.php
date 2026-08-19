<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tarjetas de Reunión Cub Scout · Scouting America</title>
<meta name="description" content="Las tarjetas de actividades de Cub Scouts, en español, para que las familias sepan qué hace su hijo cada semana.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&display=swap" rel="stylesheet">
<link rel="manifest" href="/site/manifest.webmanifest">
<meta name="theme-color" content="#F4F3EF">
<link rel="apple-touch-icon" href="/projects/scouting-america/assets/img/app-icon-192.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<link rel="stylesheet" href="/projects/scouting-america/assets/css/tokens.css?v=vffaf126a">
<link rel="stylesheet" href="/projects/scouting-america/assets/css/app.css?v=vffaf126a">
</head>
<body>

<!--
  Home, capa 2026 (18-ago-2026): hero fotográfico a sangre con esquina
  inferior redondeada —se lee tarjeta flotante, no franja de sitio web—,
  blooms de marca (SOLO paleta, .page--glow) detrás de las cards blancas
  de "Esta semana" y "Últimas noticias", tile del mazo con degradé de los
  dos azules en vez de plano. Misma información, misma funcionalidad;
  motion dentro del presupuesto de Capa B (ver design.md § Motion): entrada
  orquestada + press físico + hover lift, nada más.
-->

<a class="skip-link" href="#main">Saltar al contenido</a>

<main id="main">

  <!-- Aura: dos blooms desenfocados en azul y tan de marca. -->
  <header id="pagehead"></header>

  <!-- Primera visita: hay que saber quién entra. Sin cuenta, sin registro:
       es una preferencia local y se puede cambiar desde el pie. -->

  <!-- Hero editorial: foto a sangre + titular grande. Le da personalidad a la
       portada sin salirse de la paleta ni usar gradientes de moda. -->
  <section class="hero rise" style="--i:0" aria-labelledby="hero-titulo">
    <img src="/projects/scouting-america/assets/img/photos/bicis.webp" alt="" aria-hidden="true"
         width="1200" height="800" loading="eager" decoding="async">
    <span class="hero__eyebrow" id="hero-e">Cub Scouts · en español</span>
    <h1 class="hero__title" id="hero-titulo"><span class="hero__line">Qué hace <em>su hijo</em></span> <span class="hero__line">esta semana</span></h1>
    <p class="hero__sub" id="hero-s">Su líder le manda por WhatsApp las actividades de la semana. Las abre y las hacen juntos. Sin cuenta y sin instalar nada.</p>
    <p class="hero__cta">
      <a class="btn btn--primary" href="/site/como-usar.html" id="hero-a">Cómo funciona →</a>
    </p>
  </section>

  <!-- Bento: lo que el líder mandó esta semana. Nada más. -->
  <section class="page page--glow">

    <h2 class="page__label rise" style="--i:0" id="semana-t">
      Esta semana
    </h2>
    <ul class="bento rise" style="--i:1" id="bento"></ul>

    <section class="rise" style="--i:2; margin-top:var(--space-7)" aria-labelledby="noticias-t">
      <h2 class="page__label" id="noticias-t">Últimas noticias</h2>
      <ul class="newsfeed" id="newsfeed"></ul>
    </section>

  </section>

</main>

<footer id="pagefoot"></footer>

<script src="/projects/scouting-america/assets/js/api.js?v=vffaf126a"></script>
<script src="/projects/scouting-america/assets/js/card.js?v=vffaf126a"></script>
<script src="/projects/scouting-america/assets/js/app.js?v=vffaf126a"></script>
<script src="/projects/scouting-america/assets/js/shell.js?v=vffaf126a"></script>
<script>
(async () => {
  const lang = API.getLang();
  document.documentElement.lang = lang;
  const es = lang === 'es';

  Shell.mountHeader(document.getElementById('pagehead'), { compact: true, sobreFoto: true });
  Shell.mountFooter(document.getElementById('pagefoot'));
  // Bug 20-ago: el href estático nunca llevaba el idioma — en español pasaba
  // desapercibido porque es el default, pero en inglés mandaba de vuelta a
  // la Guía en español sin avisar.
  document.getElementById('hero-a').href = `/site/como-usar.html?lang=${lang}`;
  if (!es) {
    document.getElementById('hero-e').textContent = 'Cub Scouts · in English';
    document.getElementById('hero-titulo').innerHTML = '<span class="hero__line">What <em>your child</em></span> <span class="hero__line">does this week</span>';
    document.getElementById('hero-s').textContent = 'Their den leader sends the week’s activities on WhatsApp. You open them and do them together. No account, nothing to install.';
    document.getElementById('hero-a').textContent = 'How it works →';
    document.getElementById('semana-t').textContent = 'This week';
    document.getElementById('noticias-t').textContent = 'Latest news';
  }

  /* No hay pantalla de "¿cómo va a usar la app?". El brief no la contempla y
     el recorrido que describe la excluye: el padre «abre una tarjeta al
     instante, sin instalar nada y sin iniciar sesión» —llega por el enlace de
     WhatsApp, no elige rol— y el líder «abre la plataforma», que desde el
     14-ago significa entrar a su consola con usuario y contraseña.
     Preguntarle a un papá qué rol tiene es fricción que el brief pide evitar,
     y su respuesta correcta es siempre la misma. */

  const bento = document.getElementById('bento');
  const skel = n => Array.from({ length: n },
    () => '<li><div class="skel" style="min-height:10.5rem;border-radius:var(--radius-card)"></div></li>').join('');
  bento.innerHTML = skel(3);

  // Un color de marca por rank. Coherentes entre sí, sin recortes de foto.
  const TONO = {
    lion: 'tile--tan', tiger: 'tile--blue', wolf: 'tile--pale', bear: 'tile--navy',
    webelos: 'tile--tan', 'arrow-of-light': 'tile--blue', 'pack-planning': 'tile--red'
  };

  try {
    // El rank guardado sirve para ordenar la lista de barajas de abajo. Ya no
    // se usa para elegir qué mostrar arriba: arriba va lo que mandó el líder.
    const decks = await API.getDecks();
    const rank = API.getRank();

    /* Lo que ve el papá bajo "Esta semana" es EL MAZO QUE SU LÍDER LE MANDÓ.
       Nada más. No hay "tarjeta sugerida": el acta del 14-ago es explícita
       —«la que me llegó esta semana» / «la que me asignaron»—, así que una
       actividad elegida por el sistema bajo ese rótulo es una promesa falsa.
       Si todavía no le llegó nada, se dice; no se rellena con contenido que
       nadie envió. */
    const ultimoId = API.getUltimoMazo();
    const mazo = ultimoId ? API.getMazo(ultimoId) : null;
    const nCartas = mazo ? mazo.cardIds.length : 0;

    bento.innerHTML = mazo ? `
      <li class="is-wide">
        <a class="tile tile--navy is-wide" href="/site/mazo.html?m=${mazo.id}&lang=${lang}">
          <span class="tile__label">${es ? 'De su líder' : 'From your leader'}</span>
          <span class="tile__name">${mazo.nombre}</span>
          <span class="tile__label">${nCartas} ${es
            ? (nCartas === 1 ? 'actividad' : 'actividades')
            : (nCartas === 1 ? 'activity' : 'activities')}</span>
        </a>
      </li>
` : `
      <li class="is-wide">
        <div class="tile is-wide vacio">
          <span class="newsitem__icon newsitem__icon--blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M12 3.5a8 8 0 0 0-6.9 12.03L4.5 20l4.6-.6A8 8 0 1 0 12 3.5Z"/>
              <path d="M9 12h6M9 15h4"/>
            </svg>
          </span>
          <span>
            <span class="tile__label" style="color:var(--text-muted)">${es ? 'Todavía no le ha llegado nada' : 'Nothing yet'}</span>
            <p class="vacio__texto">${es
              ? 'Su líder le enviará las actividades por WhatsApp. Cuando toque el enlace, aparecen aquí.'
              : 'Your leader will send the week’s activities on WhatsApp. Once you tap the link, they show up here.'}</p>
          </span>
        </div>
      </li>
`;

    /* "Sus tarjetas"/"Your cards" salió del home el 20-ago: era un contador
       contra el catálogo OFICIAL completo de la baraja (p.ej. "2/90"), y para
       una familia ese 90 no significa nada — no es cuánto le mandó el líder,
       es cuánto contenido existe en todo el programa. Lo único que a la
       familia le importa es lo que YA le llegó, y eso ya lo cubre "Esta
       semana" arriba. Mostrar además una barra de progreso contra un total
       que la familia nunca va a completar (ni tiene por qué) confundía más
       de lo que ayudaba.

       La lista de barajas salió del home de la familia (acta del 14-ago:
       «¿para qué el papá necesita conocerlas?», sin respuesta). El papá viene a
       ver lo que su líder le mandó; el catálogo completo es material del líder,
       que sí lo necesita para armar el mazo. barajas.html sigue existiendo para
       él y para quien llegue por un enlace directo. */

  } catch (err) {
    App.fail(bento, err);
  }

  /* ---------- Últimas noticias ----------
     Anuncios del equipo, no del líder. Mismo trazo de ícono que el resto
     de la app (viewBox 24, stroke 1.6) para no meter una segunda familia. */
  const NEWS_ICON = {
    sparkle:   '<path d="M12 4v4M12 16v4M4 12h4M16 12h4M6.5 6.5l2 2M15.5 15.5l2 2M17.5 6.5l-2 2M8.5 15.5l-2 2"/>',
    translate: '<path d="M4 5.5h9M8.5 3.5v2M6 5.5c.4 3 2.2 5.4 5 6.8M11 5.5c-.7 3.4-3 6.2-6.5 8"/><path d="M13 20.5l4-9 4 9M14.6 17.5h4.8"/>',
    book:      '<path d="M4 5.5c2.2-1 5-1 7 0v13c-2-1-4.8-1-7 0Zm16 0c-2.2-1-5-1-7 0v13c2-1 4.8-1 7 0Z"/>',
    bell:      '<path d="M12 4a5 5 0 0 0-5 5c0 4.5-1.5 6-1.5 6h13S17 13.5 17 9a5 5 0 0 0-5-5Z"/><path d="M10 19a2 2 0 0 0 4 0"/>'
  };
  const NEWS_TONE = { sparkle: 'blue', translate: 'tan', book: 'navy', bell: 'red' };
  const newsfeed = document.getElementById('newsfeed');
  const newsSkel = n => Array.from({ length: n },
    () => '<li><div class="skel" style="min-height:5.5rem;border-radius:var(--radius-card)"></div></li>').join('');
  newsfeed.innerHTML = newsSkel(3);

  try {
    const noticias = await API.getNoticias();
    const fmtFecha = d => new Intl.DateTimeFormat(es ? 'es' : 'en', { day: 'numeric', month: 'short' }).format(new Date(d + 'T12:00:00'));

    newsfeed.innerHTML = noticias.map(n => `
      <li>
        <div class="newsitem">
          <span class="newsitem__icon newsitem__icon--${NEWS_TONE[n.icon] || 'blue'}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${NEWS_ICON[n.icon] || NEWS_ICON.sparkle}</svg>
          </span>
          <span class="newsitem__body">
            <span class="newsitem__cat">${n.category[lang] || n.category.es}</span>
            <p class="newsitem__title">${n.title[lang] || n.title.es}</p>
            <p class="newsitem__excerpt">${n.excerpt[lang] || n.excerpt.es}</p>
          </span>
          <span class="newsitem__meta">
            <span class="newsitem__date">${fmtFecha(n.date)}</span>
          </span>
        </div>
      </li>
    `).join('');
  } catch (err) {
    App.fail(newsfeed, err);
  }
})();
</script>
</body>
</html>
