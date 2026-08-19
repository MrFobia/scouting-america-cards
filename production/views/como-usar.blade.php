<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cómo se usa · Tarjetas de Reunión Cub Scout</title>
<meta name="description" content="Cómo abrir el mazo que le manda el líder de su hijo por WhatsApp, y qué hacer con él.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&display=swap" rel="stylesheet">
<link rel="manifest" href="/site/manifest.webmanifest">
<meta name="theme-color" content="#003F87">
<link rel="apple-touch-icon" href="/projects/scouting-america/assets/img/app-icon-192.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<link rel="stylesheet" href="/projects/scouting-america/assets/css/tokens.css?v=vffaf126a">
<link rel="stylesheet" href="/projects/scouting-america/assets/css/app.css?v=vffaf126a">
</head>
<body>

<!--
  Familia Documento · macroestructura 02 Long Document · nav N9 · footer Ft2

  Entregable del brief (§15): «una guía breve de cómo compartir la tarjeta de la
  semana». Escrita en lenguaje sencillo (§12): frases cortas, sin jerga de producto.
  Los pasos van en prosa con encabezados que emergen del flujo — no en círculos
  numerados en fila, que es el patrón generado más reconocible que existe.
-->

<a class="skip-link" href="#main">Saltar al contenido</a>

<header id="pagehead"></header>

<main class="page page--glow" id="main" style="max-width:46rem">
  <div class="photoband rise" style="--i:0; margin-bottom:var(--space-6)" id="pb">
      <img src="/projects/scouting-america/assets/img/photos/pinewood.webp" alt="" aria-hidden="true"
           width="1200" height="800" loading="eager" decoding="async">
      <span class="photoband__label" id="pb-label">Cub Scouts · en español</span>
      <span class="photoband__title" id="pb-title">Un toque, y ya está</span>
    </div>
    <article class="prose" id="prose"></article>
</main>

<footer id="pagefoot"></footer>

<script src="/projects/scouting-america/assets/js/api.js?v=vffaf126a"></script>
<script src="/projects/scouting-america/assets/js/shell.js?v=vffaf126a"></script>
<script src="/projects/scouting-america/assets/js/card.js?v=vffaf126a"></script>
<script>
/* Pedido 20-ago: esta parada del tab bar la comparten familia y líder, pero
   ya no puede ser el mismo texto para los dos — el líder no necesita que le
   expliquen cómo tocar un enlace de WhatsApp, necesita saber cómo armar y
   mandar el mazo, y ver eso en español lo contradice con el resto de su
   consola. Se resuelve el mismo asLeader que ya usan baraja.html/carta.html,
   y se pinta un artículo distinto entero, no frases sueltas con ternario. */
const asLeader = API.getMode() === 'lider' && API.haySesionLider();
const lang = asLeader ? 'en' : API.getLang();
const es = lang === 'es';
document.documentElement.lang = lang;

document.getElementById('pb-label').textContent = asLeader
  ? 'For den leaders'
  : (es ? 'Cub Scouts · en español' : 'Cub Scouts · in English');
document.getElementById('pb-title').textContent = asLeader
  ? 'Build it, send it, done'
  : (es ? 'Un toque, y ya está' : 'One tap, and that’s it');

Shell.mountHeader(document.getElementById('pagehead'), {
  eyebrow: asLeader ? 'For den leaders' : (es ? 'Cub Scouts · en español' : 'Cub Scouts · in English'),
  title: asLeader ? 'Help' : (es ? 'Cómo se usa' : 'How it works'),
  accent: asLeader ? 'Help' : (es ? 'se usa' : 'it works'),
  sub: asLeader
    ? 'How to build a deck, send it, and see who opened it.'
    : (es ? 'Cómo abrir el mazo que le manda el líder de su hijo, y qué hacer con él. Toma menos de un minuto.'
          : 'How to open the deck your child’s leader sends you, and what to do with it. Takes less than a minute.'),
  soloIngles: asLeader
});
Shell.mountFooter(document.getElementById('pagefoot'));

const PROSE_LIDER = `
  <h2>Build the deck</h2>
  <p>
    From your console, give the week's deck a name and pick 1 to 15 activities —
    you can mix decks, so three from Lion and four from Bear is fine. Nothing
    saves until you confirm.
  </p>

  <h2>Send it on WhatsApp</h2>
  <p>
    One tap sends the deck's link to your pack's group, signed with your name
    and pack. Families don't need the app, an account, or a password — the
    link opens straight in their phone's browser.
  </p>

  <h2>See who opened it</h2>
  <p>
    <a href="/site/envios.html">Sent</a> shows how many families opened each
    deck you sent, without knowing which family is which — same as the app
    shows the family.
  </p>

  <div class="aside">
    <span class="newsitem__icon newsitem__icon--blue">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
           stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M12 3.5 5 6.5v5c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9v-5Z"/>
        <path d="M9.5 12.2l1.8 1.8 3.2-4"/>
      </svg>
    </span>
    <div>
      <p class="aside__title">What we don't ask families for</p>
      <p>
        No account, no password, no child's name. We don't store any data
        about kids — only how many families opened a deck, never who they are.
      </p>
    </div>
  </div>

  <h2>Frequently asked</h2>

  <h3>Why is my console in English only?</h3>
  <p>
    So the same words mean the same thing every time you build a deck. The
    families you send to still get their own language switch when they open
    it — your console being English doesn't change what they see.
  </p>

  <h3>Can I edit a deck after sending it?</h3>
  <p>
    Not yet — delete it from your console and build a new one. The link you
    already sent keeps working; it just won't reflect changes.
  </p>

  <h3>Where does my photo show up?</h3>
  <p>
    Only on the decks you send, next to your name and pack — it's how
    families know the card really came from you.
  </p>
`;

