<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Guía de estilos · Cub Scouts en español · Scouting America</title>
<meta name="description" content="Sistema visual del producto, derivado del Scouting America Brand Guidelines 2024.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/projects/scouting-america/assets/css/tokens.css?v=vffaf126a">
<link rel="stylesheet" href="/projects/scouting-america/assets/css/app.css?v=vffaf126a">
<style>
  /* Solo lo que es exclusivo de este documento interno. El resto vive en app.css. */
  .guide { max-width: 64rem; margin-inline: auto; padding: 0 var(--page-gutter) var(--space-8); }
  .guide section { padding-block: var(--space-7); border-top: var(--rule-hair) solid var(--sa-tan); }
  .guide section:first-of-type { border-top: 0; }
  .guide h2 { font-family: var(--font-display); color: var(--text-display); font-size: var(--step-3); margin: 0 0 var(--space-4); }
  .guide h3 { font-family: var(--font-display); color: var(--text-strong); font-size: var(--step-1); margin: var(--space-6) 0 var(--space-3); }
  .prose-note { max-width: var(--measure); color: var(--text); }
</style>
</head>
<body>

<a class="skip-link" href="#main">Saltar al contenido</a>

<header class="nav-edge">
  <a class="nav-edge__brand" href="/site/index.html">
    <img class="nav-edge__logo" src="/projects/scouting-america/assets/img/scouting-america-signature.png"
         alt="Scouting America" width="900" height="110">
    <span class="nav-edge__title">Guía de estilos</span>
  </a>
  <a class="nav-edge__back" href="/site/index.html">Inicio</a>
</header>

