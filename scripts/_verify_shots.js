/* Verificación visual + consola del rediseño scouting-america.
   Corre con: node scripts/_verify_shots.js  (usa playwright-core del proyecto vecino) */
const path = require('path');
const { chromium } = require(path.join('/Users/nicolasbarrios/alquilando-manual/node_modules', 'playwright-core'));

const EXE = process.env.HOME + '/Library/Caches/ms-playwright/chromium-1234/chrome-mac-arm64/Google Chrome for Testing.app/Contents/MacOS/Google Chrome for Testing';
const BASE = 'http://localhost:8080';
const OUT = '/tmp/scouting-shots';

const PAGES = [
  ['index',        '/site/index.html'],
  ['barajas',      '/site/barajas.html'],
  ['baraja-bear',  '/site/baraja.html?d=bear&lang=es'],
  ['carta',        '/site/carta.html?d=bear&c=bear-adv-bobcat&lang=es'],
  ['como-usar',    '/site/como-usar.html'],
  ['progreso',     '/site/progreso.html'],
  ['guia',         '/site/guia-de-estilos.html'],
];
const LIDER = [
  ['lider-consola', '/site/lider/index.html'],
  ['lider-mazo',    '/site/lider/mazo-nuevo.html'],
  ['envios',        '/site/envios.html'],
];

(async () => {
  const browser = await chromium.launch({ executablePath: EXE, headless: true });
  const errors = [];

  async function shoot(page, name, w, h = 900) {
    await page.setViewportSize({ width: w, height: h });
    await page.waitForTimeout(900); // deja terminar la entrada orquestada
    const overflow = await page.evaluate(() =>
      document.scrollingElement.scrollWidth - document.documentElement.clientWidth);
    if (overflow > 0) errors.push(`${name}@${w}: scroll horizontal +${overflow}px`);
    // Gate 49 real: cuenta líneas del TEXTO del botón con Range, no la altura
    // de la caja (los .tab llevan icono arriba y darían falsos positivos).
    const wrap = await page.evaluate(() =>
      [...document.querySelectorAll('.btn, .tab__label, .link-arrow')]
        .filter(el => el.offsetHeight > 0 && el.firstChild)
        .map(el => {
          const r = document.createRange();
          r.selectNodeContents(el);
          const tops = new Set([...r.getClientRects()].map(x => Math.round(x.top)));
          return { t: el.textContent.trim().slice(0, 40), lineas: tops.size };
        })
        .filter(x => x.lineas > 1));
    wrap.forEach(x => errors.push(`${name}@${w}: texto clicable en ${x.lineas} líneas: «${x.t}»`));
    try {
      await page.screenshot({ path: `${OUT}/${name}-${w}.png`, animations: 'disabled', timeout: 15000 });
      console.log(`ok ${name}@${w}${overflow > 0 ? '  OVERFLOW +' + overflow : ''}`);
    } catch (e) {
      errors.push(`${name}@${w}: screenshot falló: ${e.message.split('\n')[0]}`);
    }
  }

  function wire(page, tag) {
    page.on('console', m => { if (m.type() === 'error') errors.push(`${tag}: console.error: ${m.text()}`); });
    page.on('pageerror', e => errors.push(`${tag}: pageerror: ${e.message}`));
    page.on('response', r => { if (r.status() === 404) errors.push(`${tag}: 404 ${r.url()}`); });
  }

  const ctx = await browser.newContext({ deviceScaleFactor: 2 });
  const page = await ctx.newPage();
  wire(page, 'pub');
  for (const [name, url] of PAGES) {
    await page.goto(BASE + url, { waitUntil: 'networkidle' });
    await shoot(page, name, 375);
  }
  // anchos críticos extra
  for (const w of [320, 414, 768]) {
    await page.goto(BASE + '/site/index.html', { waitUntil: 'networkidle' });
    await shoot(page, 'index', w);
    await page.goto(BASE + '/site/barajas.html', { waitUntil: 'networkidle' });
    await shoot(page, 'barajas', w);
  }

  // sesión de líder de prototipo: cualquier correo + clave >= 6.
  // Contexto aparte con reduced-motion: en headless viejo las animaciones
  // quedan congeladas en t=0 (no hay BeginFrame) y el screenshot nunca sale;
  // con motion reducido todo es instantáneo y el layout se verifica igual.
  const ctx2 = await browser.newContext({ deviceScaleFactor: 2, reducedMotion: 'reduce' });
  const page2 = await ctx2.newPage();
  wire(page2, 'lider');
  await page2.goto(BASE + '/site/lider/entrar.html', { waitUntil: 'networkidle' });
  await shoot(page2, 'lider-entrar', 375);
  await page2.fill('#correo', 'marta@example.org');
  await page2.fill('#clave', 'seis66');
  await page2.evaluate(() => document.querySelector('#acceso button[type=submit]').click());
  await page2.waitForURL('**/lider/index.html**', { timeout: 8000 });
  await page2.waitForLoadState('networkidle');
  await shoot(page2, 'lider-consola', 375);
  await shoot(page2, 'lider-consola', 320);

  await page2.goto(BASE + '/site/lider/mazo-nuevo.html', { waitUntil: 'networkidle' });
  await shoot(page2, 'lider-mazo', 375);
  await shoot(page2, 'lider-mazo', 320);
  // paso 2 con los acordeones
  await page2.fill('#nombre', 'Pack 77 · semana del 18');
  await page2.evaluate(() => document.querySelector('#form-nombre button[type=submit]').click());
  await page2.waitForTimeout(1200);
  await page2.evaluate(() => document.querySelector('.baraja__sum').click());
  await page2.waitForTimeout(500);
  await page2.evaluate(() => document.querySelector('.adv__sum').click());
  await page2.waitForTimeout(500);
  await shoot(page2, 'lider-mazo-paso2', 375);

  await page2.goto(BASE + '/site/envios.html', { waitUntil: 'networkidle' });
  await shoot(page2, 'envios', 375);
  await ctx2.close();

  await browser.close();
  console.log('\n--- RESULTADO ---');
  if (errors.length) { console.log('PROBLEMAS:'); errors.forEach(e => console.log(' -', e)); process.exitCode = 1; }
  else console.log('Sin errores de consola ni scroll horizontal.');
})();
