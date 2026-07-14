/* ═══════════════════════════════════════════════
   DocYouMen — Editor JavaScript v3
═══════════════════════════════════════════════ */
'use strict';

// ─── STATE ────────────────────────────────────
const state = {
  pdfDoc: null,
  currentPage: 1,
  totalPages: 0,
  scale: 1.5,
  pdfFile: null,
  pdfFileName: 'document.pdf',
  elements: [],
  selectedElement: null,
  nextId: 1,
  addMode: null,
  pendingData: null,
  mobileSession: null,
  mobilePolling: null,
  textStyle: {
    content: 'Teks Contoh',
    fontFamily: 'Arial',
    fontSize: 16,
    color: '#1a1a2e',
    bold: false,
    italic: false,
    underline: false,
    opacity: 1,
  },
  stampData: null,
  uploadedSigData: null,
  history: [],
};

// ─── DOM ──────────────────────────────────────
const dom = {
  get pdfFileInput()    { return document.getElementById('pdfFileInput'); },
  get editorOverlay()   { return document.getElementById('editorOverlay'); },
  get pdfCanvas()       { return document.getElementById('pdfCanvas'); },
  get overlay()         { return document.getElementById('elementsOverlay'); },
  get pdfContainer()    { return document.getElementById('pdfContainer'); },
  get currentPageEl()   { return document.getElementById('currentPage'); },
  get totalPagesEl()    { return document.getElementById('totalPages'); },
  get zoomVal()         { return document.getElementById('zoomVal'); },
  get elementsList()    { return document.getElementById('elementsList'); },
  get elementsCount()   { return document.getElementById('elementsCount'); },
  get modeIndicator()   { return document.getElementById('modeIndicator'); },
  get modeText()        { return document.getElementById('modeIndicatorText'); },
  get cursorInd()       { return document.getElementById('cursorIndicator'); },
  get toolsFileName()   { return document.getElementById('toolsFileName'); },
  get prevPageBtn()     { return document.getElementById('prevPageBtn'); },
  get nextPageBtn()     { return document.getElementById('nextPageBtn'); },
  get undoBtn()         { return document.getElementById('undoBtn'); },
  get uploadProgress()  { return document.getElementById('uploadProgress'); },
  get uploadProgressBar(){ return document.getElementById('uploadProgressBar'); },
};

// ─── INIT ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  setupUploadZone();
  setupToolTabs();
  setupSigTabs();
  setupTextControls();
  setupSigCanvas();
  setupSigUpload();
  setupStampUpload();
  setupRanges();
  setupColorPicker();
  setupStyleButtons();
  setupOverlayClick();
  setupClearAll();
});

// ─── UPLOAD ───────────────────────────────────
function setupUploadZone() {
  const zone = document.getElementById('uploadZone');
  zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('drag-over'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
  zone.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('drag-over');
    const f = e.dataTransfer.files[0];
    if (f && f.type === 'application/pdf') loadPDFFile(f);
    else showToast('File harus PDF!', 'error');
  });
  dom.pdfFileInput.addEventListener('change', e => { if (e.target.files[0]) loadPDFFile(e.target.files[0]); });
  document.getElementById('backBtn').addEventListener('click', () => {
    if (!state.pdfDoc || confirm('Kembali? Perubahan belum disimpan akan hilang.')) {
      dom.editorOverlay.style.display = 'none';
      document.body.style.overflow = '';
      document.getElementById('signUploadStep').style.display = 'block';
      document.getElementById('signWorkStep').style.display = 'none';
      resetState();
    }
  });
}

async function loadPDFFile(file) {
  state.pdfFile = file; state.pdfFileName = file.name;
  dom.toolsFileName.textContent = file.name;
  dom.uploadProgress.style.display = 'block';
  let p = 0;
  const iv = setInterval(() => { p = Math.min(p+12, 85); dom.uploadProgressBar.style.width = p+'%'; }, 80);
  try {
    const fd = new FormData(); fd.append('pdf', file);
    const r = await fetch('api/upload_pdf.php', { method:'POST', body:fd });
    const d = await r.json();
    clearInterval(iv); dom.uploadProgressBar.style.width = '100%';
    if (!d.success) throw new Error(d.error);
    await sleep(200);
    dom.uploadProgress.style.display = 'none';
    await loadPDFViewer(d.url);
  } catch(err) {
    clearInterval(iv); dom.uploadProgress.style.display = 'none';
    try { await loadPDFViewer(URL.createObjectURL(file)); }
    catch(e2) { showToast('Gagal: '+err.message, 'error'); }
  }
}

async function loadPDFViewer(url) {
  state.pdfDoc = await pdfjsLib.getDocument(url).promise;
  state.totalPages = state.pdfDoc.numPages;
  state.currentPage = 1; state.elements = [];
  dom.totalPagesEl.textContent = state.totalPages;
  dom.prevPageBtn.disabled = true;
  dom.nextPageBtn.disabled = state.totalPages <= 1;
  await renderPage(1);
  dom.editorOverlay.style.display = 'block';
  document.body.style.overflow = 'hidden';
  document.getElementById('signUploadStep').style.display = 'none';
  document.getElementById('signWorkStep').style.display = 'flex';
  showToast('PDF dimuat — '+state.totalPages+' halaman', 'success');
}

