/* TECNOMEDIC: Email corporativo estilo Webmail Ferozo. Requiere window.WM_API = {get, send, del}. */
(function () {
  'use strict';
  var API = window.WM_API || {};
  var S = { cuenta: 'contacto', folder: 'INBOX', list: [], cur: null, page: 1, per: 50, filter: 'all', q: '', att: [], draftId: null, mode: 'new', ref: null };
  var $ = function (id) { return document.getElementById(id); };
  var esc = function (s) { return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); };
  window.escapeHtml = window.escapeHtml || esc;

  function toast(msg, err) {
    var t = document.createElement('div');
    t.className = 'wm-toast' + (err ? ' err' : ''); t.textContent = msg;
    document.body.appendChild(t); setTimeout(function () { t.remove(); }, 2200);
  }
  function clean(html) {
    var d = document.createElement('div'); d.innerHTML = html || '';
    d.querySelectorAll('script,iframe,object,embed,style,link,meta').forEach(function (n) { n.remove(); });
    d.querySelectorAll('*').forEach(function (el) {
      Array.prototype.slice.call(el.attributes).forEach(function (a) {
        var n = a.name.toLowerCase(), v = (a.value || '').trim().toLowerCase();
        if (n.indexOf('on') === 0) el.removeAttribute(a.name);
        if ((n === 'href' || n === 'src') && v.indexOf('javascript:') === 0) el.removeAttribute(a.name);
      });
    });
    return d.innerHTML;
  }
  function fmtDate(s) {
    if (!s) return '';
    var d = new Date(String(s).replace(' ', 'T')); if (isNaN(d)) return '';
    var now = new Date(), hm = d.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit', hour12: false });
    if (d.toDateString() === now.toDateString()) return 'Hoy ' + hm;
    var diff = (now - d) / 864e5;
    if (diff < 6) { var w = d.toLocaleDateString('es-AR', { weekday: 'short' }).replace('.', ''); return w.charAt(0).toUpperCase() + w.slice(1) + ' ' + hm; }
    return d.toLocaleDateString('es-AR') + ' ' + hm;
  }
  function isoLong(s) { var d = new Date(String(s || '').replace(' ', 'T')); if (isNaN(d)) return ''; var p = function (n) { return ('0' + n).slice(-2); }; return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) + ' ' + p(d.getHours()) + ':' + p(d.getMinutes()); }
  function fromOf(m) { return m.remitente_display || m.remitente_nombre || m.remitente_email || ''; }
  function view(name) { document.querySelectorAll('.wm-view').forEach(function (v) { v.classList.toggle('active', v.id === 'wmView' + name); }); }
  async function getJSON(url, opt) {
    opt = Object.assign({ credentials: 'include' }, opt || {});
    var r = await fetch(url, opt);
    // Si el servidor redirige y el navegador convierte POST en GET, repetimos el POST en la URL final
    if (r.redirected && String(opt.method || 'GET').toUpperCase() === 'POST' && r.url && r.url.split('?')[0] !== new URL(url, location.href).href.split('?')[0]) {
      r = await fetch(r.url, opt);
    }
    var txt = await r.text(), d;
    try { d = JSON.parse(txt); } catch (e) {
      var i = txt.indexOf('{"ok"'); // JSON precedido por avisos de PHP
      if (i >= 0) { try { d = JSON.parse(txt.slice(i, txt.lastIndexOf('}') + 1)); } catch (e2) {} }
    }
    if (!d) { console.error('Respuesta no JSON', r.status, r.url, txt.slice(0, 300)); return { ok: false, error: 'HTTP ' + r.status + ' respuesta invalida' }; }
    return d;
  }

  /* ===== Carpetas ===== */
  var BASE = [['INBOX', '📥', 'Entrada'], ['DRAFTS', '📝', 'Borradores'], ['SENT', '📤', 'Enviados'], ['SPAM', '🔥', 'Basura'], ['TRASH', '🗑', 'Papelera']];
  var custom = [];
  var SYS = ['inbox', 'draft', 'drafts', 'sent', 'sent items', 'sent messages', 'trash', 'deleted', 'deleted items', 'deleted messages', 'spam', 'junk', 'junk e-mail', 'papelera', 'borradores', 'enviados', 'basura'];
  function shortName(f) { return String(f).replace(/^INBOX[.\/]/i, ''); }
  function renderFolders() {
    var box = $('wmFolderList'); if (!box) return;
    var all = BASE.concat(custom.map(function (f) { return [f, '📁', shortName(f)]; }));
    box.innerHTML = all.map(function (f, i) {
      var main = '<button type="button" class="wm-folder' + (f[0] === S.folder ? ' active' : '') + '" data-f="' + esc(f[0]) + '"><i>' + f[1] + '</i>' + esc(f[2]) + '<span class="wm-cnt" data-c="' + esc(f[0]) + '"></span></button>';
      if (i < BASE.length) return main;
      return '<div class="wm-fw">' + main + '<span class="wm-fa"><a data-act="ren" data-f="' + esc(f[0]) + '" title="Renombrar">✏️</a><a data-act="del" data-f="' + esc(f[0]) + '" title="Eliminar">🗑</a></span></div>';
    }).join('');
    box.querySelectorAll('.wm-folder').forEach(function (b) { b.onclick = function () { window.setEmailFolder(b.dataset.f); }; });
    box.querySelectorAll('.wm-fa a').forEach(function (a) { a.onclick = function (e) { e.stopPropagation(); folderAct(a.dataset.act, a.dataset.f); }; });
  }
  async function folderAct(act, f) {
    var fd = new FormData(); fd.append('folder', f);
    if (act === 'ren') {
      var n = prompt('Nuevo nombre para "' + shortName(f) + '":', shortName(f));
      if (!n || !n.trim()) return; fd.append('new_name', n.trim());
    } else if (!confirm('¿Eliminar la carpeta "' + shortName(f) + '" y sus mensajes?')) return;
    var d = await getJSON(API.get + '?action=' + (act === 'ren' ? 'rename_folder' : 'delete_folder') + '&cuenta=' + S.cuenta, { method: 'POST', body: fd });
    if (!d.ok) return toast((act === 'ren' ? 'No se pudo renombrar: ' : 'No se pudo eliminar: ') + (d.error || ''), true);
    if (S.folder === f) S.folder = act === 'ren' ? d.folder : 'INBOX';
    await window.cargarCarpetasEmail(); loadList();
  }
  window.cargarCarpetasEmail = async function () {
    try {
      var d = await getJSON(API.get + '?action=folders&cuenta=' + encodeURIComponent(S.cuenta));
      if (d.ok) {
        custom = (d.folders || []).map(String).filter(function (f) { return f.trim() && SYS.indexOf(shortName(f).toLowerCase()) < 0; }).slice(0, 30);
      }
    } catch (e) { console.error(e); }
    renderFolders();
  };
  window.crearCarpetaEmail = async function () {
    var i = $('wmNewFolder'), name = (i.value || '').trim(); if (!name) return;
    var fd = new FormData(); fd.append('folder_name', name);
    try {
      var d = await getJSON(API.get + '?action=create_folder&cuenta=' + S.cuenta, { method: 'POST', body: fd });
      if (!d.ok) return toast('No se pudo crear: ' + (d.error || ''), true);
      i.value = ''; await window.cargarCarpetasEmail(); window.setEmailFolder(d.folder || name);
    } catch (e) { toast('Error al crear carpeta', true); }
  };
  window.setEmailFolder = async function (f) {
    S.folder = f; S.page = 1; S.cur = null; renderFolders(); clearPreview(); await loadList();
    var first = document.querySelector('#wmList .wm-row'); if (first) first.click();
  };

  /* ===== Lista ===== */
  function visible() {
    var q = S.q.toLowerCase();
    return S.list.filter(function (m) {
      if (S.filter === 'unread' && String(m.leido) !== '0') return false;
      if (S.filter === 'read' && String(m.leido) === '0') return false;
      if (q && (fromOf(m) + ' ' + (m.asunto || '') + ' ' + (m.preview || '')).toLowerCase().indexOf(q) < 0) return false;
      return true;
    });
  }
  function renderList() {
    var list = $('wmList'), v = visible(), total = v.length;
    var from = total ? (S.page - 1) * S.per + 1 : 0, to = Math.min(total, S.page * S.per);
    $('wmCount').textContent = 'Mensajes ' + from + ' a ' + to + ' de ' + total;
    $('wmPage').value = S.page;
    var unread = S.list.filter(function (m) { return String(m.leido) === '0'; }).length;
    var c = document.querySelector('.wm-cnt[data-c="' + S.folder + '"]'); if (c) c.textContent = unread || '';
    var be = $('badge-email'); if (be && S.folder === 'INBOX') be.textContent = unread + ' sin leer';
    if (!total) { list.innerHTML = '<div class="wm-empty">✉️ Sin correos en esta carpeta.<br><small>Presioná Actualizar para consultar Ferozo IMAP.</small></div>'; return; }
    list.innerHTML = v.slice((S.page - 1) * S.per, S.page * S.per).map(function (m) {
      var f = fromOf(m), un = String(m.leido) === '0';
      return '<div class="wm-row' + (un ? ' unread' : '') + (S.cur && S.cur.id == m.id ? ' active' : '') + '" data-id="' + m.id + '">' +
        '<div class="wm-av">' + esc((f.charAt(0) || '?').toUpperCase()) + '</div>' +
        '<div class="wm-from">' + esc(f) + '</div><div class="wm-date">' + esc(fmtDate(m.creado_en)) + '</div>' +
        '<div class="wm-subj">' + esc(m.asunto || '(Sin Asunto)') + '</div></div>';
    }).join('');
    list.querySelectorAll('.wm-row').forEach(function (r) { r.onclick = function () { window.cargarEmail(r.dataset.id, r); }; });
  }
  async function loadList() {
    try {
      var d = await getJSON(API.get + '?action=list&cuenta=' + S.cuenta + '&folder=' + encodeURIComponent(S.folder));
      if (!d.ok) return; S.list = d.emails || []; renderList();
      if (!S.list.length && !window.__WM_SYNC) { window.__WM_SYNC = true; try { await window.actualizarEmailsIMAP(); } finally { window.__WM_SYNC = false; } }
    } catch (e) { console.error(e); }
  }
  window.refrescarEmailsLista = loadList;
  window.actualizarEmailsIMAP = async function () {
    try {
      var d = await getJSON(API.get + '?action=sync&cuenta=' + S.cuenta + '&folder=' + encodeURIComponent(S.folder));
      if (!d.ok) return toast('Error sync: ' + (d.error || 'desconocido'), true);
      S.list = d.emails || []; renderList();
    } catch (e) { toast('No se pudo sincronizar IMAP', true); }
  };

  /* ===== Preview ===== */
  function clearPreview() {
    $('wmPrevSubj').textContent = ''; $('wmPrevFrom').textContent = ''; $('wmPrevDate').textContent = '';
    $('wmPrevAv').textContent = ''; $('wmPrevBody').innerHTML = '';
    ['wmBtnReply', 'wmBtnReplyAll', 'wmBtnFwd', 'wmBtnDel', 'wmBtnMark'].forEach(function (i) { var b = $(i); if (b) b.disabled = true; });
  }
  window.cargarEmail = async function (id, el) {
    document.querySelectorAll('.wm-row').forEach(function (r) { r.classList.remove('active'); });
    if (el) el.classList.add('active');
    $('wmPrevBody').innerHTML = '<span style="color:#94a3b8">Cargando…</span>';
    try {
      var d = await getJSON(API.get + '?action=detail&id=' + encodeURIComponent(id));
      if (!d.ok) return toast('No se pudo cargar: ' + (d.error || ''), true);
      var m = d.email; S.cur = m; window.__EMAIL_CURRENT = m;
      var f = fromOf(m);
      $('wmPrevSubj').textContent = m.asunto || '(Sin Asunto)';
      $('wmPrevFrom').textContent = f; $('wmPrevFrom').title = m.remitente_email || '';
      $('wmPrevDate').textContent = fmtDate(m.creado_en);
      $('wmPrevAv').textContent = (f.charAt(0) || '?').toUpperCase();
      $('wmPrevBody').innerHTML = (m.cuerpo_html && m.cuerpo_html.trim()) ? clean(m.cuerpo_html) : esc(m.cuerpo_txt || '(Sin contenido)').replace(/\n/g, '<br>');
      ['wmBtnReply', 'wmBtnReplyAll', 'wmBtnFwd', 'wmBtnDel', 'wmBtnMark'].forEach(function (i) { $(i).disabled = false; });
      var li = S.list.find(function (x) { return x.id == m.id; }); if (li) { li.leido = 1; renderList(); }
    } catch (e) { console.error(e); toast('Error al cargar el email', true); }
  };

  /* ===== Acciones toolbar ===== */
  window.eliminarEmailActual = async function () {
    if (!S.cur) return;
    if (!confirm('¿Eliminar este correo?')) return;
    var fd = new FormData(); fd.append('id', S.cur.id);
    try {
      var d = await getJSON(API.get + '?action=delete&cuenta=' + S.cuenta, { method: 'POST', body: fd });
      if (d.ok && d.warning) toast(d.warning, true);
      if (!d.ok) return toast('No se pudo eliminar: ' + (d.error || ''), true);
      S.cur = null; clearPreview(); await loadList();
    } catch (e) { toast('Error al eliminar', true); }
  };
  async function markCur(flag) {
    if (!S.cur) return;
    var fd = new FormData(); fd.append('id', S.cur.id); fd.append('flag', flag);
    try { var d = await getJSON(API.get + '?action=mark&cuenta=' + S.cuenta, { method: 'POST', body: fd });
      if (d.ok) { var li = S.list.find(function (x) { return x.id == S.cur.id; }); if (li) li.leido = (flag === 'unread' ? 0 : 1); renderList(); }
      else toast('No se pudo marcar', true);
    } catch (e) { toast('Error al marcar', true); }
  }
  function bindToolbar() {
    $('wmBtnRefresh').onclick = window.actualizarEmailsIMAP;
    $('wmBtnCompose').onclick = function () { window.abrirComposeEmail({ mode: 'new' }); };
    $('wmBtnReply').onclick = function () { window.abrirComposeEmail({ mode: 'reply' }); };
    $('wmBtnReplyAll').onclick = function () { window.abrirComposeEmail({ mode: 'replyall' }); };
    $('wmBtnFwd').onclick = function () { window.abrirComposeEmail({ mode: 'fwd' }); };
    $('wmIcReply').onclick = $('wmBtnReply').onclick; $('wmIcReplyAll').onclick = $('wmBtnReplyAll').onclick; $('wmIcFwd').onclick = $('wmBtnFwd').onclick;
    $('wmBtnDel').onclick = window.eliminarEmailActual;
    $('wmBtnMark').onclick = function () { markCur(String(S.cur && S.cur.leido) === '0' ? 'read' : 'unread'); };
    $('wmBtnMore').onclick = function () { if (S.cur) { var w = window.open('', '_blank'); w.document.write('<title>' + esc(S.cur.asunto || '') + '</title><div style="font-family:Verdana;font-size:13px;padding:16px">' + $('wmPrevBody').innerHTML + '</div>'); } };
    $('wmIcPop').onclick = $('wmBtnMore').onclick;
    var accSel = $('wmAccount') || $('wmAccountSelect');
    if (accSel) accSel.onchange = async function () {
      S.cuenta = this.value; S.folder = 'INBOX'; S.page = 1; S.cur = null; S.list = [];
      $('wmFrom').value = S.cuenta + '@tecnomedic.com.ar';
      clearPreview(); renderList(); await window.cargarCarpetasEmail(); await loadList();
      var first = document.querySelector('#wmList .wm-row'); if (first) first.click();
    };
    $('wmFilter').onchange = function () { S.filter = this.value; S.page = 1; renderList(); };
    $('wmSearch').oninput = function () { S.q = this.value; S.page = 1; renderList(); };
    $('wmSearchClear').onclick = function () { $('wmSearch').value = ''; S.q = ''; renderList(); };
    var pages = function () { return Math.max(1, Math.ceil(visible().length / S.per)); };
    $('wmFirst').onclick = function () { S.page = 1; renderList(); };
    $('wmPrev').onclick = function () { S.page = Math.max(1, S.page - 1); renderList(); };
    $('wmNext').onclick = function () { S.page = Math.min(pages(), S.page + 1); renderList(); };
    $('wmLast').onclick = function () { S.page = pages(); renderList(); };
    $('wmPage').onchange = function () { S.page = Math.min(pages(), Math.max(1, parseInt(this.value, 10) || 1)); renderList(); };
    $('wmNewFolderBtn').onclick = window.crearCarpetaEmail;
  }

  /* ===== Compose ===== */
  var ed, plain, isHtml = true;
  function qa(cmd, val) { ed.focus(); document.execCommand(cmd, false, val || null); }
  function bindCompose() {
    ed = $('wmEdit'); plain = $('wmPlain');
    document.querySelectorAll('#wmEdTb [data-cmd]').forEach(function (b) {
      b.onmousedown = function (e) { e.preventDefault(); };
      b.onclick = function () { qa(b.dataset.cmd, b.dataset.val); };
    });
    $('wmFont').onchange = function () { qa('fontName', this.value); };
    $('wmSize').onchange = function () { ed.focus(); document.execCommand('fontSize', false, '7'); ed.querySelectorAll('font[size="7"]').forEach(function (f) { f.removeAttribute('size'); f.style.fontSize = $('wmSize').value; }); };
    $('wmColor').oninput = function () { $('wmColorBar').style.color = this.value; qa('foreColor', this.value); };
    $('wmHilite').oninput = function () { $('wmHiliteBar').style.color = this.value; qa('hiliteColor', this.value); };
    $('wmLink').onclick = function () { var u = prompt('URL del enlace:', 'https://'); if (u) qa('createLink', u); };
    $('wmUnlink').onclick = function () { qa('unlink'); };
    $('wmQuote').onclick = function () { qa('formatBlock', 'blockquote'); };
    $('wmLtr').onclick = function () { ed.dir = 'ltr'; }; $('wmRtl').onclick = function () { ed.dir = 'rtl'; };
    $('wmTable').onclick = function () {
      var r = parseInt(prompt('Filas:', '2'), 10), c = parseInt(prompt('Columnas:', '2'), 10); if (!r || !c) return;
      var h = '<table>'; for (var i = 0; i < r; i++) { h += '<tr>'; for (var j = 0; j < c; j++) h += '<td>&nbsp;</td>'; h += '</tr>'; } qa('insertHTML', h + '</table><br>');
    };
    $('wmMoreEd').onclick = function () { var x = $('wmEdTb2'); x.style.display = x.style.display === 'flex' ? 'none' : 'flex'; };
    $('wmEdType').onchange = function () {
      if (this.value === 'text' && isHtml) { plain.value = ed.innerText; ed.style.display = 'none'; $('wmEdTb').style.display = 'none'; plain.style.display = 'block'; isHtml = false; }
      else if (this.value === 'html' && !isHtml) { ed.innerHTML = esc(plain.value).replace(/\n/g, '<br>'); plain.style.display = 'none'; ed.style.display = 'block'; $('wmEdTb').style.display = 'flex'; isHtml = true; }
    };
    document.querySelectorAll('.wm-sub a[data-show]').forEach(function (a) {
      a.onclick = function () { var r = $(a.dataset.show); r.classList.remove('wm-hidden'); var l = document.querySelector('[data-for="' + a.dataset.show + '"]'); if (l) l.classList.remove('wm-hidden'); a.style.display = 'none'; };
    });
    $('wmAttBtn').onclick = function () { $('wmFile').click(); };
    $('wmFile').onchange = function () { addFiles(this.files); this.value = ''; };
    var side = $('wmAtt');
    side.ondragover = function (e) { e.preventDefault(); side.classList.add('drag'); };
    side.ondragleave = function () { side.classList.remove('drag'); };
    side.ondrop = function (e) { e.preventDefault(); side.classList.remove('drag'); addFiles(e.dataTransfer.files); };
    $('wmCSend').onclick = sendCompose; $('wmCCancel').onclick = function () { if (!ed.innerText.trim() || confirm('¿Descartar el mensaje?')) { view('Mail'); } };
    $('wmCSave').onclick = saveDraft;
    $('wmCSpell').onclick = function () { ed.spellcheck = !ed.spellcheck; plain.spellcheck = ed.spellcheck; toast('Ortografía ' + (ed.spellcheck ? 'activada' : 'desactivada')); ed.focus(); };
    $('wmCSign').onclick = function () { var s = localStorage.getItem('wm_firma') || ''; var n = prompt('Firma (HTML simple permitido):', s); if (n !== null) { localStorage.setItem('wm_firma', n); if (n) qa('insertHTML', '<br>-- <br>' + n); } };
    $('wmCResp').onclick = function () { var t = prompt('Respuesta rápida a insertar:', localStorage.getItem('wm_resp') || 'Gracias por comunicarte con TecnoMedic.'); if (t) { localStorage.setItem('wm_resp', t); qa('insertHTML', esc(t)); } };
    $('wmContactSearch').oninput = function () { var q = this.value.toLowerCase(); document.querySelectorAll('#wmContacts div').forEach(function (d) { d.style.display = d.textContent.toLowerCase().indexOf(q) < 0 ? 'none' : ''; }); };
    var last = 'wmTo';
    ['wmTo', 'wmCc', 'wmBcc'].forEach(function (id) { $(id).onfocus = function () { last = id; }; });
    document.querySelectorAll('.wm-comp-side-f button').forEach(function (b) { b.onclick = function () { last = b.dataset.t; $(last).focus(); }; });
    $('wmContacts').onclick = function (e) { var d = e.target.closest('div[data-m]'); if (!d) return; var i = $(last); i.value = (i.value ? i.value.replace(/[,\s]+$/, '') + ', ' : '') + d.dataset.m; };
  }
  function addFiles(fl) {
    Array.prototype.forEach.call(fl, function (f) { var tot = S.att.reduce(function (a, b) { return a + b.size; }, 0) + f.size; if (tot > 50 * 1048576) return toast('Los adjuntos no pueden superar 50 MB', true); S.att.push(f); });
    var ul = $('wmAttList'); ul.innerHTML = S.att.map(function (f, i) { return '<li><span>' + esc(f.name) + ' (' + Math.ceil(f.size / 1024) + ' KB)</span><a data-i="' + i + '">✕</a></li>'; }).join('');
    ul.querySelectorAll('a').forEach(function (a) { a.onclick = function () { S.att.splice(+a.dataset.i, 1); addFiles([]); }; });
  }
  function quoted(m) {
    var who = fromOf(m) || 'el remitente', body = (m.cuerpo_html && m.cuerpo_html.trim()) ? clean(m.cuerpo_html) : esc(m.cuerpo_txt || '').replace(/\n/g, '<br>');
    return '<p>El ' + esc(isoLong(m.creado_en)) + ', ' + esc(who) + ' escribió:</p><blockquote>' + body + '</blockquote><br>';
  }
  window.abrirComposeEmail = function (o) {
    o = o || {}; var m = S.cur, mode = o.mode || 'new';
    S.att = []; addFiles([]); S.draftId = null; S.mode = mode; S.ref = (mode !== 'new' && m) ? m.id : null;
    if (mode !== 'new' && !m) { toast('Seleccioná un correo primero', true); return; }
    var to = o.to || '', cc = '', subj = o.subject || '', html = o.bodyHtml || '';
    if (mode === 'reply' || mode === 'replyall') { to = m.remitente_email || ''; subj = /^re:/i.test(m.asunto || '') ? m.asunto : 'Re: ' + (m.asunto || ''); html = '<br>' + quoted(m); if (mode === 'replyall' && m.cc) cc = m.cc; }
    if (mode === 'fwd') { subj = /^(fwd|rv):/i.test(m.asunto || '') ? m.asunto : 'Fwd: ' + (m.asunto || ''); html = '<br><p>---------- Mensaje reenviado ----------<br>De: ' + esc(fromOf(m)) + ' &lt;' + esc(m.remitente_email || '') + '&gt;<br>Asunto: ' + esc(m.asunto || '') + '</p><blockquote>' + ((m.cuerpo_html && clean(m.cuerpo_html)) || esc(m.cuerpo_txt || '').replace(/\n/g, '<br>')) + '</blockquote>'; }
    $('wmTo').value = to; $('wmCc').value = cc; $('wmBcc').value = ''; $('wmReplyTo').value = ''; $('wmSubject').value = subj;
    if (cc) { $('wmCc').classList.remove('wm-hidden'); document.querySelector('[data-for="wmCc"]').classList.remove('wm-hidden'); }
    $('wmEdType').value = 'html'; $('wmEdType').onchange(); ed.innerHTML = html || '<br>';
    var sig = localStorage.getItem('wm_firma'); if (sig && mode === 'new' && !o.bodyHtml) ed.innerHTML = '<br><br>-- <br>' + sig;
    $('wmFrom').value = S.cuenta + '@tecnomedic.com.ar';
    view('Compose');
    setTimeout(function () { (to ? ed : $('wmTo')).focus(); if (to) { var r = document.createRange(); r.selectNodeContents(ed); r.collapse(true); var s = getSelection(); s.removeAllRanges(); s.addRange(r); } }, 50);
  };
  function fromAccount() { return String(($('wmFrom') || {}).value || '').indexOf('noreply') === 0 ? 'noreply' : 'contacto'; }
  function buildPayload() {
    var fd = new FormData(), html = isHtml ? clean(ed.innerHTML) : '', txt = isHtml ? ed.innerText : plain.value;
    fd.append('cuenta', fromAccount()); fd.append('from', $('wmFrom').value);
    fd.append('to', $('wmTo').value.trim()); fd.append('cc', $('wmCc').value.trim()); fd.append('bcc', $('wmBcc').value.trim());
    fd.append('reply_to', $('wmReplyTo').value.trim()); fd.append('subject', $('wmSubject').value.trim());
    fd.append('message', txt); fd.append('html', isHtml ? '1' : '0'); fd.append('message_html', html);
    fd.append('priority', $('wmPrio').value); fd.append('receipt', $('wmReceipt').checked ? '1' : '0'); fd.append('save_sent_in', $('wmSaveIn').value);
    if (S.ref) { fd.append('in_reply_to_id', S.ref); fd.append('mode', S.mode); }
    S.att.forEach(function (f) { fd.append('attachments[]', f, f.name); });
    return fd;
  }
  async function sendCompose() {
    var to = $('wmTo').value.trim(), subj = $('wmSubject').value.trim();
    if (!to) return toast('Falta el destinatario', true);
    if (!subj && !confirm('El asunto está vacío. ¿Enviar igual?')) return;
    var b = $('wmCSend'); b.disabled = true;
    try {
      var d = await getJSON(API.send, { method: 'POST', body: buildPayload() });
      if (!d.ok) return toast('No se pudo enviar: ' + (d.error || 'desconocido'), true);
      toast(d.warning ? d.warning : 'Mensaje enviado ✅', !!d.warning); view('Mail'); if (S.folder === 'SENT') window.actualizarEmailsIMAP();
    } catch (e) { toast('Error al enviar', true); } finally { b.disabled = false; }
  }
  async function saveDraft() {
    var fd = buildPayload(); if (S.draftId) fd.append('draft_id', S.draftId);
    try {
      var d = await getJSON(API.get + '?action=save_draft&cuenta=' + fromAccount(), { method: 'POST', body: fd });
      if (!d.ok) return toast('No se pudo guardar: ' + (d.error || ''), true);
      S.draftId = d.id || S.draftId; toast('Borrador guardado ✅'); if (S.folder === 'DRAFTS') loadList();
    } catch (e) { toast('Error al guardar borrador', true); }
  }
  window.closeComposeEmail = function () { view('Mail'); };

  /* ===== Init ===== */
  document.addEventListener('DOMContentLoaded', function () {
    if (!$('wmViewMail')) return;
    bindToolbar(); bindCompose(); renderFolders(); clearPreview();
    var f = (new URLSearchParams(location.search).get('folder') || '').trim(); if (f) S.folder = f;
    window.cargarCarpetasEmail().then(loadList).then(function () { var first = document.querySelector('#wmList .wm-row'); if (first) first.click(); });
    var tabBtn = document.querySelector('.msg-tab-btn[data-tab="email"]');
    if (tabBtn) tabBtn.addEventListener('click', function () { if (!S.list.length) loadList(); });
  });
})();
