/* ═══════════════════════════════════════════════
   DocYouMen — Split / Merge / PDF↔JPG (client-side)
   Pakai PDFLib (split, merge), pdfjsLib (render/rasterize,
   sudah dimuat editor.js), jsPDF (jpg2pdf), JSZip (bundel output).
═══════════════════════════════════════════════ */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  setupToolGrid();
  setupToolSearch();
  setupSplitTool();
  setupMergeTool();
  setupPdf2JpgTool();
  setupJpg2PdfTool();
  setupCompressTool();
  setupPageNumTool();
  setupRemovePagesTool();
  setupBackButtons();
});

function setupBackButtons() {
  document.querySelectorAll('[data-back-overlay]').forEach(btn => {
    btn.addEventListener('click', () => {
      const overlay = document.getElementById(btn.dataset.backOverlay);
      if (overlay) backToGrid(overlay);
    });
  });
}

// Render satu halaman PDF ke canvas offscreen (dipakai Split thumbnail & PDF→JPG).
async function renderPageToCanvas(pdfDoc, pageNum, scale) {
  const page = await pdfDoc.getPage(pageNum);
  const vp = page.getViewport({ scale });
  const canvas = document.createElement('canvas');
  canvas.width = vp.width; canvas.height = vp.height;
  await page.render({ canvasContext: canvas.getContext('2d'), viewport: vp }).promise;
  return canvas;
}

function canvasToJpegBlob(canvas, quality) {
  return new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', quality));
}

// Parse input seperti "1-3,5,7-9" jadi array index 0-based per grup.
// Return: array of { label, indices[] }
function parsePageRanges(input, totalPages) {
  const groups = [];
  const parts = input.split(',').map(s => s.trim()).filter(Boolean);
  for (const part of parts) {
    const m = part.match(/^(\d+)(?:-(\d+))?$/);
    if (!m) continue;
    let start = parseInt(m[1]), end = m[2] ? parseInt(m[2]) : start;
    if (start > end) [start, end] = [end, start];
    start = Math.max(1, start); end = Math.min(totalPages, end);
    if (start > totalPages) continue;
    const indices = [];
    for (let p = start; p <= end; p++) indices.push(p - 1);
    groups.push({ label: start === end ? `hal-${start}` : `hal-${start}-${end}`, indices });
  }
  return groups;
}

/* ══════════════════ SPLIT PDF ══════════════════ */
const splitState = { file: null, pdfDoc: null, arrayBuffer: null };

function setupSplitTool() {
  const dropzone = document.getElementById('splitDropzone');
  const input = document.getElementById('splitFileInput');
  setupGenericUpload({
    dropzone, input, onFiles: files => {
      const f = files[0];
      if (!f || f.type !== 'application/pdf') { showToast('File harus PDF!', 'error'); return; }
      loadSplitFile(f);
    }
  });

  document.querySelectorAll('#splitOverlay [data-splitmode]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('#splitOverlay [data-splitmode]').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('splitRangeSection').style.display = btn.dataset.splitmode === 'range' ? 'block' : 'none';
    });
  });

  document.getElementById('splitRunBtn').addEventListener('click', runSplit);
}

async function loadSplitFile(file) {
  try {
    splitState.file = file;
    splitState.arrayBuffer = await file.arrayBuffer();
    splitState.pdfDoc = await pdfjsLib.getDocument({ data: splitState.arrayBuffer.slice(0) }).promise;

    document.getElementById('splitUploadStep').style.display = 'none';
    document.getElementById('splitWorkStep').style.display = 'block';
    document.getElementById('splitFileInfo').style.display = 'flex';
    document.getElementById('splitFileName').textContent = file.name;

    const grid = document.getElementById('splitThumbGrid');
    grid.innerHTML = '';
    document.getElementById('splitPageInfo').textContent = splitState.pdfDoc.numPages + ' halaman';
    for (let p = 1; p <= splitState.pdfDoc.numPages; p++) {
      const canvas = await renderPageToCanvas(splitState.pdfDoc, p, 0.35);
      const wrap = document.createElement('div');
      wrap.className = 'thumb-item';
      wrap.appendChild(canvas);
      const label = document.createElement('span');
      label.textContent = p;
      wrap.appendChild(label);
      grid.appendChild(wrap);
    }
  } catch (err) {
    showToast('Gagal membuka PDF: ' + err.message, 'error');
  }
}

