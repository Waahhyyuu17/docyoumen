/* ═══════════════════════════════════════════════
   DocYouMen — Cek Kemiripan Dokumen & Cek Pola AI
   100% client-side. BUKAN pengecekan ke internet (Kemiripan)
   dan BUKAN detektor AI akurat (Pola AI) — keduanya heuristik
   sederhana, lihat disclaimer di masing-masing tool.
═══════════════════════════════════════════════ */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  setupCompareDocTool();
  setupAiCheckTool();
});

// ─── EKSTRAKSI TEKS (PDF via pdf.js, TXT via File.text()) ─
async function extractTextFromFile(file) {
  if (file.type === 'application/pdf' || /\.pdf$/i.test(file.name)) {
    const buf = await file.arrayBuffer();
    const pdfDoc = await pdfjsLib.getDocument({ data: buf }).promise;
    let text = '';
    for (let p = 1; p <= pdfDoc.numPages; p++) {
      const page = await pdfDoc.getPage(p);
      const content = await page.getTextContent();
      text += content.items.map(it => it.str).join(' ') + '\n';
    }
    return text;
  }
  return file.text();
}

function normalizeText(text) {
  return text.toLowerCase().replace(/[^\p{L}\p{N}\s]/gu, ' ').replace(/\s+/g, ' ').trim();
}

function getShingles(text, n = 5) {
  const words = text.split(' ').filter(Boolean);
  const shingles = new Set();
  for (let i = 0; i <= words.length - n; i++) shingles.add(words.slice(i, i + n).join(' '));
  if (!shingles.size && words.length) shingles.add(words.join(' '));
  return shingles;
}

function jaccardSimilarity(setA, setB) {
  if (!setA.size && !setB.size) return 0;
  let intersection = 0;
  setA.forEach(x => { if (setB.has(x)) intersection++; });
  const unionSize = setA.size + setB.size - intersection;
  return unionSize === 0 ? 0 : intersection / unionSize;
}

/* ══════════════════ CEK KEMIRIPAN DOKUMEN ══════════════════ */
const compareState = { fileA: null, fileB: null };

function setupCompareDocTool() {
  const dzA = document.getElementById('compareDropzoneA');
  const inA = document.getElementById('compareFileInputA');
  const dzB = document.getElementById('compareDropzoneB');
  const inB = document.getElementById('compareFileInputB');
  if (!dzA) return;

  setupGenericUpload({
    dropzone: dzA, input: inA, onFiles: files => {
      compareState.fileA = files[0];
      document.getElementById('compareFileNameA').textContent = files[0].name;
      updateCompareRunBtn();
    }
  });
  setupGenericUpload({
    dropzone: dzB, input: inB, onFiles: files => {
      compareState.fileB = files[0];
      document.getElementById('compareFileNameB').textContent = files[0].name;
      updateCompareRunBtn();
    }
  });
  document.getElementById('compareRunBtn').addEventListener('click', runCompareDocs);
}

function updateCompareRunBtn() {
  document.getElementById('compareRunBtn').disabled = !(compareState.fileA && compareState.fileB);
}

async function runCompareDocs() {
  if (!compareState.fileA || !compareState.fileB) return;
  showBusy('Membandingkan dokumen...');
  try {
    const [textA, textB] = await Promise.all([
      extractTextFromFile(compareState.fileA),
      extractTextFromFile(compareState.fileB),
    ]);
    if (!textA.trim() || !textB.trim()) {
      showToast('Salah satu dokumen tidak berisi teks yang bisa dibaca.', 'error');
      return;
    }

    const normA = normalizeText(textA), normB = normalizeText(textB);
    const shinglesA = getShingles(normA, 5), shinglesB = getShingles(normB, 5);
    const similarity = jaccardSimilarity(shinglesA, shinglesB);

    const sentences = textA.split(/(?<=[.!?])\s+/).map(s => s.trim()).filter(s => s.split(/\s+/).length >= 6);
    const matches = [];
    for (const sent of sentences) {
      const sentShingles = getShingles(normalizeText(sent), 5);
      if (!sentShingles.size) continue;
      let overlap = 0;
      sentShingles.forEach(s => { if (shinglesB.has(s)) overlap++; });
      if (overlap / sentShingles.size >= 0.5) matches.push(sent);
      if (matches.length >= 20) break;
    }

    renderCompareResult(similarity, matches);
  } catch (err) {
    showToast('Gagal membandingkan: ' + err.message, 'error');
  } finally {
    hideBusy();
  }
}

