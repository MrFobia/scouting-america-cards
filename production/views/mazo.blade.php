<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Las actividades de tu hijo · Cub Scouts</title>
<meta name="description" content="Las actividades Cub Scout que su líder mandó esta semana, explicadas en español.">
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
  Familia Vitrina · macroestructura 08 Photographic · nav N9 · footer Ft2

  La página de llegada desde WhatsApp desde la revisión del 14-ago-2026: lo que
  el líder comparte es un MAZO —su selección de la semana— y no una carta suelta.
  carta.html se queda para los enlaces viejos, que siguen circulando en chats.

  Galería, no lista: el papá pasa las cartas una por una y siempre sabe en cuál
  va. Con catorce tarjetas apiladas en vertical, la única forma de saber cuántas
  faltan es hacer scroll hasta el final.
-->

<a class="skip-link" href="#main">Saltar al contenido</a>
<header id="pagehead"></header>

<main class="page" id="main" style="padding-top:var(--space-2)">
  <div id="galeria"><section class="fold" style="background:transparent; border:0"><div class="cardstack"><div class="skel skel--card"></div></div></section></div>
  <!-- Separada de la tarjeta a propósito (pedido 20-ago): antes vivían en el
       mismo bloque que se repintaba entero en cada carta; ahora son dos
       contenedores distintos y solo el de la tarjeta se anima. -->
  <div id="navcard"></div>
</main>

<footer id="pagefoot"></footer>