const PROSE_FAMILIA_ES = `
  <h2>Toca el enlace</h2>
  <p>
    Llega al grupo de WhatsApp de la unidad, de parte del líder de tu hijo. Se abre en tu
    teléfono al instante, con las actividades de esta semana. No hay que descargar nada
    ni crear una cuenta.
  </p>

  <h2>Léelas en tu idioma</h2>
  <p>
    Cada tarjeta dice qué actividad van a hacer, cuánta energía y preparación necesita, y
    cuánto dura. Si el líder mandó varias, las pasas con los botones de abajo y siempre
    ves en cuál vas. Arriba a la derecha puedes cambiar entre español e inglés cuando
    quieras.
  </p>

  <h2>Háganla juntos</h2>
  <p>
    Si la tarjeta trae un enlace a la actividad oficial, ahí encuentran el detalle
    completo.
  </p>

  <div class="aside">
    <span class="newsitem__icon newsitem__icon--blue">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
           stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M12 3.5 5 6.5v5c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9v-5Z"/>
        <path d="M9.5 12.2l1.8 1.8 3.2-4"/>
      </svg>
    </span>
    <div>
      <p class="aside__title">Lo que no pedimos</p>
      <p>
        Ni cuenta, ni contraseña, ni el nombre de tu hijo. No guardamos datos de los niños.
        Solo contamos cuántas familias abrieron el mazo, sin saber quién eres.
      </p>
    </div>
  </div>

  <h2>Dudas frecuentes</h2>

  <h3>¿Tengo que descargar una aplicación?</h3>
  <p>
    No. Funciona en el navegador del teléfono. Si quieres, puedes agregarla a tu pantalla
    de inicio y se abre como una app.
  </p>

  <h3>¿Sirve con poca señal?</h3>
  <p>
    Sí. Las páginas son livianas y están pensadas para teléfonos sencillos y datos
    limitados. No hay video ni animaciones que gasten tus datos.
  </p>

  <h3>¿Está todo en español?</h3>
  <p>
    Las barajas traducidas, sí. Las que todavía no llegan traducidas se muestran en inglés
    y quedan marcadas como tal — preferimos decirlo antes que esconderlo.
  </p>

  <h3>¿Por qué algunos nombres siguen en inglés?</h3>
  <p>
    Porque son nombres oficiales del programa: Cub Scout, den, pack, Bobcat. Se dejan en
    inglés a propósito, con una explicación corta en español la primera vez.
  </p>
`;

const PROSE_FAMILIA_EN = `
  <h2>Tap the link</h2>
  <p>
    It arrives in the pack's WhatsApp group, from your child's leader. It opens on your
    phone right away, with this week's activities. Nothing to download, no account to
    create.
  </p>

  <h2>Read them in your language</h2>
  <p>
    Each card says what activity they'll do, how much energy and prep it needs, and how
    long it takes. If your leader sent several, you page through them with the buttons
    below and always know where you are. You can switch between Spanish and English up
    top whenever you want.
  </p>

  <h2>Do it together</h2>
  <p>
    If the card has a link to the official activity, that's where the full detail lives.
  </p>

  <div class="aside">
    <span class="newsitem__icon newsitem__icon--blue">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
           stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M12 3.5 5 6.5v5c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9v-5Z"/>
        <path d="M9.5 12.2l1.8 1.8 3.2-4"/>
      </svg>
    </span>
    <div>
      <p class="aside__title">What we don't ask for</p>
      <p>
        No account, no password, no child's name. We don't store any data about kids.
        We only count how many families opened the deck, without knowing who you are.
      </p>
    </div>
  </div>

  <h2>Frequently asked</h2>

  <h3>Do I have to download an app?</h3>
  <p>
    No. It works in your phone's browser. If you want, you can add it to your home
    screen and it opens like an app.
  </p>

  <h3>Does it work with little signal?</h3>
  <p>
    Yes. The pages are light and built for simple phones and limited data. No video,
    no animations that burn through your data.
  </p>

  <h3>Is everything in Spanish?</h3>
  <p>
    Translated decks, yes. The ones not translated yet show in English and are marked
    as such — we'd rather say so than hide it.
  </p>

  <h3>Why do some names stay in English?</h3>
  <p>
    Because they're official program names: Cub Scout, den, pack, Bobcat. They're left
    in English on purpose, with a short explanation the first time.
  </p>
`;

document.getElementById('prose').innerHTML = asLeader
  ? PROSE_LIDER
  : (es ? PROSE_FAMILIA_ES : PROSE_FAMILIA_EN);

if (!asLeader) {
  const pie = document.createElement('p');
  pie.style.cssText = 'margin-top:var(--space-6); font-size:var(--step--1); color:var(--text-muted)';
  pie.innerHTML = es
    ? '¿Eres líder de den? Esta guía es para las familias — los pasos para armar y mandar el mazo están en <a href="/site/lider/index.html">tu consola</a>.'
    : 'Are you a den leader? This guide is for families — the steps to build and send the deck are in <a href="/site/lider/index.html">your console</a>.';
  document.getElementById('prose').appendChild(pie);
}
</script>
</body>
</html>
