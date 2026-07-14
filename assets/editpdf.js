/* ═══════════════════════════════════════════════
   DocYouMen — Edit PDF (ubah teks yang sudah ada)
   Teknik: klik teks asli → tutup dengan kotak putih →
   gambar teks baru di posisi yang sama. Teks baru adalah
   teks PDF asli (bisa di-select/cari), bukan reflow paragraf.
   Paling rapi untuk dokumen berlatar putih/polos.
═══════════════════════════════════════════════ */
'use strict';

const editTextState = {
  file: null, arrayBuffer: null, pdfDoc: null,
  currentPage: 1, totalPages: 0, scale: 1.5,
  edits: new Map(), // page -> Map(itemIndex -> newText)
};

document.addEventListener('DOMContentLoaded', () => {
  setupEditTextTool();
});

function setupEditTextTool() {
  const dropzone = document.getElementById('editTextDropzone');
  const input = document.getElementById('editTextFileInput');
  if (!dropzone || !input) return;
  setupGenericUpload({
    dropzone, input, onFiles: files => {
      const f = files[0];
      if (!f || f.type !== 'application/pdf') { showToast('File harus PDF!', 'error'); return; }
      loadEditTextFile(f);
    }
  });
  document.getElementById('editTextPrevBtn').addEventListener('click', () => changeEditTextPage(-1));
  document.getElementById('editTextNextBtn').addEventListener('click', () => changeEditTextPage(1));
  document.getElementById('editTextSaveBtn').addEventListener('click', saveEditedPdf);
}

async function loadEditTextFile(file) {
  try {
    editTextState.file = file;
    editTextState.arrayBuffer = await file.arrayBuffer();
    editTextState.pdfDoc = await pdfjsLib.getDocument({ data: editTextState.arrayBuffer.slice(0) }).promise;
    editTextState.totalPages = editTextState.pdfDoc.numPages;
    editTextState.currentPage = 1;
    editTextState.edits = new Map();

    document.getElementById('editTextUploadStep').style.display = 'none';
    document.getElementById('editTextWorkStep').style.display = 'block';
    document.getElementById('editTextFileInfo').style.display = 'flex';
    document.getElementById('editTextFileName').textContent = file.name;
    document.getElementById('editTextTotalPages').textContent = editTextState.totalPages;

    await renderEditTextPage(1);
  } catch (err) {
    showToast('Gagal membuka PDF: ' + err.message, 'error');
  }
}

function changeEditTextPage(delta) {
  const n = editTextState.currentPage + delta;
  if (n < 1 || n > editTextState.totalPages) return;
  editTextState.currentPage = n;
  renderEditTextPage(n);
}

async function renderEditTextPage(n) {
  const canvas = document.getElementById('editTextCanvas');
  const page = await editTextState.pdfDoc.getPage(n);
  const viewport = page.getViewport({ scale: editTextState.scale });
  canvas.width = viewport.width;
  canvas.height = viewport.height;
  await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;

  document.getElementById('editTextCurrentPage').textContent = n;
  document.getElementById('editTextPrevBtn').disabled = n <= 1;
  document.getElementById('editTextNextBtn').disabled = n >= editTextState.totalPages;

  await buildTextHitLayer(page, viewport, n);
}

// Posisi tiap run teks dihitung dari transform pdf.js — pola yang sama
// dipakai internal text-layer pdf.js sendiri (item.transform + viewport.transform).
async function buildTextHitLayer(page, viewport, pageNum) {
  const layer = document.getElementById('editTextHitLayer');
  layer.innerHTML = '';
  const textContent = await page.getTextContent();
  const pageEdits = editTextState.edits.get(pageNum);

  textContent.items.forEach((item, idx) => {
    if (!item.str || !item.str.trim()) return;
    const tx = pdfjsLib.Util.transform(viewport.transform, item.transform);
    const fontH = Math.hypot(tx[2], tx[3]);
    const w = item.width * editTextState.scale;
    const left = tx[4];
    const top = tx[5] - fontH;

    const hit = document.createElement('div');
    hit.className = 'edit-text-hit';
    hit.style.left = left + 'px';
    hit.style.top = top + 'px';
    hit.style.width = Math.max(w, 10) + 'px';
    hit.style.height = (fontH * 1.25) + 'px';
    hit.style.fontSize = (fontH * 0.85) + 'px';
    hit.title = 'Klik untuk edit teks ini';
    hit.dataset.original = item.str;

    const editedText = pageEdits && pageEdits.has(idx) ? pageEdits.get(idx) : null;
    if (editedText !== null) {
      hit.classList.add('edited');
      hit.textContent = editedText;
    }

    hit.addEventListener('click', () => startEditTextItem(hit, item, idx, pageNum));
    layer.appendChild(hit);
  });
}