function renderCompareResult(similarity, matches) {
  const pct = Math.round(similarity * 100);
  document.getElementById('compareResultWrap').style.display = 'block';
  document.getElementById('compareResultEmpty').style.display = 'none';
  document.getElementById('comparePercent').textContent = pct + '%';

  const list = document.getElementById('compareMatchList');
  list.innerHTML = '';
  if (!matches.length) {
    list.innerHTML = '<p class="elements-empty">Tidak ada kalimat yang cocok signifikan.</p>';
  } else {
    matches.forEach(sent => {
      const item = document.createElement('div');
      item.className = 'compare-match-item';
      item.textContent = sent;
      list.appendChild(item);
    });
  }
}

/* ══════════════════ CEK POLA AI (heuristik kasar) ══════════════════ */
const AI_PHRASES = [
  'furthermore', 'moreover', 'in conclusion', 'overall', 'additionally',
  'it is important to note', 'in summary', 'on the other hand',
  'secara keseluruhan', 'sebagai kesimpulan', 'di sisi lain',
  'dengan demikian', 'perlu dicatat bahwa', 'selain itu', 'oleh karena itu',
];

function setupAiCheckTool() {
  const dropzone = document.getElementById('aiCheckDropzone');
  const input = document.getElementById('aiCheckFileInput');
  const textarea = document.getElementById('aiCheckTextarea');
  if (!dropzone) return;

  setupGenericUpload({
    dropzone, input, onFiles: async files => {
      try {
        const text = await extractTextFromFile(files[0]);
        textarea.value = text.trim();
      } catch (err) {
        showToast('Gagal membaca file: ' + err.message, 'error');
      }
    }
  });
  document.getElementById('aiCheckRunBtn').addEventListener('click', runAiCheck);
}

function analyzeAiPattern(text) {
  const sentences = text.split(/(?<=[.!?])\s+/).map(s => s.trim()).filter(Boolean);
  const words = text.toLowerCase().match(/[\p{L}\p{N}']+/gu) || [];
  const uniqueWords = new Set(words);
  const ttr = words.length ? uniqueWords.size / words.length : 0;

  const sentLengths = sentences.map(s => (s.match(/[\p{L}\p{N}']+/gu) || []).length).filter(n => n > 0);
  const avgLen = sentLengths.length ? sentLengths.reduce((a, b) => a + b, 0) / sentLengths.length : 0;
  const variance = sentLengths.length ? sentLengths.reduce((a, b) => a + (b - avgLen) ** 2, 0) / sentLengths.length : 0;
  const burstiness = avgLen ? Math.sqrt(variance) / avgLen : 0;

  const lowerText = text.toLowerCase();
  const phraseHits = AI_PHRASES.reduce((n, p) => n + (lowerText.includes(p) ? 1 : 0), 0);

  let score = 0;
  score += (1 - Math.min(burstiness / 0.6, 1)) * 40;
  score += (ttr < 0.45 ? (0.45 - ttr) / 0.45 : 0) * 25;
  score += Math.min(phraseHits * 7, 35);
  score = Math.round(Math.min(100, Math.max(0, score)));

  return {
    score,
    wordCount: words.length,
    avgSentenceLen: avgLen.toFixed(1),
    vocabDiversity: Math.round(ttr * 100),
    burstiness: burstiness.toFixed(2),
    phraseHits,
  };
}

function runAiCheck() {
  const text = document.getElementById('aiCheckTextarea').value.trim();
  if (!text || text.split(/\s+/).length < 30) {
    showToast('Isi teks minimal ~30 kata untuk analisis yang lebih berarti.', 'error');
    return;
  }
  const result = analyzeAiPattern(text);

  document.getElementById('aiCheckResultWrap').style.display = 'block';
  document.getElementById('aiCheckScore').textContent = result.score;
  document.getElementById('aiCheckDetail').innerHTML = `
    <div>Jumlah kata: <strong>${result.wordCount}</strong></div>
    <div>Rata-rata panjang kalimat: <strong>${result.avgSentenceLen} kata</strong></div>
    <div>Keragaman kosakata: <strong>${result.vocabDiversity}%</strong></div>
    <div>Variasi panjang kalimat (burstiness): <strong>${result.burstiness}</strong></div>
    <div>Frasa umum ala-AI ditemukan: <strong>${result.phraseHits}</strong></div>
  `;
}