async function runSplit() {
  if (!splitState.pdfDoc) return;
  const mode = document.querySelector('#splitOverlay [data-splitmode].active').dataset.splitmode;
  const total = splitState.pdfDoc.numPages;
  let groups;
  if (mode === 'each') {
    groups = Array.from({ length: total }, (_, i) => ({ label: `hal-${i + 1}`, indices: [i] }));
  } else {
    const raw = document.getElementById('splitRangeInput').value.trim();
    if (!raw) { showToast('Isi range halaman dulu!', 'error'); return; }
    groups = parsePageRanges(raw, total);
    if (!groups.length) { showToast('Format range tidak valid.', 'error'); return; }
  }

  showBusy('Memisah PDF...');
  try {
    const srcDoc = await PDFLib.PDFDocument.load(splitState.arrayBuffer, { ignoreEncryption: true });
    const baseName = splitState.file.name.replace(/\.pdf$/i, '');
    const outputs = [];
    for (const g of groups) {
      const newDoc = await PDFLib.PDFDocument.create();
      const pages = await newDoc.copyPages(srcDoc, g.indices);
      pages.forEach(p => newDoc.addPage(p));
      const bytes = await newDoc.save();
      outputs.push({ name: `${baseName}_${g.label}.pdf`, bytes });
    }

    if (outputs.length === 1) {
      downloadBlob(new Blob([outputs[0].bytes], { type: 'application/pdf' }), outputs[0].name);
    } else {
      const zip = new JSZip();
      outputs.forEach(o => zip.file(o.name, o.bytes));
      const zipBlob = await zip.generateAsync({ type: 'blob' });
      downloadBlob(zipBlob, baseName + '_split.zip');
    }
    showToast(`✅ Berhasil dipisah jadi ${outputs.length} file`, 'success');
  } catch (err) {
    showToast('Gagal split: ' + err.message, 'error');
  } finally {
    hideBusy();
  }
}

/* ══════════════════ MERGE PDF ══════════════════ */
const mergeQueue = [];

function setupMergeTool() {
  const dropzone = document.getElementById('mergeDropzone');
  const input = document.getElementById('mergeFileInput');
  setupGenericUpload({
    dropzone, input, onFiles: files => {
      [...files].forEach(f => { if (f.type === 'application/pdf') mergeQueue.push(f); });
      renderMergeQueue();
    }
  });
  document.getElementById('mergeRunBtn').addEventListener('click', runMerge);
}

function renderMergeQueue() {
  renderQueue(mergeQueue, document.getElementById('mergeQueueList'), document.getElementById('mergeQueueCount'), idx => {
    mergeQueue.splice(idx, 1); renderMergeQueue();
  });
  document.getElementById('mergeRunBtn').disabled = mergeQueue.length < 2;
}

async function runMerge() {
  if (mergeQueue.length < 2) { showToast('Minimal 2 file PDF untuk digabung.', 'error'); return; }
  showBusy('Menggabungkan PDF...');
  try {
    const merged = await PDFLib.PDFDocument.create();
    for (const file of mergeQueue) {
      const buf = await file.arrayBuffer();
      const donor = await PDFLib.PDFDocument.load(buf, { ignoreEncryption: true });
      const pages = await merged.copyPages(donor, donor.getPageIndices());
      pages.forEach(p => merged.addPage(p));
    }
    const bytes = await merged.save();
    downloadBlob(new Blob([bytes], { type: 'application/pdf' }), 'merged.pdf');
    showToast('✅ PDF berhasil digabung', 'success');
  } catch (err) {
    showToast('Gagal merge: ' + err.message, 'error');
  } finally {
    hideBusy();
  }
}

/* ══════════════════ PDF → JPG ══════════════════ */
const pdf2jpgState = { file: null, pdfDoc: null };

function setupPdf2JpgTool() {
  const dropzone = document.getElementById('pdf2jpgDropzone');
  const input = document.getElementById('pdf2jpgFileInput');
  setupGenericUpload({
    dropzone, input, onFiles: files => {
      const f = files[0];
      if (!f || f.type !== 'application/pdf') { showToast('File harus PDF!', 'error'); return; }
      loadPdf2JpgFile(f);
    }
  });
  document.getElementById('pdf2jpgQuality').addEventListener('input', e => {
    document.getElementById('pdf2jpgQualityVal').textContent = e.target.value + '%';
  });
  document.getElementById('pdf2jpgRunBtn').addEventListener('click', runPdf2Jpg);
}

