/**
 * app.js — piezas compartidas: barra superior, toggle de idioma, utilidades de URL.
 * El idioma es global y persiste; el papá lo elige una vez y no lo vuelve a tocar.
 */
const App = (() => {

  const params = () => new URLSearchParams(location.search);

  /**
   * N9 · Edge-aligned. Marca a la izquierda, idioma a la derecha, nada en el medio.
   * El vacío es el diseño: llenarlo con enlaces lo convierte en otra barra genérica.
   * `back` es un href opcional — se pinta como texto, no como flecha suelta, porque
   * una flecha sola no tiene área táctil ni nombre accesible.
   */
  function mountTopbar(el, { title, back } = {}) {
    const lang = API.getLang();
    const volver = lang === 'es' ? 'Volver' : 'Back';
    el.className = 'nav-edge';
    el.innerHTML = `
      <a class="nav-edge__brand" href="${back ?? '/site/index.html'}">
        <img class="nav-edge__logo" src="/projects/scouting-america/assets/img/scouting-america-signature.png"
             alt="Scouting America" width="900" height="110">
        <span class="nav-edge__title">${title ?? 'Cub Scouts'}</span>
      </a>
      <div style="display:flex; align-items:center; gap:var(--space-4)">
        ${back ? `<a class="nav-edge__back" href="${back}">← ${volver}</a>` : ''}
        <div class="lang" role="group" aria-label="Idioma / Language">
          <button class="lang__btn" type="button" data-lang="es"
                  aria-pressed="${lang === 'es'}">ES</button>
          <button class="lang__btn" type="button" data-lang="en"
                  aria-pressed="${lang === 'en'}">EN</button>
        </div>
      </div>`;

    el.querySelectorAll('.lang__btn').forEach(btn => {
      btn.addEventListener('click', () => {
        API.setLang(btn.dataset.lang);
        // Reflejamos el idioma en la URL para que el link siga siendo compartible.
        const url = new URL(location.href);
        url.searchParams.set('lang', btn.dataset.lang);
        location.replace(url);
      });
    });
  }

  /** Un error siempre lleva salida: qué pasó y qué hacer. Nunca una pantalla muerta. */
  function fail(el, err) {
    console.error(err);
    const es = API.getLang() !== 'en';
    const sinRed = !navigator.onLine;
    const titulo = sinRed
      ? (es ? 'Sin conexión' : 'Offline')
      : (es ? 'No se pudo cargar' : 'Could not load');
    const texto = sinRed
      ? (es ? 'Cuando vuelvas a tener señal, esto carga solo.'
            : 'This will load once you are back online.')
      : (es ? 'Algo falló de nuestro lado. Puedes intentar otra vez.'
            : 'Something failed on our side. You can try again.');
    const accion = `<button class="btn btn--primary" type="button" onclick="location.reload()">${
      es ? 'Intentar de nuevo' : 'Try again'}</button>`;
    el.innerHTML = window.Shell
      ? Shell.estado({ titulo, texto, accion, tono: 'error' })
      : `<p class="notice">${titulo}. ${texto}</p>`;
  }

  return { params, mountTopbar, fail };
})();

if (typeof window !== 'undefined') window.App = App;