function startEditTextItem(hitEl, item, idx, pageNum) {
  const current = hitEl.classList.contains('edited') ? hitEl.textContent : item.str;
  const input = document.createElement('input');
  input.type = 'text';
  input.className = 'edit-text-input';
  input.value = current;
  input.style.cssText = hitEl.style.cssText;

  const commit = () => {
    const val = input.value;
    if (!editTextState.edits.has(pageNum)) editTextState.edits.set(pageNum, new Map());
    const pageMap = editTextState.edits.get(pageNum);
    if (val === item.str) pageMap.delete(idx);
    else pageMap.set(idx, val);
    renderEditTextPage(editTextState.currentPage);
    updateEditTextCount();
  };
  input.addEventListener('blur', commit);
  input.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); input.blur(); }
    if (e.key === 'Escape') { input.value = current; input.blur(); }
  });

  hitEl.replaceWith(input);
  input.focus();
  input.select();
}

function updateEditTextCount() {
  let total = 0;
  editTextState.edits.forEach(m => { total += m.size; });
  document.getElementById('editTextEditCount').textContent = total;
  document.getElementById('editTextSaveBtn').disabled = total === 0;
}

async function saveEditedPdf() {
  if (!editTextState.pdfDoc) return;
  showBusy('Menyimpan PDF...');
  try {
    const { jsPDF } = window.jspdf;
    const fp = await editTextState.pdfDoc.getPage(1);
    const fvp = fp.getViewport({ scale: 1 }); // scale:1 pdf.js viewport = poin (72dpi), langsung cocok untuk unit 'pt' jsPDF
    const pdfW = fvp.width, pdfH = fvp.height;
    const doc = new jsPDF({ orientation: pdfW > pdfH ? 'landscape' : 'portrait', unit: 'pt', format: [pdfW, pdfH] });

    for (let p = 1; p <= editTextState.totalPages; p++) {
      if (p > 1) doc.addPage([pdfW, pdfH]);
      const page = await editTextState.pdfDoc.getPage(p);
      const vp2 = page.getViewport({ scale: 2 });
      const tmp = document.createElement('canvas');
      tmp.width = vp2.width; tmp.height = vp2.height;
      await page.render({ canvasContext: tmp.getContext('2d'), viewport: vp2 }).promise;
      doc.addImage(tmp.toDataURL('image/jpeg', 0.92), 'JPEG', 0, 0, pdfW, pdfH);

      const pageEdits = editTextState.edits.get(p);
      if (pageEdits && pageEdits.size) {
        const textContent = await page.getTextContent();
        const vp1 = page.getViewport({ scale: 1 });
        pageEdits.forEach((newText, idx) => {
          const item = textContent.items[idx];
          if (!item) return;
          const tx = pdfjsLib.Util.transform(vp1.transform, item.transform);
          const fontH = Math.hypot(tx[2], tx[3]);
          const w = Math.max(item.width, fontH * 0.5 * newText.length);
          const x = tx[4], yBaseline = tx[5];

          doc.setFillColor(255, 255, 255);
          doc.rect(x - 2, yBaseline - fontH, w + 4, fontH * 1.3, 'F');
          doc.setFont('helvetica', 'normal');
          doc.setFontSize(fontH * 0.85);
          doc.setTextColor(0, 0, 0);
          doc.text(newText, x, yBaseline);
        });
      }
    }

    doc.save(editTextState.file.name.replace(/\.pdf$/i, '') + '_edited.pdf');
    showToast('✅ PDF tersimpan', 'success');
  } catch (err) {
    showToast('Gagal simpan: ' + err.message, 'error');
  } finally {
    hideBusy();
  }
}