async function renderPage(n) {
  if (!state.pdfDoc) return;
  const page = await state.pdfDoc.getPage(n);
  const vp = page.getViewport({ scale: state.scale });
  dom.pdfCanvas.width = vp.width; dom.pdfCanvas.height = vp.height;
  await page.render({ canvasContext: dom.pdfCanvas.getContext('2d'), viewport: vp }).promise;
  dom.currentPageEl.textContent = n;
  dom.prevPageBtn.disabled = n <= 1;
  dom.nextPageBtn.disabled = n >= state.totalPages;
  renderElements();
}

function changePage(d) {
  const n = state.currentPage + d;
  if (n < 1 || n > state.totalPages) return;
  state.currentPage = n; renderPage(n);
}

// ─── ZOOM ─────────────────────────────────────
function zoomIn()    { state.scale = Math.min(state.scale+0.25,4);   applyZoom(); }
function zoomOut()   { state.scale = Math.max(state.scale-0.25,0.5); applyZoom(); }
function resetZoom() { state.scale = 1.5; applyZoom(); }
function applyZoom() { dom.zoomVal.textContent = Math.round(state.scale/1.5*100)+'%'; renderPage(state.currentPage); }

// ─── TABS ──────────────────────────────────────
// Semua query di sini di-scope ke #editorOverlay — beberapa class (.tool-panel,
// .sig-tab) dipakai ulang di overlay tool lain (Split/Merge/dll) hanya untuk
// styling, dan tidak boleh terpengaruh oleh tab-switching di tool Tanda Tangan ini.
function setupToolTabs() {
  dom.editorOverlay.querySelectorAll('.tool-tab').forEach(btn => btn.addEventListener('click', () => {
    dom.editorOverlay.querySelectorAll('.tool-tab').forEach(b => b.classList.remove('active'));
    dom.editorOverlay.querySelectorAll('.tool-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('panel'+cap(btn.dataset.tool)).classList.add('active');
    cancelAddMode();
  }));
}
function setupSigTabs() {
  dom.editorOverlay.querySelectorAll('.sig-tab').forEach(btn => btn.addEventListener('click', () => {
    dom.editorOverlay.querySelectorAll('.sig-tab').forEach(b => b.classList.remove('active'));
    dom.editorOverlay.querySelectorAll('.sig-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('sig'+cap(btn.dataset.sigtab)).classList.add('active');
    if (btn.dataset.sigtab === 'mobile') generateMobileQR();
  }));
}

// ─── TEXT CONTROLS ────────────────────────────
function setupTextControls() {
  document.getElementById('textContent').addEventListener('input',  e => state.textStyle.content    = e.target.value);
  document.getElementById('fontFamily').addEventListener('change',  e => state.textStyle.fontFamily = e.target.value);
  document.getElementById('fontSize').addEventListener('input',     e => state.textStyle.fontSize   = parseInt(e.target.value)||16);
  document.getElementById('addTextBtn').addEventListener('click', () => {
    if (!document.getElementById('textContent').value.trim()) { showToast('Ketik teks dulu!','error'); return; }
    startAddMode('text');
  });
}
function adjustFontSize(d) {
  const inp = document.getElementById('fontSize');
  inp.value = Math.max(6, Math.min(120,(parseInt(inp.value)||16)+d));
  state.textStyle.fontSize = parseInt(inp.value);
}
function setupColorPicker() {
  const pk = document.getElementById('fontColor'), pv = document.getElementById('colorPreview');
  pk.addEventListener('input', e => { state.textStyle.color = e.target.value; pv.style.background = e.target.value; });
  pv.style.background = pk.value;
}
function setupStyleButtons() {
  document.querySelectorAll('.style-btn').forEach(btn => btn.addEventListener('click', () => {
    const s = btn.dataset.style; state.textStyle[s] = !state.textStyle[s];
    btn.classList.toggle('active', state.textStyle[s]);
  }));
}

// ─── SIGNATURE CANVAS (mouse draw) ────────────
function setupSigCanvas() {
  const canvas = document.getElementById('sigCanvas');
  const ctx = canvas.getContext('2d');
  const hint = document.querySelector('.sig-canvas-hint');

  function resize() {
    const w = canvas.parentElement.clientWidth || 220;
    canvas.width  = w;
    canvas.height = Math.round(w * 0.5);
    // Transparent background (tidak putih)
    ctx.clearRect(0, 0, canvas.width, canvas.height);
  }
  resize();
  window.addEventListener('resize', resize);

  let drawing = false, lx = 0, ly = 0;

  function getPos(e) {
    const r = canvas.getBoundingClientRect();
    const t = e.touches ? e.touches[0] : e;
    return {
      x: (t.clientX - r.left) * (canvas.width  / r.width),
      y: (t.clientY - r.top)  * (canvas.height / r.height),
    };
  }

  function startDraw(e) {
    e.preventDefault();
    drawing = true;
    const p = getPos(e); lx = p.x; ly = p.y;
    ctx.beginPath(); ctx.moveTo(lx, ly);
    hint.style.opacity = '0';
  }
  function moveDraw(e) {
    if (!drawing) return; e.preventDefault();
    const p = getPos(e);
    ctx.strokeStyle = document.getElementById('sigColor').value || '#1a1a2e';
    ctx.lineWidth   = parseInt(document.getElementById('sigThickness').value) || 2;
    ctx.lineCap     = 'round'; ctx.lineJoin = 'round';
    ctx.lineTo(p.x, p.y); ctx.stroke();
    lx = p.x; ly = p.y;
  }
  function stopDraw() { drawing = false; }

  canvas.addEventListener('mousedown',  startDraw);
  canvas.addEventListener('mousemove',  moveDraw);
  canvas.addEventListener('mouseup',    stopDraw);
  canvas.addEventListener('mouseleave', stopDraw);
  canvas.addEventListener('touchstart', startDraw, { passive:false });
  canvas.addEventListener('touchmove',  moveDraw,  { passive:false });
  canvas.addEventListener('touchend',   stopDraw);
}

function clearSignatureCanvas() {
  const c = document.getElementById('sigCanvas');
  c.getContext('2d').clearRect(0, 0, c.width, c.height);
  document.querySelector('.sig-canvas-hint').style.opacity = '0.3';
}

function useDrawnSignature() {
  const c = document.getElementById('sigCanvas');
  const ctx = c.getContext('2d');

  // Cek apakah ada yang digambar
  const data = ctx.getImageData(0, 0, c.width, c.height).data;
  let hasPixel = false;
  for (let i = 3; i < data.length; i += 4) {
    if (data[i] > 10) { hasPixel = true; break; }
  }
  if (!hasPixel) { showToast('Gambar tanda tangan dulu!', 'error'); return; }

  // Crop area yang ada tanda tangannya saja
  const trimmed = trimCanvas(c);
  state.pendingData = { type:'signature', src: trimmed.dataUrl, width: trimmed.w * 1.2, height: trimmed.h * 1.2 };
  startAddMode('signature');
}

// Potong canvas sesuai batas pixel yang ada
function trimCanvas(c) {
  const ctx = c.getContext('2d');
  const { width, height } = c;
  const data = ctx.getImageData(0, 0, width, height);
  const px = data.data;
  let minX = width, minY = height, maxX = 0, maxY = 0;
  for (let y = 0; y < height; y++) {
    for (let x = 0; x < width; x++) {
      const a = px[(y * width + x) * 4 + 3];
      if (a > 10) {
        if (x < minX) minX = x; if (x > maxX) maxX = x;
        if (y < minY) minY = y; if (y > maxY) maxY = y;
      }
    }
  }
  const pad = 10;
  minX = Math.max(0, minX-pad); minY = Math.max(0, minY-pad);
  maxX = Math.min(width-1, maxX+pad); maxY = Math.min(height-1, maxY+pad);
  const w = maxX - minX + 1, h = maxY - minY + 1;
  const tmp = document.createElement('canvas');
  tmp.width = w; tmp.height = h;
  tmp.getContext('2d').putImageData(ctx.getImageData(minX, minY, w, h), 0, 0);
  return { dataUrl: tmp.toDataURL('image/png'), w, h };
}

// ─── SIGNATURE UPLOAD ─────────────────────────
function setupSigUpload() {
  document.getElementById('sigImageInput').addEventListener('change', e => {
    const f = e.target.files[0];
    if (!f || !f.type.startsWith('image/')) { showToast('File harus gambar!','error'); return; }
    const r = new FileReader();
    r.onload = ev => {
      state.uploadedSigData = ev.target.result;
      document.getElementById('sigUploadedImg').src = ev.target.result;
      document.getElementById('sigUploadedPreviewWrap').style.display = 'block';
      document.getElementById('useUploadedSigBtn').style.display = 'flex';
      document.getElementById('sigUploadPreview').innerHTML =
        `<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span style="color:#10b981">Gambar dipilih</span>`;
    };
    r.readAsDataURL(f);
  });
}

function useUploadedSignature() {
  if (!state.uploadedSigData) { showToast('Upload gambar dulu!','error'); return; }
  const img = new Image();
  img.onload = () => {
    const w = 200, h = Math.round(w * (img.height / img.width));
    state.pendingData = { type:'signature', src: state.uploadedSigData, width:w, height:h };
    startAddMode('signature');
  };
  img.src = state.uploadedSigData;
}

// ─── STAMP ────────────────────────────────────
function setupStampUpload() {
  document.getElementById('stampImageInput').addEventListener('change', e => {
    const f = e.target.files[0];
    if (!f || !f.type.startsWith('image/')) { showToast('File harus gambar!','error'); return; }
    const r = new FileReader();
    r.onload = ev => {
      state.stampData = ev.target.result;
      document.getElementById('stampPreviewImg').src = ev.target.result;
      document.getElementById('stampPreviewWrap').style.display = 'block';
      document.getElementById('stampHint').style.display = 'none';
      document.getElementById('useStampBtn').style.display = 'flex';
      document.getElementById('stampUploadPreview').innerHTML =
        `<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span style="color:#10b981">Stempel dipilih</span>`;
    };
    r.readAsDataURL(f);
  });
}
function useStamp() {
  if (!state.stampData) { showToast('Upload stempel dulu!','error'); return; }
  const size = parseInt(document.getElementById('stampSize').value)||150;
  const opacity = parseInt(document.getElementById('stampOpacity').value)/100;
  state.pendingData = { type:'stamp', src:state.stampData, width:size, height:size, opacity };
  startAddMode('stamp');
}

// ─── RANGES ───────────────────────────────────
function setupRanges() {
  [
    ['textOpacity',  'textOpacityVal',  v => state.textStyle.opacity = v/100, '%'],
    ['stampOpacity', 'stampOpacityVal', () => {}, '%'],
    ['stampSize',    'stampSizeVal',    () => {}, 'px'],
  ].forEach(([id, vid, cb, unit]) => {
    const el = document.getElementById(id); if (!el) return;
    el.addEventListener('input', e => { document.getElementById(vid).textContent = e.target.value+unit; cb(+e.target.value); });
  });
}

// ─── ADD MODE ─────────────────────────────────
function startAddMode(type) {
  if (!state.pdfDoc) { showToast('Upload PDF dulu!','error'); return; }
  state.addMode = type;
  const labels = { text:'Tambah Teks', signature:'Tambah Tanda Tangan', stamp:'Tambah Stempel' };
  dom.modeIndicator.style.display = 'flex';
  dom.modeText.textContent = labels[type]+' — Klik di PDF untuk menempatkan';
  dom.pdfContainer.style.cursor = 'crosshair';
  document.getElementById('addTextBtn').classList.toggle('active', type==='text');
  showToast('Klik pada PDF untuk menempatkan', 'info');
}

function cancelAddMode() {
  state.addMode = null; state.pendingData = null;
  dom.modeIndicator.style.display = 'none';
  dom.pdfContainer.style.cursor = 'default';
  dom.cursorInd.style.display = 'none';
  const b = document.getElementById('addTextBtn'); if (b) b.classList.remove('active');
}

// ─── PDF CLICK ────────────────────────────────
dom.pdfContainer.addEventListener('click', e => {
  if (!state.addMode || e.target.closest('.pdf-element')) return;
  const r = dom.pdfContainer.getBoundingClientRect();
  placeElement(
    (e.clientX - r.left) / dom.pdfCanvas.width,
    (e.clientY - r.top)  / dom.pdfCanvas.height
  );
});
dom.pdfContainer.addEventListener('mousemove', e => {
  if (!state.addMode) { dom.cursorInd.style.display = 'none'; return; }
  const r = dom.pdfContainer.getBoundingClientRect();
  dom.cursorInd.style.display = 'block';
  dom.cursorInd.style.left = (e.clientX-r.left)+'px';
  dom.cursorInd.style.top  = (e.clientY-r.top)+'px';
});
dom.pdfContainer.addEventListener('mouseleave', () => dom.cursorInd.style.display='none');

// ─── PLACE ELEMENT ────────────────────────────
function placeElement(pctX, pctY) {
  if (!state.addMode) return;
  if ((state.addMode==='signature'||state.addMode==='stamp') && !state.pendingData) {
    showToast('Data tidak ada, coba lagi.','error'); cancelAddMode(); return;
  }
  const el = {
    id: state.nextId++, type: state.addMode,
    page: state.currentPage,
    pctX: Math.max(0,Math.min(1,pctX)),
    pctY: Math.max(0,Math.min(1,pctY)),
    rotation: 0,
  };
  if (state.addMode === 'text') {
    Object.assign(el, {
      content:    document.getElementById('textContent').value || 'Teks',
      fontFamily: state.textStyle.fontFamily,
      fontSize:   state.textStyle.fontSize,
      color:      state.textStyle.color,
      bold:       state.textStyle.bold,
      italic:     state.textStyle.italic,
      underline:  state.textStyle.underline,
      opacity:    state.textStyle.opacity,
    });
  } else {
    Object.assign(el, state.pendingData);
    el.type = state.addMode; el.id = el.id;
    el.page = state.currentPage;
    el.pctX = Math.max(0,Math.min(1,pctX));
    el.pctY = Math.max(0,Math.min(1,pctY));
    el.rotation = 0;
  }
  state.history.push({ action:'add', elementId: el.id });
  state.elements.push(el);
  renderElements(); updateElementsList(); cancelAddMode();
  dom.undoBtn.disabled = false;
  // Pilih element yang baru saja dibuat
  setTimeout(() => selectEl(el.id), 50);
}

// ─── RENDER ELEMENTS ──────────────────────────
function renderElements() {
  dom.overlay.innerHTML = '';
  state.elements.filter(el => el.page === state.currentPage)
    .forEach(el => dom.overlay.appendChild(buildElDOM(el)));
}

// ─── BUILD ELEMENT DOM ────────────────────────
function buildElDOM(el) {
  const div = document.createElement('div');
  div.className = 'pdf-element';
  div.dataset.id = el.id;

  const rot = el.rotation || 0;

  if (el.type === 'text') {
    let font = '';
    if (el.italic) font += 'italic ';
    if (el.bold)   font += 'bold ';
    font += el.fontSize+'px '+el.fontFamily;

    div.style.cssText = [
      'position:absolute',
      `left:${el.pctX*100}%`,
      `top:${el.pctY*100}%`,
      `transform:translate(-50%,-50%) rotate(${rot}deg)`,
      `font:${font}`,
      `color:${el.color}`,
      `opacity:${el.opacity}`,
      `text-decoration:${el.underline?'underline':'none'}`,
      'white-space:nowrap',       // ← tidak wrap
      'overflow:visible',
      'padding:2px 4px',
      'line-height:1.4',
      'pointer-events:all',
      'cursor:move',
      'z-index:10',
      'user-select:none',
      'display:inline-block',
    ].join(';');

    // Buat span yang bisa di-edit inline
    const span = document.createElement('span');
    span.className = 'text-content';
    span.textContent = el.content;
    span.style.cssText = 'outline:none;min-width:4px;display:inline-block;';

    // Double-click untuk edit inline
    span.addEventListener('dblclick', e => {
      e.stopPropagation();
      span.contentEditable = 'true';
      span.style.cursor = 'text';
      span.style.outline = '1px dashed rgba(99,102,241,0.6)';
      span.focus();
      // Select all
      const range = document.createRange(); range.selectNodeContents(span);
      const sel = window.getSelection(); sel.removeAllRanges(); sel.addRange(range);
    });
    span.addEventListener('blur', () => {
      span.contentEditable = 'false';
      span.style.cursor = '';
      span.style.outline = 'none';
      el.content = span.textContent;
    });
    span.addEventListener('keydown', e => {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); span.blur(); }
      e.stopPropagation(); // jangan trigger keyboard shortcut
    });
    span.addEventListener('mousedown', e => {
      if (span.contentEditable === 'true') e.stopPropagation();
    });

    div.appendChild(span);

  } else {
    // signature / stamp
    const w = el.width||200, h = el.height||100;
    div.style.cssText = [
      'position:absolute',
      `left:${el.pctX*100}%`,
      `top:${el.pctY*100}%`,
      `transform:translate(-50%,-50%) rotate(${rot}deg)`,
      `width:${w}px`,
      `height:${h}px`,
      'pointer-events:all',
      'cursor:move',
      'z-index:10',
      'user-select:none',
    ].join(';');

    const img = document.createElement('img');
    img.src = el.src;
    img.style.cssText = `width:100%;height:100%;object-fit:contain;opacity:${el.opacity!==undefined?el.opacity:1};pointer-events:none;display:block;`;
    div.appendChild(img);

    // Resize handles
    ['tl','tr','bl','br'].forEach(pos => {
      const h2 = document.createElement('div');
      h2.className = `resize-handle ${pos}`; h2.dataset.pos = pos;
      div.appendChild(h2);
      setupResize(div, h2, el);
    });

    // Rotate handle
    const rotHandle = document.createElement('div');
    rotHandle.className = 'rotate-handle';
    rotHandle.title = 'Putar';
    rotHandle.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
    div.appendChild(rotHandle);
    setupRotate(div, rotHandle, el);
  }

  // Controls bar (hover)
  const ctrl = document.createElement('div');
  ctrl.className = 'element-controls';

  if (el.type === 'signature' || el.type === 'stamp') {
    // Size display untuk signature/stamp
    ctrl.innerHTML = `
      <span style="font-size:10px;color:var(--muted);padding:0 4px;">${Math.round(el.width||200)}×${Math.round(el.height||100)}</span>
      <span style="font-size:10px;color:var(--muted);padding:0 4px;">${Math.round(el.rotation||0)}°</span>
      <button class="ec-btn" onclick="duplicateEl(${el.id})">Duplikat</button>
      <button class="ec-btn delete" onclick="deleteEl(${el.id})">Hapus</button>`;
  } else {
    ctrl.innerHTML = `
      <button class="ec-btn" onclick="makeTextEditable(${el.id})">✏️ Edit</button>
      <button class="ec-btn" onclick="duplicateEl(${el.id})">Duplikat</button>
      <button class="ec-btn delete" onclick="deleteEl(${el.id})">Hapus</button>`;
  }
  div.appendChild(ctrl);

  // Drag
  div.addEventListener('mousedown', e => {
    if (e.target.classList.contains('resize-handle') ||
        e.target.classList.contains('rotate-handle') ||
        e.target.closest('.rotate-handle') ||
        e.target.closest('.element-controls')) return;
    // Jika text sedang diedit, jangan drag
    const span = div.querySelector('.text-content');
    if (span && span.contentEditable === 'true') return;
    e.stopPropagation();
    selectEl(el.id);
    dragEl(div, el, e);
  });
  div.addEventListener('touchstart', e => {
    if (e.target.classList.contains('resize-handle') ||
        e.target.closest('.element-controls')) return;
    e.stopPropagation();
    selectEl(el.id);
    touchDragEl(div, el, e);
  }, { passive:false });

  if (state.selectedElement === el.id) div.classList.add('selected');
  return div;
}

