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
}
.form-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}
.form-group {
    flex: 1;
    min-width: 180px;
    margin-bottom: 14px;
}
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
.form-group textarea.numbers-input { min-height: 180px; font-family: 'Courier New', monospace; font-size: 13px; }
.form-hint {
    font-size: 12px;
    color: #888;
    margin-top: 3px;
}
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}
.btn-primary {
    background: #25d366;
    color: white;
}
.btn-primary:hover { background: #1da851; }
.btn-primary:disabled {
    background: #94d3a2;
    cursor: not-allowed;
}
.btn-danger {
    background: #e53935;
    color: white;
}
.btn-danger:hover { background: #c62828; }
.btn-danger:disabled {
    background: #ef9a9a;
    cursor: not-allowed;
}
.actions {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-top: 8px;
    flex-wrap: wrap;
}
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
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 12px;
}
.stat {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 10px 16px;
    flex: 1;
    min-width: 100px;
    text-align: center;
}
.stat .num {
    font-size: 24px;
    font-weight: 700;
    color: #128c7e;
}
.stat .num.success { color: #25d366; }
.stat .num.failed { color: #e53935; }
.stat .num.pending { color: #ff9800; }
.stat .label {
    font-size: 12px;
    color: #888;
    margin-top: 2px;
}
.log-container {
    background: #1a1a2e;
    color: #e0e0e0;
    border-radius: 8px;
    padding: 12px;
    max-height: 320px;
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
@media (max-width: 600px) {
    .container { padding: 12px; }
    .card { padding: 14px; }
    .form-group { min-width: 100%; }
    .stats { gap: 8px; }
    .stat { min-width: 80px; padding: 8px; }
    .stat .num { font-size: 20px; }
}
</style>
</head>
<body>

<div class="header">
    <h1> WhatsApp Blast</h1>
    <p>Broadcast pesan WhatsApp massal dengan rate limiting & cooldown</p>
</div>

<div class="container">

    <!-- CONFIG CARD -->
    <div class="card">
        <h2> Konfigurasi</h2>
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

    <!-- NUMBERS & MESSAGE CARD -->
    <div class="card">
        <h2> Data Pengiriman</h2>
        <div class="form-row">
            <div class="form-group" style="flex:1.4">
                <label>Nomor Telepon</label>
                <textarea class="numbers-input" id="numbersInput" placeholder="628117774884&#10;6281234567890">628117774884</textarea>
                <div class="form-hint">Satu nomor per baris. Format 628xx (tanpa +/spasi)</div>
            </div>
            <div class="form-group" style="flex:1">
                <label>Pesan</label>
                <textarea id="messageInput" placeholder="Tulis pesan WhatsApp..." style="min-height:180px">Halo, ini pesan broadcast.</textarea>
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
            <div class="actions">
                <button class="btn btn-primary" id="btnStart" onclick="startBlast()"> Mulai Blast</button>
                <button class="btn btn-danger" id="btnStop" onclick="stopBlast()" disabled> Hentikan</button>
            </div>
        </div>

        <div id="cooldownInfo" class="cooldown-info"></div>

        <div id="progressContainer" style="display:none">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div class="stats">
                <div class="stat"><div class="num" id="statTotal">0</div><div class="label">Total</div></div>
                <div class="stat"><div class="num success" id="statSuccess">0</div><div class="label">Terkirim</div></div>
                <div class="stat"><div class="num failed" id="statFailed">0</div><div class="label">Gagal</div></div>
                <div class="stat"><div class="num pending" id="statRemaining">0</div><div class="label">Sisa</div></div>
            </div>
        </div>

        <h3 style="font-size:13px; color:#888; margin:12px 0 6px;">Log Pengiriman</h3>
        <div class="log-container" id="logContainer">
            <div class="log-entry" style="color:#666;">— Belum ada pengiriman —</div>
        </div>
    </div>

</div>

<script>
// ===== STATE =====
let isRunning = false;
let shouldStop = false;
let sentInWindow = 0;
let windowStart = Date.now();

// ===== DOM REFS =====
const els = {
    numbers:      () => document.getElementById('numbersInput'),
    message:      () => document.getElementById('messageInput'),
    limitPerMin:  () => parseInt(document.getElementById('limitPerMinute').value) || 10,
    cooldown:     () => parseFloat(document.getElementById('cooldown').value) || 0,
    token:        () => document.getElementById('apiToken').value,
    btnStart:     () => document.getElementById('btnStart'),
    btnStop:      () => document.getElementById('btnStop'),
    badge:        () => document.getElementById('statusBadge'),
    progress:     () => document.getElementById('progressContainer'),
    fill:         () => document.getElementById('progressFill'),
    statTotal:    () => document.getElementById('statTotal'),
    statOk:       () => document.getElementById('statSuccess'),
    statFail:     () => document.getElementById('statFailed'),
    statRem:      () => document.getElementById('statRemaining'),
    log:          () => document.getElementById('logContainer'),
    cooldownInfo: () => document.getElementById('cooldownInfo'),
    etaInfo:      () => document.getElementById('etaInfo'),
};

function setBadge(text, cls) {
    const b = els.badge();
    b.textContent = ' ' + text;
    b.className = 'status-badge ' + cls;
}

function log(msg, type) {
    const el = els.log();
    const d = document.createElement('div');
    d.className = 'log-entry';
    d.innerHTML = msg;
    el.appendChild(d);
    el.scrollTop = el.scrollHeight;
}

function clearLog() {
    els.log().innerHTML = '';
}

function updateStats(total, ok, fail) {
    els.statTotal().textContent = total;
    els.statOk().textContent = ok;
    els.statFail().textContent = fail;
    els.statRem().textContent = Math.max(0, total - ok - fail);
    const pct = total > 0 ? ((ok + fail) / total * 100) : 0;
    els.fill().style.width = pct + '%';
}

async function sendOne(phone, message) {
    const fd = new FormData();
    fd.append('phone', phone);
    fd.append('message', message);

    try {
        const res = await fetch('api/send.php', { method: 'POST', body: fd });
        const data = await res.json();
        return data;
    } catch (e) {
        return { success: false, error: e.message || 'Network error' };
    }
}

function sleep(ms) {
    return new Promise(r => setTimeout(r, ms));
}

async function startBlast() {
    const numbersRaw = els.numbers().value.trim();
    const message    = els.message().value.trim();

    if (!numbersRaw) { alert('Masukkan nomor telepon!'); return; }
    if (!message)    { alert('Masukkan pesan!'); return; }

    // Parse numbers
    const numbers = numbersRaw.split('\n')
        .map(l => l.trim())
        .filter(l => l && !l.startsWith('#') && !l.startsWith('//'))
        .map(l => l.replace(/[^0-9]/g, ''))
        .filter(l => l.length > 0);

    if (numbers.length === 0) { alert('Tidak ada nomor valid!'); return; }

    // Reset
    shouldStop = false;
    isRunning = true;
    sentInWindow = 0;
    windowStart = Date.now();

    els.btnStart().disabled = true;
    els.btnStop().disabled = false;
    els.progress().style.display = 'block';
    clearLog();
    updateStats(numbers.length, 0, 0);

    const limitPerMin = els.limitPerMin();
    const cd          = els.cooldown();

    // Show cooldown info
    const cooldownDiv = els.cooldownInfo();
    if (cd > 0) {
        cooldownDiv.style.display = 'block';
        cooldownDiv.innerHTML = '⏳ Cooldown: <strong>' + cd + 's</strong> antar pesan | Limit: <strong>' + limitPerMin + '</strong> per menit';
    } else {
        cooldownDiv.style.display = 'none';
    }

    // ETA estimate
    const msgPerMinEffective = Math.min(limitPerMin, 60 / (cd || 1));
    const etaMin = Math.ceil(numbers.length / msgPerMinEffective);
    els.etaInfo().textContent = '⏱ Estimasi: ~' + etaMin + ' menit';

    setBadge('Mengirim...', 'running');
    log('<span style="color:#888;">Memulai blast ke <strong>' + numbers.length + '</strong> nomor...</span>', 'info');

    let ok = 0, fail = 0;

    for (let i = 0; i < numbers.length; i++) {
        if (shouldStop) {
            log('<span style="color:#ff9800;">⏸ Dihentikan pengguna.</span>', 'warn');
            break;
        }

        const phone = numbers[i];

        // --- Rate limit check ---
        const elapsed = (Date.now() - windowStart) / 1000;
        if (elapsed >= 60) {
            // reset window
            sentInWindow = 0;
            windowStart = Date.now();
        }
        if (sentInWindow >= limitPerMin) {
            // wait until window resets
            const waitMs = Math.max(100, 60000 - (Date.now() - windowStart));
            log(`<span style="color:#ff9800;">⏳ Rate limit tercapai, tunggu ${Math.ceil(waitMs/1000)}d...</span>`, 'warn');
            if (shouldStop) break;
            await sleep(waitMs);
            sentInWindow = 0;
            windowStart = Date.now();
        }

        // --- Send ---
        const result = await sendOne(phone, message);
        sentInWindow++;

        const idx = i + 1;
        if (result.success) {
            ok++;
            log(`<span class="status ok">✓</span> <span class="idx">#${idx}</span> <span class="phone">${phone}</span> <span class="msg">— OK</span>`);
        } else {
            fail++;
            const errMsg = result.error || (result.response ? (result.response.length > 60 ? result.response.substr(0,60)+'...' : result.response) : 'Unknown');
            log(`<span class="status err">✗</span> <span class="idx">#${idx}</span> <span class="phone">${phone}</span> <span class="msg">— ${errMsg}</span>`);
        }

        updateStats(numbers.length, ok, fail);

        // --- Cooldown ---
        if (cd > 0 && i < numbers.length - 1 && !shouldStop) {
            await sleep(cd * 1000);
        }
    }

    // Done
    isRunning = false;
    els.btnStart().disabled = false;
    els.btnStop().disabled = true;
    els.etaInfo().textContent = '';

    if (shouldStop) {
        setBadge('Dihentikan', 'stopped');
        log('<span style="color:#ff9800;font-weight:700;">⏸ BLAST DIHENTIKAN</span>', 'warn');
    } else {
        setBadge('Selesai', 'done');
        log('<span style="color:#25d366;font-weight:700;">✅ BLAST SELESAI: ' + ok + ' terkirim, ' + fail + ' gagal</span>', 'done');
    }

    updateStats(numbers.length, ok, fail);
    els.cooldownInfo().style.display = 'none';
}

function stopBlast() {
    shouldStop = true;
    els.btnStop().disabled = true;
    els.btnStop().textContent = ' Menghentikan...';
    setTimeout(() => {
        els.btnStop().textContent = ' Hentikan';
    }, 2000);
}
</script>

</body>
</html>
