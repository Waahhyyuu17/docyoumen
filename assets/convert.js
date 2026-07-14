/* ═══════════════════════════════════════════════
   DocYouMen — Konversi server-side (PDF↔Word, dst)
   Semua tool di sini punya alur identik: upload 1 file →
   POST ke endpoint PHP → endpoint stream balik file hasil
   langsung (bukan JSON+URL) → download. Ditangani generik
   lewat CONVERTERS + runConversion(), bukan 1 fungsi per tool.
═══════════════════════════════════════════════ */
'use strict';

// prefix harus sama persis dengan prefix id di index.php (lihat $simpleConverters).
const CONVERTERS = [
  {
    prefix: 'pdf2word', endpoint: 'api/pdf_to_word.php', fieldName: 'pdf',
    busyMsg: 'Mengubah PDF ke Word...', validate: f => f.type === 'application/pdf', errMsg: 'File harus PDF!',
  },
  {
    prefix: 'word2pdf', endpoint: 'api/word_to_pdf.php', fieldName: 'docx',
    busyMsg: 'Mengubah Word ke PDF...', validate: f => /\.docx?$/i.test(f.name), errMsg: 'File harus .doc atau .docx!',
  },
  {
    prefix: 'excel2pdf', endpoint: 'api/excel_to_pdf.php', fieldName: 'excel',
    busyMsg: 'Mengubah Excel ke PDF...', validate: f => /\.xlsx?$/i.test(f.name), errMsg: 'File harus .xlsx atau .xls!',
  },
  {
    prefix: 'pdf2excel', endpoint: 'api/pdf_to_excel.php', fieldName: 'pdf',
    busyMsg: 'Mengubah PDF ke Excel...', validate: f => f.type === 'application/pdf', errMsg: 'File harus PDF!',
  },
  {
    prefix: 'ppt2pdf', endpoint: 'api/ppt_to_pdf.php', fieldName: 'ppt',
    busyMsg: 'Mengubah PowerPoint ke PDF...', validate: f => /\.pptx?$/i.test(f.name), errMsg: 'File harus .pptx atau .ppt!',
  },
  {
    prefix: 'pdf2ppt', endpoint: 'api/pdf_to_ppt.php', fieldName: 'pdf',
    busyMsg: 'Mengubah PDF ke PowerPoint...', validate: f => f.type === 'application/pdf', errMsg: 'File harus PDF!',
  },
];

document.addEventListener('DOMContentLoaded', () => {
  CONVERTERS.forEach(setupConverterTool);
});

function setupConverterTool(cfg) {
  const p = cfg.prefix;
  const dropzone = document.getElementById(p + 'Dropzone');
  const input = document.getElementById(p + 'FileInput');
  if (!dropzone || !input) return; // overlay belum ada di halaman ini
  setupGenericUpload({
    dropzone, input, onFiles: files => {
      const f = files[0];
      if (!f || !cfg.validate(f)) { showToast(cfg.errMsg, 'error'); return; }
      document.getElementById(p + 'FileInfo').style.display = 'flex';
      document.getElementById(p + 'FileName').textContent = f.name;
      document.getElementById(p + 'ResultWrap').style.display = 'none';
      document.getElementById(p + 'RunBtn').style.display = 'flex';
      document.getElementById(p + 'RunBtn').onclick = () => runConversion({
        file: f,
        endpoint: cfg.endpoint,
        fieldName: cfg.fieldName,
        resultWrapId: p + 'ResultWrap',
        resultLinkId: p + 'ResultLink',
        busyMsg: cfg.busyMsg,
      });
    }
  });
}

// Endpoint mengirim file hasil langsung sebagai body respons (bukan JSON
// berisi URL) — server tidak menyisakan file yang menunggu diunduh.
async function runConversion({ file, endpoint, fieldName, resultWrapId, resultLinkId, busyMsg }) {
  showBusy(busyMsg);
  try {
    const fd = new FormData(); fd.append(fieldName, file);
    const res = await fetch(endpoint, { method: 'POST', body: fd });
    const ctype = res.headers.get('content-type') || '';

    if (ctype.includes('application/json')) {
      const data = await res.json();
      throw new Error(data.error || 'Konversi gagal');
    }
    if (!res.ok) throw new Error('HTTP ' + res.status);

    const blob = await res.blob();
    const cd = res.headers.get('content-disposition') || '';
    const match = cd.match(/filename="?([^"]+)"?/);
    const filename = match ? match[1] : 'hasil-konversi';

    const link = document.getElementById(resultLinkId);
    const url = URL.createObjectURL(blob);
    link.href = url;
    link.download = filename;
    link.textContent = '⬇ Download ' + filename;
    document.getElementById(resultWrapId).style.display = 'block';
    downloadBlob(blob, filename);
    showToast('✅ Konversi berhasil', 'success');
  } catch (err) {
    showToast('Gagal: ' + err.message, 'error');
  } finally {
    hideBusy();
  }
}
