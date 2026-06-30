<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>WhatsApp Blast Dashboard</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #f0f2f5;
    color: #333;
    min-height: 100vh;
}
.header {
    background: linear-gradient(135deg, #075e54, #128c7e);
    color: white;
    padding: 20px 0;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,.15);
}
.header h1 { font-size: 24px; margin-bottom: 4px; }
.header p { opacity: .85; font-size: 14px; }
.container { max-width: 960px; margin: 0 auto; padding: 20px 16px; }
.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    padding: 20px;
    margin-bottom: 20px;
}
.card h2 {
    font-size: 16px;
    color: #128c7e;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid #25d366;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}
.card h2 .badge-count {
    font-size: 12px;
    background: #e8f5e9;
    color: #2e7d32;
    padding: 2px 10px;
    border-radius: 10px;
    font-weight: 600;
}
.form-row { display: flex; gap: 16px; flex-wrap: wrap; }
.form-group { flex: 1; min-width: 180px; margin-bottom: 14px; }
.form-group.full { flex: 0 0 100%; }
.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 5px;
    color: #555;
}
.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1.5px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color .2s;
    font-family: inherit;
}
.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #25d366;
    box-shadow: 0 0 0 3px rgba(37,211,102,.15);
}
.form-group textarea { resize: vertical; min-height: 90px; }
.form-group textarea.numbers-input { min-height: 160px; font-family: 'Courier New', monospace; font-size: 13px; }
.form-hint { font-size: 12px; color: #888; margin-top: 3px; }
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 18px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    white-space: nowrap;
}
.btn-sm { padding: 6px 12px; font-size: 12px; }
.btn-primary { background: #25d366; color: white; }
.btn-primary:hover { background: #1da851; }
.btn-primary:disabled { background: #94d3a2; cursor: not-allowed; }
.btn-danger { background: #e53935; color: white; }
.btn-danger:hover { background: #c62828; }
.btn-danger:disabled { background: #ef9a9a; cursor: not-allowed; }
.btn-outline { background: transparent; color: #128c7e; border: 1.5px solid #128c7e; }
.btn-outline:hover { background: #e8f5e9; }
.btn-secondary { background: #f5f5f5; color: #333; border: 1.5px solid #ddd; }
.btn-secondary:hover { background: #eee; }
.btn-warning { background: #ff9800; color: white; }
.btn-warning:hover { background: #f57c00; }
.actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.progress-bar {
    width: 100%;
    height: 6px;
    background: #e0e0e0;
    border-radius: 3px;
    overflow: hidden;
    margin: 12px 0 8px;
}
.progress-fill {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #25d366, #128c7e);
    border-radius: 3px;
    transition: width .4s ease;
}
.stats {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 12px;
}
.stat {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 10px 16px;
    flex: 1;
    min-width: 90px;
    text-align: center;
}
.stat .num { font-size: 24px; font-weight: 700; color: #128c7e; }
.stat .num.success { color: #25d366; }
.stat .num.failed { color: #e53935; }
.stat .num.pending { color: #ff9800; }
.stat .label { font-size: 12px; color: #888; margin-top: 2px; }
.log-container {
    background: #1a1a2e;
    color: #e0e0e0;
    border-radius: 8px;
    padding: 12px;
    max-height: 300px;
    overflow-y: auto;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.6;
    margin-top: 8px;
}
.log-entry { padding: 2px 0; border-bottom: 1px solid #2a2a3e; }
.log-entry:last-child { border-bottom: none; }
.log-entry .status { font-weight: 700; margin-right: 6px; }
.log-entry .ok { color: #25d366; }
.log-entry .err { color: #e53935; }
.log-entry .idx { color: #888; }
.log-entry .phone { color: #64b5f6; }
.log-entry .msg { color: #aaa; }
.status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}
.status-badge.idle { background: #e0e0e0; color: #666; }
.status-badge.running { background: #fff3cd; color: #856404; }
.status-badge.done { background: #d4edda; color: #155724; }
.status-badge.stopped { background: #f8d7da; color: #721c24; }
.cooldown-info {
    font-size: 13px;
    color: #666;
    padding: 8px 12px;
    background: #fff8e1;
    border-radius: 6px;
    margin-bottom: 10px;
    display: none;
}
.list-bar {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eee;
}
.list-bar select {
    flex: 1; min-width: 150px; padding: 8px 10px;
    border: 1.5px solid #ddd; border-radius: 6px; font-size: 13px;
}
.list-bar select:focus { outline: none; border-color: #25d366; }
.save-dialog {
    background: #f8fff9; border: 1.5px solid #25d366;
    border-radius: 8px; padding: 12px; margin-bottom: 12px; display: none;
}
.save-dialog .row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.save-dialog input { flex: 1; min-width: 140px; padding: 8px 10px; border: 1.5px solid #ddd; border-radius: 6px; font-size: 13px; }
.notif { font-size: 13px; padding: 8px 12px; border-radius: 6px; margin-bottom: 10px; display: none; }
.notif.success { background: #d4edda; color: #155724; display: block; }
.notif.error { background: #f8d7da; color: #721c24; display: block; }
.notif.info { background: #d1ecf1; color: #0c5460; display: block; }
.template-bar { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-bottom: 8px; }
.template-bar select { flex: 1; min-width: 140px; padding: 8px 10px; border: 1.5px solid #ddd; border-radius: 6px; font-size: 13px; }
.template-bar select:focus { outline: none; border-color: #25d366; }

/* Cron mode indicator */
.mode-toggle {
    display: flex;
    gap: 4px;
    align-items: center;
    font-size: 12px;
    background: #f0f2f5;
    border-radius: 20px;
    padding: 2px;
}
.mode-toggle button {
    border: none;
    background: transparent;
    padding: 4px 12px;
    border-radius: 16px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    color: #888;
}
.mode-toggle button.active {
    background: #128c7e;
    color: white;
}

/* Jobs list */
.job-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    border: 1px solid #eee;
    border-radius: 8px;
    margin-bottom: 6px;
    flex-wrap: wrap;
    gap: 8px;
    transition: background .15s;
}
.job-item:hover { background: #f8fff9; }
.job-item .info { flex: 1; min-width: 150px; }
.job-item .info .name { font-weight: 600; font-size: 14px; }
.job-item .info .date { font-size: 12px; color: #888; }
.job-item .summary { text-align: right; font-size: 13px; }
.job-item .summary .s { color: #25d366; font-weight: 700; }
.job-item .summary .f { color: #e53935; font-weight: 700; }
.job-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
}
.job-badge.pending { background: #fff3cd; color: #856404; }
.job-badge.running { background: #cce5ff; color: #004085; }
.job-badge.completed { background: #d4edda; color: #155724; }
.job-badge.cancelled { background: #f8d7da; color: #721c24; }
.progress-micro {
    height: 3px;
    background: #e0e0e0;
    border-radius: 2px;
    overflow: hidden;
    margin-top: 4px;
}
.progress-micro .fill {
    height: 100%;
    border-radius: 2px;
    transition: width .5s;
    background: #25d366;
}
.empty-state { text-align: center; padding: 30px 20px; color: #aaa; }
.empty-state p { font-size: 14px; margin-top: 6px; }

@media (max-width: 600px) {
    .container { padding: 12px; }
    .card { padding: 14px; }
    .form-group { min-width: 100%; }
    .stats { gap: 8px; }
    .stat { min-width: 75px; padding: 8px; }
    .stat .num { font-size: 20px; }
}
</style>
</head>
<body>

<div class="header">
    <h1> WhatsApp Blast</h1>
    <p>Broadcast massal | Background processing via cron</p>
</div>

<div class="container">

    <!-- CONTACT LIST MANAGER -->
    <div class="card">
        <h2>
            <span> Daftar Kontak</span>
            <span class="badge-count" id="contactCount">0 nomor</span>
        </h2>
        <div id="notifArea" class="notif"></div>
        <div class="list-bar">
            <select id="listSelector" onchange="loadList(this.value)"><option value="">-- Pilih daftar --</option></select>
            <button class="btn btn-outline btn-sm" onclick="showSaveDialog()"> Simpan</button>
            <button class="btn btn-secondary btn-sm" onclick="showNewDialog()"> Baru</button>
            <button class="btn btn-danger btn-sm" onclick="deleteList()"> Hapus</button>
        </div>
        <div class="save-dialog" id="saveDialog">
            <div class="row">
                <span style="font-size:13px;font-weight:600;">Nama daftar:</span>
                <input type="text" id="saveListName" placeholder="nama-daftar" onkeydown="if(event.key==='Enter') saveList()">
                <button class="btn btn-primary btn-sm" onclick="saveList()"> Simpan</button>
                <button class="btn btn-secondary btn-sm" onclick="hideSaveDialog()">Batal</button>
            </div>
        </div>
        <div class="form-group full" style="margin-bottom:6px">
            <label>Nomor Telepon <span style="font-weight:400;color:#888;">(satu per baris)</span></label>
            <textarea class="numbers-input" id="numbersInput" placeholder="628117774884"></textarea>
            <div class="form-hint">Klik "Bersihkan" untuk format 62xx, hapus duplikat, urutkan</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button class="btn btn-secondary btn-sm" onclick="cleanNumbers()"> Bersihkan & Urutkan</button>
            <button class="btn btn-secondary btn-sm" onclick="countNumbers()"> Hitung</button>
        </div>
    </div>

    <!-- TEMPLATE & CONFIG -->
    <div class="card">
        <h2> Template & Konfigurasi</h2>
        <div class="template-bar">
            <span style="font-size:13px;font-weight:600;">Template:</span>
            <select id="templateSelector" onchange="loadTemplate(this.value)"><option value="">-- Template --</option></select>
            <button class="btn btn-outline btn-sm" onclick="showTemplateSave()"> Simpan</button>
            <button class="btn btn-secondary btn-sm" onclick="templateNew()"> Baru</button>
            <button class="btn btn-danger btn-sm" onclick="deleteTemplate()"> Hapus</button>
        </div>
        <div class="save-dialog" id="templateSaveDialog">
            <div class="row">
                <span style="font-size:13px;font-weight:600;">Nama template:</span>
                <input type="text" id="templateSaveName" placeholder="nama-template" onkeydown="if(event.key==='Enter') saveTemplate()">
                <button class="btn btn-primary btn-sm" onclick="saveTemplate()"> Simpan</button>
                <button class="btn btn-secondary btn-sm" onclick="hideTemplateSave()">Batal</button>
            </div>
        </div>
        <div class="form-group full" style="margin-bottom:10px">
            <label>Pesan</label>
            <textarea id="messageInput" placeholder="Tulis pesan WhatsApp..." style="min-height:80px">Halo, ini pesan broadcast.</textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Max per Menit</label>
                <input type="number" id="limitPerMinute" value="10" min="1" max="1000">
                <div class="form-hint">Maksimal kirim per 60 detik</div>
            </div>
            <div class="form-group">
                <label>Cooldown (detik)</label>
                <input type="number" id="cooldown" value="3" min="0" max="60" step="0.5">
                <div class="form-hint">Jeda antar pesan</div>
            </div>
            <div class="form-group">
                <label>API Token</label>
                <input type="text" id="apiToken" value="takeoff">
            </div>
        </div>
    </div>

    <!-- CONTROL CARD -->
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <div>
                <span id="statusBadge" class="status-badge idle"> Idle</span>
                <span id="etaInfo" style="margin-left:12px; font-size:13px; color:#888;"></span>
            </div>
            <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                <div class="mode-toggle">
                    <button id="modeBrowser" class="active" onclick="setMode('browser')"> Browser</button>
                    <button id="modeCron" onclick="setMode('cron')"> Cron</button>
                </div>
                <button class="btn btn-primary" id="btnStart" onclick="startBlast()"> Mulai Blast</button>
                <button class="btn btn-danger" id="btnStop" onclick="stopBlast()" disabled> Hentikan</button>
            </div>
        </div>

        <div id="cooldownInfo" class="cooldown-info"></div>

        <div id="progressContainer" style="display:none">
            <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
            <div class="stats">
                <div class="stat"><div class="num" id="statTotal">0</div><div class="label">Total</div></div>
                <div class="stat"><div class="num success" id="statSuccess">0</div><div class="label">Terkirim</div></div>
                <div class="stat"><div class="num failed" id="statFailed">0</div><div class="label">Gagal</div></div>
                <div class="stat"><div class="num pending" id="statRemaining">0</div><div class="label">Sisa</div></div>
            </div>
        </div>

        <h3 style="font-size:13px; color:#888; margin:12px 0 6px;">Log</h3>
        <div class="log-container" id="logContainer">
            <div class="log-entry" style="color:#666;">— Belum ada pengiriman —</div>
        </div>
    </div>

    <!-- ACTIVE JOBS -->
    <div class="card">
        <h2>
            <span> Pekerjaan Aktif</span>
            <span class="badge-count" id="jobsCount">0</span>
        </h2>
        <div id="jobsList">
            <div class="empty-state"><p>Belum ada pekerjaan.</p></div>
        </div>
    </div>

</div>

<script>
// ===== STATE =====
let isRunning = false;
let shouldStop = false;
let sentInWindow = 0;
let windowStart = Date.now();
let currentList = null;
let currentTemplate = null;
let blastResults = [];
let currentJobId = null;
let jobPollTimer = null;
let cronPollTimer = null;
let mode = 'browser'; // 'browser' or 'cron'

const $ = id => document.getElementById(id);
const els = {
    numbers:      () => $('numbersInput'),
    message:      () => $('messageInput'),
    limitPerMin:  () => parseInt($('limitPerMinute').value) || 10,
    cooldown:     () => parseFloat($('cooldown').value) || 0,
    token:        () => $('apiToken').value,
    btnStart:     () => $('btnStart'),
    btnStop:      () => $('btnStop'),
    badge:        () => $('statusBadge'),
    progress:     () => $('progressContainer'),
    fill:         () => $('progressFill'),
    statTotal:    () => $('statTotal'),
    statOk:       () => $('statSuccess'),
    statFail:     () => $('statFailed'),
    statRem:      () => $('statRemaining'),
    log:          () => $('logContainer'),
    cooldownInfo: () => $('cooldownInfo'),
    etaInfo:      () => $('etaInfo'),
    listSel:      () => $('listSelector'),
    contactCnt:   () => $('contactCount'),
    notif:        () => $('notifArea'),
    saveDialog:   () => $('saveDialog'),
    saveName:     () => $('saveListName'),
    tmplSel:      () => $('templateSelector'),
    tmplSaveDlg:  () => $('templateSaveDialog'),
    tmplSaveName: () => $('templateSaveName'),
    jobsList:     () => $('jobsList'),
    jobsCount:    () => $('jobsCount'),
};

function notif(msg, type) {
    const el = els.notif();
    el.textContent = msg;
    el.className = 'notif ' + type;
    if (type) el.style.display = 'block';
    else el.style.display = 'none';
    if (type) setTimeout(() => { el.style.display = 'none'; }, 4000);
}

function setMode(m) {
    mode = m;
    $('modeBrowser').className = m === 'browser' ? 'active' : '';
    $('modeCron').className = m === 'cron' ? 'active' : '';
    if (m === 'cron') {
        notif('Mode Cron: pastikan cron aktif setiap beberapa detik', 'info');
    }
}

// =============================================
// CONTACT LIST API
// =============================================
async function fetchLists() { return (await fetch('api/contacts.php?lists')).json(); }
async function fetchList(n) { return (await fetch('api/contacts.php?list='+encodeURIComponent(n))).json(); }
async function saveListApi(n, nums) {
    return (await fetch('api/contacts.php?save='+encodeURIComponent(n), { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({numbers:nums}) })).json();
}
async function deleteListApi(n) { return (await fetch('api/contacts.php?list='+encodeURIComponent(n), {method:'DELETE'})).json(); }

async function refreshLists() {
    const d = await fetchLists();
    if (!d.success) return;
    const sel = els.listSel(), cur = sel.value;
    sel.innerHTML = '<option value="">-- Pilih daftar --</option>';
    d.lists.forEach(l => { const o=document.createElement('option'); o.value=l.name; o.textContent=l.name+' ('+l.count+')'; sel.appendChild(o); });
    if (cur && [...sel.options].some(o=>o.value===cur)) sel.value = cur;
}
async function loadList(n) {
    if (!n) return;
    const d = await fetchList(n);
    if (!d.success) { notif(d.error,'error'); return; }
    els.numbers().value = d.numbers.join('\n');
    currentList = n; els.contactCnt().textContent = d.numbers.length+' nomor';
    notif('Loaded: '+n+' ('+d.numbers.length+' nomor)','success');
}
function showSaveDialog() { els.saveName().value = currentList||'daftar-'+Date.now(); els.saveDialog().style.display='block'; els.saveName().focus(); }
function hideSaveDialog() { els.saveDialog().style.display='none'; }
async function saveList() {
    const n = els.saveName().value.trim();
    if (!n) { notif('Nama daftar wajib diisi','error'); return; }
    const raw = els.numbers().value.trim(), nums = raw ? raw.split('\n').map(l=>l.trim()).filter(l=>l) : [];
    const d = await saveListApi(n, nums);
    if (d.success) { notif('Tersimpan: '+n+' ('+d.count+' nomor)','success'); currentList=n; els.contactCnt().textContent=d.count+' nomor'; hideSaveDialog(); await refreshLists(); els.listSel().value=n; }
    else notif(d.error,'error');
}
function showNewDialog() { els.saveName().value='daftar-'+new Date().toISOString().slice(0,10); els.saveDialog().style.display='block'; els.saveName().focus(); }
async function deleteList() {
    const n = els.listSel().value;
    if (!n) { notif('Pilih daftar','error'); return; }
    if (!confirm('Hapus daftar "'+n+'"?')) return;
    await deleteListApi(n); currentList=null; els.numbers().value=''; els.contactCnt().textContent='0 nomor';
    notif('Daftar "'+n+'" dihapus','success'); await refreshLists();
}

// =============================================
// TEMPLATE API
// =============================================
async function fetchTemplates() { return (await fetch('api/templates.php?list')).json(); }
async function fetchTemplate(n) { return (await fetch('api/templates.php?load='+encodeURIComponent(n))).json(); }
async function saveTemplateApi(n,c) {
    return (await fetch('api/templates.php?save='+encodeURIComponent(n), { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({content:c}) })).json();
}
async function deleteTemplateApi(n) { return (await fetch('api/templates.php?delete='+encodeURIComponent(n))).json(); }

async function refreshTemplates() {
    const d = await fetchTemplates();
    if (!d.success) return;
    const sel = els.tmplSel(), cur = sel.value;
    sel.innerHTML = '<option value="">-- Template --</option>';
    d.templates.forEach(t => { const o=document.createElement('option'); o.value=t.name; o.textContent=t.name; sel.appendChild(o); });
    if (cur && [...sel.options].some(o=>o.value===cur)) sel.value = cur;
}
async function loadTemplate(n) {
    if (!n) return;
    const d = await fetchTemplate(n);
    if (!d.success) { notif(d.error,'error'); return; }
    els.message().value = d.content; currentTemplate = n; notif('Template "'+n+'" dimuat','success');
}
function showTemplateSave() { els.tmplSaveName().value = currentTemplate||'template-'+Date.now(); els.tmplSaveDlg().style.display='block'; els.tmplSaveName().focus(); }
function hideTemplateSave() { els.tmplSaveDlg().style.display='none'; }
async function saveTemplate() {
    const n = els.tmplSaveName().value.trim();
    if (!n) { notif('Nama template wajib','error'); return; }
    const d = await saveTemplateApi(n, els.message().value);
    if (d.success) { notif('Template "'+n+'" tersimpan','success'); currentTemplate=n; hideTemplateSave(); await refreshTemplates(); els.tmplSel().value=n; }
    else notif(d.error,'error');
}
function templateNew() { els.message().value=''; currentTemplate=null; els.tmplSel().value=''; }
async function deleteTemplate() {
    const n = els.tmplSel().value;
    if (!n) return; if (!confirm('Hapus template "'+n+'"?')) return;
    await deleteTemplateApi(n); currentTemplate=null; notif('Template "'+n+'" dihapus','success'); await refreshTemplates(); els.tmplSel().value='';
}

// =============================================
// NUMBER TOOLS
// =============================================
function cleanNumbers() {
    const raw = els.numbers().value.trim();
    if (!raw) return;
    const nums = raw.split('\n').map(l=>l.trim()).filter(l=>l&&!l.startsWith('#')&&!l.startsWith('//'))
        .flatMap(l=>l.split('/').map(s=>s.trim())).map(l=>l.replace(/[^0-9\+]/g,''))
        .map(l=>{if(l.startsWith('+62'))return'62'+l.slice(3);if(l.startsWith('0'))return'62'+l.slice(1);return l;})
        .filter(l=>l.length>=10&&l.length<=15);
    const unique = [...new Set(nums)].sort();
    els.numbers().value = unique.join('\n');
    els.contactCnt().textContent = unique.length+' nomor';
    notif('Dibersihkan: '+unique.length+' nomor valid','success');
}
function countNumbers() {
    const raw = els.numbers().value.trim();
    notif('Total: '+(raw?raw.split('\n').filter(l=>l.trim()).length:0)+' nomor','info');
}

// =============================================
// BLAST — Browser mode (AJAX)
// =============================================
function setBadge(text, cls) {
    $('statusBadge').textContent = ' '+text;
    $('statusBadge').className = 'status-badge '+cls;
}
function log(msg) {
    const el = els.log();
    const d = document.createElement('div'); d.className='log-entry'; d.innerHTML=msg;
    el.appendChild(d); el.scrollTop = el.scrollHeight;
}
function clearLog() { els.log().innerHTML = ''; }
function updateStats(total, ok, fail) {
    els.statTotal().textContent = total;
    els.statOk().textContent = ok;
    els.statFail().textContent = fail;
    els.statRem().textContent = Math.max(0, total - ok - fail);
    els.fill().style.width = (total>0?((ok+fail)/total*100):0)+'%';
}
async function sendOne(phone, message) {
    const fd = new FormData(); fd.append('phone',phone); fd.append('message',message);
    try { return await (await fetch('api/send.php',{method:'POST',body:fd})).json(); }
    catch(e) { return {success:false,error:e.message||'Network error'}; }
}
function sleep(ms) { return new Promise(r=>setTimeout(r,ms)); }

// =============================================
// QUEUE API (cron mode)
// =============================================
async function createJob(name, numbers, message, config) {
    return (await fetch('api/queue.php?create', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({name, numbers, message, config})
    })).json();
}
async function getJobStatus(id) {
    return (await fetch('api/queue.php?status='+encodeURIComponent(id))).json();
}
async function cancelJob(id) {
    return (await fetch('api/queue.php?cancel='+encodeURIComponent(id), {method:'POST'})).json();
}
async function listJobs() {
    return (await fetch('api/queue.php?list')).json();
}

// =============================================
// MAIN START / STOP
// =============================================
async function startBlast() {
    const numbersRaw = els.numbers().value.trim();
    const message    = els.message().value.trim();
    if (!numbersRaw) { alert('Masukkan nomor telepon!'); return; }
    if (!message)    { alert('Masukkan pesan!'); return; }

    let numbers = numbersRaw.split('\n').map(l=>l.trim()).filter(l=>l&&!l.startsWith('#')&&!l.startsWith('//'))
        .map(l=>l.replace(/[^0-9]/g,'')).filter(l=>l.length>0);
    if (numbers.length===0) { alert('Tidak ada nomor valid!'); return; }

    els.btnStart().disabled = true;
    els.btnStop().disabled = false;
    els.progress().style.display = 'block';
    clearLog();
    updateStats(numbers.length, 0, 0);

    const limitPerMin = els.limitPerMin();
    const cd = els.cooldown();

    if (mode === 'cron') {
        await startBlastCron(numbers, message, limitPerMin, cd);
    } else {
        await startBlastBrowser(numbers, message, limitPerMin, cd);
    }

    els.btnStart().disabled = false;
    els.btnStop().disabled = true;
    els.cooldownInfo().style.display = 'none';
    refreshJobs();
}

async function startBlastBrowser(numbers, message, limitPerMin, cd) {
    shouldStop = false;
    sentInWindow = 0;
    windowStart = Date.now();
    blastResults = [];

    const cdDiv = els.cooldownInfo();
    if (cd>0) { cdDiv.style.display='block'; cdDiv.innerHTML='⏳ Cooldown: <strong>'+cd+'s</strong> | Limit: <strong>'+limitPerMin+'</strong>/menit'; }
    else cdDiv.style.display='none';

    const mpm = Math.min(limitPerMin, 60/(cd||1));
    els.etaInfo().textContent = '⏱ Estimasi: ~'+Math.ceil(numbers.length/mpm)+' menit';
    setBadge('Mengirim...','running');
    log('<span style="color:#888;">Browser mode — '+numbers.length+' nomor...</span>');

    let ok=0, fail=0;
    for (let i=0; i<numbers.length; i++) {
        if (shouldStop) { log('<span style="color:#ff9800;">⏸ Dihentikan.</span>'); break; }
        const phone = numbers[i];
        const elapsed = (Date.now()-windowStart)/1000;
        if (elapsed>=60) { sentInWindow=0; windowStart=Date.now(); }
        if (sentInWindow>=limitPerMin) {
            const w = Math.max(100, 60000-(Date.now()-windowStart));
            log(`<span style="color:#ff9800;">⏳ Rate limit, tunggu ${Math.ceil(w/1000)}d...</span>`);
            if (shouldStop) break;
            await sleep(w); sentInWindow=0; windowStart=Date.now();
        }
        const result = await sendOne(phone, message);
        sentInWindow++;
        if (result.success) { ok++; log(`<span class="status ok">✓</span> <span class="idx">#${i+1}</span> <span class="phone">${phone}</span>`); }
        else {
            fail++;
            const em = result.error||(result.response?JSON.stringify(result.response).substring(0,60):'Unknown');
            log(`<span class="status err">✗</span> <span class="idx">#${i+1}</span> <span class="phone">${phone}</span> <span class="msg">— ${em}</span>`);
        }
        updateStats(numbers.length, ok, fail);
        if (cd>0 && i<numbers.length-1 && !shouldStop) await sleep(cd*1000);
    }

    setBadge(shouldStop?'Dihentikan':'Selesai', shouldStop?'stopped':'done');
    log(shouldStop
        ? '<span style="color:#ff9800;font-weight:700;">⏸ DIHENTIKAN</span>'
        : '<span style="color:#25d366;font-weight:700;">✅ SELESAI: '+ok+' terkirim, '+fail+' gagal</span>');
    updateStats(numbers.length, ok, fail);
}

async function startBlastCron(numbers, message, limitPerMin, cd) {
    setBadge('Membuat job...','running');
    log('<span style="color:#888;">Cron mode — membuat job antrian untuk '+numbers.length+' nomor...</span>');

    const config = { limitPerMin, cooldown: cd };
    const now = new Date();
    const name = 'Blast '+now.toLocaleDateString('id-ID',{day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'});

    const job = await createJob(name, numbers, message, config);
    if (!job.success) {
        notif('Gagal membuat job: '+job.error,'error');
        setBadge('Error','idle');
        return;
    }

    currentJobId = job.id;
    notif('Job dibuat: '+job.id+' ('+job.total+' nomor)','success');
    log('<span style="color:#25d366;">✅ Job #'+job.id+' dibuat — total '+job.total+' nomor</span>');
    setBadge('Menunggu worker...','running');

    // Poll for status
    if (jobPollTimer) clearInterval(jobPollTimer);
    if (cronPollTimer) clearInterval(cronPollTimer);

    // Poll the job every 2 seconds
    jobPollTimer = setInterval(async () => {
        if (!currentJobId) return;
        const st = await getJobStatus(currentJobId);
        if (!st.success) { clearInterval(jobPollTimer); return; }

        updateStats(st.total, st.sent, st.failed);
        const pct = st.total > 0 ? ((st.sent+st.failed)/st.total*100) : 0;
        els.fill().style.width = pct+'%';
        els.etaInfo().textContent = '📊 '+st.sent+' terkirim, '+st.failed+' gagal';

        // Show latest results
        const newResults = st.results || [];
        if (newResults.length > 0) {
            // Only show new ones since last check
            // We'll just show the latest few each poll
        }

        if (st.status === 'completed') {
            clearInterval(jobPollTimer);
            setBadge('Selesai','done');
            log('<span style="color:#25d366;font-weight:700;">✅ JOB SELESAI: '+st.sent+' terkirim, '+st.failed+' gagal</span>');
            els.etaInfo().textContent = '';
            currentJobId = null;
        } else if (st.status === 'cancelled') {
            clearInterval(jobPollTimer);
            setBadge('Dihentikan','stopped');
            log('<span style="color:#ff9800;font-weight:700;">⏸ JOB DIHENTIKAN</span>');
            els.etaInfo().textContent = '';
            currentJobId = null;
        }
    }, 2000);

    // Also try to trigger the worker every 5 seconds (browser acts as pseudo-cron)
    cronPollTimer = setInterval(async () => {
        if (!currentJobId) { clearInterval(cronPollTimer); return; }
        try {
            const r = await fetch('api/cron.php?key=blastwa-secret-2024');
            const d = await r.json();
            if (d.success) {
                // Show each sent line
                log(`<span class="status ${d.status==='ok'?'ok':'err'}">${d.status==='ok'?'✓':'✗'}</span> <span class="idx">#${d.index}</span> <span class="phone">${d.phone} [${d.sent}T/${d.failed}F]</span>`);
            }
        } catch(e) {}
    }, 1500);
}

function stopBlast() {
    if (mode === 'cron' && currentJobId) {
        cancelJob(currentJobId);
        if (jobPollTimer) clearInterval(jobPollTimer);
        if (cronPollTimer) clearInterval(cronPollTimer);
        jobPollTimer = null; cronPollTimer = null;
        currentJobId = null;
    }
    shouldStop = true;
    els.btnStop().disabled = true;
    els.btnStop().textContent = ' Menghentikan...';
    setTimeout(() => { els.btnStop().textContent = ' Hentikan'; }, 2000);
}

// =============================================
// JOBS LIST
// =============================================
async function refreshJobs() {
    try {
        const d = await listJobs();
        if (!d.success) return;
        renderJobs(d.jobs);
    } catch(e) {}
}
function renderJobs(jobs) {
    const el = els.jobsList();
    els.jobsCount().textContent = jobs.length;
    if (jobs.length===0) { el.innerHTML = '<div class="empty-state"><p>Belum ada pekerjaan.</p></div>'; return; }
    el.innerHTML = jobs.map(j => `
        <div class="job-item">
            <div class="info">
                <div class="name">${j.name} <span class="job-badge ${j.status}">${j.status}</span></div>
                <div class="date">${j.created||''}</div>
                <div class="progress-micro"><div class="fill" style="width:${j.progress||0}%"></div></div>
            </div>
            <div class="summary">
                ${j.total} · <span class="s">${j.sent}✓</span>${j.failed>0?' <span class="f">'+j.failed+'✗</span>':''}
            </div>
        </div>
    `).join('');
}

// ===== INIT =====
refreshLists();
refreshTemplates();
refreshJobs();
setInterval(refreshJobs, 15000); // refresh job list every 15s
</script>

</body>
</html>
