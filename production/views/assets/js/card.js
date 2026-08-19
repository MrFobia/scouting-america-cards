/**
 * card.js — pinta una carta a partir del dato. Un renderer por skin.
 * No toca red ni almacenamiento: eso es de api.js.
 */
const Card = (() => {

  const PLACE = {
    indoor:          { es: 'Bajo techo',        en: 'Indoor' },
    outdoor:         { es: 'Al aire libre',     en: 'Outdoor' },
    outing:          { es: 'Excursión',         en: 'Outing' },
    'indoor-outdoor':{ es: 'Interior/Exterior', en: 'Indoor/Outdoor' }
  };

  const METER = {
    energy:   { es: 'Energía',     en: 'Energy' },
    prep:     { es: 'Preparación', en: 'Preparation' },
    duration: { es: 'Duración',    en: 'Duration' }
  };

  const esc = s => String(s ?? '').replace(/[&<>"']/g,
    c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));

  /** Un valor cuenta como presente si es texto no vacío o una lista con ítems. */
  const has = v => Array.isArray(v) ? v.length > 0 : Boolean(v && String(v).trim());

  /**
   * Valor en el idioma pedido, con caída al otro si falta.
   * Sirve tanto para strings como para listas (los pasos de una carta de recurso).
   * Un baraja sin traducir se muestra en inglés — nunca se esconde ni sale vacío.
   */
  function t(field, lang) {
    if (!field) return '';
    const other = lang === 'es' ? 'en' : 'es';
    if (has(field[lang])) return field[lang];
    return has(field[other]) ? field[other] : (Array.isArray(field[lang]) ? [] : '');
  }

  /**
   * Categoría de la Adventure, con respaldo.
   *
   * Las Adventures electivas no traen categoría impresa: la lámina solo dice
   * ELECTIVE. Sin respaldo, la etiqueta quedaba vacía y la tarjeta se veía
   * rota. Decir si es obligatoria o electiva es además lo que el papá
   * necesita saber para entender por qué su hijo la está haciendo.
   */
  function categoria(adv, lang) {
    if (!adv) return '';
    const propia = t(adv.category, lang);
    if (propia) return propia;
    const es = lang !== 'en';
    return adv.required ? (es ? 'Obligatoria' : 'Required')
                        : (es ? 'Electiva' : 'Elective');
  }

  function isFallback(field, lang) {
    return Boolean(field && !has(field[lang]) && has(field[lang === 'es' ? 'en' : 'es']));
  }

  function meters(m, lang) {
    if (!m) return '';
    const rows = Object.entries(m).map(([key, value]) => {
      const pips = Array.from({ length: 5 }, (_, i) =>
        `<span class="meter__pip" data-on="${i < value}"></span>`).join('');
      const label = esc(METER[key]?.[lang] ?? key);
      return `<div class="meter">
        <span class="meter__label" id="m-${key}">${label}</span>
        <span class="meter__track" role="img" aria-labelledby="m-${key}"
              aria-label="${label}: ${value} de 5">${pips}</span>
        <span class="meter__value metric-value">${value}/5</span>
      </div>`;
    }).join('');
    return `<div class="meters">${rows}</div>`;
  }

  function links(list, lang) {
    if (!list?.length) return '';
    return list.map(l =>
      `<a class="btn btn--accent" href="${esc(l.url)}" target="_blank" rel="noopener">
         ${esc(t(l.label, lang) || (lang === 'es' ? 'Ver la actividad' : 'See the activity'))}
       </a>`).join('');
  }

  function foot(deck) {
    return `<div class="card__foot">${esc(deck.copyright || '')}</div>`;
  }

  /* --------------------------------------------------------- los 5 skins */

  const skins = {

    cover(card, ctx) {
      const { deck, lang } = ctx;
      return `<article class="card card--cover">
        <div class="card__head">
          <p class="card__kicker">Cub Scouts · Scouting America</p>
          <h2 class="card__rank">${esc(t(deck.name, lang))}</h2>
          <p class="card__original">${esc(t(card.title, lang))}</p>
        </div>
        <div class="card__body"><p class="card__desc">${esc(t(card.body, lang))}</p></div>
        ${foot(deck)}
      </article>`;
    },

    legend(card, ctx) {
      const { deck, lang } = ctx;
      const list = (card.meterLegend || []).map(m =>
        `<li><strong>${esc(t(m.label, lang))}</strong>
             <span class="legend-hint">${esc(t(m.hint, lang))}</span></li>`).join('');
      const chips = (card.placeLegend || []).map(p =>
        `<span class="place">${esc(t(p.label, lang))}</span>`).join('');
      return `<article class="card card--legend">
        <div class="card__head">
          <p class="card__kicker">${lang === 'es' ? 'Guía de íconos' : 'Icon guide'}</p>
          <h2 class="card__title">${esc(t(card.title, lang))}</h2>
        </div>
        <div class="card__body">
          <p class="card__desc">${esc(t(card.body, lang))}</p>
          <ul class="legend-list">${list}</ul>
          <div class="chips">${chips}</div>
          <p class="card__desc" style="margin-top:var(--space-4)">${esc(t(card.note, lang))}</p>
        </div>
        ${foot(deck)}
      </article>`;
    },

    resource(card, ctx) {
      const { deck, lang } = ctx;
      const steps = (t(card.steps, lang) || []);
      return `<article class="card card--resource">
        <div class="card__head">
          <p class="card__kicker">${lang === 'es' ? 'Carta de recursos' : 'Resource card'}</p>
          <h2 class="card__title">${esc(t(card.title, lang))}</h2>
        </div>
        <div class="card__body">
          <p class="card__desc">${esc(t(card.body, lang))}</p>
          ${steps.length ? `<ol>${steps.map(s => `<li>${esc(s)}</li>`).join('')}</ol>` : ''}
          ${links(card.links, lang)}
        </div>
        ${foot(deck)}
      </article>`;
    },

    adventure(card, ctx) {
      const { deck, lang } = ctx;
      const adv = deck.adventures.find(a => a.id === card.adventureId);
      if (!adv) return '';
      const required = adv.required
        ? (lang === 'es' ? 'Obligatoria' : 'Required')
        : (lang === 'es' ? 'Electiva' : 'Elective');
      return `<article class="card card--adventure">
        <div class="card__head">
          <p class="card__kicker">${esc(categoria(adv, lang))}</p>
          <h2 class="card__title">${esc(t(adv.name, lang))}</h2>
          <span class="badge">${required} · ${adv.requirementCount} ${lang === 'es' ? 'requisitos' : 'requirements'}</span>
        </div>
        <div class="card__body"><p class="card__desc">${esc(t(adv.summary, lang))}</p></div>
        ${foot(deck)}
      </article>`;
    },

    activity(card, ctx) {
      const { deck, lang } = ctx;
      const adv = deck.adventures.find(a => a.id === card.adventureId);
      const req = card.requirement;
      const dots = Array.from({ length: req.of }, (_, i) =>
        `<li${i + 1 === req.index ? ' aria-current="step"' : ''}>${i + 1}</li>`).join('');
      const place = PLACE[card.place]?.[lang] ?? card.place;
      const reqLabel = lang === 'es'
        ? `Requisito ${req.index} de ${req.of}`
        : `Requirement ${req.index} of ${req.of}`;

      return `<article class="card card--activity">
        <div class="card__head">
          <ul class="reqdots" aria-label="${reqLabel}">${dots}</ul>
          <p class="card__kicker">${esc(categoria(adv, lang))}</p>
          <h2 class="card__title">${esc(t(card.title, lang))}</h2>
          ${card.originalTitle && t(card.title, lang) !== card.originalTitle
            ? `<p class="card__original">${lang === 'es' ? 'Actividad original' : 'Original activity'}: ${esc(card.originalTitle)}</p>`
            : ''}
        </div>
        <div class="card__body">
          <div class="requirement">
            <span class="requirement__label">${esc(adv ? t(adv.name, lang) : '')} · ${reqLabel}</span>
            ${esc(t(req.text, lang))}
          </div>
          <p class="card__desc">${esc(t(card.description, lang))}</p>
          <p><span class="place">${esc(place)}</span></p>
          ${meters(card.meters, lang)}
          ${accionQR(card, lang)}
        </div>
        ${band(ctx.person, lang)}
        ${foot(deck)}
      </article>`;
    }
  };

  /**
   * Banda de personalización. El brief la pide explícita (§6): cada tarjeta
   * compartida muestra el nombre del líder y el número de pack. El líder lo
   * configura una vez en su perfil; nunca lo escribe al compartir.
   */
  function band(person, lang) {
    if (!person || (!person.leader && !person.pack)) return '';
    const from = lang === 'es' ? 'Te la envía' : 'Shared by';
    const parts = [person.leader, person.pack].filter(Boolean).map(esc).join(' · ');
    return `<p class="card__band">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
           stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M4.5 12 20 4.5 15.5 20l-4-6.5-7-1.5Z"/>
      </svg>
      <span>${from}</span> ${parts}</p>`;
  }

  /**
   * Tarjeta real: la lámina oficial del baraja, tal cual la diseñó Scouting
   * America. El brief lo exige (§12): las tarjetas son contenido oficial y hay
   * que preservar su apariencia.
   *
   * Debajo va el mismo contenido en texto, plegado. No es decoración: una
   * imagen no la lee un lector de pantalla, no se puede copiar, y en una
   * conexión lenta el texto aparece antes que la lámina.
   */
  /**
   * La lámina del idioma pedido. Decisión del 14-ago: la tarjeta bilingüe NO es
   * texto traducido debajo de una imagen en inglés — es OTRA LÁMINA. El switch
   * cambia la imagen. No se van a producir ochocientas tarjetas editables:
   * sale más barato una tarjeta nueva que editar en masa.
   *
   * Acepta las dos formas del dato: `images: { en, es }` (la nueva) y `image`
   * suelta (la que hay hoy, en inglés). Mientras la versión en español no
   * exista, devuelve la que haya y avisa de cuál es, en vez de romper.
   */
  function lamina(card, lang) {
    const imgs = card.images || (card.image ? { [card.image.lang || 'en']: card.image } : null);
    if (!imgs) return null;
    const pedida = imgs[lang];
    if (pedida) return { ...pedida, lang, esFallback: false };
    const otra = imgs.en || imgs.es || Object.values(imgs)[0];
    return otra ? { ...otra, lang: otra.lang || (imgs.en ? 'en' : 'es'), esFallback: true } : null;
  }

  function art(card, ctx) {
    const { lang, person } = ctx;
    const alt = t(card.title, lang) || (lang === 'es' ? 'Tarjeta de actividad' : 'Activity card');

    const img = lamina(card, lang);
    if (!img) return textOnly(card, ctx);

    /* Pedido 20-ago: solo queda la lámina, quién la manda y las dos acciones
       (ver en grande / descargar). El aviso de idioma, el enlace QR y el
       texto plegado accesible salieron a pedido del cliente.
       OJO — esto es una regresión de accesibilidad real, no cosmética: una
       imagen no la lee un lector de pantalla, no se copia, y en 3G llega
       después que el texto. El alt del <img> (título nomás) es lo único
       que le queda a quien navega con lector. Documentado, no escondido. */
    const nombreArchivo = `${card.id}-${img.lang}.webp`;

    /* Pedido 20-ago: los botones (y la banda "te la envía") ya no viven
       pegados adentro de la caja de la lámina — son su propio bloque,
       separado. La caja de la lámina se queda sola dentro de .cardstack:
       así las dos capas rotadas de atrás (el "mazo") quedan del tamaño de
       la lámina nomás, no estiradas detrás de los botones también. */
    /* Bug 20-ago: sin loading="lazy" a propósito. Esta lámina se inserta vía
       innerHTML cada vez que se cambia de carta (nunca es contenido que ya
       estaba en el documento), y arranca con opacity:0 por la transición
       WAAPI de entrada() — esa combinación le rompía la detección de
       intersección al navegador y la imagen se quedaba sin pedir nunca
       (reportado como "la tercera imagen no aparece"). Es la carta que el
       papá vino a ver: no hay nada más abajo que "ahorrar" cargando tarde. */
    return `<div class="cardstack"><div class="card card--art">
      <figure class="card__art">
        <img src="${esc(img.src)}" alt="${esc(alt)}"
             width="${img.width}" height="${img.height}"
             data-lamina data-descarga="${esc(nombreArchivo)}"
             decoding="async">
      </figure>
    </div></div>
    <div class="card__meta">
      <p class="card__acciones">
        <button type="button" class="card__accion" data-ampliar>
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
               stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/>
            <circle cx="12" cy="12" r="2.8"/>
          </svg>
          ${lang === 'es' ? 'Ver en grande' : 'View full screen'}
        </button>
        <a class="card__accion" href="${esc(img.src)}" download="${esc(nombreArchivo)}">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
               stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 4v11m0 0-4-4m4 4 4-4"/><path d="M4.5 17.5v2a1.5 1.5 0 0 0 1.5 1.5h12a1.5 1.5 0 0 0 1.5-1.5v-2"/>
          </svg>
          ${lang === 'es' ? 'Descargar la imagen' : 'Download the image'}
        </a>
      </p>
      ${band(person, lang)}
    </div>`;
  }

  /**
   * El QR impreso, convertido en enlace.
   *
   * En la baraja física el QR se escanea con OTRO teléfono. En pantalla no
   * sirve para nada: nadie escanea su propio celular. Por eso la versión
   * digital lo reemplaza por un botón, y se dice explícitamente para que
   * quien conoce la carta impresa entienda la equivalencia.
   */
  function accionQR(card, lang) {
    const es = lang !== 'en';
    const enlace = (card.links || [])[0];
    if (enlace) {
      return `
        <a class="btn btn--accent btn--block" href="${esc(enlace.url)}"
           target="_blank" rel="noopener">
          ${esc(t(enlace.label, lang) || (es ? 'Ver la actividad oficial' : 'See the official activity'))} ↗
        </a>
        <p class="card__qrnote">${es
          ? 'En la tarjeta impresa esto es un código QR. Aquí es un enlace.'
          : 'On the printed card this is a QR code. Here it is a link.'}</p>`;
    }
    // Sin la matriz de enlaces todavía: se dice, no se finge un botón muerto.
    return `<p class="card__qrnote">${es
      ? 'El código QR de la tarjeta impresa va a ser un enlace aquí. Falta cargar la dirección de esta actividad.'
      : 'The printed card’s QR code becomes a link here. This activity’s address is not loaded yet.'}</p>`;
  }

  /** Versión en texto de una carta de actividad, para el <details> y para a11y. */
  function textOnly(card, ctx) {
    const { deck, lang } = ctx;
    if (card.skin !== 'activity') {
      return `<p>${esc(t(card.body, lang))}</p>` +
        ((t(card.steps, lang) || []).length
          ? `<ol>${t(card.steps, lang).map(s => `<li>${esc(s)}</li>`).join('')}</ol>` : '');
    }
    const adv = deck.adventures.find(a => a.id === card.adventureId);
    const req = card.requirement;
    const reqLabel = lang === 'es'
      ? `Requisito ${req.index} de ${req.of}`
      : `Requirement ${req.index} of ${req.of}`;
    return `
      <div class="requirement">
        <span class="requirement__label">${esc(adv ? t(adv.name, lang) : '')} · ${reqLabel}</span>
        ${esc(t(req.text, lang))}
      </div>
      <p class="card__desc">${esc(t(card.description, lang))}</p>
      <p><span class="place">${esc(PLACE[card.place]?.[lang] ?? card.place)}</span></p>
      ${meters(card.meters, lang)}`;
  }

  /** Devuelve el HTML de una carta. `ctx` = { deck, lang, person? }. */
  function render(card, ctx) {
    // Si tenemos la lámina oficial, esa manda. La plantilla es el respaldo
    // para las tarjetas que todavía no se exportaron del PDF.
    if (card.image?.src) return art(card, ctx);
    const skin = skins[card.skin];
    if (!skin) return `<p class="notice">Skin desconocido: ${esc(card.skin)}</p>`;
    return skin(card, ctx);
  }

  /* ---------- Visor a pantalla completa ----------
     Reemplaza al enlace de visualización individual, que salió el 14-ago: la
     tarjeta se mira en grande donde estás, sin navegar a otra página. Para el
     líder eso importa el doble — abrir una tarjeta a mitad de armar el mazo
     perdería la selección.

     <dialog> nativo: trae el foco atrapado, el cierre con ESC y el fondo
     inerte sin escribir una línea de eso. */
  function montarVisor() {
    if (document.getElementById('visor')) return document.getElementById('visor');
    // Bug 20-ago: "Cerrar"/"Descargar la imagen" estaban fijos en español acá
    // adentro, sin mirar el idioma — mientras que "Ver en grande" (arriba en
    // art()) sí lo hacía. Se armó una sola vez perezoso, así que la corrección
    // lee el idioma del documento en vez de necesitar que cada caller lo pase.
    const es = document.documentElement.lang !== 'en';
    const d = document.createElement('dialog');
    d.id = 'visor';
    d.className = 'visor';
    d.innerHTML = `
      <button class="visor__cerrar" type="button" aria-label="${es ? 'Cerrar' : 'Close'}" data-cerrar>&times;</button>
      <img class="visor__img" alt="">
      <p class="visor__pie">
        <a class="card__accion" data-bajar download>
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
               stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 4v11m0 0-4-4m4 4 4-4"/><path d="M4.5 17.5v2a1.5 1.5 0 0 0 1.5 1.5h12a1.5 1.5 0 0 0 1.5-1.5v-2"/>
          </svg>
          ${es ? 'Descargar la imagen' : 'Download the image'}
        </a>
      </p>`;
    document.body.appendChild(d);
    d.addEventListener('click', e => {
      // Clic fuera de la imagen cierra: en el móvil es el gesto que espera todo
      // el mundo, y el botón queda para quien navega con teclado.
      if (e.target === d || e.target.closest('[data-cerrar]')) d.close();
    });
    return d;
  }

  /** Abre una lámina en grande. `src` y `nombre` salen del propio <img>. */
  function ampliar(src, nombre, alt) {
    const d = montarVisor();
    const img = d.querySelector('.visor__img');
    const a = d.querySelector('[data-bajar]');
    img.src = src;
    img.alt = alt || '';
    a.href = src;
    a.download = nombre || '';
    d.showModal();
  }

  /**
   * Cablea los botones "Ver en grande" de un contenedor. Se llama después de
   * pintar; delegado, así que sirve igual si el contenido se repinta.
   */
  function conectarAmpliar(raiz = document) {
    if (raiz.dataset && raiz.dataset.ampliarListo) return;
    if (raiz.dataset) raiz.dataset.ampliarListo = '1';
    raiz.addEventListener('click', e => {
      const b = e.target.closest('[data-ampliar]');
      if (!b) return;
      const card = b.closest('.card, .cardstack, article, li') || document;
      const img = card.querySelector('[data-lamina]');
      if (img) ampliar(img.src, img.dataset.descarga, img.alt);
    });
  }

  return { render, band, textOnly, t, categoria, isFallback, ampliar, conectarAmpliar, lamina, PLACE, METER };
})();

if (typeof window !== 'undefined') window.Card = Card;