<main id="main" class="guide">

  <section>
    <p class="subhead">Sistema visual</p>
    <h1 class="display" style="font-size:var(--step-4)">Guía de estilos</h1>
    <p class="prose-note" style="font-size:var(--step-1)">
      Todo lo que sigue sale del <em>Scouting America Brand Guidelines 2024</em>, leído
      página por página. No es una interpretación: los colores, el logo, las taglines y
      las reglas de uso son los del manual. Los contrastes están <strong>medidos</strong>,
      no estimados.
    </p>
  </section>

  <section>
    <p class="subhead">Identidad</p>
    <h2 class="display">El logo</h2>
    <div class="cols">
      <div>
        <div class="logo-box">
          <img src="/projects/scouting-america/assets/img/scouting-america-signature.png"
               alt="Firma de Scouting America" width="900" height="110">
        </div>
        <p class="prose-note" style="margin-top:var(--space-3)">
          <strong>Firma Scouting America.</strong> Va en la barra superior de cada pantalla,
          a color sobre blanco. El espacio libre alrededor debe ser igual o mayor a la altura
          de la flor de lis.
        </p>
      </div>
      <div>
        <div class="logo-box">
          <img src="/projects/scouting-america/assets/img/cub-scouts-trademark.png"
               alt="Trademark de Cub Scouts" width="600" height="618"
               style="width:min(14rem,70%)">
        </div>
        <p class="prose-note" style="margin-top:var(--space-3)">
          <strong>Trademark Cub Scouts.</strong> El lobo, las palabras «Cub Scouts» y la
          flor de lis. Es la marca del programa al que pertenece este producto. El espacio
          libre debe ser igual o mayor al alto del rombo.
        </p>
      </div>
    </div>

    <h3>Lo que el manual prohíbe</h3>
    <ul class="dont">
      <li>Reproducir el logo en tinta, trama o pastel.</li>
      <li>Agregar efectos: sombra, bisel o glow.</li>
      <li>Alterar la firma, incluso cambiándole la tipografía.</li>
      <li>Reproducirlo a color sobre fondo oscuro — ahí <strong>debe</strong> ir la versión en blanco.</li>
      <li>Usar el elemento del lobo por fuera del trademark aprobado.</li>
      <li>Usar la flor de lis suelta: está retirada.</li>
    </ul>
    <div class="aside aside--blue" style="margin-top:var(--space-5)">
      <strong>Consecuencia para el producto:</strong> no tenemos la versión en blanco de la firma,
      así que sobre superficies oscuras el pie usa el nombre en texto, no el PNG a color.
      Pedirle al cliente los vectores oficiales cierra este pendiente.
    </div>
  </section>

  <section>
    <p class="subhead">Taglines</p>
    <h2 class="display">Preparados para el futuro.®</h2>
    <p class="prose-note">
      El manual es explícito: la tagline en español <em>«se debe colocar en todas las
      comunicaciones, literatura y productos en español de Scouting America»</em>. Este producto
      es en español por definición, así que <strong>Preparados para el futuro.®</strong> es la
      tagline por defecto y <em>Prepared. For Life.®</em> queda para la vista en inglés.
    </p>
    <div class="aside">
      Nunca aparece sola: siempre acompañada del trademark o la firma, aunque estén en
      lugares distintos de la pantalla. El símbolo ® siempre presente.
    </div>
  </section>

  <section>
    <p class="subhead">Color</p>
    <h2 class="display">Paleta</h2>

    <h3>Primarios</h3>
    <p class="prose-note">
      «The Scouting America palette is inspired by a blend of our iconic uniform colors along
      with outdoors-inspired beige and warm gray.» El blanco cuenta como el quinto color.
    </p>
    <div class="swatches" id="sw-primary"></div>
    <div class="aside aside--blue" style="margin-top:var(--space-4)">
      <strong>No se crean tintes ni sombras del rojo.</strong> El manual lo dice literal.
      Cuando hace falta una variante, se usa un secundario, no una versión aguada del rojo.
    </div>

    <h3>Secundarios</h3>
    <p class="prose-note">
      Derivados de azul, tan y gris. Existen para armar capas en el layout junto a su color
      padre — no para reemplazarlo.
    </p>
    <div class="swatches" id="sw-secondary"></div>

    <div class="aside" style="margin-top:var(--space-5)">
      <strong>Sobre el dorado.</strong> El azul y dorado de la pág. 23 son de la sub-marca
      <em>Cub Scouts</em>, no de la paleta Scouting America. Por decisión del cliente, la
      interfaz usa solo la paleta Scouting America. El dorado aparece únicamente dentro del
      trademark Cub Scouts, que no se altera.
    </div>
  </section>

  <section>
    <p class="subhead">Accesibilidad</p>
    <h2 class="display">Contrastes medidos</h2>
    <div class="scroller">
      <table>
        <caption>Calculado en esta misma página. WCAG 2.2 AA: 4.5:1 texto normal, 3:1 texto grande.</caption>
        <thead>
          <tr><th>Par</th><th class="num">Ratio</th><th>Normal</th><th>Grande</th></tr>
        </thead>
        <tbody id="contrast-rows"></tbody>
      </table>
    </div>

    <h3>Las reglas que salen de esos números</h3>
    <div class="aside aside--blue">
      <strong>El rojo es display y acento, no texto corrido.</strong> Sobre blanco da 5.63:1 y
      pasa AA, pero el manual lo reserva para titulares y reglas. Sobre Tan cae a 3.60:1:
      ahí solo tamaño grande.
    </div>
    <div class="aside aside--blue">
      <strong>Azul sobre blanco es el par de lectura.</strong> 10.19:1. Y aguanta las dos
      superficies cálidas: 8.37:1 sobre Light Tan, 6.51:1 sobre Tan.
    </div>
    <div class="aside aside--blue">
      <strong>Dark Tan y Pale Gray son decorativos.</strong> 2.66:1 y 3.61:1 sobre blanco.
      No llevan texto pequeño encima; sirven de superficie o de regla.
    </div>
    <div class="aside aside--blue">
      <strong>Sobre azul oscuro, Pale Blue.</strong> 5.87:1 — es el gris del texto secundario
      cuando el fondo es Dark Blue, por ejemplo en el pie de página.
    </div>
  </section>

  <section>
    <p class="subhead">Tipografía</p>
    <h2 class="display">Serif para leer, condensada para etiquetar</h2>
    <p class="prose-note">
      El manual aprueba cuatro fuentes: <strong>Times New Roman</strong>, <strong>Arial</strong>,
      <strong>Helvetica Neue LT Std 77 Bold Condensed</strong> y <strong>Proxima Nova</strong>
      (alterna de Google: Montserrat). Y aclara que <em>«additional fonts may be used as design
      elements»</em> mientras la base esté en esas.
    </p>
    <p class="prose-note">
      Pero lo importante no es la lista, es <strong>cómo las usa</strong>: el manual entero está
      compuesto en serif —titulares en rojo, prosa en gris— y reserva la
      <strong>condensada bold</strong> para las micro-etiquetas: <code>HEX:</code>, <code>RGB:</code>,
      los encabezados de tabla. Ese reparto es lo que replicamos.
    </p>
    <div class="aside aside--blue">
      <strong>Serif</strong> — Source Serif 4, con Times New Roman (aprobada) de respaldo real.
      Va en titulares y en toda la prosa.<br>
      <strong>Condensada</strong> — Roboto Condensed, con Arial Narrow detrás. Cumple el rol de
      Helvetica Neue 77 Bold Condensed: etiquetas, botones, datos, encabezados de tabla.
    </div>
    <div class="aside">
      <strong>Pendiente de confirmar:</strong> el serif y la condensada exactos del manual son
      licenciados. Si el cliente los tiene, se cambian dos tokens y no se toca nada más.
    </div>
    <div id="type-scale" style="margin-top:var(--space-6)"></div>
  </section>

  <section>
    <p class="subhead">Layout</p>
    <h2 class="display">La regla vertical</h2>
    <p class="prose-note">
      El manual separa contenido de imagen con una regla vertical fina de color: roja en las
      páginas Scouting America, dorada en las de Cub Scouts. Es el gesto que hace reconocible
      la marca sin repetir el logo, y lo usamos igual.
    </p>
    <div class="cols" style="margin-top:var(--space-5)">
      <div class="hero__mark ruled" style="padding:var(--space-6)">
        <p style="margin:0; color:var(--text-muted)">Panel Cub Scouts · regla dorada</p>
      </div>
      <div class="hero__mark ruled ruled--sa" style="padding:var(--space-6)">
        <p style="margin:0; color:var(--text-muted)">Panel Scouting America · regla roja</p>
      </div>
    </div>
  </section>

  <section>
    <p class="subhead">Motion y profundidad</p>
    <h2 class="display">Motion por capas</h2>
    <p class="prose-note">
      El sistema se mueve, pero no por igual en todas partes. <strong>Capa A</strong> —lo que
      abre el papá desde WhatsApp (<code>carta.html</code>, <code>mazo.html</code>)— es
      ultraligera: solo microtransiciones de 160 ms o menos. <strong>Capa B</strong> —el resto—
      lleva la animación completa: entrada orquestada, contadores, reveals al hacer scroll.
    </p>
    <div class="scroller" style="margin-top:var(--space-4)">
      <table>
        <caption>Duraciones y curvas tokenizadas. Solo se animan <code>transform</code> y <code>opacity</code>. Con <code>prefers-reduced-motion</code> todo colapsa.</caption>
        <thead>
          <tr><th>Token</th><th class="num">Valor</th><th>Uso</th></tr>
        </thead>
        <tbody>
          <tr><td><code>--dur-micro</code></td><td class="num">120 ms</td><td>press de botones y chips</td></tr>
          <tr><td><code>--dur-short</code></td><td class="num">160 ms</td><td>microtransiciones (capa A incluida)</td></tr>
          <tr><td><code>--dur-mid</code></td><td class="num">280 ms</td><td>acordeones, barras de progreso</td></tr>
          <tr><td><code>--dur-long</code></td><td class="num">420 ms</td><td>entradas orquestadas, diálogos</td></tr>
          <tr><td><code>--ease-out</code> / <code>--ease-in</code> / <code>--ease-in-out</code></td><td class="num">—</td><td>las tres curvas del sistema; nada fuera de ellas</td></tr>
        </tbody>
      </table>
    </div>
    <p class="prose-note" style="margin-top:var(--space-4)">
      Una sola entrada orquestada por página (<code>.rise</code> escalonado por <code>--i</code>,
      techo ~500 ms), reveals de una sola vez con <code>.rise-io</code> + <code>Shell.reveals</code>,
      contadores con <code>Shell.tick</code>, y view transitions entre páginas cuando el navegador
      las soporta. El fade-up por sección al hacer scroll está prohibido.
    </p>

    <h3>Profundidad</h3>
    <p class="prose-note">
      Dos sombras de papel, sin color y cortas: <code>--shadow-paper</code> en reposo (tiles) y
      <code>--shadow-lift</code> al levantarse (hover en tarjetas y miniaturas). Nunca sobre el
      logo ni sobre superficies oscuras.
    </p>

    <h3>Tile fotográfico</h3>
    <p class="prose-note">
      <code>.tile--foto</code> lleva una foto oficial del brandbook con un velo
      (<code>--scrim-tile-a/b</code>) que garantiza el contraste del nombre. La imagen crece
      3 % al hover. Es la variante por defecto del índice de barajas.
    </p>
  </section>

  <section>
    <p class="subhead">Componentes</p>
    <h2 class="display">Botones</h2>
    <p class="prose-note">Área táctil mínima 44 px. El primario es azul; el dorado se reserva para la acción de la carta.</p>
    <div style="max-width:22rem; margin-top:var(--space-4)">
      <button class="btn btn--primary" type="button">Ver las barajas</button>
      <button class="btn btn--accent" type="button">Ver la actividad</button>
      <button class="btn btn--ghost" type="button">Soy líder de den</button>
    </div>
  </section>

  <section>
    <p class="subhead">Contenido</p>
    <h2 class="display">Las tarjetas</h2>
    <p class="prose-note">
      La app muestra <strong>la lámina oficial</strong>, extraída del PDF del baraja. El brief lo
      exige (§12): las tarjetas son contenido del programa y hay que preservar su apariencia.
      Debajo de cada una va el mismo contenido en texto, plegado — una imagen no la lee un
      lector de pantalla ni se puede copiar, y en conexión lenta el texto llega primero.
    </p>
    <p class="prose-note">
      La plantilla propia queda como respaldo, para las tarjetas que todavía no se exportaron.
    </p>
    <div class="specimens" id="specimens" style="margin-top:var(--space-6)"></div>
  </section>

