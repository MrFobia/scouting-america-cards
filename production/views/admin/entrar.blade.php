<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in · Scouting America Admin</title>
<meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/projects/scouting-america-cards/assets/css/tokens.css?v=v4f83ebeb">
<link rel="stylesheet" href="/projects/scouting-america-cards/assets/css/app.css?v=v4f83ebeb">
</head>
<body data-consola="admin">

<!--
  ATENCIÓN — ESTO NO ES SEGURIDAD. Igual que la puerta del líder: no hay
  backend, la "sesión" es una marca en localStorage y quien la borre entra
  igual. No debe presentarse al cliente como una zona protegida.

  Diferencia con la del líder: acá el correo SÍ tiene que estar en
  assets/data/admins.json. La consola del líder deja pasar cualquier correo
  porque es una demo de uso; el tablero del admin muestra a TODOS los líderes,
  y una puerta abierta ahí enseña de más aunque sea un prototipo.

  La contraseña no se guarda: se valida el largo y se descarta.
-->

<a class="skip-link" href="#main">Skip to content</a>
<header id="pagehead"></header>

<main class="page" id="main" style="max-width:28rem">
  <section class="progresscard rise" style="--i:0">
    <p style="margin:0 0 var(--space-4); color:var(--text-muted)">
      This part is for Scouting America. Den leaders sign in
      <a href="/preview/950">on their own console</a>.
    </p>
    <form id="acceso">
      <label class="field">
        <span class="field__label">Email</span>
        <input id="correo" name="correo" type="email" autocomplete="email" required>
      </label>
      <label class="field">
        <span class="field__label">Password</span>
        <input id="clave" name="clave" type="password" autocomplete="current-password"
               required minlength="6" aria-describedby="clave-ayuda">
      </label>
      <p id="clave-ayuda" style="margin:0 0 var(--space-4); font-size:var(--step--1); color:var(--text-muted)">
        Prototype: the password is validated and never stored anywhere.
      </p>
      <p class="form-msg" id="msg" aria-live="polite"></p>
      <p class="actions">
        <button class="btn btn--primary btn--block" type="submit">Sign in</button>
      </p>
    </form>
    <p style="margin:var(--space-4) 0 0; font-size:var(--step--1); color:var(--text-muted)">
      <a href="/preview/1005">Forgot your password?</a>
    </p>
  </section>
</main>

<footer id="pagefoot"></footer>

<script src="/projects/scouting-america-cards/assets/js/api.js?v=v4f83ebeb"></script>
<script src="/projects/scouting-america-cards/assets/js/shell.js?v=v4f83ebeb"></script>
<script>
(async () => {
  Shell.mountHeader(document.getElementById('pagehead'), {
    eyebrow: 'Scouting America',
    title: 'Program dashboard',
    accent: 'dashboard',
    sub: 'Leaders, decks sent and open rates across the program.',
    soloIngles: true
  });
  Shell.mountFooter(document.getElementById('pagefoot'));

  const form = document.getElementById('acceso');
  const msg = document.getElementById('msg');

  form.addEventListener('submit', async e => {
    e.preventDefault();
    const correo = form.correo.value.trim();
    if (!correo || form.clave.value.length < 6) {
      msg.textContent = 'Enter your email and a password of at least 6 characters.';
      return;
    }
    // La contraseña muere acá. El correo sí se comprueba contra admins.json.
    const ok = await API.entrarComoAdmin(correo);
    if (!ok) {
      msg.textContent = 'That email is not an administrator account.';
      return;
    }
    location.href = new URLSearchParams(location.search).get('volver')
      || '/preview/1003';
  });
})();
</script>
</body>
</html>
