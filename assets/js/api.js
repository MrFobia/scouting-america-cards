/**
 * api.js — única frontera de datos de la app.
 *
 * Hoy responde desde JSON estático + localStorage (Capa A, prototipo en ai.backbone).
 * Cuando exista el backend (Capa B: my.business / API propia), se cambia MODE a 'remote'
 * y se implementan los mismos métodos contra HTTP. Ninguna vista se reescribe.
 *
 * Regla: ninguna vista lee JSON ni localStorage directo. Todo pasa por acá.
 * Privacidad: no se guarda ningún dato del papá ni del niño. Solo contadores por envío.
 */
const API = (() => {
  const MODE = 'local';           // 'local' | 'remote'
  const BASE = '/projects/scouting-america-cards/assets/data';  // los JSON viajan con los assets
  const REMOTE = '';              // base de la API real, se llena en Capa B
  const NS = 'sa:';               // prefijo de localStorage

  const store = {
    get(key, fallback) {
      try { return JSON.parse(localStorage.getItem(NS + key)) ?? fallback; }
      catch { return fallback; }
    },
    set(key, value) {
      try { localStorage.setItem(NS + key, JSON.stringify(value)); } catch { /* modo privado */ }
    }
  };

  const cache = new Map();
  async function loadJSON(path) {
    if (cache.has(path)) return cache.get(path);
    const res = await fetch(path, { credentials: 'omit' });
    if (!res.ok) throw new Error(`No se pudo cargar ${path} (${res.status})`);
    const data = await res.json();
    cache.set(path, data);
    return data;
  }

  /* ---------- Noticias ---------- */

  /** Anuncios editoriales del equipo, más nuevo primero. No son datos del
   *  líder ni del papá — viven en assets/data/noticias.json, igual que
   *  las barajas. */
  async function getNoticias() {
    const { noticias } = await loadJSON(`${BASE}/noticias.json`);
    return [...noticias].sort((a, b) => b.date.localeCompare(a.date));
  }

  /* ---------- Barajas y tarjetas ---------- */

  async function getDecks() {
    const { decks } = await loadJSON(`${BASE}/decks/_index.json`);
    return decks.sort((a, b) => a.order - b.order);
  }

  async function getDeck(deckId) {
    return loadJSON(`${BASE}/decks/${deckId}.json`);
  }

  /** Los ids de carta llevan el id del baraja como prefijo (`bear-a-bobcat-01`). */
  async function resolveDeckId(cardId) {
    const decks = await getDecks();
    const deck = decks.find(d => cardId.startsWith(d.id + '-'));
    if (!deck) throw new Error(`No se pudo ubicar el baraja de ${cardId}`);
    return deck.id;
  }

  async function getCard(cardId) {
    const deck = await getDeck(await resolveDeckId(cardId));
    const card = deck.cards.find(c => c.id === cardId);
    if (!card) throw new Error(`Tarjeta desconocida: ${cardId}`);
    return { card, deck };
  }

  /** Solo las cartas de actividad entran al sorteo: las de portada, guía,
   *  recurso y Aventura no son "la actividad del día". */
  const drawable = deck => deck.cards.filter(c => c.skin === 'activity');

  /**
   * Saca una carta al azar del baraja sin repetir hasta agotarlo.
   * Cuando se agota, reinicia el ciclo (y lo avisa con `cycled`).
   */
  async function drawCard(deckId) {
    const deck = await getDeck(deckId);
    const all = drawable(deck);
    const seen = new Set(store.get(`seen:${deckId}`, []));
    let pool = all.filter(c => !seen.has(c.id));
    let cycled = false;
    if (pool.length === 0) { pool = all; seen.clear(); cycled = true; }
    const card = pool[Math.floor(Math.random() * pool.length)];
    markSeen(deckId, card.id, cycled ? [] : [...seen]);
    return { card, deck, cycled, remaining: pool.length - 1, total: all.length };
  }

  function markSeen(deckId, cardId, base) {
    const seen = new Set(base ?? store.get(`seen:${deckId}`, []));
    seen.add(cardId);
    store.set(`seen:${deckId}`, [...seen]);
  }

  /* Ver una tarjeta y hacer la actividad no son lo mismo.
     `seen` existe para que el sorteo no repita; lo marca la app sola. `done` lo
     marca la familia cuando ya la hicieron con el niño, y es lo único que
     puede llamarse progreso: antes la barra subía por abrir tarjetas. */

  function markDone(deckId, cardId, hecha = true) {
    const done = new Set(store.get(`done:${deckId}`, []));
    hecha ? done.add(cardId) : done.delete(cardId);
    store.set(`done:${deckId}`, [...done]);
    return hecha;
  }

  function isDone(deckId, cardId) {
    return store.get(`done:${deckId}`, []).includes(cardId);
  }

  async function getProgress(deckId) {
    const deck = await getDeck(deckId);
    const seen = store.get(`seen:${deckId}`, []);
    const done = store.get(`done:${deckId}`, []);
    return { deckId, seen: seen.length, done: done.length,
             total: drawable(deck).length, cardIds: seen, doneIds: done };
  }

  /** Reglas de negocio que el cliente cambia sin tocar código. */
  async function getConfig() {
    return loadJSON(`${BASE}/config.json`);
  }

  /* ---------- Perfil del líder ----------
     El brief (§6) pide que el nombre y el número de pack se configuren UNA vez
     y se apliquen solos a cada tarjeta compartida. Acá vive esa configuración.
     Solo se guarda lo mínimo para personalizar y reportar (§13). */

  function getProfile() {
    const p = store.get('profile', { leader: '', pack: '', council: '', lang: 'es' });
    // Llave estable: el nombre se puede corregir sin perder los envíos hechos.
    if (!p.id) { p.id = 'ld-' + Math.random().toString(36).slice(2, 10); store.set('profile', p); }
    return p;
  }

  function setProfile(patch) {
    const next = { ...getProfile(), ...patch };
    store.set('profile', next);
    return next;
  }

  /**
   * Arma el enlace rastreable que se comparte. El brief (§9) es claro: el objeto
   * que se comparte tiene que ser un enlace alojado y rastreable, porque WhatsApp
   * no reporta aperturas. La personalización y el idioma viajan en el enlace, así
   * que sobreviven a los reenvíos.
   */
  function shareUrl(share) {
    const p = getProfile();
    const esMazo = Boolean(share.mazoId);
    const q = new URLSearchParams({
      ...(esMazo ? { m: share.mazoId } : { c: share.cardId }),
      s: share.id,
      lang: share.lang
    });
    if (p.leader) q.set('l', p.leader);
    if (p.pack) q.set('p', p.pack);
    // Los envíos nuevos abren la vista del mazo; los viejos, la carta suelta.
    const vista = esMazo ? 'mazo.html' : 'carta.html';
    return `${location.origin}/site/${vista}?${q}`;
  }

  /** Enlace click-to-chat de WhatsApp: un tap desde el teléfono del líder. */
  function whatsappUrl(share, text) {
    return `https://wa.me/?text=${encodeURIComponent(`${text} ${shareUrl(share)}`)}`;
  }

  /* ---------- Mazos — la selección semanal del líder ----------
     Un MAZO no es una baraja. La baraja (deck) es contenido oficial de
     Scouting America y no la arma nadie; el mazo lo crea el líder cada semana:
     le pone un nombre ("Pack 77") y elige cartas que pueden venir de VARIAS
     barajas. Es el objeto que se comparte por WhatsApp y el que abre el papá.
     Ver el glosario en CLAUDE.md.

     Vive en localStorage como el resto de la Capa A. No guarda un solo dato
     del papá ni del niño: solo el líder que lo creó y los ids de las cartas. */

  function getMazos(leaderId) {
    const yo = leaderId || getProfile().id;
    return store.get('mazos', []).filter(m => m.leaderId === yo);
  }

  function getMazo(mazoId) {
    return store.get('mazos', []).find(m => m.id === mazoId) || null;
  }

  function createMazo({ nombre, cardIds = [], packId, leaderId, lang }) {
    const nom = (nombre || '').trim();
    if (!nom) throw new Error('El mazo necesita un nombre');
    const perfil = getProfile();
    const mazo = {
      id: 'mz-' + Math.random().toString(36).slice(2, 8),
      nombre: nom,
      leaderId: leaderId || perfil.id,
      packId: packId ?? perfil.pack ?? null,
      cardIds: [...new Set(cardIds)],          // sin repetidas
      lang: lang || perfil.lang || 'es',
      createdAt: new Date().toISOString()
    };
    store.set('mazos', [...store.get('mazos', []), mazo]);
    return mazo;
  }

  function updateMazo(mazoId, patch = {}) {
    const todos = store.get('mazos', []);
    const i = todos.findIndex(m => m.id === mazoId);
    if (i < 0) throw new Error(`No existe el mazo ${mazoId}`);
    const next = { ...todos[i], ...patch };
    if ('cardIds' in patch) next.cardIds = [...new Set(patch.cardIds)];
    todos[i] = next;
    store.set('mazos', todos);
    return next;
  }

  function deleteMazo(mazoId) {
    store.set('mazos', store.get('mazos', []).filter(m => m.id !== mazoId));
  }

  /**
   * Resuelve un mazo a sus cartas completas, en el orden en que el líder las
   * eligió. Una carta que ya no exista en los datos se omite en vez de tumbar
   * la vista entera: el papá ve las trece que sí están, no una pantalla rota.
   */
  async function getMazoCards(mazoId) {
    const mazo = getMazo(mazoId);
    if (!mazo) throw new Error(`No existe el mazo ${mazoId}`);
    const out = [];
    for (const id of mazo.cardIds) {
      try { out.push(await getCard(id)); }
      catch { /* carta retirada del contenido: se omite */ }
    }
    return { mazo, cards: out };
  }

  /**
   * La baraja que MÁS aporta a un mazo.
   *
   * El mazo del líder puede mezclar cartas de varias barajas, pero casi siempre
   * es "el mazo de Bear con un par de sueltas". Esa baraja mayoritaria es la
   * que le da identidad visual al mazo del lado de la familia: su portada, su
   * color y su tinta de texto, en vez de un bloque azul igual para todos.
   *
   * Se cuenta por el PREFIJO del id de carta (`bear-a-bobcat-01`), que ya es
   * como resolveDeckId() ubica una carta, y se lee del índice —no de los mazos
   * completos—: la vista que lo usa es el home del papá, que tiene que pintar
   * en menos de dos segundos en 3G y no puede bajar 100 KB de JSON para elegir
   * un color. Empate: gana la baraja de menor `order`, que es el rank más
   * chico, para que el criterio sea estable y no dependa del orden del array.
   */
  async function getMazoBaraja(mazoId) {
    const mazo = getMazo(mazoId);
    if (!mazo || !mazo.cardIds.length) return null;
    const decks = await getDecks();
    let mejor = null, mejorN = 0;
    for (const d of decks) {
      const n = mazo.cardIds.filter(id => id.startsWith(d.id + '-')).length;
      if (n > mejorN || (n === mejorN && n > 0 && d.order < mejor.order)) {
        mejor = d; mejorN = n;
      }
    }
    return mejor ? { deck: mejor, cartas: mejorN, total: mazo.cardIds.length } : null;
  }

  /* ---------- Envíos y aperturas ---------- */

  /**
   * Un envío apunta a un MAZO, no a una carta: desde la revisión del 14-ago el
   * líder comparte la selección de la semana completa, no una actividad suelta.
   * `cardId` sigue aceptándose para no invalidar los envíos ya guardados en el
   * teléfono de quien venía usando el prototipo.
   */
  function createShare({ mazoId, cardId, leaderId, packId, lang = 'es' }) {
    if (!mazoId && !cardId) throw new Error('Un envío necesita un mazo');
    const share = {
      id: 'sh-' + Math.random().toString(36).slice(2, 8),
      ...(mazoId ? { mazoId } : { cardId }),
      leaderId: leaderId || getProfile().id,   // llave estable, no el nombre
      packId, lang,
      channel: 'whatsapp',
      sentAt: new Date().toISOString()
    };
    store.set('shares', [...store.get('shares', []), share]);
    return share;
  }

  /** Registra una apertura. Anónima: sin IP, sin nombre, sin identidad del papá. */
  function trackOpen(shareId, lang) {
    const key = `opened:${shareId}`;
    const isFirst = !store.get(key, false);
    store.set(key, true);
    const open = {
      shareId,
      at: new Date().toISOString(),
      lang,
      device: matchMedia('(min-width: 768px)').matches ? 'desktop' : 'mobile',
      isFirst
    };
    store.set('opens', [...store.get('opens', []), open]);
    return open;
  }

  /* ---------- Tableros ---------- */

  function getLeaderStats(leaderId) {
    const yo = leaderId || getProfile().id;
    // Aceptamos también los envíos viejos guardados con el nombre como llave.
    const nombre = getProfile().leader;
    const shares = store.get('shares', [])
      .filter(s => s.leaderId === yo || (nombre && s.leaderId === nombre));
    const opens = store.get('opens', []);
    return shares.map(s => {
      const own = opens.filter(o => o.shareId === s.id);
      return {
        share: s,
        opens: own.length,
        uniqueOpens: own.filter(o => o.isFirst).length,
        inSpanish: own.filter(o => o.lang === 'es').length
      };
    });
  }

  function getOrgStats({ packId, councilId } = {}) {
    const shares = store.get('shares', [])
      .filter(s => (!packId || s.packId === packId));
    const opens = store.get('opens', []);
    const openIds = new Set(opens.map(o => o.shareId));
    return {
      packId: packId ?? null,
      councilId: councilId ?? null,
      shares: shares.length,
      opened: shares.filter(s => openIds.has(s.id)).length,
      openRate: shares.length ? shares.filter(s => openIds.has(s.id)).length / shares.length : 0
    };
  }

  /* ================================================================
     CAPA ADMIN — Scouting America, el dueño de la aplicación
     ================================================================
     Pedido de la alineación del 20-ago-2026. Hasta acá toda la analítica era
     del LÍDER: qué mandé yo y cuántos lo abrieron. Falta la capa de arriba —la
     que el brief pide y la que nadie estaba viendo—: la analítica de la
     APLICACIÓN, para la organización. Eduardo no es un líder; no puede entrar
     por la puerta del líder ni quedarse sin tablero.

     Lo que se responde acá y por qué, textual de la reunión:
       · cuántos líderes hay y cuáles están activos
       · cuántos mazos se mandaron, en total y POR MES — "no es solo la foto de
         hoy, sino cómo se ha ido comportando en el tiempo"
       · el ÍNDICE de apertura, no el conteo crudo — "más importante que decir
         abrieron nueve es el índice de apertura, lo calculamos"
       · qué líder comparte más y cuál tiene mejor apertura
       · las actividades más compartidas — el top del contenido
       · el reparto de idioma, que es la razón de ser del producto

     Todo sale de datos que YA se guardan (`shares`, `opens`, `mazos`): no se
     agrega ni un dato del papá ni del niño. La regla de cero datos de menores
     no se toca para hacer un tablero. */

  /* ---------- Sesión del admin ----------
     Misma advertencia que la del líder: NO es autenticación. Es una marca en
     localStorage para que el tablero tenga puerta y la demo muestre que es
     privado. En la Capa B lo reemplaza auth real con rol.

     Se guarda aparte de `lider` a propósito. Con una sola llave, entrar como
     admin dejaba sesión de líder abierta y el tab bar mezclaba las dos
     consolas; y peor, `haySesionLider()` es lo que decide medio shell. */

  async function entrarComoAdmin(correo) {
    const mail = (correo || '').trim().toLowerCase();
    let cuenta = null;
    try {
      const { admins } = await loadJSON(`${BASE}/admins.json`);
      cuenta = admins.find(a => a.correo.toLowerCase() === mail) || null;
    } catch { /* sin archivo, no hay admins */ }
    if (!cuenta) return false;      // acá SÍ hay muro: el admin ve a todos
    store.set('admin', {
      correo: cuenta.correo, nombre: cuenta.nombre,
      organizacion: cuenta.organizacion, desde: new Date().toISOString()
    });
    return true;
  }

  function haySesionAdmin() { return Boolean(store.get('admin', null)); }
  function getAdmin() { return store.get('admin', null); }
  function salirAdmin() { store.set('admin', null); }

  /* ---------- Registro de líderes ----------
     El alta de líderes (registro, validación documental, aprobación) quedó
     FUERA del MVP: se marcó en la reunión que no está estimada y que hay que
     validarla con el cliente. Lo que sí hace falta ya es que el admin VEA a
     sus líderes y pueda desactivar a los que se fueron.

     El registro se arma con dos fuentes:
       · `lideres.json` — las cuentas que existen
       · `lideresVistos` — quien haya entrado alguna vez, aunque no esté en el
         archivo (la puerta del líder deja pasar cualquier correo en Capa A)
     El estado activo/inactivo vive en localStorage; en Capa B es un campo. */

  function recordarLider(perfil, correo) {
    const vistos = store.get('lideresVistos', []);
    const i = vistos.findIndex(v => v.id === perfil.id);
    const fila = {
      id: perfil.id,
      correo: correo || null,
      nombre: perfil.leader || '',
      pack: perfil.pack || '',
      council: perfil.council || '',
      ultimaEntrada: new Date().toISOString(),
      entradas: (i >= 0 ? vistos[i].entradas || 0 : 0) + 1,
      alta: i >= 0 ? vistos[i].alta : new Date().toISOString()
    };
    if (i >= 0) vistos[i] = fila; else vistos.push(fila);
    store.set('lideresVistos', vistos);
  }

  function setLiderActivo(leaderId, activo) {
    const bajas = new Set(store.get('lideresBaja', []));
    if (activo) bajas.delete(leaderId); else bajas.add(leaderId);
    store.set('lideresBaja', [...bajas]);
    return activo;
  }

  /**
   * Los líderes con sus métricas, más activo primero.
   *
   * El índice de apertura es por MAZO enviado, no por apertura: un envío que
   * se abrió catorce veces en un grupo de catorce familias sigue siendo UN
   * envío abierto. Contar aperturas crudas premiaba al grupo grande, no al
   * líder que comunica bien.
   */
  async function getLideres() {
    let cuentas = [];
    try { ({ lideres: cuentas } = await loadJSON(`${BASE}/lideres.json`)); }
    catch { /* sin archivo */ }

    const vistos = store.get('lideresVistos', []);
    const bajas = new Set(store.get('lideresBaja', []));
    const shares = store.get('shares', []);
    const opens = store.get('opens', []);
    const abiertos = new Set(opens.map(o => o.shareId));
    const mazos = store.get('mazos', []);

    // Una fila por líder. Las cuentas del archivo entran aunque nunca hayan
    // entrado: un líder dado de alta que no usa la app es justo el dato que el
    // admin necesita ver, no una fila que falta.
    const filas = new Map();
    for (const c of cuentas) {
      filas.set(c.correo, {
        id: c.correo, correo: c.correo, nombre: c.nombre, pack: c.pack,
        council: c.councilId || '', entradas: 0, ultimaEntrada: null, alta: null
      });
    }
    for (const v of vistos) {
      const clave = v.correo || v.id;
      filas.set(clave, { ...(filas.get(clave) || {}), ...v, id: v.id, correo: v.correo || clave });
    }

    const salida = [...filas.values()].map(f => {
      // Se empareja por id, por correo y por nombre: los envíos guardados
      // antes de derivar el id del correo llevan la llave vieja, y los de las
      // primeras versiones llevaban el nombre. Ninguno se pierde del conteo.
      const llaves = new Set([f.id, f.correo, f.nombre].filter(Boolean));
      const suyos = shares.filter(s => llaves.has(s.leaderId));
      const abiertosSuyos = suyos.filter(s => abiertos.has(s.id));
      const misMazos = mazos.filter(m => llaves.has(m.leaderId));
      return {
        ...f,
        activo: !bajas.has(f.id),
        mazos: misMazos.length,
        envios: suyos.length,
        abiertos: abiertosSuyos.length,
        aperturas: opens.filter(o => suyos.some(s => s.id === o.shareId)).length,
        indice: suyos.length ? abiertosSuyos.length / suyos.length : null,
        ultimoEnvio: suyos.length
          ? suyos.map(s => s.sentAt).sort().at(-1) : null
      };
    });

    return salida.sort((a, b) => b.envios - a.envios || (b.entradas || 0) - (a.entradas || 0));
  }

  /* ---------- Analítica del sistema ---------- */

  const mesDe = iso => (iso || '').slice(0, 7);      // 2026-08

  /** Foto del sistema hoy. */
  async function getAdminStats() {
    const lideres = await getLideres();
    const shares = store.get('shares', []);
    const opens = store.get('opens', []);
    const abiertos = new Set(opens.map(o => o.shareId));
    const conEnvio = shares.filter(s => abiertos.has(s.id)).length;
    // El idioma se cuenta sobre APERTURAS, no sobre envíos: el envío lleva el
    // idioma que eligió el líder; la apertura, el que usó la familia. Lo que el
    // producto quiere saber es en qué idioma LEEN, que es su razón de existir.
    const es = opens.filter(o => o.lang === 'es').length;
    return {
      lideres: lideres.length,
      lideresActivos: lideres.filter(l => l.activo).length,
      lideresConEnvios: lideres.filter(l => l.envios > 0).length,
      mazos: store.get('mazos', []).length,
      envios: shares.length,
      abiertos: conEnvio,
      aperturas: opens.length,
      indice: shares.length ? conEnvio / shares.length : null,
      idioma: { es, en: opens.length - es, total: opens.length }
    };
  }

  /**
   * Progresión por mes. El punto explícito de la reunión: un tablero sin serie
   * es una foto y no deja comparar. Devuelve los últimos `meses` con envíos,
   * aperturas e índice, del más viejo al más nuevo, sin huecos.
   */
  function getSerieMensual(meses = 6) {
    const shares = store.get('shares', []);
    const opens = store.get('opens', []);
    const abiertos = new Set(opens.map(o => o.shareId));

    const hoy = new Date();
    const claves = [];
    for (let i = meses - 1; i >= 0; i--) {
      const d = new Date(hoy.getFullYear(), hoy.getMonth() - i, 1);
      claves.push(`${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`);
    }
    return claves.map(mes => {
      const delMes = shares.filter(s => mesDe(s.sentAt) === mes);
      const abiertosMes = delMes.filter(s => abiertos.has(s.id)).length;
      return {
        mes,
        envios: delMes.length,
        abiertos: abiertosMes,
        aperturas: opens.filter(o => mesDe(o.at) === mes).length,
        indice: delMes.length ? abiertosMes / delMes.length : null
      };
    });
  }

  /**
   * Las actividades más compartidas. Es contenido, no personas: dice qué del
   * catálogo está funcionando y qué nadie usa, que es lo que el programa
   * necesita para decidir qué producir.
   */
  async function getTopCartas(n = 10) {
    const mazos = store.get('mazos', []);
    const shares = store.get('shares', []);
    // Una carta cuenta una vez por ENVÍO, no por mazo guardado: un mazo que se
    // armó y nunca se mandó no compartió nada.
    const cuenta = new Map();
    for (const sh of shares) {
      const mazo = mazos.find(m => m.id === sh.mazoId);
      const ids = mazo ? mazo.cardIds : (sh.cardId ? [sh.cardId] : []);
      for (const id of ids) cuenta.set(id, (cuenta.get(id) || 0) + 1);
    }
    const top = [...cuenta.entries()].sort((a, b) => b[1] - a[1]).slice(0, n);
    const salida = [];
    for (const [cardId, veces] of top) {
      try {
        const { card, deck } = await getCard(cardId);
        salida.push({ card, deck, veces });
      } catch { /* carta retirada del contenido */ }
    }
    return salida;
  }

  /** Reparto de envíos por baraja: qué ranks se están moviendo. */
  async function getEnviosPorBaraja() {
    const decks = await getDecks();
    const mazos = store.get('mazos', []);
    const shares = store.get('shares', []);
    const cuenta = new Map(decks.map(d => [d.id, 0]));
    for (const sh of shares) {
      const mazo = mazos.find(m => m.id === sh.mazoId);
      if (!mazo) continue;
      for (const d of decks) {
        const n = mazo.cardIds.filter(id => id.startsWith(d.id + '-')).length;
        if (n) cuenta.set(d.id, cuenta.get(d.id) + n);
      }
    }
    return decks
      .map(d => ({ deck: d, cartas: cuenta.get(d.id) }))
      .filter(x => x.cartas > 0)
      .sort((a, b) => b.cartas - a.cartas);
  }

  /* ---------- Modo de uso ----------
     La app sirve a dos personas con recorridos opuestos: la familia, que
     consulta y hace las actividades, y el líder, que reparte y mide. Sin
     backend no hay cuentas, así que el modo es una preferencia local: se
     elige una vez y define qué ve cada quien.
     Un enlace compartido (?s=) siempre se abre como familia, sin importar
     el modo guardado: el papá que lo recibe no es el líder que lo mandó. */

  function getMode() {
    const forzado = new URLSearchParams(location.search).get('modo');
    if (forzado === 'familia' || forzado === 'lider') { store.set('mode', forzado); return forzado; }
    return store.get('mode', null);
  }

  function setMode(m) { store.set('mode', m); return m; }

  /* ---------- Lo que le ha llegado al papá ----------
     El papá entra por WhatsApp, pero después vuelve a abrir la app desde el
     ícono y ahí no hay enlace. Se guarda EL HISTORIAL de mazos que abrió en
     este teléfono, no solo el último: con eso la portada le muestra lo de esta
     semana y además cuántas tarjetas lleva de la baraja de su hijo.

     Es una preferencia local, como el rank y el idioma. No identifica al papá,
     no viaja a ningún lado y no guarda nada del niño: solo ids de mazo. */

  function recordarMazo(mazoId, person) {
    if (!mazoId) return mazoId;
    const previos = store.get('mazosRecibidos', []).filter(id => id !== mazoId);
    store.set('mazosRecibidos', [...previos, mazoId]);   // el más reciente al final
    // Bug 20-ago: la firma "Te la envía..." solo viajaba en el enlace de
    // WhatsApp (?l=&p=). La primera vez que el papá lo abre así, se guarda
    // acá también — de esta baraja para adentro es lo mismo que cualquier
    // otra preferencia local — así que si vuelve a entrar al mismo mazo
    // desde el ícono o desde "Esta semana" en el home, la banda no
    // desaparece solo porque esa segunda visita no trae el enlace completo.
    if (person && (person.leader || person.pack)) {
      const mapa = store.get('personaPorMazo', {});
      mapa[mazoId] = { leader: person.leader || '', pack: person.pack || '' };
      store.set('personaPorMazo', mapa);
    }
    return mazoId;
  }

  /** La firma guardada la primera vez que se abrió este mazo (ver recordarMazo). */
  function getPersonaMazo(mazoId) {
    return store.get('personaPorMazo', {})[mazoId] || null;
  }

  /**
   * Lo que le ha llegado al papá, de dos fuentes:
   *
   *  1. Los mazos que abrió por enlace en este teléfono.
   *  2. Los mazos que TIENEN UN ENVÍO en este dispositivo.
   *
   * La segunda existe por la Capa A: sin backend, el líder y la familia
   * comparten el mismo localStorage, así que un mazo ya enviado está acá aunque
   * nadie haya tocado el enlace todavía. Sin esto, mandar algo y entrar por el
   * ícono mostraba "todavía no le ha llegado nada", que es falso: sí le llegó,
   * solo que no lo abrió.
   *
   * En la Capa B esto se cae solo: el servidor sabrá qué envíos son de este
   * papá y la fuente será una sola. Ver docs/data-model.md.
   */
  function getMazosRecibidos() {
    const abiertos = store.get('mazosRecibidos', []);
    const enviados = store.get('shares', [])
      .filter(sh => sh.mazoId)
      .sort((a, b) => new Date(a.sentAt) - new Date(b.sentAt))
      .map(sh => sh.mazoId);

    // Sin repetir y con los abiertos primero, para que el orden refleje
    // "lo último que pasó" sin importar por dónde entró.
    const ids = [...new Set([...enviados, ...abiertos])];
    return ids.map(getMazo).filter(Boolean);   // un mazo borrado se descarta
  }

  function getUltimoMazo() {
    const recibidos = getMazosRecibidos();
    return recibidos.length ? recibidos[recibidos.length - 1].id : null;
  }

  /**
   * Qué lleva el papá, baraja por baraja: cuántas tarjetas le han llegado y
   * cuántas tiene esa baraja en total. Solo aparecen las barajas de las que
   * recibió algo — el catálogo completo es material del líder.
   *
   * Se cuenta contra las actividades, no contra todas las cartas: las de
   * instrucciones no son algo que un niño haga, así que sumarlas al total
   * haría que el papá nunca llegue al final por una razón que no entiende.
   */
  async function getRecibidasPorBaraja() {
    const cartas = new Set();
    for (const m of getMazosRecibidos()) m.cardIds.forEach(id => cartas.add(id));
    if (!cartas.size) return [];

    const porBaraja = new Map();
    for (const cardId of cartas) {
      try {
        const deckId = await resolveDeckId(cardId);
        if (!porBaraja.has(deckId)) porBaraja.set(deckId, new Set());
        porBaraja.get(deckId).add(cardId);
      } catch { /* carta retirada del contenido */ }
    }

    const salida = [];
    for (const [deckId, recibidas] of porBaraja) {
      const deck = await getDeck(deckId);
      const total = deck.cards.filter(c => c.skin === 'activity').length;
      salida.push({
        deck,
        recibidas: recibidas.size,
        total,
        faltan: Math.max(0, total - recibidas.size)
      });
    }
    return salida.sort((a, b) => b.recibidas - a.recibidas);
  }

  /* ---------- Sesión del líder ----------
     OJO: esto NO es autenticación. No hay backend, así que la sesión es una
     marca en localStorage y cualquiera que la borre entra igual. Existe para
     que la consola tenga puerta y para que la demo muestre que es privada.
     Cuando llegue la Capa B se reemplaza por auth real contra la API.

     La contraseña no se recibe ni se guarda: se valida en el formulario y se
     descarta. Guardarla en claro en el navegador es un patrón que sobrevive al
     prototipo, y la gente reutiliza sus contraseñas en otros lados.

     Esto no contradice "el papá nunca crea cuenta": el papá sigue sin login,
     sin correo y sin pantalla que se lo pida. La puerta es solo del líder. */

  /**
   * Entrar es identificarse, no presentarse: el nombre y el pack son datos de
   * la CUENTA y llegan con ella. En la Capa A viven en assets/data/lideres.json;
   * en la Capa B los devolverá el servidor al autenticar.
   *
   * Un correo que no está en el archivo entra igual —es un prototipo, no un
   * muro— con su parte antes de la arroba como nombre provisional, y la consola
   * le ofrece corregirlo. Lo que no se hace es preguntarle el nombre a alguien
   * que acaba de identificarse.
   */
  async function entrarComoLider(correo) {
    const mail = (correo || '').trim().toLowerCase();
    /* Un líder dado de baja por el admin no entra. Sin esto, la pantalla del
       admin decía "closes their access" y no cerraba nada: el líder seguía
       entrando y mandando mazos como si nada. Devuelve `false` para que la
       puerta lo diga en vez de dejarlo pasar en silencio. */
    const bajas = new Set(store.get('lideresBaja', []));
    if (mail && bajas.has('ld-' + mail.replace(/[^a-z0-9]+/g, '-'))) return false;
    let cuenta = null;
    try {
      const { lideres } = await loadJSON(`${BASE}/lideres.json`);
      cuenta = lideres.find(l => l.correo.toLowerCase() === mail) || null;
    } catch { /* sin archivo, se sigue con el provisional */ }

    const nombreProvisional = mail.split('@')[0]
      .replace(/[._-]+/g, ' ')
      .replace(/\b\p{L}/gu, c => c.toUpperCase());

    store.set('lider', { correo: mail || null, desde: new Date().toISOString() });
    /* La identidad del líder se deriva del CORREO, no del navegador.
       `getProfile()` inventa un id por dispositivo, y eso alcanzaba mientras
       cada líder miraba solo sus propios envíos. Con el tablero del admin dejó
       de alcanzar: en la Capa A los cuatro líderes de la demo comparten un
       navegador, así que compartían id — el admin veía el total correcto y
       CERO en cada fila. Derivarlo del correo le da a cada uno su identidad,
       que es lo que la Capa B va a devolver de todos modos. */
    setProfile({
      id: mail ? 'ld-' + mail.replace(/[^a-z0-9]+/g, '-') : getProfile().id,
      leader: cuenta ? cuenta.nombre : nombreProvisional,
      pack: cuenta ? cuenta.pack : (getProfile().pack || ''),
      council: cuenta ? cuenta.councilId : (getProfile().council || ''),
      cuentaConocida: Boolean(cuenta)
    });
    // El admin necesita ver a los líderes que existen de verdad, no solo a
    // los del archivo: en Capa A la puerta deja pasar cualquier correo.
    recordarLider(getProfile(), mail || null);
    setMode('lider');
    return true;
  }

  function haySesionLider() {
    return Boolean(store.get('lider', null));
  }

  function salir() {
    store.set('lider', null);
    store.set('mode', null);
  }

  /* ---------- El rank del hijo ----------
     La familia tiene un hijo en un rank, no siete. Sin guardarlo, la app le
     sugería tarjetas de Bear a un papá de Wolf y el progreso le mostraba las
     siete barajas en 0 %. Es una preferencia local, sin nada del niño: solo
     el id de la baraja. */

  function getRank() { return store.get('rank', null); }
  function setRank(deckId) { store.set('rank', deckId); return deckId; }

  /* ---------- Idioma ---------- */

  function getLang() {
    const fromUrl = new URLSearchParams(location.search).get('lang');
    if (fromUrl === 'es' || fromUrl === 'en') { store.set('lang', fromUrl); return fromUrl; }
    // El líder arranca en inglés y la familia en español (acta del 14-ago:
    // «pongámoslo inicialmente por default en inglés» para el líder). El
    // producto existe porque los papás no leen inglés; los líderes sí. El
    // selector sigue estando para los dos: esto es el arranque, no un candado.
    const porDefecto = haySesionLider() ? 'en' : 'es';
    return store.get('lang', porDefecto);
  }

  function setLang(lang) { store.set('lang', lang); return lang; }

  return {
    MODE, REMOTE,
    getNoticias,
    getDecks, getDeck, getCard, drawCard, markSeen, markDone, isDone, getProgress, getConfig,
    getProfile, setProfile, shareUrl, whatsappUrl,
    getMazos, getMazo, createMazo, updateMazo, deleteMazo, getMazoCards,
    getMazoBaraja,
    createShare, trackOpen, getLeaderStats, getOrgStats,
    getLang, setLang, getMode, setMode, getRank, setRank,
    entrarComoLider, haySesionLider, salir,
    recordarMazo, getUltimoMazo, getPersonaMazo, getMazosRecibidos, getRecibidasPorBaraja,
    entrarComoAdmin, haySesionAdmin, getAdmin, salirAdmin,
    getLideres, setLiderActivo, getAdminStats, getSerieMensual, getTopCartas, getEnviosPorBaraja
  };
})();

if (typeof window !== 'undefined') window.API = API;