<script src="/projects/scouting-america-cards/assets/js/api.js?v=vffaf126a"></script>
<script src="/projects/scouting-america-cards/assets/js/card.js?v=vffaf126a"></script>
<script src="/projects/scouting-america-cards/assets/js/app.js?v=vffaf126a"></script>
<script src="/projects/scouting-america-cards/assets/js/shell.js?v=vffaf126a"></script>
<script>
(async () => {
  const lang = API.getLang();
  document.documentElement.lang = lang;
  const es = lang === 'es';
  const q = App.params();
  const mazoId = q.get('m');
  const shareId = q.get('s');
  const galeria = document.getElementById('galeria');

  // Mismo componente que el resto de la app (ícono + texto), no un <p>
  // suelto — Shell ya está cargado, no cuesta nada extra pedírselo.
  const aviso = (titulo, texto, accion = '', tono = 'error') =>
    galeria.innerHTML = Shell.estado({ titulo, texto, accion, tono });

  if (!mazoId) {
    aviso(es ? 'Falta un dato' : 'Missing info',
          es ? 'Este enlace no trae el identificador del mazo.' : 'This link is missing the deck id.');
    return;
  }

  let mazo, cards;
  try {
    ({ mazo, cards } = await API.getMazoCards(mazoId));
  } catch {
    // El mazo vive en el teléfono del líder: si el papá abre el enlace en otro
    // dispositivo, acá no hay nada. Se dice claro en vez de mostrar un error.
    aviso(
      es ? 'No encontramos este mazo' : 'We couldn’t find this deck',
      es ? 'Puede que el enlace sea viejo. Pídele a tu líder que te lo reenvíe.'
         : 'The link may be old. Ask your leader to resend it.',
      `<a class="btn btn--primary" href="/preview/945?lang=${lang}">${es ? 'Ir al inicio' : 'Go home'}</a>`
    );
    return;
  }

  if (!cards.length) {
    aviso(es ? 'Todavía no hay actividades' : 'No activities yet',
          es ? 'Este mazo todavía no tiene actividades cargadas.' : 'This deck has no activities loaded yet.',
          '', 'vacio');
    return;
  }

  // Bug 20-ago: si el enlace no trae ?l=/&p= (p.ej. se volvió a entrar desde
  // el tile de "Esta semana" del home, no desde el WhatsApp original), se cae
  // a lo que quedó guardado la primera vez que se abrió este mismo mazo —
  // así la banda "Te la envía..." no desaparece en la segunda visita.
  const guardada = API.getPersonaMazo(mazoId);
  const person = {
    leader: q.get('l') || guardada?.leader || '',
    pack:   q.get('p') || guardada?.pack   || ''
  };
  const total = cards.length;
  document.title = `${mazo.nombre} · ${es ? 'Las actividades de tu hijo' : 'Your child’s activities'}`;

  /* Bug 20-ago: crumbs=[mazo.nombre] era una sola miga sin link, repetía
     literal el eyebrow de arriba ("2 veces el mismo texto") y no daba
     ninguna salida — el papá quedaba encerrado en la pantalla. Esta es una
     hoja de primer nivel (se llega de WhatsApp, no navegando la app), así
     que lo que corresponde es "back" con flecha, no una miga de pan. */
  Shell.mountHeader(document.getElementById('pagehead'), {
    eyebrow: mazo.nombre,
    title: es ? 'Lo que hace su hijo esta semana' : 'What your child does this week',
    sub: total === 1
      ? (es ? '1 actividad' : '1 activity')
      : `${total} ${es ? 'actividades' : 'activities'}`,
    back: `/preview/945?lang=${lang}`,
    compact: true
  });
  Shell.mountFooter(document.getElementById('pagefoot'));

  // ---- Galería ----------------------------------------------------------
  // El contador NO lleva role=status: shell.js ya monta el aviso de conexión
  // como live region, y dos regiones anunciándose se pisan en el lector de
  // pantalla. aria-live=polite en un id propio alcanza y no compite.
  // Una sola carta en el DOM a la vez: en un Android de gama baja, catorce
  // láminas montadas de golpe cuestan memoria y retrasan el primer pintado,
  // que es justo lo que el brief pide cuidar (§5).
  // La posición viaja en el hash: cambiar de idioma recarga la página y sin
  // esto el papá volvía a la carta 1. En un mazo de catorce, eso es perder el
  // lugar cada vez que toca ES/EN, que es justo lo que la app promete facilitar.
  const desdeHash = parseInt(location.hash.replace('#c', ''), 10);
  let i = Number.isInteger(desdeHash) && desdeHash > 0 && desdeHash <= cards.length
    ? desdeHash - 1 : 0;

  const navcard = document.getElementById('navcard');
  const prefiereMenosMovimiento = () =>
    window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- transición de tarjeta ----------
     Excepción documentada a Capa A (design.md § Motion), acordada 20-ago-2026:
     el resto de esta página sigue sin reveals ni observers, pero pasar de
     carta se pidió explícitamente como un gesto real, no instantáneo — que
     la que sale se sienta ir DETRÁS del mazo (las dos capas rotadas de
     .cardstack::before/::after ya son "el mazo"; la carta actual se une a
     ellas, la siguiente se despega de ahí). Solo transform + opacity, con
     Web Animations API: nunca se anima layout, y con reduced-motion no
     corre nada — el cambio es instantáneo, como antes. */
  const salida = el => new Promise(resolve => {
    if (prefiereMenosMovimiento() || !el.animate) return resolve();
    el.animate([
      { transform: 'translateY(0) rotate(0) scale(1)', opacity: 1 },
      { transform: 'translateY(6px) rotate(-3deg) scale(0.94)', opacity: 0 }
    ], { duration: 180, easing: 'cubic-bezier(0.7,0,0.84,0)', fill: 'forwards' })
      .finished.then(resolve).catch(resolve);
  });
  const entrada = el => {
    if (prefiereMenosMovimiento() || !el.animate) return;
    el.animate([
      { transform: 'translateY(10px) rotate(2deg) scale(0.92)', opacity: 0 },
      { transform: 'translateY(0) rotate(0) scale(1)', opacity: 1 }
    ], { duration: 240, easing: 'cubic-bezier(0.16,1,0.3,1)' });
  };

  // Mismo trazo que el resto de los íconos de la app (viewBox 24, stroke 1.6).
  const CHEVRON_IZQ = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>';
  const CHEVRON_DER = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>';

  /* Rediseño 20-ago: de una tarjeta con barra+dos botones de ancho completo
     (que en mobile se apilaban uno debajo del otro, altísimo) a flechas
     circulares en una sola fila — mismo patrón que un carrusel de fotos,
     nunca se envuelve sin importar el ancho. */
  const pintarNav = () => {
    navcard.innerHTML = total > 1 ? `
      <div class="cardnav">
        <button type="button" class="cardnav__arrow" id="ant"
                aria-label="${es ? 'Actividad anterior' : 'Previous activity'}" ${i === 0 ? 'disabled' : ''}>
          ${CHEVRON_IZQ}
        </button>
        <p class="cardnav__pos" aria-live="polite" aria-atomic="true">
          <strong>${i + 1}</strong><span> / ${total}</span>
        </p>
        <button type="button" class="cardnav__arrow cardnav__arrow--primary" id="sig"
                aria-label="${es ? 'Siguiente actividad' : 'Next activity'}" ${i === total - 1 ? 'disabled' : ''}>
          ${CHEVRON_DER}
        </button>
      </div>` : '';
    const ant = document.getElementById('ant');
    const sig = document.getElementById('sig');
    if (ant) ant.onclick = () => ir(i - 1);
    if (sig) sig.onclick = () => ir(i + 1);
  };

  const pintar = () => {
    const { card, deck } = cards[i];
    const adv = deck.adventures.find(a => a.id === card.adventureId);
    galeria.innerHTML = `
      <p class="tile__label" style="color:var(--text-muted); margin:0 0 var(--space-2)">
        ${deck.name[lang] || deck.name.es}${adv ? ' · ' + Card.t(adv.name, lang) : ''}
      </p>
      <section class="fold" style="background:transparent; border:0">
        ${Card.render(card, { deck, lang, person })}
      </section>
    `;
    Card.conectarAmpliar(galeria);
    // El nav vive pegado a la lámina, ANTES de los botones/banda — se movía
    // "muy abajo" cuando quedaba después de todo el bloque (pedido 20-ago).
    // Es el mismo nodo #navcard de siempre, solo se reubica en el DOM.
    const meta = galeria.querySelector('.card__meta');
    if (meta) meta.before(navcard); else galeria.appendChild(navcard);
    // replaceState y no un salto de hash: no ensucia el historial, así que el
    // botón "atrás" del teléfono sale del mazo en vez de recorrerlo al revés.
    history.replaceState(null, '', location.pathname + location.search + '#c' + (i + 1));
    pintarNav();
  };

  /* Cambia de carta con transición. Si no hay carta montada todavía (primer
     pintado) o el índice no cambia, no hay nada que animar: directo. */
  const ir = async nuevoI => {
    if (nuevoI < 0 || nuevoI >= total || nuevoI === i) return;
    const saliente = galeria.querySelector('.cardstack');
    if (saliente) await salida(saliente);
    i = nuevoI;
    pintar();
    const entrante = galeria.querySelector('.cardstack');
    if (entrante) entrada(entrante);
  };

  pintar();

  /* ---------- deslizar para cambiar de carta ----------
     Pedido 20-ago. Delegado en #galeria (no en la lámina, que se reemplaza
     entera en cada pintar()): el listener se pone una sola vez y sobrevive
     a los repintados. Umbral de 40px y el eje horizontal tiene que ganarle
     claramente al vertical, para no comerse el scroll normal de la página
     ni un toque que tiembla un poco. touch-action:pan-y en el CSS deja que
     el navegador siga scrolleando vertical mientras esto decide el gesto. */
  let touchX = null, touchY = null;
  galeria.addEventListener('touchstart', e => {
    const t = e.touches[0];
    touchX = t.clientX; touchY = t.clientY;
  }, { passive: true });
  galeria.addEventListener('touchend', e => {
    if (touchX === null) return;
    const t = e.changedTouches[0];
    const dx = t.clientX - touchX, dy = t.clientY - touchY;
    touchX = null;
    if (Math.abs(dx) < 40 || Math.abs(dx) < Math.abs(dy) * 1.4) return;
    ir(dx < 0 ? i + 1 : i - 1);
  }, { passive: true });

  // La apertura se registra después de pintar: primero el papá ve su tarjeta.
  // Y NO se registra si quien abre es el líder: revisar el propio envío antes
  // de mandarlo es lo normal, y contarlo inflaría "cuántas familias lo abrieron"
  // justo en la métrica que el líder usa para decidir. carta.html ya lo evitaba.
  if (shareId && !API.haySesionLider()) API.trackOpen(shareId, lang);

  // Para que al volver por el ícono la portada le ofrezca ESTO y no una
  // actividad cualquiera. Solo si es el papá: el líder abre muchos mazos.
  if (!API.haySesionLider()) API.recordarMazo(mazoId, person);

  // Sin salida hacia el catálogo: el papá terminó lo que vino a hacer. Mandarlo
  // a explorar barajas es la sección que salió de su recorrido el 14-ago.
})();
</script>
</body>
</html>