// ─── MAKE TEXT EDITABLE ───────────────────────
function makeTextEditable(id) {
  const div = dom.overlay.querySelector(`[data-id="${id}"]`);
  if (!div) return;
  const span = div.querySelector('.text-content');
  if (!span) return;
  span.contentEditable = 'true';
  span.style.cursor = 'text';
  span.style.outline = '1px dashed rgba(99,102,241,0.6)';
  span.focus();
  const range = document.createRange(); range.selectNodeContents(span);
  const sel = window.getSelection(); sel.removeAllRanges(); sel.addRange(range);
}

// ─── DRAG ─────────────────────────────────────
function dragEl(div, el, e0) {
  const sx=e0.clientX, sy=e0.clientY, spx=el.pctX, spy=el.pctY;
  const onMove = e => {
    el.pctX = Math.max(0,Math.min(1, spx+(e.clientX-sx)/dom.pdfCanvas.width));
    el.pctY = Math.max(0,Math.min(1, spy+(e.clientY-sy)/dom.pdfCanvas.height));
    div.style.left = el.pctX*100+'%'; div.style.top = el.pctY*100+'%';
  };
  const onUp = () => { document.removeEventListener('mousemove',onMove); document.removeEventListener('mouseup',onUp); };
  document.addEventListener('mousemove',onMove); document.addEventListener('mouseup',onUp);
}
function touchDragEl(div, el, e0) {
  const t0=e0.touches[0], sx=t0.clientX, sy=t0.clientY, spx=el.pctX, spy=el.pctY;
  const onMove = e => {
    e.preventDefault();
    const t=e.touches[0];
    el.pctX = Math.max(0,Math.min(1, spx+(t.clientX-sx)/dom.pdfCanvas.width));
    el.pctY = Math.max(0,Math.min(1, spy+(t.clientY-sy)/dom.pdfCanvas.height));
    div.style.left = el.pctX*100+'%'; div.style.top = el.pctY*100+'%';
  };
  const onEnd = () => { document.removeEventListener('touchmove',onMove); document.removeEventListener('touchend',onEnd); };
  document.addEventListener('touchmove',onMove,{passive:false}); document.addEventListener('touchend',onEnd);
}

