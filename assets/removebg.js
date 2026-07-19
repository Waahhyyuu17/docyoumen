/* ═══════════════════════════════════════════════
   DocYouMen — Hapus Background
   Auto (warna latar / AI orang via MediaPipe Selfie
   Segmentation, lazy-loaded) + kuas manual hapus/kembalikan.
   Hasil selalu PNG transparan, diproses 100% di browser.
═══════════════════════════════════════════════ */
'use strict';

const REMOVEBG_MAX_DIM = 1500;
const MEDIAPIPE_SELFIE_URL = 'https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation/selfie_segmentation.js';

const removeBgState = {
  file: null,
  originalCanvas: null, // pristine backup, tidak pernah digambar ulang
  workCanvas: null,     // canvas yang tampil & diedit
  ctx: null,
  brushMode: 'erase',
  brushSize: 30,
  history: [],
  drawing: false,
  lastX: 0, lastY: 0,
  segmenter: null,
};

onDomReady(setupRemoveBgTool);

function setupRemoveBgTool() {
  const dropzone = document.getElementById('removeBgDropzone');
  const input = document.getElementById('removeBgFileInput');
  if (!dropzone || !input) return; // halaman lain / overlay belum ada

  setupGenericUpload({
    dropzone, input, onFiles: files => {
      const f = files[0];
      if (!f || !f.type.startsWith('image/')) { showToast('File harus gambar!', 'error'); return; }
      loadRemoveBgImage(f);
    }
  });

  document.getElementById('removeBgMode').addEventListener('change', e => {
    document.getElementById('removeBgToleranceWrap').style.display = e.target.value === 'color' ? 'block' : 'none';
  });
  document.getElementById('removeBgTolerance').addEventListener('input', e => {
    document.getElementById('removeBgToleranceVal').textContent = e.target.value;
  });
  document.getElementById('removeBgAutoBtn').addEventListener('click', runRemoveBgAuto);

  document.querySelectorAll('#removeBgBrushMode .style-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('#removeBgBrushMode .style-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      removeBgState.brushMode = btn.dataset.brushmode;
    });
  });
  document.getElementById('removeBgBrushSize').addEventListener('input', e => {
    removeBgState.brushSize = parseInt(e.target.value) || 30;
    document.getElementById('removeBgBrushSizeVal').textContent = e.target.value + 'px';
  });

  document.getElementById('removeBgUndoBtn').disabled = true;
  document.getElementById('removeBgUndoBtn').addEventListener('click', removeBgUndo);
  document.getElementById('removeBgResetBtn').addEventListener('click', removeBgReset);
  document.getElementById('removeBgDownloadBtn').addEventListener('click', downloadRemoveBgPng);

  setupRemoveBgCanvas();
}

// ─── LOAD IMAGE ────────────────────────────────
function loadRemoveBgImage(file) {
  removeBgState.file = file;
  const reader = new FileReader();
  reader.onload = e => {
    const img = new Image();
    img.onload = () => {
      let w = img.width, h = img.height;
      if (Math.max(w, h) > REMOVEBG_MAX_DIM) {
        const scale = REMOVEBG_MAX_DIM / Math.max(w, h);
        w = Math.round(w * scale); h = Math.round(h * scale);
      }
      const orig = document.createElement('canvas');
      orig.width = w; orig.height = h;
      orig.getContext('2d').drawImage(img, 0, 0, w, h);
      removeBgState.originalCanvas = orig;

      const work = document.getElementById('removeBgCanvas');
      work.width = w; work.height = h;
      const ctx = work.getContext('2d');
      ctx.drawImage(orig, 0, 0);
      removeBgState.workCanvas = work;
      removeBgState.ctx = ctx;
      removeBgState.history = [];
      document.getElementById('removeBgUndoBtn').disabled = true;

      document.getElementById('removeBgUploadStep').style.display = 'none';
      document.getElementById('removeBgWorkStep').style.display = 'block';
      document.getElementById('removeBgFileInfo').style.display = 'flex';
      document.getElementById('removeBgFileName').textContent = file.name;
      document.getElementById('removeBgEmptyHint').style.display = 'none';
      document.getElementById('removeBgCanvasWrap').style.display = 'inline-block';
      document.getElementById('removeBgStatus').textContent = w + '×' + h + ' px';
    };
    img.onerror = () => showToast('Gagal membaca gambar', 'error');
    img.src = e.target.result;
  };
  reader.readAsDataURL(file);
}