async function loadPdf2JpgFile(file) {
  try {
    pdf2jpgState.file = file;
    const buf = await file.arrayBuffer();
    pdf2jpgState.pdfDoc = await pdfjsLib.getDocument({ data: buf }).promise;

    document.getElementById('pdf2jpgUploadStep').style.display = 'none';
    document.getElementById('pdf2jpgWorkStep').style.display = 'block';
    document.getElementById('pdf2jpgFileInfo').style.display = 'flex';
    document.getElementById('pdf2jpgFileName').textContent = file.name;
    document.getElementById('pdf2jpgPageInfo').textContent = pdf2jpgState.pdfDoc.numPages + ' halaman';

    const grid = document.getElementById('pdf2jpgThumbGrid');
    grid.innerHTML = '';
    for (let p = 1; p <= pdf2jpgState.pdfDoc.numPages; p++) {
      const canvas = await renderPageToCanvas(pdf2jpgState.pdfDoc, p, 0.35);
      const wrap = document.createElement('div');
      wrap.className = 'thumb-item';
      wrap.appendChild(canvas);
      const label = document.createElement('span');
      label.textContent = p;
      wrap.appendChild(label);
      grid.appendChild(wrap);
    }
  } catch (err) {
    showToast('Gagal membuka PDF: ' + err.message, 'error');
  }
}

async function runPdf2Jpg() {
  if (!pdf2jpgState.pdfDoc) return;
  const quality = (parseInt(document.getElementById('pdf2jpgQuality').value) || 90) / 100;
  const baseName = pdf2jpgState.file.name.replace(/\.pdf$/i, '');
  const total = pdf2jpgState.pdfDoc.numPages;

  showBusy('Mengubah PDF ke JPG...');
  try {
    if (total === 1) {
      const canvas = await renderPageToCanvas(pdf2jpgState.pdfDoc, 1, 2);
      const blob = await canvasToJpegBlob(canvas, quality);
      downloadBlob(blob, baseName + '.jpg');
    } else {
      const zip = new JSZip();
      for (let p = 1; p <= total; p++) {
        const canvas = await renderPageToCanvas(pdf2jpgState.pdfDoc, p, 2);
        const blob = await canvasToJpegBlob(canvas, quality);
        zip.file(`${baseName}_hal-${p}.jpg`, blob);
      }
      const zipBlob = await zip.generateAsync({ type: 'blob' });
      downloadBlob(zipBlob, baseName + '_jpg.zip');
    }
    showToast(`✅ Berhasil, ${total} halaman dikonversi`, 'success');
  } catch (err) {
    showToast('Gagal konversi: ' + err.message, 'error');
  } finally {
    hideBusy();
  }
}

/* ══════════════════ JPG → PDF ══════════════════ */
const jpg2pdfQueue = [];

function setupJpg2PdfTool() {
  const dropzone = document.getElementById('jpg2pdfDropzone');
  const input = document.getElementById('jpg2pdfFileInput');
  setupGenericUpload({
    dropzone, input, onFiles: files => {
      [...files].forEach(f => { if (f.type.startsWith('image/')) jpg2pdfQueue.push(f); });
      renderJpg2PdfQueue();
    }
  });
  document.getElementById('jpg2pdfRunBtn').addEventListener('click', runJpg2Pdf);
}

function renderJpg2PdfQueue() {
  renderQueue(jpg2pdfQueue, document.getElementById('jpg2pdfQueueList'), document.getElementById('jpg2pdfQueueCount'), idx => {
    jpg2pdfQueue.splice(idx, 1); renderJpg2PdfQueue();
  });
  document.getElementById('jpg2pdfRunBtn').disabled = jpg2pdfQueue.length < 1;
}

function loadImage(src) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.onload = () => resolve(img);
    img.onerror = reject;
    img.src = src;
  });
}

