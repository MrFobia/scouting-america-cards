/**
 * sw.js — service worker.
 *
 * No está acá para poner un badge de "PWA". Está porque la audiencia del brief
 * (§5, perfil D) abre esto en un Android viejo, a veces compartido, con datos
 * limitados. Cada lámina que no se vuelve a descargar es plata del papá.
 *
 * Tres estrategias, una por tipo de recurso:
 *   · navegación (HTML)         → network-first con caída al caché: si hay
 *     señal ves lo último, si no ves lo de ayer.
 *   · CSS/JS/logos              → stale-while-revalidate. Cache-first acá sería
 *     un bug: una corrección no llegaría nunca al teléfono que ya tiene la
 *     versión vieja guardada.
 *   · láminas de tarjeta        → cache-first. Son inmutables: la p059 de Bear
 *     no va a cambiar nunca. Bajarla dos veces es un bug de respeto.
 *   · datos JSON                → stale-while-revalidate: pinta al instante lo
 *     guardado y refresca en segundo plano.
 */

const VERSION = 'vffaf126a';
const SHELL = `shell-${VERSION}`;
const CARDS = `cards-${VERSION}`;
const DATA  = `data-${VERSION}`;

const SHELL_URLS = [
  '/site/index.html',
  '/site/barajas.html',
  '/site/mazo.html',
  '/site/lider/mazo-nuevo.html',
  '/site/lider/entrar.html',
  '/site/baraja.html',
  '/site/carta.html',
  '/site/envios.html',
  '/site/como-usar.html',
  '/site/progreso.html',
  '/site/lider/index.html',
  '/site/lider/perfil.html',
  '/site/assets/css/tokens.css',
  '/site/assets/css/app.css',
  '/site/assets/js/api.js',
  '/site/assets/js/app.js',
  '/site/assets/js/card.js',
  '/site/assets/js/shell.js',
  '/site/assets/img/scouting-america-signature.png',
  '/site/assets/img/scouting-america-signature-white.png',
  '/site/manifest.webmanifest'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(SHELL)
      // addAll falla entero si un recurso falla; acá preferimos degradar.
      .then(cache => Promise.allSettled(SHELL_URLS.map(u => cache.add(u))))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  const vigentes = [SHELL, CARDS, DATA];
  event.waitUntil(
    caches.keys()
      .then(keys => Promise.all(keys.filter(k => !vigentes.includes(k)).map(k => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

async function cacheFirst(request, cacheName) {
  const cache = await caches.open(cacheName);
  const hit = await cache.match(request);
  if (hit) return hit;
  // Bug 20-ago: esto no tenía try/catch. Si el fetch fallaba (un corte de
  // datos móviles a mitad de carga, algo que en este público SÍ pasa), la
  // promesa de respondWith() rechazaba sin capturar — Chrome trata eso como
  // error de red duro, sin reintento, y la lámina queda rota para siempre
  // (nunca llegó a cachearse, así que el próximo intento vuelve a fallar
  // igual). Un reintento entero, no solo capturar el error: en datos
  // móviles el primer intento es justo el que más falla.
  try {
    const res = await fetch(request);
    if (res.ok) cache.put(request, res.clone());
    return res;
  } catch {
    try {
      const res = await fetch(request);
      if (res.ok) cache.put(request, res.clone());
      return res;
    } catch {
      return Response.error();
    }
  }
}

async function staleWhileRevalidate(request, cacheName) {
  const cache = await caches.open(cacheName);
  const hit = await cache.match(request);

  const revalidar = () => fetch(request).then(res => {
    if (res.ok) cache.put(request, res.clone());
    return res;
  });

  /* Bug 20-ago — la raíz de los "esqueletos para siempre" y las imágenes
     rotas al entrar/salir de sesión. La versión anterior era
     `return hit || fresh || Response.error()` con fresh.catch(() => null):
     dos errores en una línea.
       1. `fresh` es una Promise (siempre truthy) — el Response.error()
          era código muerto.
       2. Si no había copia en caché y el fetch fallaba, se le respondía
          NULL al navegador → error de red duro. Si el recurso era shell.js
          o app.js, la página quedaba muerta sin mensaje: esqueletos
          congelados, imágenes rotas. Y como cada versión nueva del SW
          arranca con el caché vacío, la ventana para pegarle a esto se
          abría en cada actualización + cada recarga de login/logout.
     Ahora: con caché, se sirve el caché y se revalida detrás (igual que
     antes). Sin caché, red con un reintento — mismo criterio que
     cacheFirst — y recién ahí Response.error() de verdad. */
  if (hit) {
    revalidar().catch(() => { /* la revalidación de fondo puede fallar */ });
    return hit;
  }
  try {
    return await revalidar();
  } catch {
    try {
      return await revalidar();
    } catch {
      return Response.error();
    }
  }
}

async function networkFirst(request) {
  const cache = await caches.open(SHELL);
  try {
    // `no-store` salta el caché HTTP del navegador, no solo el del SW. Sin
    // esto el servidor devolvía el HTML nuevo pero el teléfono seguía pintando
    // el viejo: las páginas no llevan ?v= como el CSS y el JS.
    const res = await fetch(request.url, { cache: 'no-store', credentials: 'omit' });
    if (res.ok) cache.put(request, res.clone());
    return res;
  } catch {
    const hit = await cache.match(request) || await cache.match('/site/index.html');
    if (hit) return hit;
    return new Response(
      '<!doctype html><meta charset="utf-8"><title>Sin conexión</title>' +
      '<p style="font-family:system-ui;padding:2rem">Sin conexión. Vuelve a intentar cuando tengas señal.</p>',
      { headers: { 'Content-Type': 'text/html; charset=utf-8' }, status: 503 }
    );
  }
}

self.addEventListener('fetch', event => {
  const { request } = event;
  if (request.method !== 'GET') return;

  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;   // Google Fonts se resuelve solo

  if (request.mode === 'navigate') {
    event.respondWith(networkFirst(request));
  } else if (url.pathname.includes('/assets/img/cards/')) {
    event.respondWith(cacheFirst(request, CARDS));
  } else if (url.pathname.includes('/assets/data/')) {
    // Los barajas viven con los assets para quedar dentro del scope del SW: en
    // /data suelto no se cacheaban, así que la app no servía nada sin red y el
    // navegador se quedaba con su copia vieja del índice.
    event.respondWith(staleWhileRevalidate(request, DATA));
  } else if (url.pathname.includes('/assets/')) {
    // CSS y JS van stale-while-revalidate, NO cache-first: con cache-first una
    // corrección no llegaría nunca al teléfono que ya tiene la versión vieja.
    // Las láminas sí son cache-first porque son inmutables (rama de arriba).
    event.respondWith(staleWhileRevalidate(request, SHELL));
  }
});
