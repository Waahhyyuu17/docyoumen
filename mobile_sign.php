<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>DocYouMen — Tanda Tangan</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cabinet+Grotesk:wght@400;700;800&family=Instrument+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
  * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
  :root {
    --bg:#0d0d14; --surface:#14141f; --surface2:#1e1e2e;
    --border:rgba(255,255,255,0.08); --accent:#6366f1; --accent2:#ec4899;
    --text:#f1f1f8; --muted:#6b7280; --success:#10b981;
  }
  html, body {
    font-family:'Instrument Sans',sans-serif;
    background:var(--bg); color:var(--text);
    height:100%; overflow:hidden;
  }
  /* Seluruh halaman scrollable di dalam wrapper */
  .page-scroll {
    height: 100vh;
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
    display: flex;
    flex-direction: column;
  }
  header {
    padding:14px 20px;
    display:flex; align-items:center; justify-content:space-between;
    border-bottom:1px solid var(--border);
    background:var(--surface);
    flex-shrink:0;
    position:sticky; top:0; z-index:10;
  }
  .logo-text { font-family:'Cabinet Grotesk',sans-serif; font-weight:800; font-size:18px; letter-spacing:-0.5px; }
  .logo-text em { font-style:normal; background:linear-gradient(135deg,var(--accent),var(--accent2)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
  .status-badge { font-size:12px; padding:4px 10px; border-radius:20px; border:1px solid rgba(99,102,241,0.3); color:#a5b4fc; background:rgba(99,102,241,0.1); }
  .main { flex:1; display:flex; flex-direction:column; padding:14px 16px 24px; gap:12px; }
  .instruction { text-align:center; flex-shrink:0; }
  .instruction h2 { font-family:'Cabinet Grotesk',sans-serif; font-size:18px; font-weight:700; margin-bottom:3px; }
  .instruction p { font-size:12px; color:var(--muted); }

  /* Canvas area — transparan, background checkerboard */
  .canvas-wrap {
    flex:1;
    min-height:220px;
    border-radius:14px;
    overflow:hidden;
    position:relative;
    touch-action:none;
    /* Checkerboard untuk indikasi transparan */
    background-color:#fff;
    background-image:
      linear-gradient(45deg,#e5e5e5 25%,transparent 25%),
      linear-gradient(-45deg,#e5e5e5 25%,transparent 25%),
      linear-gradient(45deg,transparent 75%,#e5e5e5 75%),
      linear-gradient(-45deg,transparent 75%,#e5e5e5 75%);
    background-size:16px 16px;
    background-position:0 0,0 8px,8px -8px,-8px 0;
    border:1px solid rgba(0,0,0,0.1);
  }
  #mobileCanvas {
    display:block; width:100%; height:100%;
    cursor:crosshair;
    background:transparent; /* Canvas transparan */
  }
  .canvas-hint {
    position:absolute; inset:0;
    display:flex; align-items:center; justify-content:center;
    pointer-events:none; opacity:0.35;
  }
  .canvas-hint span { font-size:15px; color:#666; font-style:italic; }

  .toolbar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; flex-shrink:0; }
  .color-chips { display:flex; gap:8px; }
  .color-chip { width:30px; height:30px; border-radius:50%; border:2px solid transparent; cursor:pointer; transition:transform 0.2s,border-color 0.2s; }
  .color-chip.active { border-color:var(--accent); transform:scale(1.2); }

  .thickness-btns { display:flex; gap:6px; }
  .thick-btn { padding:6px 12px; background:var(--surface2); border:1px solid var(--border); color:var(--text); border-radius:8px; font-size:13px; cursor:pointer; font-family:'Instrument Sans',sans-serif; transition:all 0.2s; }
  .thick-btn.active { background:var(--accent); border-color:var(--accent); color:white; }

  .btn-clear-mob { padding:8px 14px; background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.25); color:#fca5a5; border-radius:10px; font-size:13px; cursor:pointer; font-family:'Instrument Sans',sans-serif; flex-shrink:0; }

  /* Tombol kirim — fixed di bottom agar selalu terlihat */
  .submit-wrap {
    flex-shrink:0;
    padding:0 0 4px;
  }
  .btn-submit {
    width:100%; padding:16px;
    background:linear-gradient(135deg,var(--accent),#8b5cf6);
    color:white; border:none; border-radius:14px;
    font-family:'Cabinet Grotesk',sans-serif; font-size:18px; font-weight:700;
    cursor:pointer; transition:all 0.3s; letter-spacing:0.3px;
    box-shadow:0 4px 20px rgba(99,102,241,0.4);
  }
  .btn-submit:active { opacity:0.85; transform:scale(0.98); }
  .btn-submit:disabled { opacity:0.5; }

  /* Success */
  .success-screen { display:none; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:60px 24px; flex:1; }
  .success-screen.show { display:flex; }
  .main.hide { display:none; }
  .success-icon { width:80px; height:80px; background:linear-gradient(135deg,rgba(16,185,129,0.2),rgba(16,185,129,0.05)); border:2px solid rgba(16,185,129,0.4); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:36px; margin:0 auto 24px; animation:pop 0.5s cubic-bezier(0.175,0.885,0.32,1.275) both; }
  @keyframes pop { from{transform:scale(0);opacity:0;} to{transform:scale(1);opacity:1;} }
  .success-screen h2 { font-family:'Cabinet Grotesk',sans-serif; font-size:28px; font-weight:800; margin-bottom:10px; }
  .success-screen p { color:var(--muted); font-size:15px; }

  /* Loading overlay */
  .loading-overlay { position:fixed; inset:0; background:rgba(13,13,20,0.85); backdrop-filter:blur(4px); display:none; align-items:center; justify-content:center; z-index:999; flex-direction:column; gap:16px; }
  .loading-overlay.show { display:flex; }
  .spinner-ring { width:44px; height:44px; border:3px solid rgba(255,255,255,0.1); border-top-color:var(--accent); border-radius:50%; animation:spin 0.8s linear infinite; }
  @keyframes spin{to{transform:rotate(360deg);}}
</style>
</head>
<body>

<div class="page-scroll">
  <header>
    <div class="logo-text">Doc<em>YouMen</em></div>
    <div class="status-badge">📱 Mobile Sign</div>
  </header>

  <div class="main" id="mainSection">
    <div class="instruction">
      <h2>✍️ Tanda Tangan</h2>
      <p>Gambar tanda tangan di area bawah, lalu tekan Kirim</p>
    </div>

    <div class="canvas-wrap" id="canvasWrap">
      <canvas id="mobileCanvas"></canvas>
      <div class="canvas-hint" id="canvasHint"><span>Tanda tangan di sini...</span></div>
    </div>

    <div class="toolbar">
      <div class="color-chips">
        <div class="color-chip active" style="background:#1a1a2e" data-color="#1a1a2e" onclick="setColor(this)"></div>
        <div class="color-chip" style="background:#1e40af" data-color="#1e40af" onclick="setColor(this)"></div>
        <div class="color-chip" style="background:#dc2626" data-color="#dc2626" onclick="setColor(this)"></div>
        <div class="color-chip" style="background:#065f46" data-color="#065f46" onclick="setColor(this)"></div>
      </div>
      <div class="thickness-btns">
        <button class="thick-btn"        onclick="setThick(1,this)">S</button>
        <button class="thick-btn active" onclick="setThick(2,this)">M</button>
        <button class="thick-btn"        onclick="setThick(4,this)">L</button>
      </div>
      <button class="btn-clear-mob" onclick="clearCanvas()">🗑 Hapus</button>
    </div>

    <div class="submit-wrap">
      <button class="btn-submit" id="submitBtn" onclick="submitSignature()">
        ✅ Kirim Tanda Tangan
      </button>
    </div>
  </div><!-- /main -->

  <div class="success-screen" id="successScreen">
    <div class="success-icon">✅</div>
    <h2>Berhasil!</h2>
    <p>Tanda tangan dikirim.<br>Kembali ke komputer Anda.</p>
  </div>
</div><!-- /page-scroll -->

<div class="loading-overlay" id="loadingOverlay">
  <div class="spinner-ring"></div>
  <span style="font-size:13px;color:var(--muted);">Mengirim...</span>
</div>

<script>
const sessionId = new URLSearchParams(window.location.search).get('session');
let isDrawing=false, lx=0, ly=0, hasDrawn=false;
let curColor='#1a1a2e', curThick=2;

const canvas = document.getElementById('mobileCanvas');
const ctx    = canvas.getContext('2d');
const wrap   = document.getElementById('canvasWrap');
const hint   = document.getElementById('canvasHint');

function resizeCanvas() {
  const rect = wrap.getBoundingClientRect();
  const w = Math.floor(rect.width), h = Math.max(200, Math.floor(rect.height));
  canvas.width = w; canvas.height = h;
  // TIDAK fill putih — biarkan transparan
  ctx.clearRect(0, 0, w, h);
}
window.addEventListener('resize', resizeCanvas);
// Tunggu layout selesai
requestAnimationFrame(() => { resizeCanvas(); });

function getPos(e) {
  const r = canvas.getBoundingClientRect();
  const t = e.touches ? e.touches[0] : e;
  return {
    x: (t.clientX - r.left) * (canvas.width  / r.width),
    y: (t.clientY - r.top)  * (canvas.height / r.height),
  };
}

canvas.addEventListener('mousedown', e => {
  e.preventDefault(); isDrawing=true;
  const p=getPos(e); lx=p.x; ly=p.y;
  ctx.beginPath(); ctx.moveTo(lx,ly);
  if(!hasDrawn){hint.style.opacity='0';hasDrawn=true;}
});
canvas.addEventListener('mousemove', e => {
  if(!isDrawing)return; e.preventDefault();
  draw(getPos(e));
});
canvas.addEventListener('mouseup',    e=>{e.preventDefault();isDrawing=false;});
canvas.addEventListener('touchstart', e=>{
  e.preventDefault(); isDrawing=true;
  const p=getPos(e); lx=p.x; ly=p.y;
  ctx.beginPath(); ctx.moveTo(lx,ly);
  if(!hasDrawn){hint.style.opacity='0';hasDrawn=true;}
},{passive:false});
canvas.addEventListener('touchmove', e=>{
  if(!isDrawing)return; e.preventDefault();
  draw(getPos(e));
},{passive:false});
canvas.addEventListener('touchend',  e=>{e.preventDefault();isDrawing=false;},{passive:false});

function draw(p) {
  ctx.strokeStyle = curColor;
  ctx.lineWidth   = curThick;
  ctx.lineCap='round'; ctx.lineJoin='round';
  ctx.lineTo(p.x,p.y); ctx.stroke();
  lx=p.x; ly=p.y;
}

function setColor(el) {
  document.querySelectorAll('.color-chip').forEach(c=>c.classList.remove('active'));
  el.classList.add('active'); curColor=el.dataset.color;
}
function setThick(v,el) {
  document.querySelectorAll('.thick-btn').forEach(b=>b.classList.remove('active'));
  el.classList.add('active'); curThick=v;
}
function clearCanvas() {
  ctx.clearRect(0,0,canvas.width,canvas.height);
  hasDrawn=false; hint.style.opacity='0.35';
}

async function submitSignature() {
  if (!hasDrawn) { alert('Gambar tanda tangan dulu!'); return; }
  if (!sessionId) { alert('Session tidak valid! Scan ulang QR code.'); return; }

  document.getElementById('loadingOverlay').classList.add('show');
  document.getElementById('submitBtn').disabled = true;

  // Export sebagai PNG transparan (tanpa background putih)
  const signatureData = canvas.toDataURL('image/png');

  const base = window.location.href.replace(/\/[^\/]*(\?.*)?$/, '');
  const apiUrl = base + '/api/mobile_signature.php';

  try {
    const res = await fetch(apiUrl, {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ session: sessionId, signature: signatureData })
    });
    if (!res.ok) throw new Error('HTTP '+res.status);
    const data = await res.json();
    document.getElementById('loadingOverlay').classList.remove('show');
    if (data.success) {
      document.getElementById('mainSection').classList.add('hide');
      document.getElementById('successScreen').classList.add('show');
    } else {
      alert('Gagal: '+(data.error||'Unknown'));
      document.getElementById('submitBtn').disabled=false;
    }
  } catch(err) {
    document.getElementById('loadingOverlay').classList.remove('show');
    alert('Error: '+err.message+'\nURL: '+apiUrl);
    document.getElementById('submitBtn').disabled=false;
  }
}
</script>
</body>
</html>