async function runJpg2Pdf() {
  if (!jpg2pdfQueue.length) { showToast('Upload minimal 1 gambar.', 'error'); return; }
  showBusy('Membuat PDF dari gambar...');
  try {
    const { jsPDF } = window.jspdf;
    let doc = null;
    for (const file of jpg2pdfQueue) {
      const dataUrl = await new Promise(resolve => {
        const r = new FileReader(); r.onload = e => resolve(e.target.result); r.readAsDataURL(file);
      });
      const img = await loadImage(dataUrl);
      const w = img.width * 0.75, h = img.height * 0.75; // px(96dpi) -> pt
      if (!doc) {
        doc = new jsPDF({ orientation: w > h ? 'landscape' : 'portrait', unit: 'pt', format: [w, h] });
      } else {
        doc.addPage([w, h]);
      }
      const fmt = /png/i.test(file.type) ? 'PNG' : 'JPEG';
      doc.addImage(dataUrl, fmt, 0, 0, w, h);
    }
    doc.save('images_to_pdf.pdf');
    showToast(`✅ PDF berhasil dibuat dari ${jpg2pdfQueue.length} gambar`, 'success');
  } catch (err) {
    showToast('Gagal konversi: ' + err.message, 'error');
  } finally {
    hideBusy();
  }
}

/* ══════════════════ COMPRESS PDF ══════════════════ */
const compressState = { file: null, arrayBuffer: null, pdfDoc: null };

const COMPRESS_MODE_DESC = {
  light: 'Pertahankan kualitas & teks asli. Cocok untuk PDF berbasis teks — pengurangan ukuran moderat.',
  aggressive: 'Tiap halaman diubah jadi gambar terkompresi — ukuran jauh lebih kecil, tapi teks tidak lagi bisa di-select/di-cari.',
};

function setupCompressTool() {
  const dropzone = document.getElementById('compressDropzone');
  const input = document.getElementById('compressFileInput');
  setupGenericUpload({
    dropzone, input, onFiles: files => {
      const f = files[0];
      if (!f || f.type !== 'application/pdf') { showToast('File harus PDF!', 'error'); return; }
      loadCompressFile(f);
    }
  });

  document.querySelectorAll('#compressOverlay [data-compressmode]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('#compressOverlay [data-compressmode]').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const mode = btn.dataset.compressmode;
      document.getElementById('compressModeDesc').textContent = COMPRESS_MODE_DESC[mode];
      document.getElementById('compressQualitySection').style.display = mode === 'aggressive' ? 'block' : 'none';
    });
  });
  document.getElementById('compressQuality').addEventListener('input', e => {
    document.getElementById('compressQualityVal').textContent = e.target.value + '%';
  });
  document.getElementById('compressRunBtn').addEventListener('click', runCompress);
}

async function loadCompressFile(file) {
  try {
    compressState.file = file;
    compressState.arrayBuffer = await file.arrayBuffer();
    compressState.pdfDoc = await pdfjsLib.getDocument({ data: compressState.arrayBuffer.slice(0) }).promise;

    document.getElementById('compressUploadStep').style.display = 'none';
    document.getElementById('compressWorkStep').style.display = 'block';
    document.getElementById('compressFileInfo').style.display = 'flex';
    document.getElementById('compressFileName').textContent = file.name;
    document.getElementById('compressOrigSize').textContent =
      formatFileSize(file.size) + ' — ' + compressState.pdfDoc.numPages + ' halaman';
  } catch (err) {
    showToast('Gagal membuka PDF: ' + err.message, 'error');
  }
}

async function runCompress() {
  if (!compressState.pdfDoc) return;
  const mode = document.querySelector('#compressOverlay [data-compressmode].active').dataset.compressmode;
  const baseName = compressState.file.name.replace(/\.pdf$/i, '');

  showBusy('Mengompres PDF...');
  try {
    let outBlob;
    if (mode === 'light') {
      const srcDoc = await PDFLib.PDFDocument.load(compressState.arrayBuffer, { ignoreEncryption: true });
      const bytes = await srcDoc.save({ useObjectStreams: true });
      outBlob = new Blob([bytes], { type: 'application/pdf' });
    } else {
      const quality = (parseInt(document.getElementById('compressQuality').value) || 70) / 100;
      const { jsPDF } = window.jspdf;
      let doc = null;
      const total = compressState.pdfDoc.numPages;
      for (let p = 1; p <= total; p++) {
        const canvas = await renderPageToCanvas(compressState.pdfDoc, p, 1.5);
        const w = canvas.width * 0.75, h = canvas.height * 0.75;
        if (!doc) doc = new jsPDF({ orientation: w > h ? 'landscape' : 'portrait', unit: 'pt', format: [w, h] });
        else doc.addPage([w, h]);
        doc.addImage(canvas.toDataURL('image/jpeg', quality), 'JPEG', 0, 0, w, h);
      }
      outBlob = doc.output('blob');
    }

    downloadBlob(outBlob, baseName + '_compressed.pdf');
    const before = compressState.file.size, after = outBlob.size;
    const pct = Math.round((1 - after / before) * 100);
    const pctText = pct > 0 ? `-${pct}%` : 'tidak lebih kecil dari asli';
    showToast(`✅ ${formatFileSize(before)} → ${formatFileSize(after)} (${pctText})`, 'success');
  } catch (err) {
    showToast('Gagal kompres: ' + err.message, 'error');
  } finally {
    hideBusy();
  }
}