// ─── RESIZE ───────────────────────────────────
function setupResize(div, handle, el) {
  handle.addEventListener('mousedown', e => {
    e.stopPropagation(); e.preventDefault();
    const sx=e.clientX, sy=e.clientY, sw=el.width, sh=el.height;
    const pos = handle.dataset.pos;
    const onMove = e => {
      const dx=e.clientX-sx, dy=e.clientY-sy;
      if (pos==='br') { el.width=Math.max(30,sw+dx); el.height=Math.max(20,sh+dy); }
      if (pos==='bl') { el.width=Math.max(30,sw-dx); el.height=Math.max(20,sh+dy); }
      if (pos==='tr') { el.width=Math.max(30,sw+dx); el.height=Math.max(20,sh-dy); }
      if (pos==='tl') { el.width=Math.max(30,sw-dx); el.height=Math.max(20,sh-dy); }
      div.style.width=el.width+'px'; div.style.height=el.height+'px';
      // Update size display
      const ctrl = div.querySelector('.element-controls span');
      if (ctrl) ctrl.textContent = Math.round(el.width)+'×'+Math.round(el.height);
    };
    const onUp = () => { document.removeEventListener('mousemove',onMove); document.removeEventListener('mouseup',onUp); };
    document.addEventListener('mousemove',onMove); document.addEventListener('mouseup',onUp);
  });
}

