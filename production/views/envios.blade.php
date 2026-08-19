<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Envíos · Cub Scouts</title>
<meta name="description" content="Las siete barajas de tarjetas de reunión Cub Scout, por rank.">
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

<!-- Modo líder · lo que el brief §7 llama "el líder revisa el engagement".
     Escribe contra la capa simulada; cuando exista backend cambia api.js. -->

<a class="skip-link" href="#main">Saltar al contenido</a>
<header id="pagehead"></header>

<main class="page page--glow" id="main" style="max-width:46rem">
  <div id="lista"></div>
</main>

<footer id="pagefoot"></footer>

<script src="/projects/scouting-america/assets/js/api.js?v=vffaf126a"></script>
<script src="/projects/scouting-america/assets/js/card.js?v=vffaf126a"></script>
<script src="/projects/scouting-america/assets/js/app.js?v=vffaf126a"></script>
<script src="/projects/scouting-america/assets/js/shell.js?v=vffaf126a"></script>
<script>
(async () => {
  // Puerta del prototipo, igual que la consola y el armador. No es seguridad
  // (ver api.js), pero envios.html es una vista de líder y sin esto se abría
  // sola: quedaba fuera de la puerta que el resto de la consola sí tiene.
  if (!API.haySesionLider()) {
    location.replace('/site/lider/entrar.html?volver=' + encodeURIComponent(location.pathname + location.search));
    return;
  }

  // Pedido 20-ago: envios.html es líder-only (ver la puerta arriba) — inglés
  // fijo, igual que el resto de la consola.
  const lang = 'en', es = false;
  const perfil = API.getProfile();
  Shell.mountHeader(document.getElementById('pagehead'), {
    eyebrow: es ? 'Líder de den' : 'Den leader',
    title: es ? 'Lo que has mandado' : 'What you have sent',
    accent: es ? 'has mandado' : 'have sent',
    sub: es
      ? 'Lo que compartiste, en orden. Sin nombres de familias: solo cuántas.'
      : 'What you shared, in order. No family names: just counts.',
    soloIngles: true
  });
  Shell.mountFooter(document.getElementById('pagefoot'));

  // El singular importa: "1 familias" delata que nadie leyó la pantalla.
  const plural = (n, s1, s2, e1, e2) => `${n} ${es ? (n === 1 ? s1 : s2) : (n === 1 ? e1 : e2)}`;

  const cont = document.getElementById('lista');

  // El dato vive en el teléfono, y está bien: es lo acordado para esta fase.
  // Igual conviene decir qué está contando, para que el número no confunda.
  const aviso = `<p style="margin:0 0 var(--space-5); font-size:var(--step--1); color:var(--text-muted)">${es
    ? 'Cuenta las aperturas registradas en este teléfono.'
    : 'Counts opens recorded on this device.'}</p>`;

  try {
    const stats = API.getLeaderStats();
    if (!stats.length) {
      cont.innerHTML = Shell.estado({
        titulo: es ? 'Todavía no has mandado ninguna tarjeta' : 'You have not sent any card yet',
        texto: es
          ? 'Cuando compartas la primera, acá vas a ver cuántas familias la abrieron.'
          : 'Once you share your first one, you will see how many families opened it.',
        accion: `<a class="btn btn--primary" href="/site/lider/index.html">${es ? 'Mandar una tarjeta' : 'Send a card'}</a>`
      });
      return;
    }
    const tot = stats.reduce((a, s) => a + s.uniqueOpens, 0);
    cont.innerHTML = aviso + `
      <div class="progresscard rise" style="--i:0; margin-bottom:var(--space-5)">
        <div class="progresscard__top">
          <div>
            <p class="tile__label" style="color:var(--text-muted); margin:0">${es ? 'Resumen' : 'Summary'}</p>
            <p style="margin:var(--space-1) 0 0">${plural(stats.length,'envío','envíos','send','sends')} · ${plural(tot,'familia','familias','family','families')}</p>
          </div>
          <span class="progresscard__n" data-tick="${tot}">0</span>
        </div>
      </div>` +
      [...stats].reverse().map((s, si) => {
        const f = new Date(s.share.sentAt);
        return `<div class="progresscard rise-io" style="margin-bottom:var(--space-3)">
          <div class="progresscard__top">
            <div>
              <p class="tile__label" style="color:var(--text-muted); margin:0">${
                f.toLocaleDateString(es ? 'es' : 'en', { day: 'numeric', month: 'short' })} · ${s.share.lang.toUpperCase()}</p>
              <p style="margin:var(--space-1) 0 0"${
                // Un envío de mazo se nombra por el mazo ("Pack 77 · 14 cartas");
                // los envíos viejos, de una carta suelta, siguen mostrando su id
                // y su data-card, que es lo que el resolvedor de nombres usa.
                s.share.mazoId ? '' : ` data-card="${s.share.cardId}"`
              }>${(() => {
                if (!s.share.mazoId) return s.share.cardId;
                const m = API.getMazo(s.share.mazoId);
                if (!m) return es ? 'Mazo eliminado' : 'Deleted deck';
                const n = m.cardIds.length;
                return `${m.nombre} · ${n} ${es ? (n === 1 ? 'carta' : 'cartas') : (n === 1 ? 'card' : 'cards')}`;
              })()}</p>
            </div>
            <span class="progresscard__n" data-tick="${s.uniqueOpens}">0</span>
          </div>
          <p style="margin:0 0 var(--space-3); font-size:var(--step--1); color:var(--text-muted)">${
            // La frase tiene que leerse sola: "familia la abrió" sin el número
            // al lado no dice nada, y el número vive en otra esquina de la fila.
            s.uniqueOpens === 0
              ? (es ? 'Todavía no la ha abierto nadie.' : 'Nobody has opened it yet.')
              : es ? `${s.uniqueOpens} ${s.uniqueOpens === 1 ? 'familia la abrió' : 'familias distintas la abrieron'}`
                   : `${s.uniqueOpens} ${s.uniqueOpens === 1 ? 'family opened it' : 'distinct families opened it'}`}</p>
          <button class="linklike" type="button" data-copiar="${s.share.id}">${
            es ? 'Copiar el enlace otra vez' : 'Copy the link again'}</button>
        </div>`;
      }).join('');

    // Contadores que suben al valor real y tarjetas que aparecen al hacer scroll.
    cont.querySelectorAll('[data-tick]').forEach(el =>
      Shell.tick(el, +el.dataset.tick));
    Shell.reveals(cont);

    // Volver a copiar un enlace ya mandado, sin tener que rehacer el envío.
    cont.addEventListener('click', async ev => {
      const b = ev.target.closest('[data-copiar]');
      if (!b) return;
      const envio = JSON.parse(localStorage.getItem('sa:shares') || '[]')
        .find(x => x.id === b.dataset.copiar);
      if (!envio) return;
      try { await navigator.clipboard.writeText(API.shareUrl(envio)); } catch { /* sin permiso */ }
      b.textContent = es ? 'Enlace copiado' : 'Link copied';
      setTimeout(() => { b.textContent = es ? 'Copiar el enlace otra vez' : 'Copy the link again'; }, 2500);
    });

    // Cambiamos el id por el título real de la tarjeta.
    for (const el of cont.querySelectorAll('[data-card]')) {
      try {
        const { card } = await API.getCard(el.dataset.card);
        el.textContent = Card.t(card.title, lang);
      } catch { /* la tarjeta pudo cambiar de baraja */ }
    }
  } catch (err) { App.fail(cont, err); }
})();
</script>
</body>
</html>
