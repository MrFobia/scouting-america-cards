<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Your profile · Den Leader Console</title>
<meta name="robots" content="noindex">
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

<!--
  Consola del líder · Índice · nav N9 · footer Ft2

  Pedido del 19-ago: "cerrar sesión" solo vivía en el pie de página y nadie
  lo encontraba ahí. Esto le da a la cuenta un lugar propio —foto, nombre,
  pack— con la salida separada y visible, no escondida en un texto chico.

  La foto es SOLO del líder, un adulto que se identificó con su correo. No es
  dato de menor (ver design.md § Fotografía y CLAUDE.md "cero datos de
  menores"): no hay entidad "niño" en el sistema y esto no la crea.
-->

<a class="skip-link" href="#main">Skip to content</a>
<header id="pagehead"></header>

<main class="page page--glow" id="main" style="max-width:32rem">

  <section class="progresscard rise" style="--i:0; margin-bottom:var(--space-5)">
    <div style="display:flex; align-items:center; gap:var(--space-4)">
      <div class="avatar-edit">
        <button type="button" class="avatar avatar--lg" id="avatar-preview"
                aria-label="Change your photo">?</button>
        <span class="avatar-edit__btn" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
               stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 8.5A1.5 1.5 0 0 1 5.5 7h1.8l.9-1.5a1.5 1.5 0 0 1 1.3-.7h5a1.5 1.5 0 0 1 1.3.7L16.7 7h1.8A1.5 1.5 0 0 1 20 8.5v9A1.5 1.5 0 0 1 18.5 19h-13A1.5 1.5 0 0 1 4 17.5Z"/>
            <circle cx="12" cy="13" r="3.2"/>
          </svg>
        </span>
        <input type="file" id="avatar-input" accept="image/*" hidden>
      </div>
      <div style="min-width:0">
        <p style="margin:0; font-weight:700; font-size:var(--step-1)" id="preview-nombre">—</p>
        <p style="margin:var(--space-1) 0 0; color:var(--text-muted)" id="preview-pack">—</p>
      </div>
    </div>
    <p style="margin:var(--space-3) 0 0; font-size:var(--step--1); color:var(--text-muted)">
      This is how families will see you on every deck you send.
    </p>
  </section>

  <section class="progresscard rise" style="--i:1; margin-bottom:var(--space-5)">
    <p class="tile__label" style="color:var(--text-muted); margin:0 0 var(--space-4)">Your info</p>
    <form id="form-perfil">
      <label class="field">
        <span class="field__label">Your name</span>
        <input id="leader" name="leader" type="text" autocomplete="name" required maxlength="60">
      </label>
      <label class="field">
        <span class="field__label">Pack</span>
        <input id="pack" name="pack" type="text" autocomplete="off" maxlength="40" placeholder="Pack 77">
      </label>
      <label class="field">
        <span class="field__label">Council (optional)</span>
        <input id="council" name="council" type="text" autocomplete="off" maxlength="60">
      </label>
      <p class="form-msg" id="msg" aria-live="polite"></p>
      <p class="actions">
        <button class="btn btn--primary" type="submit">Save</button>
      </p>
    </form>
  </section>

  <!-- Separada del resto — no es una acción más de la cuenta, es la salida. -->
  <section class="rise" style="--i:2">
    <button class="btn btn--ghost btn--block" id="salir" type="button">Sign out</button>
  </section>

</main>

<footer id="pagefoot"></footer>

<script src="/projects/scouting-america/assets/js/api.js?v=vffaf126a"></script>
<script src="/projects/scouting-america/assets/js/shell.js?v=vffaf126a"></script>
<script>
(async () => {
  if (!API.haySesionLider()) {
    location.replace('/site/lider/entrar.html?volver=' + encodeURIComponent(location.pathname));
    return;
  }

  Shell.mountHeader(document.getElementById('pagehead'), {
    eyebrow: 'For den leaders',
    title: 'Your profile',
    accent: 'profile',
    sub: 'Your name, your pack, and your photo: what families see on every deck.',
    back: '/site/lider/index.html',
    crumbs: [{ label: 'My console', href: '/site/lider/index.html' }, { label: 'Profile' }]
  });
  Shell.mountFooter(document.getElementById('pagefoot'));

  const $ = id => document.getElementById(id);
  let perfil = API.getProfile();
  let fotoNueva = null;   // dataURL en memoria hasta que se guarde

  const pintarAvatar = () => {
    const inicial = (perfil.leader || '?').trim().charAt(0).toUpperCase();
    $('avatar-preview').innerHTML = (fotoNueva || perfil.photo)
      ? `<img src="${fotoNueva || perfil.photo}" alt="">`
      : inicial;
  };

  const pintarPreview = () => {
    $('preview-nombre').textContent = perfil.leader || 'No name yet';
    $('preview-pack').textContent = [perfil.pack, perfil.council].filter(Boolean).join(' · ') || 'No pack';
  };

  $('leader').value = perfil.leader || '';
  $('pack').value = perfil.pack || '';
  $('council').value = perfil.council || '';
  pintarAvatar();
  pintarPreview();

  /* La foto se achica a 200×200 antes de guardar: una foto de teléfono sin
     comprimir en localStorage (5 MB de cupo total) se come el cupo de las
     tarjetas que el papá ya tiene cacheadas. Un avatar no necesita más. */
  const achicar = file => new Promise((resolve, reject) => {
    const img = new Image();
    const url = URL.createObjectURL(file);
    img.onload = () => {
      const T = 200;
      const canvas = document.createElement('canvas');
      canvas.width = T; canvas.height = T;
      const ctx = canvas.getContext('2d');
      const s = Math.min(img.width, img.height);
      const sx = (img.width - s) / 2, sy = (img.height - s) / 2;
      ctx.drawImage(img, sx, sy, s, s, 0, 0, T, T);
      URL.revokeObjectURL(url);
      resolve(canvas.toDataURL('image/jpeg', 0.85));
    };
    img.onerror = reject;
    img.src = url;
  });

  $('avatar-preview').addEventListener('click', () => $('avatar-input').click());
  $('avatar-input').addEventListener('change', async e => {
    const file = e.target.files[0];
    if (!file) return;
    try {
      fotoNueva = await achicar(file);
      pintarAvatar();
    } catch {
      $('msg').textContent = "Couldn't read that image. Try another one.";
    }
  });

  $('form-perfil').addEventListener('submit', e => {
    e.preventDefault();
    const leader = $('leader').value.trim();
    if (!leader) {
      $('msg').textContent = "Enter your name: it's what families will see.";
      $('leader').focus();
      return;
    }
    perfil = API.setProfile({
      leader,
      pack: $('pack').value.trim(),
      council: $('council').value.trim(),
      ...(fotoNueva ? { photo: fotoNueva } : {})
    });
    fotoNueva = null;
    pintarPreview();
    // El estado se muestra donde ocurrió la acción, no en un toast.
    $('msg').textContent = 'Saved ✓';
    setTimeout(() => { $('msg').textContent = ''; }, 2500);
  });

  $('salir').addEventListener('click', () => {
    API.salir();
    location.href = '/site/index.html';
  });
})();
</script>
</body>
</html>
