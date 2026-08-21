<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Leaders · Scouting America Admin</title>
<meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&display=swap" rel="stylesheet">
<link rel="manifest" href="/site/manifest.webmanifest">
<meta name="theme-color" content="#003F87">
<link rel="stylesheet" href="/projects/scouting-america-cards/assets/css/tokens.css?v=v4f83ebeb">
<link rel="stylesheet" href="/projects/scouting-america-cards/assets/css/app.css?v=v4f83ebeb">
</head>
<body data-consola="admin">

<!--
  Los líderes y cómo usan la app.

  Lo que el admin puede hacer acá es VER y DAR DE BAJA. El alta de un líder
  —registro, validación documental, aprobación— quedó fuera del MVP: se marcó
  en la reunión del 20-ago que no está estimada y que hay que validarla con el
  cliente antes de construirla. Se dice en pantalla en vez de dejar un botón
  que no hace nada.
-->

<a class="skip-link" href="#main">Skip to content</a>
<header id="pagehead"></header>

<main class="page page--admin" id="main">
  <div id="contenido"><div class="skel skel--row"></div></div>
</main>

<footer id="pagefoot"></footer>

<script src="/projects/scouting-america-cards/assets/js/api.js?v=v4f83ebeb"></script>
<script src="/projects/scouting-america-cards/assets/js/app.js?v=v4f83ebeb"></script>
<script src="/projects/scouting-america-cards/assets/js/shell.js?v=v4f83ebeb"></script>
<script>
(async () => {
  if (!API.haySesionAdmin()) {
    location.replace('/preview/1002?volver=' + encodeURIComponent(location.pathname));
    return;
  }
  document.documentElement.lang = 'en';

  Shell.mountHeader(document.getElementById('pagehead'), {
    eyebrow: API.getAdmin().organizacion,
    title: 'Den leaders',
    accent: 'leaders',
    sub: 'Who is registered, how much they send and how often it gets opened.',
    compact: true,
    soloIngles: true
  });
  Shell.mountFooter(document.getElementById('pagefoot'));

  const cont = document.getElementById('contenido');
  const pct = v => v === null ? '—' : `${Math.round(v * 100)}%`;
  const fecha = iso => iso
    ? new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
    : '—';

  /* Tres estados, no dos. "Nunca entró" y "lo dimos de baja" son problemas
     distintos para quien administra: uno se resuelve con una llamada, el otro
     es una decisión ya tomada. */
  const estado = l => !l.activo
    ? '<span class="estado estado--baja">Deactivated</span>'
    : l.envios
      ? '<span class="estado estado--activo">Active</span>'
      : '<span class="estado estado--dormido">No activity</span>';

  async function pintar() {
    const lideres = await API.getLideres();
    if (!lideres.length) {
      cont.innerHTML = Shell.estado({
        titulo: 'No leaders yet',
        texto: 'Leader accounts will appear here once they exist.'
      });
      return;
    }

    /* Resumen arriba y barra lateral al costado: en escritorio la tabla sola
       dejaba media pantalla vacía, y el dato que el admin busca primero —quién
       tiene la mejor apertura— había que sacarlo leyendo la columna a ojo,
       porque la tabla ordena por volumen. */
    const conEnvios = lideres.filter(l => l.envios > 0);
    const mejores = [...conEnvios].sort((a, b) => b.indice - a.indice).slice(0, 5);
    const activos = lideres.filter(l => l.activo).length;
    const dormidos = lideres.filter(l => l.activo && !l.envios).length;

    cont.innerHTML = `
      <div class="metrics rise" style="--i:0">
        <div class="metric">
          <span class="metric__label">Registered</span>
          <span class="metric__value" data-tick="${lideres.length}">${lideres.length}</span>
          <p class="metric__note">${activos} active</p>
        </div>
        <div class="metric">
          <span class="metric__label">Sending</span>
          <span class="metric__value" data-tick="${conEnvios.length}">${conEnvios.length}</span>
          <p class="metric__note">Have sent at least one deck</p>
        </div>
        <div class="metric">
          <span class="metric__label">No activity</span>
          <span class="metric__value" data-tick="${dormidos}">${dormidos}</span>
          <p class="metric__note">Active accounts that never sent</p>
        </div>
        <div class="metric">
          <span class="metric__label">Deactivated</span>
          <span class="metric__value" data-tick="${lideres.length - activos}">${lideres.length - activos}</span>
          <p class="metric__note">Access closed by an admin</p>
        </div>
      </div>

      <div class="panel-cols panel-cols--aside rise" style="--i:1">
      <section class="panel panel--tabla">
        <h2 class="page__label">All leaders</h2>
      <div class="tabla-wrap">
        <table class="tabla" role="table">
          <caption class="sr-only">Den leaders and their activity</caption>
          <!-- Los role="..." son explícitos a propósito: en el teléfono el CSS
               cambia el display de la tabla para apilarla en fichas, y eso le
               borra al navegador los roles implícitos. Sin estos, un lector de
               pantalla dejaría de poder recorrerla por filas y columnas. -->
          <thead>
            <tr role="row">
              <th scope="col" role="columnheader">Leader</th>
              <th scope="col" role="columnheader">Status</th>
              <th scope="col" class="num" role="columnheader">Decks sent</th>
              <th scope="col" class="num" role="columnheader">Opened</th>
              <th scope="col" class="num" role="columnheader">Open rate</th>
              <th scope="col" class="num" role="columnheader">Last sent</th>
              <th scope="col" role="columnheader"><span class="sr-only">Actions</span></th>
            </tr>
          </thead>
          <tbody role="rowgroup">
            ${lideres.map(l => `
              <tr role="row">
                <th scope="row" role="rowheader">
                  <span class="tabla__nombre">${l.nombre || l.correo}</span>
                  <span class="tabla__sub">${[l.correo, l.pack].filter(Boolean).join(' · ')}</span>
                </th>
                <td role="cell" data-label="Status">${estado(l)}</td>
                <td class="num" role="cell" data-label="Decks sent">${l.envios}</td>
                <td class="num" role="cell" data-label="Opened">${l.abiertos}</td>
                <td class="num" role="cell" data-label="Open rate">${pct(l.indice)}</td>
                <td class="num" role="cell" data-label="Last sent">${fecha(l.ultimoEnvio)}</td>
                <td class="tabla__accion" role="cell">
                  <button class="linklike" type="button"
                          data-lider="${l.id}" data-activo="${l.activo}">
                    ${l.activo ? 'Deactivate' : 'Reactivate'}
                  </button>
                </td>
              </tr>`).join('')}
          </tbody>
        </table>
      </div>
      </section>

      <aside>
        <section class="panel">
          <h2 class="page__label">Best open rate</h2>
          ${mejores.length ? `<ol class="ranking">
            ${mejores.map((l, i) => `
              <li class="rise-io" style="--i:${i}">
                <span class="ranking__pos">${i + 1}</span>
                <span>
                  <span class="ranking__nombre">${l.nombre || l.correo}</span>
                  <span class="ranking__meta">${l.envios} sent · ${l.abiertos} opened</span>
                </span>
                <span class="ranking__veces">${pct(l.indice)}</span>
              </li>`).join('')}
          </ol>` : '<p class="metric__note">No decks sent yet.</p>'}
          <p class="metric__note" style="margin-top:var(--space-4)">
            Ranked by open rate, not by volume: the table on the left is sorted
            by how much each leader sends.
          </p>
        </section>

        <section class="panel" style="margin-top:var(--space-5)">
          <h2 class="page__label">About this list</h2>
          <p class="metric__note">
            Deactivating a leader closes their access; nothing they already sent
            is deleted, so the numbers stay honest.
          </p>
          <p class="metric__note" style="margin-top:var(--space-3)">
            Leader sign-up is not built yet. Registration needs document checks
            and an approval step, and that flow was not part of this scope — it
            has to be defined with Scouting America first.
          </p>
        </section>
      </aside>
      </div>`;

    // Mismos números que cuentan que en el tablero: una sola forma de que un
    // dato aparezca en toda la consola.
    cont.querySelectorAll('[data-tick]').forEach(el =>
      Shell.tick(el, Number(el.dataset.tick), { formato: n => String(Math.round(n)) }));

    cont.querySelectorAll('[data-lider]').forEach(b => {
      b.addEventListener('click', () => {
        API.setLiderActivo(b.dataset.lider, b.dataset.activo !== 'true');
        pintar();
      });
    });
    Shell.reveals(cont);
  }

  try { await pintar(); } catch (err) { App.fail(cont, err); }
})();
</script>
</body>
</html>