// ─── ROTATE ───────────────────────────────────
function setupRotate(div, handle, el) {
  handle.addEventListener('mousedown', e => {
    e.stopPropagation(); e.preventDefault();
    const rect = div.getBoundingClientRect();
    const cx = rect.left + rect.width/2;
    const cy = rect.top  + rect.height/2;
    const startAngle = Math.atan2(e.clientY-cy, e.clientX-cx) * 180/Math.PI;
    const startRot   = el.rotation || 0;

    const onMove = e => {
      const angle = Math.atan2(e.clientY-cy, e.clientX-cx) * 180/Math.PI;
      el.rotation = startRot + (angle - startAngle);
      div.style.transform = `translate(-50%,-50%) rotate(${el.rotation}deg)`;
      // Update rotation display
      const ctrl = div.querySelectorAll('.element-controls span');
      if (ctrl[1]) ctrl[1].textContent = Math.round(el.rotation)+'°';
    };
    const onUp = () => { document.removeEventListener('mousemove',onMove); document.removeEventListener('mouseup',onUp); };
    document.addEventListener('mousemove',onMove); document.addEventListener('mouseup',onUp);
  });
}

// ─── SELECT / DELETE / DUPLICATE ──────────────
function selectEl(id) {
  state.selectedElement = id;
  dom.overlay.querySelectorAll('.pdf-element').forEach(d => d.classList.toggle('selected', parseInt(d.dataset.id)===id));
  updateElementsList();
}
function deleteEl(id) {
  state.elements = state.elements.filter(e => e.id!==id);
  if (state.selectedElement===id) state.selectedElement=null;
  renderElements(); updateElementsList(); showToast('Dihapus','info');
}
function duplicateEl(id) {
  const src = state.elements.find(e => e.id===id); if (!src) return;
  const copy = {...src, id:state.nextId++, pctX:src.pctX+0.02, pctY:src.pctY+0.02};
  state.elements.push(copy); renderElements(); updateElementsList();
  showToast('Diduplikat','success');
}
function undoElement() {
  const last = state.history.pop(); if (!last) return;
  if (last.action==='add') {
    state.elements = state.elements.filter(e => e.id!==last.elementId);
    if (state.selectedElement===last.elementId) state.selectedElement=null;
    renderElements(); updateElementsList(); showToast('Undo','info');
  }
  dom.undoBtn.disabled = state.history.length===0;
}