/* ══════════════════ HAPUS HALAMAN ══════════════════ */
const removePagesState = { file: null, arrayBuffer: null, pdfDoc: null, marked: new Set() };

function setupRemovePagesTool() {
  const dropzone = document.getElementById('removePagesDropzone');
  const input = document.getElementById('removePagesFileInput');
  setupGenericUpload({
    dropzone, input, onFiles: files => {
      const f = files[0];
      if (!f || f.type !== 'application/pdf') { showToast('File harus PDF!', 'error'); return; }
      loadRemovePagesFile(f);
    }
  });
  document.getElementById('removePagesRunBtn').addEventListener('click', runRemovePages);
}

async function loadRemovePagesFile(file) {
  try {
    removePagesState.file = file;
    removePagesState.arrayBuffer = await file.arrayBuffer();
    removePagesState.pdfDoc = await pdfjsLib.getDocument({ data: removePagesState.arrayBuffer.slice(0) }).promise;
    removePagesState.marked = new Set();

    document.getElementById('removePagesUploadStep').style.display = 'none';
    document.getElementById('removePagesWorkStep').style.display = 'block';
    document.getElementById('removePagesFileInfo').style.display = 'flex';
    document.getElementById('removePagesFileName').textContent = file.name;

    const grid = document.getElementById('removePagesThumbGrid');
    grid.innerHTML = '';
    const total = removePagesState.pdfDoc.numPages;
    document.getElementById('removePagesPageInfo').textContent = total + ' halaman';
    for (let p = 1; p <= total; p++) {
      const canvas = await renderPageToCanvas(removePagesState.pdfDoc, p, 0.35);
      const wrap = document.createElement('div');
      wrap.className = 'thumb-item removable';
      wrap.dataset.page = p;
      wrap.appendChild(canvas);
      const label = document.createElement('span');
      label.textContent = p;
      wrap.appendChild(label);
      wrap.addEventListener('click', () => {
        if (removePagesState.marked.has(p)) { removePagesState.marked.delete(p); wrap.classList.remove('marked-remove'); }
        else { removePagesState.marked.add(p); wrap.classList.add('marked-remove'); }
        updateRemovePagesCount();
      });
      grid.appendChild(wrap);
    }
    updateRemovePagesCount();
  } catch (err) {
    showToast('Gagal membuka PDF: ' + err.message, 'error');
  }
}

function updateRemovePagesCount() {
  const total = removePagesState.pdfDoc ? removePagesState.pdfDoc.numPages : 0;
  const marked = removePagesState.marked.size;
  document.getElementById('removePagesMarkedCount').textContent = marked;
  document.getElementById('removePagesRunBtn').disabled = marked === 0 || marked >= total;
}

async function runRemovePages() {
  if (!removePagesState.pdfDoc) return;
  const total = removePagesState.pdfDoc.numPages;
  if (removePagesState.marked.size >= total) { showToast('Tidak bisa hapus semua halaman.', 'error'); return; }

  showBusy('Menghapus halaman...');
  try {
    const pdfDoc = await PDFLib.PDFDocument.load(removePagesState.arrayBuffer, { ignoreEncryption: true });
    // Hapus dari index terbesar ke terkecil supaya index sisanya tidak bergeser.
    const indicesDesc = [...removePagesState.marked].sort((a, b) => b - a).map(p => p - 1);
    indicesDesc.forEach(idx => pdfDoc.removePage(idx));
    const bytes = await pdfDoc.save();
    const baseName = removePagesState.file.name.replace(/\.pdf$/i, '');
    downloadBlob(new Blob([bytes], { type: 'application/pdf' }), baseName + '_edited.pdf');
    showToast(`✅ ${removePagesState.marked.size} halaman dihapus`, 'success');
  } catch (err) {
    showToast('Gagal hapus halaman: ' + err.message, 'error');
  } finally {
    hideBusy();
  }
}

