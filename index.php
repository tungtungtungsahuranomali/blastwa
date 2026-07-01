<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>WhatsApp Blast</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f0f2f5;color:#333;min-height:100vh}
.header{background:linear-gradient(135deg,#075e54,#128c7e);color:white;padding:20px 0;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.15)}
.header h1{font-size:24px;margin-bottom:4px}
.header p{opacity:.85;font-size:14px}
.container{max-width:960px;margin:0 auto;padding:20px 16px}
.card{background:white;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);padding:20px;margin-bottom:20px}
.card h2{font-size:16px;color:#128c7e;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #25d366}
.form-group{margin-bottom:14px}
.form-group label{display:block;font-size:13px;font-weight:600;margin-bottom:5px;color:#555}
.form-group textarea,.form-group input,.form-group select{width:100%;padding:10px 12px;border:1.5px solid #ddd;border-radius:6px;font-size:14px;font-family:inherit}
.form-group textarea:focus,.form-group input:focus{outline:none;border-color:#25d366;box-shadow:0 0 0 3px rgba(37,211,102,.15)}
.form-group textarea{resize:vertical}
.form-row{display:flex;gap:16px;flex-wrap:wrap}
.form-row .fg{flex:1;min-width:140px}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 24px;border:none;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer;transition:all .2s}
.btn-primary{background:#25d366;color:white}
.btn-primary:hover{background:#1da851}
.btn-primary:disabled{background:#94d3a2;cursor:not-allowed}
.btn-danger{background:#e53935;color:white}
.btn-danger:hover{background:#c62828}
.btn-secondary{background:#f5f5f5;color:#333;border:1.5px solid #ddd}
.btn-secondary:hover{background:#eee}
.actions{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:4px}
.progress-bar{width:100%;height:6px;background:#e0e0e0;border-radius:3px;overflow:hidden;margin:12px 0 8px}
.progress-fill{height:100%;width:0%;background:linear-gradient(90deg,#25d366,#128c7e);border-radius:3px;transition:width .4s}
.stats{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px}
.stat{background:#f8f9fa;border-radius:8px;padding:10px 16px;flex:1;min-width:80px;text-align:center}
.stat .num{font-size:24px;font-weight:700;color:#128c7e}
.stat .num.ok{color:#25d366}
.stat .num.fail{color:#e53935}
.stat .label{font-size:12px;color:#888;margin-top:2px}
.log-box{background:#1a1a2e;color:#e0e0e0;border-radius:8px;padding:12px;max-height:300px;overflow-y:auto;font-family:'Courier New',monospace;font-size:12px;line-height:1.6;margin-top:8px}
.log-box .ok{color:#25d366}
.log-box .err{color:#e53935}
.log-box .time{color:#888}
.log-box .phone{color:#64b5f6}
.status-badge{display:inline-block;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600}
.status-badge.idle{background:#e0e0e0;color:#666}
.status-badge.running{background:#fff3cd;color:#856404}
.status-badge.done{background:#d4edda;color:#155724}
.status-badge.stopped{background:#f8d7da;color:#721c24}
#logList div{padding:6px 0;border-bottom:1px solid #eee;cursor:pointer;font-size:13px;display:flex;justify-content:space-between}
#logList div:hover{background:#f8fff9}
#logList .fn{color:#128c7e;font-weight:600}
#logList .s{color:#888;font-size:12px}
@media(max-width:600px){.container{padding:12px}.card{padding:14px}}
</style>
</head>
<body>

<div class="header">
    <h1> WhatsApp Blast</h1>
    <p>API: blast.php + log progress</p>
</div>

<div class="container">

    <!-- FORM BLAST -->
    <div class="card">
        <h2> Kirim Blast</h2>
        <div class="form-group">
            <label>Nomor Telepon <span style="font-weight:400;color:#888">(satu per baris)</span></label>
            <textarea id="numbersInput" rows="6" placeholder="628117774884&#10;082382726553&#10;+62 821-7348-7778"></textarea>
        </div>
        <div class="form-group">
            <label>Pesan</label>
            <textarea id="messageInput" rows="3" placeholder="Tulis pesan...">Halo, ini pesan broadcast.</textarea>
        </div>
        <div class="form-row">
            <div class="fg"><label>Max/menit</label><input type="number" id="limitInput" value="10" min="1"></div>
            <div class="fg"><label>Cooldown (dtk)</label><input type="number" id="cooldownInput" value="3" min="0" step="0.5"></div>
            <div class="fg"><label>Token</label><input type="text" id="tokenInput" value="technical"></div>
        </div>
        <div class="actions">
            <button class="btn btn-primary" id="btnStart" onclick="startBlast()"> Kirim Blast</button>
            <button class="btn btn-danger" id="btnStop" onclick="stopBlast()" disabled> Hentikan</button>
            <span id="statusBadge" class="status-badge idle"> Idle</span>
        </div>
    </div>

    <!-- PROGRESS -->
    <div class="card" id="progressCard" style="display:none">
        <h2> Progress</h2>
        <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
        <div class="stats">
            <div class="stat"><div class="num" id="statTotal">0</div><div class="label">Total</div></div>
            <div class="stat"><div class="num ok" id="statOk">0</div><div class="label">Terkirim</div></div>
            <div class="stat"><div class="num fail" id="statFail">0</div><div class="label">Gagal</div></div>
            <div class="stat"><div class="num" id="statRem">0</div><div class="label">Sisa</div></div>
        </div>
        <div class="log-box" id="logBox"><div class="time">— Menunggu —</div></div>
    </div>

    <!-- LOG HISTORY -->
    <div class="card">
        <h2> Riwayat Log</h2>
        <div id="logList"><div class="time">Memuat...</div></div>
    </div>

</div>

<script>
const $ = id => document.getElementById(id);
const els = { numbers:()=>$('numbersInput'), message:()=>$('messageInput'), limit:()=>parseInt($('limitInput').value)||10, cooldown:()=>parseFloat($('cooldownInput').value)||0, token:()=>$('tokenInput').value, btnStart:()=>$('btnStart'), btnStop:()=>$('btnStop'), badge:()=>$('statusBadge'), progress:()=>$('progressCard'), fill:()=>$('progressFill'), tTotal:()=>$('statTotal'), tOk:()=>$('statOk'), tFail:()=>$('statFail'), tRem:()=>$('statRem'), log:()=>$('logBox'), logList:()=>$('logList') };

let isRunning = false;
let shouldStop = false;
let controller = null;

function badge(text, cls) { const b=els.badge(); b.textContent=' '+text; b.className='status-badge '+cls; }
function log(msg){ const el=els.log(); const d=document.createElement('div');d.innerHTML=msg;el.appendChild(d);el.scrollTop=el.scrollHeight; }
function clearLog(){ els.log().innerHTML=''; }
function stats(total, ok, fail){
    els.tTotal().textContent=total;
    els.tOk().textContent=ok;
    els.tFail().textContent=fail;
    els.tRem().textContent=Math.max(0,total-ok-fail);
    els.fill().style.width=(total>0?((ok+fail)/total*100):0)+'%';
}

function sleep(ms){ return new Promise(r=>setTimeout(r,ms)); }

async function startBlast(){
    const numbersRaw = els.numbers().value.trim();
    const message = els.message().value.trim();
    if(!numbersRaw||!message){ alert('Isi nomor dan pesan!'); return; }

    let numbers = numbersRaw.split('\n').map(l=>l.trim()).filter(l=>l&&!l.startsWith('#')&&!l.startsWith('//'))
        .flatMap(l=>l.split('/').map(s=>s.trim())).map(l=>l.replace(/[^0-9\+]/g,''))
        .map(l=>{if(l.startsWith('+62'))return'62'+l.slice(3);if(l.startsWith('0'))return'62'+l.slice(1);return l;})
        .filter(l=>l.length>=10);
    numbers = [...new Set(numbers)];
    if(!numbers.length){ alert('Tidak ada nomor valid!'); return; }

    shouldStop = false;
    isRunning = true;
    els.btnStart().disabled=true;
    els.btnStop().disabled=false;
    els.progress().style.display='block';
    clearLog();
    stats(numbers.length,0,0);
    badge('Mengirim...','running');
    log('Memulai blast ke <strong>'+numbers.length+'</strong> nomor...');

    let ok=0, fail=0;
    let windowSent=0, windowStart=Date.now();
    const limit = els.limit();
    const cd = els.cooldown();

    for(let i=0; i<numbers.length; i++){
        if(shouldStop){ log('<span class="time">⏸ Dihentikan</span>'); break; }

        const phone = numbers[i];

        // Rate limit
        const elapsed = (Date.now()-windowStart)/1000;
        if(elapsed>=60){ windowSent=0; windowStart=Date.now(); }
        if(windowSent>=limit){
            const w = Math.max(100, 60000-(Date.now()-windowStart));
            log(`<span class="time">⏳ Rate limit, tunggu ${Math.ceil(w/1000)}d...</span>`);
            if(shouldStop) break;
            await sleep(w); windowSent=0; windowStart=Date.now();
        }

        // Send via API
        try {
            const r = await fetch('api/blast.php', {
                method:'POST',
                headers:{'Content-Type':'application/json','Token': els.token()},
                body: JSON.stringify({numbers:[phone], message, limit, cooldown:0}),
            });
            const d = await r.json();
            windowSent++;
            if(d.success){ ok++; log(`<span class="ok">✓</span> <span class="time">#${i+1}</span> <span class="phone">${phone}</span>`); }
            else { fail++; log(`<span class="err">✗</span> <span class="time">#${i+1}</span> <span class="phone">${phone}</span> <span class="time">— ${d.error||'?'}</span>`); }
        } catch(e){
            fail++;
            log(`<span class="err">✗</span> <span class="time">#${i+1}</span> <span class="phone">${phone}</span> <span class="time">— ${e.message}</span>`);
        }

        stats(numbers.length, ok, fail);
        if(cd>0 && i<numbers.length-1 && !shouldStop) await sleep(cd*1000);
    }

    isRunning=false; els.btnStart().disabled=false; els.btnStop().disabled=true;
    badge(shouldStop?'Dihentikan':'Selesai', shouldStop?'stopped':'done');
    log(shouldStop?'<span class="time">⏸ DIHENTIKAN</span>':'<span class="ok">✅ SELESAI: '+ok+' terkirim, '+fail+' gagal</span>');
    stats(numbers.length, ok, fail);
    refreshLogs();
}

function stopBlast(){ shouldStop=true; els.btnStop().disabled=true; }

async function refreshLogs(){
    try{
        const r = await fetch('api/blast-log.php');
        const d = await r.json();
        if(!d.success) return;
        const el = els.logList();
        if(!d.logs.length){ el.innerHTML='<div class="time">Belum ada log.</div>'; return; }
        el.innerHTML = d.logs.map(l => `<div onclick="viewLog('${l.file}')"><span class="fn">${l.file}</span><span class="s">${l.modified} · ${(l.size/1024).toFixed(1)}KB</span></div>`).join('');
    }catch(e){}
}

async function viewLog(file){
    try{
        const r = await fetch('api/blast-log.php?file='+encodeURIComponent(file));
        const d = await r.json();
        if(!d.success) return;
        els.progress().style.display='block';
        clearLog();
        d.content.split('\n').forEach(line => {
            const cls = line.includes('✓')?'ok':line.includes('✗')?'err':'time';
            log(`<span class="${cls}">${escapeHtml(line)}</span>`);
        });
        document.querySelector('#progressCard h2').textContent = '📄 '+file;
    }catch(e){}
}

function escapeHtml(t){ const d=document.createElement('div'); d.textContent=t; return d.innerHTML; }

refreshLogs();
setInterval(refreshLogs, 15000);
</script>
</body>
</html>