// ─── AUTO REMOVE ───────────────────────────────
async function runRemoveBgAuto() {
  if (!removeBgState.originalCanvas) return;
  const mode = document.getElementById('removeBgMode').value;
  pushRemoveBgHistory();

  if (mode === 'color') {
    runRemoveBgColor();
    return;
  }

  showBusy('Mendeteksi orang di foto (AI)...');
  try {
    const mask = await detectSelfieMask(removeBgState.originalCanvas);
    applyRemoveBgMask(mask);
    showToast('Background berhasil dihapus otomatis (AI)', 'success');
  } catch (err) {
    showToast('Deteksi AI gagal: ' + err.message + '. Coba mode "Tanda Tangan / Latar Polos" atau kuas manual.', 'error');
  } finally {
    hideBusy();
  }
}

function runRemoveBgColor() {
  const { originalCanvas, ctx } = removeBgState;
  const w = originalCanvas.width, h = originalCanvas.height;
  const origCtx = originalCanvas.getContext('2d');
  const data = origCtx.getImageData(0, 0, w, h);
  const px = data.data;

  // Sample warna latar dari 4 pojok gambar
  const sampleSize = Math.max(2, Math.min(8, Math.floor(Math.min(w, h) / 20)));
  const corners = [[0, 0], [w - sampleSize, 0], [0, h - sampleSize], [w - sampleSize, h - sampleSize]];
  let sr = 0, sg = 0, sb = 0, sn = 0;
  corners.forEach(([cx, cy]) => {
    for (let y = cy; y < cy + sampleSize; y++) {
      for (let x = cx; x < cx + sampleSize; x++) {
        const i = (y * w + x) * 4;
        sr += px[i]; sg += px[i + 1]; sb += px[i + 2]; sn++;
      }
    }
  });
  const avgR = sr / sn, avgG = sg / sn, avgB = sb / sn;

  const tolerance = parseInt(document.getElementById('removeBgTolerance').value) || 35;
  const maxDist = tolerance * 4.41; // 0-100 -> 0-441 (jarak RGB maksimum)
  const featherBand = Math.max(10, maxDist * 0.4);

  for (let i = 0; i < px.length; i += 4) {
    const dr = px[i] - avgR, dg = px[i + 1] - avgG, db = px[i + 2] - avgB;
    const dist = Math.sqrt(dr * dr + dg * dg + db * db);
    let alpha = 255;
    if (dist < maxDist - featherBand) alpha = 0;
    else if (dist < maxDist) alpha = Math.round(255 * (dist - (maxDist - featherBand)) / featherBand);
    px[i + 3] = alpha;
  }
  removeBgState.ctx.putImageData(data, 0, 0);
  showToast('Background berhasil dihapus otomatis', 'success');
}

// ─── AI: MEDIAPIPE SELFIE SEGMENTATION (lazy-load) ──
async function ensureSelfieSegmentation() {
  if (removeBgState.segmenter) return removeBgState.segmenter;
  await loadScriptOnce(MEDIAPIPE_SELFIE_URL);
  const seg = new SelfieSegmentation({
    locateFile: file => `https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation/${file}`,
  });
  seg.setOptions({ modelSelection: 1 });
  removeBgState.segmenter = seg;
  return seg;
}

function detectSelfieMask(sourceCanvas) {
  return ensureSelfieSegmentation().then(seg => new Promise((resolve, reject) => {
    const timer = setTimeout(() => reject(new Error('Waktu deteksi habis')), 25000);
    seg.onResults(results => { clearTimeout(timer); resolve(results.segmentationMask); });
    seg.send({ image: sourceCanvas }).catch(err => { clearTimeout(timer); reject(err); });
  }));
}

function applyRemoveBgMask(maskSource) {
  const { originalCanvas, ctx } = removeBgState;
  const w = originalCanvas.width, h = originalCanvas.height;

  const maskCanvas = document.createElement('canvas');
  maskCanvas.width = w; maskCanvas.height = h;
  const mctx = maskCanvas.getContext('2d');
  mctx.drawImage(maskSource, 0, 0, w, h);
  const maskPx = mctx.getImageData(0, 0, w, h).data;

  const origCtx = originalCanvas.getContext('2d');
  const imgData = origCtx.getImageData(0, 0, w, h);
  const px = imgData.data;
  for (let i = 0; i < px.length; i += 4) {
    px[i + 3] = maskPx[i]; // channel merah mask = tingkat "foreground"
  }
  ctx.putImageData(imgData, 0, 0);
}