// ─── ELEMENTS LIST ────────────────────────────
function updateElementsList() {
  dom.elementsCount.textContent = state.elements.length;
  dom.undoBtn.disabled = state.history.length===0;
  if (!state.elements.length) { dom.elementsList.innerHTML='<p class="elements-empty">Belum ada elemen</p>'; return; }
  dom.elementsList.innerHTML='';
  [...state.elements].reverse().forEach(el => {
    const item = document.createElement('div');
    item.className = 'element-item'+(state.selectedElement===el.id?' selected':'');
    item.innerHTML = `
      <span class="element-item-icon">${el.type==='text'?'📝':el.type==='signature'?'✍️':'🔖'}</span>
      <span class="element-item-label">${el.type==='text'?(el.content||'').substring(0,20):'Tanda Tangan'}</span>
      <span style="font-size:10px;color:var(--muted);flex-shrink:0">Hal.${el.page}</span>
      <button class="element-del-btn" onclick="event.stopPropagation();deleteEl(${el.id})">✕</button>`;
    item.addEventListener('click', () => {
      if (el.page!==state.currentPage) { state.currentPage=el.page; renderPage(el.page); }
      selectEl(el.id);
    });
    dom.elementsList.appendChild(item);
  });
}

function setupClearAll() {
  document.getElementById('clearAllBtn').addEventListener('click', () => {
    if (!state.elements.length||!confirm('Hapus semua?')) return;
    state.elements=[]; state.history=[]; state.selectedElement=null;
    dom.undoBtn.disabled=true; renderElements(); updateElementsList();
    showToast('Semua dihapus','info');
  });
}
function setupOverlayClick() {
  dom.overlay.addEventListener('click', e => {
    if (!e.target.closest('.pdf-element')) {
      state.selectedElement=null;
      dom.overlay.querySelectorAll('.pdf-element').forEach(d=>d.classList.remove('selected'));
      updateElementsList();
    }
  });
}

