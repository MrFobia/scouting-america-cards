<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Profile · Scouting America Admin</title>
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
  Perfil del admin. Datos básicos de la cuenta y las dos salidas que una cuenta
  con contraseña tiene que tener: cambiarla y cerrar sesión.

  El correo NO se edita acá: es la llave de la cuenta, y en Capa A además es lo
  que empareja contra admins.json. Cambiarlo desde el navegador dejaría al
  admin fuera de su propio tablero.
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
    location.replace('/site/admin/entrar.html?volver=' + encodeURIComponent(location.pathname));
    return;
  }
  const admin = API.getAdmin();
  document.documentElement.lang = 'en';

  Shell.mountHeader(document.getElementById('pagehead'), {
    eyebrow: admin.organizacion,
    title: 'Your account',
    accent: 'account',
    sub: 'Account details and access.',
    compact: true,
    soloIngles: true
  });
  Shell.mountFooter(document.getElementById('pagefoot'));

  const cont = document.getElementById('contenido');
  const desde = new Date(admin.desde).toLocaleString('en-US',
    { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });

  /* Dos columnas en escritorio: los datos de la cuenta a la izquierda y lo que
     se puede HACER con ella a la derecha. En una sola columna angosta —como
     estaba— el perfil era la única pantalla del tablero que no usaba el ancho,
     y había que bajar hasta el final para encontrar el botón de salir. */
  cont.innerHTML = `
    <div class="panel-cols">
    <section class="panel rise" style="--i:0">
      <h2 class="page__label">Account</h2>
      <dl class="datalist">
        <div><dt>Name</dt><dd>${admin.nombre}</dd></div>
        <div><dt>Email</dt><dd>${admin.correo}</dd></div>
        <div><dt>Organization</dt><dd>${admin.organizacion}</dd></div>
        <div><dt>Role</dt><dd>Administrator — sees every leader</dd></div>
        <div><dt>Signed in since</dt><dd>${desde}</dd></div>
      </dl>
      <p class="metric__note" style="margin-top:var(--space-4)">
        The email is the key to this account and can't be changed here.
        Ask Scouting America to move it.
      </p>

    </section>

    <aside>
    <section class="panel rise" style="--i:1">
      <h2 class="page__label">Password</h2>
      <form id="clave-form">
        <label class="field">
          <span class="field__label">Current password</span>
          <input name="actual" type="password" autocomplete="current-password" required minlength="6">
        </label>
        <label class="field">
          <span class="field__label">New password</span>
          <input name="nueva" type="password" autocomplete="new-password" required minlength="8"
                 aria-describedby="clave-ayuda">
        </label>
        <label class="field">
          <span class="field__label">Repeat new password</span>
          <input name="repite" type="password" autocomplete="new-password" required minlength="8">
        </label>
        <p id="clave-ayuda" class="metric__note" style="margin:0 0 var(--space-4)">
          At least 8 characters.
        </p>
        <p class="form-msg" id="msg-clave" aria-live="polite"></p>
        <p class="actions">
          <button class="btn btn--primary" type="submit">Change password</button>
        </p>
      </form>
      <p class="metric__note">
        Forgot it? <a href="/site/admin/recuperar.html">Reset it by email</a>.
      </p>
    </section>

    <section class="panel rise" style="--i:2; margin-top:var(--space-5)">
      <h2 class="page__label">Session</h2>
      <p class="metric__note" style="margin-bottom:var(--space-4)">
        Signing out only closes it on this device.
      </p>
      <p class="actions">
        <button class="btn" type="button" id="salir">Sign out</button>
      </p>
    </section>
    </aside>
    </div>`;

  /* ATENCIÓN — no hay backend. La contraseña no se guarda en ningún lado (ni
     acá ni en la puerta), así que este formulario VALIDA y confirma, pero no
     tiene nada contra qué cambiar. Se deja construido porque la pantalla es
     parte del flujo que se pidió, y se dice en pantalla en vez de fingir que
     algo cambió. */
  const form = document.getElementById('clave-form');
  form.addEventListener('submit', e => {
    e.preventDefault();
    const msg = document.getElementById('msg-clave');
    if (form.nueva.value !== form.repite.value) {
      msg.textContent = 'The two new passwords don’t match.';
      return;
    }
    if (form.nueva.value === form.actual.value) {
      msg.textContent = 'The new password has to be different from the current one.';
      return;
    }
    form.reset();
    msg.innerHTML = Shell.notice({ icono: 'info', html:
      'Prototype: passwords are not stored yet, so nothing changed. '
      + 'This screen is here so the flow is agreed before the backend exists.' });
  });

  document.getElementById('salir').addEventListener('click', () => {
    API.salirAdmin();
    location.href = '/preview/945';
  });

  Shell.reveals(cont);
})();
</script>
</body>
</html>