</main>

<footer class="foot-line">
  <p>Derivado del Scouting America Brand Guidelines 2024 · Preparados para el futuro.®</p>
  <a href="/site/index.html">Inicio</a>
</footer>

<script src="/projects/scouting-america/assets/js/api.js?v=vffaf126a"></script>
<script src="/projects/scouting-america/assets/js/card.js?v=vffaf126a"></script>
<script>
(async () => {

  const paint = (id, list) => {
    document.getElementById(id).innerHTML = list.map(c => `
      <div class="swatch">
        <div class="swatch__chip" style="background:${c.hex}; color:${c.on}">Aa</div>
        <div class="swatch__meta">
          <div>${c.name}</div>
          <code>${c.hex}</code>
          <span>${c.note}</span>
        </div>
      </div>`).join('');
  };

  paint('sw-primary', [
    { name: 'Scouting America Red',   hex: '#CE1126', on: '#FFFFFF', note: 'PMS 186 · display y acento' },
    { name: 'Scouting America Blue',  hex: '#003F87', on: '#FFFFFF', note: 'PMS 294 · acción y enlaces' },
    { name: 'Scouting America Tan',   hex: '#D6CEBD', on: '#232528', note: 'reglas y superficies' },
    { name: 'Scouting America Gray',  hex: '#515354', on: '#FFFFFF', note: 'texto' },
    { name: 'Scouting America White', hex: '#FFFFFF', on: '#232528', note: 'el quinto color' }
  ]);

  paint('sw-secondary', [
    { name: 'Pale Blue', hex: '#9AB3D5', on: '#232528', note: 'de Blue · sobre azul oscuro' },
    { name: 'Dark Blue', hex: '#003366', on: '#FFFFFF', note: 'de Blue · superficies oscuras' },
    { name: 'Light Tan', hex: '#E9E9E4', on: '#232528', note: 'de Tan · superficie alterna' },
    { name: 'Dark Tan',  hex: '#AD9D7B', on: '#232528', note: 'de Tan · decorativo' },
    { name: 'Pale Gray', hex: '#858787', on: '#FFFFFF', note: 'de Gray · texto secundario' },
    { name: 'Dark Gray', hex: '#232528', on: '#FFFFFF', note: 'de Gray · texto fuerte' }
  ]);

  /* Contrastes: se calculan acá, no se copian a mano. */
  const lum = hex => {
    const [r, g, b] = [1, 3, 5].map(i => parseInt(hex.substr(i, 2), 16) / 255);
    const f = c => c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
    return 0.2126 * f(r) + 0.7152 * f(g) + 0.0722 * f(b);
  };
  const ratio = (a, b) => {
    const [x, y] = [lum(a), lum(b)].sort((m, n) => n - m);
    return (x + 0.05) / (y + 0.05);
  };

  const pairs = [
    ['Dark Gray / blanco', '#232528', '#FFFFFF'],
    ['Dark Blue / blanco', '#003366', '#FFFFFF'],
    ['Blue / blanco', '#003F87', '#FFFFFF'],
    ['Gray / blanco', '#515354', '#FFFFFF'],
    ['Blue / Light Tan', '#003F87', '#E9E9E4'],
    ['Blue / Tan', '#003F87', '#D6CEBD'],
    ['Red / blanco', '#CE1126', '#FFFFFF'],
    ['Blanco / Blue', '#FFFFFF', '#003F87'],
    ['Blanco / Red', '#FFFFFF', '#CE1126'],
    ['Pale Blue / Dark Blue', '#9AB3D5', '#003366'],
    ['Red / Tan', '#CE1126', '#D6CEBD'],
    ['Dark Tan / blanco', '#AD9D7B', '#FFFFFF'],
    ['Pale Gray / blanco', '#858787', '#FFFFFF']
  ];

  document.getElementById('contrast-rows').innerHTML = pairs.map(([label, a, b]) => {
    const r = ratio(a, b);
    const ok = v => v ? '<span class="pass">pasa</span>' : '<span class="fail">falla</span>';
    return `<tr><td>${label}</td><td class="num">${r.toFixed(2)}:1</td>
            <td>${ok(r >= 4.5)}</td><td>${ok(r >= 3)}</td></tr>`;
  }).join('');

  /* Escala tipográfica */
  document.getElementById('type-scale').innerHTML = [
    ['display', '--step-4', 'Sepa qué hace su hijo'],
    ['display', '--step-3', 'Las barajas'],
    ['serif',   '--step-1', 'Bobcat (Gato Montés)'],
    ['serif',   '--step-0', 'Durante una caminata, los Cub Scouts toman fotos o llevan un cuaderno.'],
    ['cond',    '--step--1', 'REQUISITO 5 DE 9'],
    ['cond',    '--step-0',  'ENVIAR POR WHATSAPP']
  ].map(([kind, step, text]) => {
    const style = {
      display: 'font-family:var(--font-display); color:var(--text-display);',
      serif:   'font-family:var(--font-text); color:var(--text);',
      cond:    'font-family:var(--font-ui); font-weight:700; letter-spacing:.06em; color:var(--text-strong);'
    }[kind];
    const rol = { display: 'display · serif rojo', serif: 'prosa · serif', cond: 'etiqueta · condensada bold' }[kind];
    return `<p style="${style} font-size:var(${step}); line-height:1.15; margin:0 0 var(--space-4)">
      ${text}
      <span style="font-family:var(--font-ui); font-size:var(--step--1); font-weight:400; letter-spacing:0; text-transform:none; color:var(--text-muted)">
        — ${step} · ${rol}
      </span></p>`;
  }).join('');

  /* Especímenes de carta */
  const deck = await API.getDeck('bear');
  const muestras = [
    ['resource',  'Carta de recursos'],
    ['adventure', 'Portada de Adventure'],
    ['activity',  'Carta de actividad']
  ].map(([skin, label]) => [deck.cards.find(c => c.skin === skin), label]).filter(([c]) => c);

  muestras.forEach(([card, label]) => {
    const wrap = document.createElement('div');
    wrap.innerHTML = `<p class="specimen__label">${label}</p>` +
                     Card.render(card, { deck, lang: 'es',
                                         person: { leader: 'Marta Rivas', pack: 'Pack 77' } });
    document.getElementById('specimens').append(wrap);
  });
})();
</script>
</body>
</html>