// ─── KUAS MANUAL ───────────────────────────────
function paintDab(x, y) {
  const { ctx, brushSize, brushMode, originalCanvas } = removeBgState;
  const r = brushSize / 2;
  ctx.save();
  ctx.beginPath();
  ctx.arc(x, y, r, 0, Math.PI * 2);
  if (brushMode === 'erase') {
    ctx.globalCompositeOperation = 'destination-out';
    ctx.fill();
  } else {
    ctx.clip();
    ctx.drawImage(originalCanvas, 0, 0);
  }
  ctx.restore();
}
function paintStroke(x0, y0, x1, y1) {
  const dist = Math.hypot(x1 - x0, y1 - y0);
  const step = Math.max(1, removeBgState.brushSize / 4);
  const n = Math.max(1, Math.ceil(dist / step));
  for (let i = 0; i <= n; i++) {
    const t = i / n;
    paintDab(x0 + (x1 - x0) * t, y0 + (y1 - y0) * t);
  }
}

function setupRemoveBgCanvas() {
  const canvas = document.getElementById('removeBgCanvas');
  const cursor = document.getElementById('removeBgBrushCursor');

  function getPos(e) {
    const r = canvas.getBoundingClientRect();
    const t = e.touches ? e.touches[0] : e;
    return {
      x: (t.clientX - r.left) * (canvas.width / r.width),
      y: (t.clientY - r.top) * (canvas.height / r.height),
    };
  }
  function updateCursor(e) {
    const r = canvas.getBoundingClientRect();
    const t = e.touches ? e.touches[0] : e;
    const cssX = t.clientX - r.left, cssY = t.clientY - r.top;
    const scale = r.width / canvas.width;
    const size = removeBgState.brushSize * scale;
    cursor.style.width = size + 'px';
    cursor.style.height = size + 'px';
    cursor.style.left = cssX + 'px';
    cursor.style.top = cssY + 'px';
  }

  function start(e) {
    if (!removeBgState.workCanvas) return;
    e.preventDefault();
    pushRemoveBgHistory();
    removeBgState.drawing = true;
    const p = getPos(e);
    removeBgState.lastX = p.x; removeBgState.lastY = p.y;
    paintDab(p.x, p.y);
    updateCursor(e);
  }
  function move(e) {
    if (!removeBgState.workCanvas) return;
    updateCursor(e);
    if (!removeBgState.drawing) return;
    e.preventDefault();
    const p = getPos(e);
    paintStroke(removeBgState.lastX, removeBgState.lastY, p.x, p.y);
    removeBgState.lastX = p.x; removeBgState.lastY = p.y;
  }
  function stop() { removeBgState.drawing = false; }

  canvas.addEventListener('mousedown', start);
  canvas.addEventListener('mousemove', move);
  canvas.addEventListener('mouseup', stop);
  canvas.addEventListener('mouseenter', () => { cursor.style.display = 'block'; });
  canvas.addEventListener('mouseleave', () => { cursor.style.display = 'none'; stop(); });
  canvas.addEventListener('touchstart', start, { passive: false });
  canvas.addEventListener('touchmove', move, { passive: false });
  canvas.addEventListener('touchend', stop);
}

// ─── HISTORY / RESET / DOWNLOAD ────────────────
function pushRemoveBgHistory() {
  const { ctx, workCanvas } = removeBgState;
  if (!ctx) return;
  removeBgState.history.push(ctx.getImageData(0, 0, workCanvas.width, workCanvas.height));
  if (removeBgState.history.length > 15) removeBgState.history.shift();
  document.getElementById('removeBgUndoBtn').disabled = false;
}
function removeBgUndo() {
  const { ctx, history } = removeBgState;
  if (!history.length) return;
  const prev = history.pop();
  ctx.putImageData(prev, 0, 0);
  if (!history.length) document.getElementById('removeBgUndoBtn').disabled = true;
}
function removeBgReset() {
  if (!removeBgState.originalCanvas) return;
  pushRemoveBgHistory();
  const { ctx, workCanvas, originalCanvas } = removeBgState;
  ctx.clearRect(0, 0, workCanvas.width, workCanvas.height);
  ctx.drawImage(originalCanvas, 0, 0);
  showToast('Direset ke gambar asli', 'info');
}
function downloadRemoveBgPng() {
  if (!removeBgState.workCanvas) { showToast('Upload gambar dulu!', 'error'); return; }
  removeBgState.workCanvas.toBlob(blob => {
    if (!blob) { showToast('Gagal membuat PNG', 'error'); return; }
    const base = (removeBgState.file && removeBgState.file.name || 'image').replace(/\.[^.]+$/, '');
    downloadBlob(blob, base + '_no_bg.png');
    showToast('✅ PNG berhasil diunduh', 'success');
  }, 'image/png');
}
