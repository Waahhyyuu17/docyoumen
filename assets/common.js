/* ═══════════════════════════════════════════════
   DocYouMen — Shared Utilities
   Dipakai oleh editor.js, tools.js, convert.js
═══════════════════════════════════════════════ */
'use strict';

// ─── TOAST ────────────────────────────────────
function showToast(msg, type = '') {
  const t = document.getElementById('toast');
  t.textContent = msg; t.className = 'toast show ' + type;
  clearTimeout(window._tt); window._tt = setTimeout(() => t.classList.remove('show'), 3500);
}

// ─── MISC ─────────────────────────────────────
function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }
function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

function formatFileSize(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

// ─── DOWNLOAD ─────────────────────────────────
function downloadBlob(blob, filename) {
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url; a.download = filename;
  document.body.appendChild(a); a.click(); a.remove();
  setTimeout(() => URL.revokeObjectURL(url), 1000);
}

// ─── BUSY OVERLAY ─────────────────────────────
function showBusy(message) {
  const ov = document.getElementById('busyOverlay');
  document.getElementById('busyText').textContent = message || 'Memproses...';
  ov.classList.add('show');
}
function hideBusy() {
  document.getElementById('busyOverlay').classList.remove('show');
}

// ─── TOOL NAVIGATION ──────────────────────────
// Setiap kartu di #toolGrid membuka satu overlay full-screen (id-nya = data-overlay).
function setupToolGrid() {
  document.querySelectorAll('.tool-card').forEach(card => {
    card.addEventListener('click', () => {
      const overlayId = card.dataset.overlay;
      if (!overlayId) return;
      const overlay = document.getElementById(overlayId);
      if (!overlay) return;
      overlay.style.display = 'block';
      document.body.style.overflow = 'hidden';
    });
  });
}

function backToGrid(overlayEl) {
  overlayEl.style.display = 'none';
  document.body.style.overflow = '';
}

// ─── SEARCH & CATEGORY FILTER (landing grid) ──
function setupToolSearch() {
  const input = document.getElementById('toolSearchInput');
  const pills = document.querySelectorAll('.category-pill');
  const cards = document.querySelectorAll('.tool-card');
  const emptyMsg = document.getElementById('toolEmptyMsg');
  if (!input) return;

  let activeCategory = 'all';

  function applyFilter() {
    const q = input.value.trim().toLowerCase();
    let visibleCount = 0;
    cards.forEach(card => {
      const matchesCategory = activeCategory === 'all' || card.dataset.category === activeCategory;
      const matchesSearch = !q || card.textContent.toLowerCase().includes(q);
      const show = matchesCategory && matchesSearch;
      card.style.display = show ? '' : 'none';
      if (show) visibleCount++;
    });
    emptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';
  }

  input.addEventListener('input', applyFilter);
  pills.forEach(pill => {
    pill.addEventListener('click', () => {
      pills.forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      activeCategory = pill.dataset.category;
      applyFilter();
    });
  });
}

// ─── GENERIC FILE QUEUE (Merge PDF / JPG→PDF) ─
// queue: array of File, dipertahankan urutan tampil = urutan proses.
function renderQueue(queue, listEl, countEl, onRemove) {
  if (countEl) countEl.textContent = queue.length;
  if (!queue.length) { listEl.innerHTML = '<p class="elements-empty">Belum ada file</p>'; return; }
  listEl.innerHTML = '';
  queue.forEach((file, idx) => {
    const item = document.createElement('div');
    item.className = 'element-item';
    item.innerHTML = `
      <span class="element-item-icon">📄</span>
      <span class="element-item-label" title="${file.name}">${idx + 1}. ${file.name}</span>
      <span style="font-size:10px;color:var(--muted);flex-shrink:0">${formatFileSize(file.size)}</span>
      <button class="element-del-btn" data-idx="${idx}">✕</button>`;
    item.querySelector('.element-del-btn').addEventListener('click', () => onRemove(idx));
    listEl.appendChild(item);
  });
}

// ─── GENERIC UPLOAD ZONE WIRING ───────────────
// config: { dropzone, input, accept, multiple, onFiles(FileList) }
function setupGenericUpload(config) {
  const { dropzone, input, onFiles } = config;
  dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('drag-over'); });
  dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
  dropzone.addEventListener('drop', e => {
    e.preventDefault(); dropzone.classList.remove('drag-over');
    if (e.dataTransfer.files.length) onFiles(e.dataTransfer.files);
  });
  input.addEventListener('change', e => { if (e.target.files.length) onFiles(e.target.files); });
}

window.showToast = showToast;