// ─── SAVE PDF ─────────────────────────────────
async function savePDF() {
  if (!state.pdfDoc) { showToast('Tidak ada PDF!','error'); return; }
  const btn = document.getElementById('saveBtn');
  const orig = btn.innerHTML;
  btn.innerHTML = '<div style="width:14px;height:14px;border:2px solid rgba(255,255,255,0.3);border-top-color:white;border-radius:50%;animation:spin 0.8s linear infinite;"></div> Menyimpan...';
  btn.disabled = true;
  try {
    const { jsPDF } = window.jspdf;
    const fp = await state.pdfDoc.getPage(1);
    const fvp = fp.getViewport({scale:1});
    const pdfW = fvp.width*0.75, pdfH = fvp.height*0.75;
    const doc = new jsPDF({ orientation:pdfW>pdfH?'landscape':'portrait', unit:'pt', format:[pdfW,pdfH] });

    for (let p=1; p<=state.totalPages; p++) {
      if (p>1) doc.addPage([pdfW,pdfH]);
      const page = await state.pdfDoc.getPage(p);
      const vp = page.getViewport({scale:2});
      const tmp = document.createElement('canvas');
      tmp.width=vp.width; tmp.height=vp.height;
      await page.render({canvasContext:tmp.getContext('2d'),viewport:vp}).promise;
      doc.addImage(tmp.toDataURL('image/jpeg',0.92),'JPEG',0,0,pdfW,pdfH);

      for (const el of state.elements.filter(e=>e.page===p)) {
        const ex=el.pctX*pdfW, ey=el.pctY*pdfH;
        const rot = el.rotation||0;

        if (el.type==='text') {
          const fmap = {'Arial':'helvetica',"'Times New Roman'":'times',"'Courier New'":'courier','Georgia':'times','Verdana':'helvetica'};
          let style = 'normal';
          if (el.bold&&el.italic) style='bolditalic';
          else if (el.bold) style='bold';
          else if (el.italic) style='italic';
          doc.setFont(fmap[el.fontFamily]||'helvetica', style);
          doc.setFontSize(el.fontSize*0.75);
          const hex=el.color.replace('#','');
          doc.setTextColor(parseInt(hex.slice(0,2),16),parseInt(hex.slice(2,4),16),parseInt(hex.slice(4,6),16));
          if (el.opacity<1) doc.setGState(doc.GState({opacity:el.opacity}));
          if (rot!==0) {
            doc.saveGraphicsState();
            // jsPDF text rotation
            doc.text(el.content, ex, ey, { baseline:'middle', angle: -rot });
          } else {
            doc.text(el.content, ex, ey, {baseline:'middle'});
          }
          doc.setGState(doc.GState({opacity:1})); doc.setTextColor(0,0,0);
        } else {
          try {
            const iw=(el.width/dom.pdfCanvas.width)*pdfW;
            const ih=(el.height/dom.pdfCanvas.height)*pdfH;
            if (el.opacity<1) doc.setGState(doc.GState({opacity:el.opacity}));
            if (rot!==0) {
              doc.saveGraphicsState();
              const rad = rot*Math.PI/180;
              doc.internal.write(
                `q ${Math.cos(rad).toFixed(4)} ${Math.sin(rad).toFixed(4)} ${-Math.sin(rad).toFixed(4)} ${Math.cos(rad).toFixed(4)} ${ex.toFixed(2)} ${(pdfH-ey).toFixed(2)} cm`
              );
              doc.addImage(el.src,'PNG',-iw/2,-ih/2,iw,ih);
              doc.internal.write('Q');
            } else {
              doc.addImage(el.src,'PNG',ex-iw/2,ey-ih/2,iw,ih);
            }
            doc.setGState(doc.GState({opacity:1}));
          } catch(ie) { console.warn('img err',ie); }
        }
      }
    }
    doc.save(state.pdfFileName.replace(/\.pdf$/i,'')+'_edited.pdf');
    showToast('✅ PDF tersimpan!','success');
  } catch(err) { showToast('Gagal: '+err.message,'error'); console.error(err); }
  btn.innerHTML=orig; btn.disabled=false;
}

