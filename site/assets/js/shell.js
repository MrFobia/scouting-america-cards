/**
 * shell.js — el armazón de la app.
 *
 * Convierte un sitio de páginas sueltas en algo que se usa como app:
 *   · navegación persistente al alcance del pulgar (abajo en el teléfono,
 *     arriba en pantallas grandes — un solo DOM, dos posiciones)
 *   · estado activo visible, que es lo que te dice dónde estás
 *   · aviso de sin conexión
 *   · instalación en la pantalla de inicio, ofrecida y no rogada
 *   · esqueletos de carga, para no dejar la pantalla congelada
 *
 * Cuatro destinos, no cinco. Todos de primer nivel. La navegación secundaria
 * (una Adventure, una tarjeta) vive en las migas de pan, no acá.
 */
const Shell = (() => {

  // Iconos propios: una sola familia, trazo 1.6, caja de 24. Sin librería,
  // sin emoji. Los emoji cambian de forma según el teléfono y no se pueden
  // teñir con los tokens de marca.
  const ICON = {
    inicio: '<path d="M3 10.5 12 3l9 7.5"/><path d="M5.5 9.5V20h13V9.5"/><path d="M9.5 20v-5.5h5V20"/>',
    enviar: '<path d="M4.5 12 20 4.5 15.5 20l-4-6.5-7-1.5Z"/>',
    envios: '<path d="M3.5 6.5h17v11h-17z"/><path d="m3.5 7.5 8.5 6 8.5-6"/>',
    barajas: '<rect x="3.5" y="6.5" width="11" height="14" rx="1"/><path d="M7.5 3.5h9a1 1 0 0 1 1 1v12"/>',
    guia: '<circle cx="12" cy="12" r="8.5"/><path d="M9.6 9.4a2.5 2.5 0 1 1 3.4 2.3c-.6.3-1 .9-1 1.6v.4"/><path d="M12 17.2h.01"/>',
    lider: '<circle cx="12" cy="8" r="3.5"/><path d="M4.5 20a7.5 7.5 0 0 1 15 0"/>',
    tablero: '<path d="M3.5 19.5h17"/><rect x="5" y="11" width="3.5" height="6" rx=".6"/><rect x="10.2" y="7" width="3.5" height="10" rx=".6"/><rect x="15.4" y="13" width="3.5" height="4" rx=".6"/>',
    lideres: '<circle cx="9" cy="9" r="3"/><path d="M3 19a6 6 0 0 1 12 0"/><path d="M16 6.3a3 3 0 0 1 0 5.4M17.5 19a6 6 0 0 0-2-4.5"/>'
  };

  // Progreso salió el 14-ago-2026: sin login no se puede acumular y el SOW
  // solo pide que el papá visualice el mazo. La vista queda en disco, fuera
  // de la navegación, porque el MVP Plus la retoma.
  // "Barajas" salió de la navegación de la familia el 18-ago-2026: el catálogo
  // completo es material del líder para armar el mazo, no algo que el papá
  // necesite recorrer. barajas.html/baraja.html/carta.html siguen en disco
  // para quien llegue por un enlace directo, solo que sin tab que los anuncie.
  const TABS_FAMILIA = [
    { id: 'inicio',  href: '/site/index.html',     es: 'Inicio',  en: 'Home',  match: ['/site/index.html', '/site/mazo.html'] },
    { id: 'guia',    href: '/site/como-usar.html', es: 'Guía',    en: 'Help',  match: ['/site/como-usar.html'] }
  ];

  const TABS_LIDER = [
    { id: 'enviar',  href: '/site/lider/index.html', es: 'Enviar',  en: 'Send',
      // Páginas explícitas, no el prefijo '/site/lider/': en ai.backbone cada
      // vista se sirve en /preview/{id} y no hay carpeta que prefijar, así que
      // el prefijo no casaba nunca y la pestaña no se encendía.
      match: ['/site/lider/index.html', '/site/lider/mazo-nuevo.html', '/site/lider/perfil.html'] },
    { id: 'barajas', href: '/site/barajas.html',       es: 'Barajas', en: 'Decks',  match: ['/site/barajas.html', '/site/baraja.html', '/site/carta.html'] },
    { id: 'envios',  href: '/site/envios.html',      es: 'Envíos',  en: 'Sent',   match: ['/site/envios.html'] },
    { id: 'guia',    href: '/site/como-usar.html',   es: 'Guía',    en: 'Help',   match: ['/site/como-usar.html'] }
  ];

  /* La consola del ADMIN (Scouting America) es una tercera navegación, no una
     variante de la del líder: son dos personas distintas con dos trabajos
     distintos. El admin no arma mazos ni los manda — mira el programa entero.
     Va siempre en inglés, mismo criterio que la consola del líder. */
  const TABS_ADMIN = [
    { id: 'tablero', href: '/site/admin/index.html',   es: 'Tablero', en: 'Overview', match: ['/site/admin/index.html'] },
    { id: 'lideres', href: '/site/admin/lideres.html', es: 'Líderes', en: 'Leaders',  match: ['/site/admin/lideres.html'] },
    { id: 'lider',   href: '/site/admin/perfil.html',  es: 'Perfil',  en: 'Profile',  match: ['/site/admin/perfil.html'] }
  ];
  /* Tres paradas, no cinco. "Barajas" y "Guía" salieron el 20-ago: el catálogo
     y el instructivo son material del LÍDER —quien arma y manda mazos—, y el
     admin no hace ninguna de las dos cosas. Ofrecerle esas dos puertas era
     llenarle la barra con el trabajo de otro. En su lugar entra su perfil, que
     es lo único suyo que le faltaba. */

  const svg = paths =>
    `<svg class="tab__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
          stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
          aria-hidden="true" focusable="false">${paths}</svg>`;

  function activo(tab, path) {
    // Una ruta que termina en "/" casa por prefijo (la consola y sus hijas);
    // el resto casa exacto, para que Inicio no se encienda en toda la app.
    return tab.match.some(m => m.endsWith('/') ? path.startsWith(m) : path === m);
  }

  function montarTabs(lang, modo) {
    if (document.querySelector('.tabbar')) return;
    const es = lang !== 'en';
    const path = location.pathname;
    const TABS = modo === 'admin' ? TABS_ADMIN
              : modo === 'lider' ? TABS_LIDER : TABS_FAMILIA;

    const nav = document.createElement('nav');
    nav.className = 'tabbar';
    nav.setAttribute('aria-label', es ? 'Navegación principal' : 'Primary');
    nav.innerHTML = `<ul class="tabbar__list">${TABS.map(t => {
      const on = activo(t, path);
      return `<li>
        <a class="tab" href="${t.href}"${on ? ' aria-current="page"' : ''}>
          ${svg(ICON[t.id])}
          <span class="tab__label">${es ? t.es : t.en}</span>
        </a></li>`;
    }).join('')}</ul>`;

    // Va justo después de la cabecera, no al final del body: en el teléfono se
    // fija abajo igual (position: fixed ignora el orden del DOM), y en pantalla
    // grande queda en flujo, pegada bajo la cabecera, que es donde corresponde.
    const cabecera = document.querySelector('header');
    if (cabecera) cabecera.after(nav); else document.body.prepend(nav);
    document.body.classList.add('has-tabbar');
  }

  /* ---------- sin conexión ---------- */

  function montarAvisoRed(lang) {
    const es = lang !== 'en';
    const aviso = document.createElement('p');
    aviso.className = 'netbar';
    aviso.setAttribute('role', 'status');
    aviso.hidden = navigator.onLine;
    aviso.textContent = es
      ? 'Sin conexión. Estás viendo lo que ya se había guardado.'
      : 'Offline. You are seeing previously saved content.';
    document.body.prepend(aviso);

    addEventListener('offline', () => { aviso.hidden = false; });
    addEventListener('online',  () => { aviso.hidden = true; });
  }

  /* ---------- instalación ---------- */

  function montarInstalacion(lang) {
    const es = lang !== 'en';
    let prompt = null;

    addEventListener('beforeinstallprompt', e => {
      // Se ofrece, no se ruega: un botón discreto, y si lo rechazan no vuelve.
      e.preventDefault();
      prompt = e;
      if (localStorage.getItem('sa:install-dismissed')) return;
      // No en la primera visita: aparecía tapando la pantalla de bienvenida,
      // antes de que el papá supiera qué es la app que le piden instalar.
      const visitas = Number(localStorage.getItem('sa:visitas') || 0) + 1;
      localStorage.setItem('sa:visitas', String(visitas));
      if (visitas < 2) return;

      const barra = document.createElement('div');
      barra.className = 'installbar';
      barra.innerHTML = `
        <p class="installbar__text">${es
          ? 'Ténla en tu pantalla de inicio. No ocupa casi nada.'
          : 'Add it to your home screen. It barely takes any space.'}</p>
        <button class="btn btn--primary" type="button" data-act="add">${es ? 'Agregar' : 'Add'}</button>
        <button class="installbar__close" type="button" data-act="no"
                aria-label="${es ? 'Ahora no' : 'Not now'}">✕</button>`;
      document.body.append(barra);

      barra.addEventListener('click', async e => {
        const act = e.target.closest('[data-act]')?.dataset.act;
        if (!act) return;
        if (act === 'add' && prompt) { prompt.prompt(); await prompt.userChoice; }
        if (act === 'no') localStorage.setItem('sa:install-dismissed', '1');
        barra.remove();
      });
    });
  }

  /* Avatar del líder en la cabecera → su perfil. Antes "cerrar sesión" solo
     vivía en el pie de página: cierto, pero nadie busca la cuenta ahí abajo.
     Con foto si ya subió una; si no, la inicial de su nombre sobre azul de
     marca, mismo lenguaje que los badges de ícono del resto de la app. */
  function avatarLink(es) {
    const p = API.getProfile();
    const inicial = (p.leader || '?').trim().charAt(0).toUpperCase();
    return `
      <a class="avatar avatar--sm" href="${(window.API && API.haySesionAdmin())
         ? '/site/admin/index.html' : '/site/lider/perfil.html'}"
         aria-label="${es ? 'Tu perfil' : 'Your profile'}">
        ${p.photo
          ? `<img src="${p.photo}" alt="">`
          : inicial}
      </a>`;
  }

  /* Pedido 20-ago (Carlos y equipo): el ingreso del líder vivía solo en el
     pie de cada página — nadie "estudia toda la página" buscando dónde
     loguearse. Sube a la cabecera, junto al selector de idioma, visible sin
     scroll. Solo aparece sin sesión: con sesión abierta ya está el avatar. */
  function loginLink(es) {
    return `
      <a class="avatar avatar--sm avatar--empty" href="/site/lider/entrar.html"
         aria-label="${es ? 'Líder: entra a tu consola' : 'Leader: sign in'}">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
             stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
             aria-hidden="true" focusable="false">${ICON.lider}</svg>
      </a>`;
  }

  /* ---------- cabecera de página ----------
     UNA sola cabecera para toda la app. Antes el home tenía aura + titular
     grande y las internas una barra blanca distinta: parecían dos productos.
     Cualquier página que quiera cabecera llama a esto y nada más. */

  function mountHeader(el, { eyebrow, title, accent, sub, crumbs, compact, back, sobreFoto, soloIngles, tinte } = {}) {
    // Pedido 20-ago: la consola del líder es en inglés siempre, sin selector —
    // solo el papá elige idioma. Toda página bajo /site/lider/ ya cae acá sola
    // por la ruta. Pero cuatro paradas del tab bar del líder (Barajas, Envíos,
    // Guía) viven en archivos COMPARTIDOS con la familia —barajas.html,
    // envios.html, como-usar.html— así que esas páginas mandan `soloIngles`
    // explícito según su propio `asLeader`, y esto no puede adivinarlo por ruta.
    /* Qué consola es esto NO se deduce de la ruta. En local las páginas viven
       en /site/admin/ y /site/lider/, pero en ai.backbone cada vista se sirve
       en /preview/{id} y esos `startsWith` daban false: la consola del líder
       salía con selector de idioma y con el pie de familia. La página lo
       declara en el <body data-consola="...">, que viaja con el marcado. */
    const consola = document.body.dataset.consola || '';
    const esConsolaAdmin = consola === 'admin' || (window.API && API.haySesionAdmin());
    const esConsolaLider = soloIngles ?? (esConsolaAdmin || consola === 'lider');
    const lang = esConsolaLider ? 'en' : ((window.API && API.getLang()) || 'es');
    const es = lang !== 'en';
    // El idioma del documento se fija acá: corre en todas las pantallas.
    document.documentElement.lang = lang;

    const titulo = accent
      ? String(title).replace(accent, `<em>${accent}</em>`)
      : title;

    // sobreFoto: SOLO el home (pedido 19-ago). El componente sigue siendo
    // el mismo de siempre —mismo DOM, mismas clases base—, esto es un
    // modificador, no una cabecera nueva (design.md: "un solo componente
    // de cabecera", enmienda 13-ago). Flota transparente sobre la foto del
    // hero en vez de una franja propia; el manual de marca exige blanco
    // reversado sobre fondo oscuro (pág. 11: "Do not reproduce in color on
    // a dark background" / "Reversed: white must be used") — no es un
    // capricho de color, es la única forma permitida ahí.
    // tinte: la interna de una baraja (pedido 20-ago, 3.ª pasada). El color del
    // rank deja de ser una imagen debajo del texto y pasa a ser EL FONDO de la
    // cabecera, con la información encima. Otro modificador del mismo
    // componente, igual que sobreFoto — no una cabecera nueva.
    //
    // `tinte.on` NO se elige acá: viene medido desde el PDF del estuche
    // (render_deck_art.py, WCAG 2.2). Sobre el dorado del Lion el texto va
    // oscuro; sobre el rojo del Wolf, blanco. Elegirlo a ojo en el CSS era
    // garantizar que un rank quedara ilegible.
    const tintaClara = tinte && tinte.on === '#FFFFFF';
    el.className = 'aura pagehead'
      + (compact ? ' aura--compact' : '')
      + (sobreFoto ? ' pagehead--sobre-foto' : '')
      + (tinte ? ' pagehead--tinte' : '');
    if (tinte) {
      el.style.setProperty('--deck-ink', tinte.ink);
      el.style.setProperty('--deck-dot', tinte.dot);
      el.style.setProperty('--deck-on', tinte.on);
      // La portada es el arte del cliente entero (trama, tagline, wordmark y
      // mascota), no piezas recompuestas por nosotros. El color plano queda
      // igual como fondo debajo: es lo que se ve mientras la imagen carga y en
      // la parte de la cabecera que la portada no alcanza a cubrir.
      if (tinte.portada) {
        el.style.setProperty('--deck-portada', `url("${tinte.portada.src}")`);
      }
    }
    // Botón de volver explícito. Un chip no se lee como "atrás": si la pantalla
    // no es un destino de primer nivel, tiene que haber una flecha con nombre.
    const flecha = back ? `
      <a class="backbtn" href="${back}" aria-label="${es ? 'Volver' : 'Back'}"
         data-back>
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M15 18l-6-6 6-6"/>
        </svg>
      </a>` : '';

    // Bug 20-ago: el logo llevaba SIEMPRE a /site/index.html (la portada de
    // familia), aunque quien lo tocara fuera un líder con sesión abierta —
    // el gesto universal de "logo = ir a mi inicio" lo mandaba fuera de su
    // propia consola sin avisar, a una pantalla que no es la suya. Con sesión
    // de líder, "inicio" es su consola.
    const inicioHref = (window.API && API.haySesionAdmin()) ? '/site/admin/index.html'
      : (window.API && API.haySesionLider()) ? '/site/lider/index.html'
      : '/site/index.html';
    // El manual exige la firma reversada en blanco sobre fondo oscuro (pág. 11).
    // Con tinte eso ya no depende de la página sino del rank: sobre Wolf o
    // Webelos va la blanca, sobre Lion o Bear la de color. La misma medición
    // que decide el color del texto decide esto.
    const logoBlanco = sobreFoto || tintaClara;
    el.innerHTML = `
      <div class="aura__greet">
        ${flecha}
        <a class="pagehead__brand" href="${inicioHref}" aria-label="${es ? 'Inicio' : 'Home'}">
          <img src="/site/assets/img/scouting-america-signature${logoBlanco ? '-white' : ''}.png"
               alt="Scouting America" width="900" height="110" class="pagehead__logo">
        </a>
        ${esConsolaLider ? '' : `
        <div class="lang" role="group" aria-label="Idioma / Language">
          <button class="lang__btn" type="button" data-lang="es"
                  aria-pressed="${lang === 'es'}">ES</button>
          <button class="lang__btn" type="button" data-lang="en"
                  aria-pressed="${lang === 'en'}">EN</button>
        </div>`}
        ${(window.API && API.haySesionLider())
          ? avatarLink(es)
          : (esConsolaLider ? '' : loginLink(es))}
      </div>
      ${crumbs && crumbs.length ? `<nav aria-label="${es ? 'Migas de pan' : 'Breadcrumb'}">
        <ol class="crumbs">${crumbs.map((c, i) => {
          const ultimo = i === crumbs.length - 1;
          return `<li>${ultimo
            ? `<span aria-current="page">${c.label}</span>`
            : `<a href="${c.href}">${c.label}</a>`}</li>`;
        }).join('')}</ol></nav>` : ''}
      ${eyebrow ? `<p class="aura__hello">${eyebrow}</p>` : ''}
      ${title ? `<h1 class="bigtype">${titulo}</h1>` : ''}
      ${sub ? `<p class="aura__sub">${sub}</p>` : ''}`;

    // Si venimos navegando dentro de la app, volver = atrás en el historial,
    // para no perder scroll ni estado. Si se entró directo (enlace de WhatsApp),
    // el href lleva al lugar que corresponde en la jerarquía.
    const b = el.querySelector('[data-back]');
    if (b) b.addEventListener('click', e => {
      if (document.referrer && new URL(document.referrer).origin === location.origin) {
        e.preventDefault(); history.back();
      }
    });

    el.querySelectorAll('.lang__btn').forEach(btn => {
      btn.addEventListener('click', () => {
        API.setLang(btn.dataset.lang);
        const url = new URL(location.href);
        url.searchParams.set('lang', btn.dataset.lang);
        location.replace(url);
      });
    });
  }

  /** Pie único y callado, igual en todas las páginas. */
  function mountFooter(el) {
    // Bug 20-ago: el pie no miraba si esto era la consola del líder —
    // mountHeader() ya no muestra el selector de idioma ahí, pero el footer
    // seguía leyendo API.getLang() derecho, así que en /site/lider/* podía
    // quedar en español mientras el resto de la pantalla estaba en inglés.
    // Mismo criterio que mountHeader: ruta primero, idioma guardado después.
    const esConsolaAdmin = (window.API && API.haySesionAdmin());
    const esConsolaLider = esConsolaAdmin || document.body.dataset.consola === 'lider';
    const es = !esConsolaLider && (((window.API && API.getLang()) || 'es') !== 'en');
    const anio = new Date().getFullYear();
    el.className = 'pagefoot';
    el.innerHTML = `
      <p>©${anio} Scouting America · ${es ? 'Preparados para el futuro.®' : 'Prepared. For Life.®'}</p>
      <p>${es
        ? 'Traducción al español no oficial. No guardamos datos de menores.'
        : 'Unofficial Spanish translation. We never store data about minors.'}</p>
      ${esConsolaAdmin
        ? `<p><button class="linklike" type="button" data-salir-admin>Sign out of the admin dashboard</button></p>`
        : (window.API && API.haySesionLider())
        ? `<p><button class="linklike" type="button" data-salir>${
            es ? 'Salir de la consola de líder' : 'Sign out of the leader console'}</button></p>`
        : ''}`;
    // El ingreso del líder vive en la cabecera (loginLink), no acá — 20-ago:
    // repetirlo en el pie era ruido, nadie mira ahí primero.

    // Ya no se "cambia de modo": el líder entra a su consola y sale de ella.
    // El papá no tiene nada que elegir — llega por el enlace de WhatsApp.
    const bs = el.querySelector('[data-salir]');
    if (bs) bs.addEventListener('click', () => {
      API.salir();
      location.href = '/site/index.html';
    });
    const ba = el.querySelector('[data-salir-admin]');
    if (ba) ba.addEventListener('click', () => {
      API.salirAdmin();
      location.href = '/site/index.html';
    });
  }

  /* ---------- esqueletos ---------- */

  /** Bloques del alto real del contenido: no mueven la página al llegar el dato. */
  function skeletonFilas(n = 5) {
    return `<ul class="index-list" aria-hidden="true">${
      Array.from({ length: n }, () => '<li><div class="skel skel--row"></div></li>').join('')
    }</ul>`;
  }

  function skeletonTarjeta() {
    return '<div class="skel skel--card" aria-hidden="true"></div>';
  }

  /* Ícono por tono — mismo trazo (viewBox 24, stroke 1.6) que el resto de
     la app. "Vacío" no es un error: usa el mismo glifo de mensaje que el
     estado vacío del home, no la alerta. */
  const ESTADO_ICON = {
    vacio: '<path d="M12 3.5a8 8 0 0 0-6.9 12.03L4.5 20l4.6-.6A8 8 0 1 0 12 3.5Z"/><path d="M9 12h6M9 15h4"/>',
    error: '<path d="M12 9v4"/><path d="M12 16.5h.01"/><path d="M10.3 4.3 3.5 17a1.7 1.7 0 0 0 1.5 2.5h14a1.7 1.7 0 0 0 1.5-2.5L13.7 4.3a1.7 1.7 0 0 0-3.4 0Z"/>'
  };

  /* Pedido 20-ago: los avisos inline ("You'll sign as...", "not translated
     yet", "deleted, undo") eran un <p class="notice"> con una barra de color
     a la izquierda — el patrón plano de hace unos años. Un solo componente
     nuevo, mismo lenguaje de ícono-círculo que .newsitem__icon/Shell.estado,
     para no inventar un sistema aparte. */
  const NOTICE_ICON = {
    info:     '<circle cx="12" cy="12" r="8.5"/><path d="M12 10.5v5.5M12 7.8h.01"/>',
    persona:  '<circle cx="12" cy="8.3" r="3.3"/><path d="M5 19.5a7 7 0 0 1 14 0"/>',
    traducir: '<path d="M4 5.5h9M8.5 3.5v2M6 5.5c.4 3 2.2 5.4 5 6.8M11 5.5c-.7 3.4-3 6.2-6.5 8"/><path d="M13 20.5l4-9 4 9M14.6 17.5h4.8"/>',
    reciclar: '<path d="M4 12a8 8 0 0 1 13.9-5.4M20 12a8 8 0 0 1-13.9 5.4"/><path d="M17 3v4h-4M7 21v-4h4"/>',
    borrar:   '<path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-.8 12a2 2 0 0 1-2 1.8H9.8a2 2 0 0 1-2-1.8L7 7"/>'
  };
  const NOTICE_TONO = { info: 'blue', persona: 'blue', traducir: 'tan', reciclar: 'blue', borrar: 'navy' };

  /**
   * Aviso inline: ícono-círculo + texto, tarjeta con sombra suave — reemplaza
   * el viejo <p class="notice"> de barra plana en TODOS los lugares donde se
   * usaba (carta.html, baraja.html, lider/index.html). `html` acepta marcado
   * (para el "Fix it"/"Deshacer" en línea); `icono` es una llave de
   * NOTICE_ICON, default 'info'.
   */
  function notice({ html, icono = 'info', id }) {
    const svg = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
      stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${NOTICE_ICON[icono] || NOTICE_ICON.info}</svg>`;
    return `<div class="notice"${id ? ` id="${id}"` : ''}>
      <span class="newsitem__icon newsitem__icon--${NOTICE_TONO[icono] || 'blue'} notice__icon">${svg}</span>
      <p class="notice__text">${html}</p>
    </div>`;
  }

  /** Estado vacío o de error: siempre con salida, nunca una pantalla muerta.
   *  `tono` es 'vacio' (default) o 'error' — decide ícono y color del badge,
   *  no la estructura: las dos son la misma tarjeta. */
  function estado({ titulo, texto, accion, tono = 'vacio' }) {
    const svg = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
      stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ESTADO_ICON[tono] || ESTADO_ICON.vacio}</svg>`;
    return `<div class="state">
      <span class="newsitem__icon newsitem__icon--${tono === 'error' ? 'red' : 'blue'}">${svg}</span>
      <div>
        <p class="state__title">${titulo}</p>
        <p class="state__text">${texto}</p>
        ${accion ? `<p class="actions">${accion}</p>` : ''}
      </div>
    </div>`;
  }

  /* ---------- motion 2026 ---------- */

  const reduceMotion = () =>
    window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;

  /** Number tick: cuenta de 0 al valor una sola vez. Reduced-motion: valor final. */
  function tick(el, valor, { dur = 600, formato } = {}) {
    const fmt = formato || (n => new Intl.NumberFormat(
      (window.API && API.getLang()) === 'en' ? 'en-US' : 'es').format(Math.round(n)));
    el.setAttribute('aria-live', 'polite');
    /* Sin animación posible → el valor final, ya.
       `visibilityState` importa de verdad: requestAnimationFrame NO corre en
       una pestaña en segundo plano, y abrir un enlace en pestaña nueva para
       mirarlo después es de lo más común. Sin esta salida, el contador se
       quedaba clavado en el número con el que arrancó —un 0— y el tablero
       mostraba "0 %" de apertura hasta que alguien recargara. */
    if (reduceMotion() || !window.requestAnimationFrame
        || document.visibilityState !== 'visible') {
      el.textContent = fmt(valor);
      return;
    }
    const t0 = performance.now();
    const paso = t => {
      const p = Math.min(1, (t - t0) / dur);
      const e = 1 - Math.pow(1 - p, 3);        // ease-out cúbico sobre el VALOR
      el.textContent = fmt(valor * e);
      if (p < 1) requestAnimationFrame(paso);
    };
    requestAnimationFrame(paso);
  }

  /** Reveal one-shot bajo el fold. Sin IO (o con reduced-motion) entra todo ya. */
  function reveals(root) {
    const items = [...root.querySelectorAll('.rise-io')];
    if (!items.length) return;
    if (reduceMotion() || !('IntersectionObserver' in window)) {
      items.forEach(el => el.classList.add('is-in'));
      return;
    }
    const io = new IntersectionObserver(entries => {
      // Los que entran juntos se escalonan como un solo lote, en orden del DOM.
      const lote = entries.filter(e => e.isIntersecting).map(e => e.target);
      lote.forEach((el, i) => {
        el.style.setProperty('--i', i);
        el.classList.add('is-in');
        io.unobserve(el);
      });
    }, { rootMargin: '0px 0px -8% 0px' });
    items.forEach(el => io.observe(el));
  }

  function init() {
    // Habilita la capa de motion: sin JS no hay estados ocultos esperando
    // animación, la página se sirve completa y estática.
    document.documentElement.classList.add('fx');
    const lang = (window.API && API.getLang()) || 'es';
    // Un enlace compartido siempre se abre como familia: quien lo recibe no es
    // quien lo mandó. Si no hay modo elegido todavía, familia es el default.
    const esEnlaceCompartido = new URLSearchParams(location.search).has('s');
    // La navegación de líder exige SESIÓN, no solo el modo guardado. El modo
    // sobrevive en el teléfono desde la vez pasada, así que sin esto la app le
    // ofrecía "Enviar" y "Envíos" a alguien que al tocarlos rebota al login.
    // Ofrecer una puerta que no abre es peor que no ofrecerla.
    const esLider = (window.API && API.getMode() === 'lider' && API.haySesionLider());
    // El admin manda sobre el modo guardado: si hay sesión de admin, la app
    // es su tablero, aunque en este teléfono se haya usado antes como líder.
    const esAdmin = (window.API && API.haySesionAdmin());
    const modo = esAdmin ? 'admin'
               : (esEnlaceCompartido || !esLider) ? 'familia' : 'lider';
    // Pedido 20-ago: el tab bar del líder (Send/Decks/Sent/Help) es inglés
    // siempre, igual que el resto de su consola — no lee la preferencia
    // guardada, que puede venir de haber probado el lado de familia antes.
    montarTabs(modo === 'familia' ? lang : 'en', modo);
    montarAvisoRed(lang);
    montarInstalacion(lang);

    if ('serviceWorker' in navigator) {
      addEventListener('load', () => {
        navigator.serviceWorker.register('/site/sw.js', { scope: '/site/' })
          .catch(err => console.warn('SW no registrado:', err.message));
      });
    }
  }

  return { init, mountHeader, mountFooter, skeletonFilas, skeletonTarjeta, estado, notice, tick, reveals };
})();

if (typeof window !== 'undefined') {
  window.Shell = Shell;
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', Shell.init);
  } else {
    Shell.init();
  }
}