/* ══════════════════ NOMOR HALAMAN ══════════════════ */
const pagenumState = { file: null, arrayBuffer: null, pdfDoc: null };

function setupPageNumTool() {
  const dropzone = document.getElementById('pagenumDropzone');
  const input = document.getElementById('pagenumFileInput');
  setupGenericUpload({
    dropzone, input, onFiles: files => {
      const f = files[0];
      if (!f || f.type !== 'application/pdf') { showToast('File harus PDF!', 'error'); return; }
      loadPageNumFile(f);
    }
  });
  document.getElementById('pagenumRunBtn').addEventListener('click', runPageNum);
}

async function loadPageNumFile(file) {
  try {
    pagenumState.file = file;
    pagenumState.arrayBuffer = await file.arrayBuffer();
    pagenumState.pdfDoc = await pdfjsLib.getDocument({ data: pagenumState.arrayBuffer.slice(0) }).promise;

    document.getElementById('pagenumUploadStep').style.display = 'none';
    document.getElementById('pagenumWorkStep').style.display = 'block';
    document.getElementById('pagenumFileInfo').style.display = 'flex';
    document.getElementById('pagenumFileName').textContent = file.name;
    document.getElementById('pagenumPageInfo').textContent = pagenumState.pdfDoc.numPages + ' halaman';

    const grid = document.getElementById('pagenumThumbGrid');
    grid.innerHTML = '';
    for (let p = 1; p <= pagenumState.pdfDoc.numPages; p++) {
      const canvas = await renderPageToCanvas(pagenumState.pdfDoc, p, 0.35);
      const wrap = document.createElement('div');
      wrap.className = 'thumb-item';
      wrap.appendChild(canvas);
      const label = document.createElement('span');
      label.textContent = p;
      wrap.appendChild(label);
      grid.appendChild(wrap);
    }
  } catch (err) {
    showToast('Gagal membuka PDF: ' + err.message, 'error');
  }
}

async function runPageNum() {
  if (!pagenumState.pdfDoc) return;
  const position = document.getElementById('pagenumPosition').value;
  const format = document.getElementById('pagenumFormat').value.trim() || 'Halaman {n} dari {total}';
  const startFrom = parseInt(document.getElementById('pagenumStart').value) || 1;

  showBusy('Menambah nomor halaman...');
  try {
    const pdfDoc = await PDFLib.PDFDocument.load(pagenumState.arrayBuffer, { ignoreEncryption: true });
    const font = await pdfDoc.embedFont(PDFLib.StandardFonts.Helvetica);
    const pages = pdfDoc.getPages();
    const total = pages.length;
    const fontSize = 10, margin = 28;

    pages.forEach((page, i) => {
      const { width, height } = page.getSize();
      const num = i + startFrom;
      const text = format.replace(/\{n\}/g, num).replace(/\{total\}/g, total);
      const textWidth = font.widthOfTextAtSize(text, fontSize);
      let x, y;
      switch (position) {
        case 'bottom-left':  x = margin;                    y = margin - 8;         break;
        case 'bottom-right': x = width - textWidth - margin; y = margin - 8;         break;
        case 'top-left':     x = margin;                    y = height - margin;    break;
        case 'top-right':    x = width - textWidth - margin; y = height - margin;    break;
        case 'top-center':   x = (width - textWidth) / 2;    y = height - margin;    break;
        default:              x = (width - textWidth) / 2;    y = margin - 8; // bottom-center
      }
      page.drawText(text, { x, y, size: fontSize, font, color: PDFLib.rgb(0.15, 0.15, 0.2) });
    });

    const bytes = await pdfDoc.save();
    downloadBlob(new Blob([bytes], { type: 'application/pdf' }), pagenumState.file.name.replace(/\.pdf$/i, '') + '_numbered.pdf');
    showToast('✅ Nomor halaman berhasil ditambahkan', 'success');
  } catch (err) {
    showToast('Gagal: ' + err.message, 'error');
  } finally {
    hideBusy();
  }
}
