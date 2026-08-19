<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Su progreso · Cub Scouts</title>
<meta name="description" content="Las siete barajas de tarjetas de reunión Cub Scout, por rank.">
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

<!-- Modo familia · lo que Juan pidió en la reunión: "aquí ya he completado
     tantas actividades y me hace falta esta". -->

<a class="skip-link" href="#main">Saltar al contenido</a>
<header id="pagehead"></header>

<main class="page page--glow" id="main" style="max-width:46rem">
  <div id="lista"></div>
</main>

<footer id="pagefoot"></footer>

<script src="/projects/scouting-america-cards/assets/js/api.js?v=vffaf126a"></script>
<script src="/projects/scouting-america-cards/assets/js/card.js?v=vffaf126a"></script>
<script src="/projects/scouting-america-cards/assets/js/app.js?v=vffaf126a"></script>
<script src="/projects/scouting-america-cards/assets/js/shell.js?v=vffaf126a"></script>
<script>
(async () => {
  const lang = API.getLang(), es = lang !== 'en';
  Shell.mountHeader(document.getElementById('pagehead'), {
    eyebrow: es ? 'Familia' : 'Family',
    title: es ? 'Su progreso' : 'Your progress',
    accent: es ? 'progreso' : 'progress',
    sub: es
      ? 'Las actividades que hicieron juntos. Se guarda solo en este teléfono.'
      : 'The activities you did together. Stored only on this phone.'
  });
  Shell.mountFooter(document.getElementById('pagefoot'));

  const cont = document.getElementById('lista');
  cont.innerHTML = Shell.skeletonFilas(3);
  try {
    const decks = await API.getDecks();
    const filas = [];
    // La familia tiene un hijo, no siete. Se muestra su baraja y cualquier
    // otra donde ya hayan hecho algo; las cinco restantes en 0 % eran ruido
    // que enterraba la única fila que le importa.
    const rank = API.getRank();
    for (const d of decks) {
      try {
        const p = await API.getProgress(d.id);
        if (p.total && (p.done || d.id === rank)) filas.push({ d, p });
      } catch { /* baraja sin contenido todavía */ }
    }
    if (!filas.length) {
      cont.innerHTML = Shell.estado({
        titulo: es ? 'Todavía no hay nada que mostrar' : 'Nothing to show yet',
        texto: es
          ? 'Elija la baraja del rank de su hijo y marque «Ya la hicimos» en cada tarjeta que hagan juntos.'
          : 'Pick your child’s rank deck and tap “We did it” on each card you do together.',
        accion: `<a class="btn btn--primary" href="/preview/942">${es ? 'Ver las barajas' : 'See the decks'}</a>`
      });
      return;
    }
    cont.innerHTML = filas.map(({ d, p }) => `
      <div class="progresscard rise-io" style="margin-bottom:var(--space-4)">
        <div class="progresscard__top">
          <div>
            <p class="tile__label" style="color:var(--text-muted); margin:0">${d.name[lang] || d.name.es}</p>
            <p style="margin:var(--space-1) 0 0">${p.done} ${es ? 'de' : 'of'} ${p.total} ${es ? 'actividades hechas' : 'activities done'}</p>
          </div>
          <span class="progresscard__n">${Math.round((p.done / p.total) * 100)}%</span>
        </div>
        <div class="progresscard__bar">
          <div class="progresscard__fill" data-p="${p.done / p.total}" style="--p:0"></div>
        </div>
        <p style="margin:0 0 var(--space-2); font-size:var(--step--1); color:var(--text-muted)">${
          p.done === p.total
            ? (es ? '¡Hicieron todas las actividades de esta baraja!'
                  : 'You have done every activity in this deck!')
            : p.done
              ? (es ? `Faltan ${p.total - p.done}.` : `${p.total - p.done} to go.`)
              : (es ? `Vieron ${p.seen}. Marque «Ya la hicimos» en la tarjeta para que cuente acá.`
                    : `${p.seen} seen. Tap “We did it” on a card so it counts here.`)}</p>
        <p style="margin:0">
          <a class="link-arrow" href="/preview/947?d=${d.id}&lang=${lang}">${es ? 'Seguir' : 'Continue'} →</a>
        </p>
      </div>`).join('');
    // Las barras arrancan en 0 y crecen al valor real una vez pintadas; las
    // tarjetas aparecen al entrar en pantalla.
    requestAnimationFrame(() => requestAnimationFrame(() =>
      cont.querySelectorAll('.progresscard__fill').forEach(el =>
        el.style.setProperty('--p', el.dataset.p))));
    Shell.reveals(cont);
  } catch (err) { App.fail(cont, err); }
})();
</script>
</body>
</html>