// ─── MOBILE QR ────────────────────────────────
async function generateMobileQR() {
  const qrWrap=document.getElementById('qrWrap');
  const dot=document.getElementById('qrStatusDot');
  const txt=document.getElementById('qrStatusText');
  qrWrap.innerHTML='<div class="qr-loading"><div class="spinner"></div><span>Membuat QR...</span></div>';
  dot.className='qr-status-dot waiting'; txt.textContent='Membuat sesi...';
  if (state.mobilePolling) { clearInterval(state.mobilePolling); state.mobilePolling=null; }
  try {
    const res = await fetch('api/generate_qr.php');
    if (!res.ok) throw new Error('HTTP '+res.status);
    const data = await res.json();
    if (!data.success) throw new Error(data.error);
    state.mobileSession = data.session;
    qrWrap.innerHTML='';
    const qrDiv=document.createElement('div');
    qrDiv.style.cssText='display:flex;justify-content:center;padding:10px;background:#fff;border-radius:8px;';
    qrWrap.appendChild(qrDiv);
    new QRCode(qrDiv,{text:data.mobile_url,width:200,height:200,colorDark:'#1a1a2e',colorLight:'#ffffff',correctLevel:QRCode.CorrectLevel.M});
    const urlDiv=document.createElement('div');
    urlDiv.style.cssText='font-size:9px;word-break:break-all;color:#999;text-align:center;padding:6px 4px 0;line-height:1.5;';
    urlDiv.textContent=data.mobile_url; qrWrap.appendChild(urlDiv);
    dot.className='qr-status-dot waiting'; txt.textContent='Menunggu tanda tangan dari HP...';
    let ticks=0;
    state.mobilePolling=setInterval(async()=>{
      if(++ticks>150){clearInterval(state.mobilePolling);state.mobilePolling=null;txt.textContent='Sesi habis. Refresh QR.';return;}
      try{
        const pr=await fetch('api/mobile_signature.php?session='+encodeURIComponent(data.session)+'&_='+Date.now());
        if(!pr.ok)return;
        const pd=await pr.json();
        if(pd.success&&pd.status==='completed'&&pd.signature){
          clearInterval(state.mobilePolling);state.mobilePolling=null;
          dot.className='qr-status-dot done'; txt.textContent='✅ Diterima!';
          receiveMobileSignature(pd.signature);
        }
      }catch(_){}
    },2000);
  } catch(err) {
    qrWrap.innerHTML=`<div style="text-align:center;padding:20px;color:#fca5a5;font-size:12px;">⚠️ ${err.message}</div>`;
  }
}

function receiveMobileSignature(src) {
  const img = new Image();
  img.onload = () => {
    const aspect = (img.width&&img.height) ? img.width/img.height : 2;
    const w=220, h=Math.max(40,Math.round(w/aspect));
    state.pendingData = { type:'signature', src, width:w, height:h };
    state.addMode = 'signature';
    if (state.pdfDoc) {
      placeElement(0.5, 0.65);
      showToast('✅ Tanda tangan ditempatkan! Drag untuk pindahkan.','success');
    } else {
      state.addMode=null; state.pendingData=null;
      state.uploadedSigData=src;
      showToast('Tanda tangan tersimpan. Upload PDF lalu gunakan tab Upload.','info');
    }
  };
  img.onerror = () => {
    state.pendingData={type:'signature',src,width:220,height:100};
    state.addMode='signature';
    if(state.pdfDoc){placeElement(0.5,0.65);}
    else{state.addMode=null;state.pendingData=null;}
  };
  img.src=src;
}

// ─── RESET ────────────────────────────────────
function resetState() {
  if(state.mobilePolling)clearInterval(state.mobilePolling);
  Object.assign(state,{pdfDoc:null,currentPage:1,totalPages:0,scale:1.5,pdfFile:null,elements:[],selectedElement:null,nextId:1,addMode:null,pendingData:null,mobileSession:null,mobilePolling:null,stampData:null,uploadedSigData:null,history:[],textStyle:{content:'Teks Contoh',fontFamily:'Arial',fontSize:16,color:'#1a1a2e',bold:false,italic:false,underline:false,opacity:1}});
  dom.pdfFileInput.value=''; document.body.style.overflow='';
}

// ─── KEYBOARD ─────────────────────────────────
document.addEventListener('keydown', e => {
  if(!state.pdfDoc)return;
  const tag=document.activeElement.tagName;
  const ce=document.activeElement.contentEditable;
  if(e.key==='Escape')cancelAddMode();
  if((e.key==='Delete'||e.key==='Backspace')&&tag!=='INPUT'&&tag!=='TEXTAREA'&&ce!=='true'){
    if(state.selectedElement)deleteEl(state.selectedElement);
  }
  if((e.ctrlKey||e.metaKey)&&e.key==='z'){e.preventDefault();undoElement();}
  if((e.ctrlKey||e.metaKey)&&e.key==='s'){e.preventDefault();savePDF();}
  if(e.key==='ArrowLeft'&&tag!=='INPUT')changePage(-1);
  if(e.key==='ArrowRight'&&tag!=='INPUT')changePage(1);
});

// ─── EXPORTS ──────────────────────────────────
window.cancelAddMode=cancelAddMode; window.deleteEl=deleteEl; window.duplicateEl=duplicateEl;
window.savePDF=savePDF; window.changePage=changePage; window.zoomIn=zoomIn; window.zoomOut=zoomOut;
window.resetZoom=resetZoom; window.undoElement=undoElement; window.clearSignatureCanvas=clearSignatureCanvas;
window.useDrawnSignature=useDrawnSignature; window.useUploadedSignature=useUploadedSignature;
window.useStamp=useStamp; window.generateMobileQR=generateMobileQR;
window.receiveMobileSignature=receiveMobileSignature; window.adjustFontSize=adjustFontSize;
window.makeTextEditable=makeTextEditable;
