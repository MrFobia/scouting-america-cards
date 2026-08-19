// Arnés mínimo: localStorage y location falsos, y los JSON desde disco.
import fs from 'fs';
const mem = new Map();
global.localStorage = { getItem:k=>mem.has(k)?mem.get(k):null, setItem:(k,v)=>mem.set(k,v) };
global.location = { origin:'http://localhost:8099', search:'' };
global.matchMedia = () => ({ matches:false });
global.fetch = async (p) => {
  const disk = 'site' + p.replace('/site','');
  return { ok:true, json: async () => JSON.parse(fs.readFileSync(disk,'utf8')) };
};
const src = fs.readFileSync('site/assets/js/api.js','utf8');
eval(src + '\nglobalThis.API = API;');

const A = globalThis.API;
let fail = 0;
const ok = (c,m)=>{ console.log((c?'  ok   ':'  FALLA')+' '+m); if(!c) fail++; };

// crear
const m = A.createMazo({ nombre:'Pack 77', cardIds:['bear-r-instructions','bear-r-instructions','bear-r-welcome-new-den-leader'] });
ok(m.id.startsWith('mz-'), 'createMazo genera id mz-*');
ok(m.cardIds.length===2, 'cartas repetidas se deduplican (3 dadas -> 2)');
ok(A.getMazo(m.id).nombre==='Pack 77', 'getMazo lo recupera');
ok(A.getMazos().length===1, 'getMazos lista el del lider');
try { A.createMazo({nombre:'  '}); ok(false,'nombre vacio deberia fallar'); }
catch { ok(true,'createMazo rechaza nombre vacio'); }

// update / delete
A.updateMazo(m.id,{ nombre:'Pack 77 · semana 2' });
ok(A.getMazo(m.id).nombre==='Pack 77 · semana 2', 'updateMazo cambia el nombre');

// share por mazo
const sh = A.createShare({ mazoId:m.id, lang:'es' });
ok(sh.mazoId===m.id && !('cardId' in sh), 'createShare guarda mazoId y no cardId');
const url = A.shareUrl(sh);
ok(url.includes('/site/mazo.html?m='+m.id), 'shareUrl apunta a mazo.html con ?m=');
ok(url.includes('s='+sh.id), 'shareUrl conserva el shareId para medir');

// legacy
const shOld = A.createShare({ cardId:'bear-r-instructions', lang:'en' });
ok(shOld.cardId && !shOld.mazoId, 'envio legacy conserva cardId');
ok(A.shareUrl(shOld).includes('/site/carta.html?c='), 'shareUrl legacy sigue yendo a carta.html');
try { A.createShare({}); ok(false,'share sin nada deberia fallar'); }
catch { ok(true,'createShare rechaza envio sin mazo ni carta'); }

// resolver cartas
const { cards } = await A.getMazoCards(m.id);
ok(cards.length===2, `getMazoCards resuelve las 2 cartas (dio ${cards.length})`);
A.updateMazo(m.id,{ cardIds:[...A.getMazo(m.id).cardIds,'inexistente-99'] });
const r2 = await A.getMazoCards(m.id);
ok(r2.cards.length===2, 'una carta inexistente se omite en vez de tumbar la vista');

// stats siguen vivas
ok(A.getLeaderStats().length===2, 'getLeaderStats cuenta los dos envios');

A.deleteMazo(m.id);
ok(A.getMazo(m.id)===null, 'deleteMazo lo quita');

console.log(fail===0 ? '\nTODO VERDE' : `\n${fail} FALLAS`);
process.exit(fail?1:0);
