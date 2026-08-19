/**
 * Sintaxis de los <script> inline de cada vista.
 *
 * Un error de sintaxis en un script inline NO rompe la página: se ve la
 * cabecera, el pie y el CSS, y solo queda vacío el trozo que ese script
 * pintaba. Es decir, degrada a "se ve casi bien", que es la peor forma de
 * fallar. Pasó de verdad: una colision de `const n` dejó el bento de la
 * portada vacío y la página parecía correcta.
 */
import fs from 'fs';
import path from 'path';

const raiz = 'site';
const vistas = [];
(function walk(d) {
  for (const f of fs.readdirSync(d)) {
    const p = path.join(d, f);
    if (fs.statSync(p).isDirectory()) walk(p);
    else if (f.endsWith('.html')) vistas.push(p);
  }
})(raiz);

let fallos = 0;
for (const v of vistas) {
  const s = fs.readFileSync(v, 'utf8');
  const bloques = [...s.matchAll(/<script(?![^>]*\bsrc=)[^>]*>([\s\S]*?)<\/script>/g)];
  bloques.forEach((b, i) => {
    try { new Function(b[1]); }
    catch (e) { console.log(`  FALLA ${v} · script ${i}: ${e.message}`); fallos++; }
  });
}
// Y los .js sueltos
for (const f of fs.readdirSync('site/assets/js')) {
  if (!f.endsWith('.js')) continue;
  try { new Function(fs.readFileSync(`site/assets/js/${f}`, 'utf8')); }
  catch (e) { console.log(`  FALLA site/assets/js/${f}: ${e.message}`); fallos++; }
}

console.log(fallos === 0
  ? `Sintaxis OK — ${vistas.length} vistas y sus scripts inline`
  : `${fallos} scripts con error de sintaxis`);
process.exit(fallos ? 1 : 0);
