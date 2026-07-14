<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DocYouMen — PDF Editor & Signature</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cabinet+Grotesk:wght@400;500;700;800;900&family=Instrument+Sans:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- Background effects -->
<div class="bg-grid"></div>
<div class="bg-glow bg-glow-1"></div>
<div class="bg-glow bg-glow-2"></div>
<div class="bg-glow bg-glow-3"></div>

<div class="page-wrap">

  <!-- HEADER -->
  <header class="site-header">
    <div class="header-inner">
      <div class="logo">
        <div class="logo-mark">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
            <path d="M14 2v6h6M9 13h6M9 17h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <span class="logo-text">Doc<em>YouMen</em></span>
      </div>
      <nav class="header-nav">
        <a href="#toolGrid" class="nav-link">Tools</a>
        <span class="nav-tag">Gratis</span>
      </nav>
    </div>
  </header>

  <!-- HERO SECTION -->
  <section class="hero">
    <div class="hero-intro">
      <div class="hero-eyebrow">
        <span class="eyebrow-dot"></span>
        Editor & Konversi PDF — Gratis
      </div>
      <h1 class="hero-title">
        Semua Tools Dokumen Anda<br>
        <span class="gradient-text">Dalam Satu Tempat</span>
      </h1>
      <p class="hero-desc">
        Edit, tanda tangan, konversi, dan analisis dokumen — semua diproses langsung di browser Anda.
      </p>
    </div>

    <!-- SEARCH & FILTER -->
    <div class="tool-search-wrap">
      <svg class="tool-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      <input type="text" class="tool-search-input" id="toolSearchInput" placeholder="Cari fitur... (cth: Split, Kompres, Excel)">
    </div>
    <div class="category-pills" id="categoryPills">
      <button class="category-pill active" data-category="all">Semua</button>
      <button class="category-pill" data-category="sign">Tanda Tangan & Anotasi</button>
      <button class="category-pill" data-category="organize">Organisir PDF</button>
      <button class="category-pill" data-category="convert">Konversi File</button>
      <button class="category-pill" data-category="analysis">Analisis Teks</button>
    </div>

    <!-- TOOL GRID -->
    <div class="tool-grid" id="toolGrid">
      <div class="tool-card" data-overlay="editorOverlay" data-category="sign">
        <div class="tool-card-icon c-indigo"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M3 17c3.333-5.333 6.667-8 10-8s6.667 2.667 10 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></div>
        <h3>Tanda Tangan</h3>
        <p>Edit, tanda tangan & stempel PDF</p>
      </div>
      <div class="tool-card" data-overlay="splitOverlay" data-category="organize">
        <div class="tool-card-icon c-green"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><circle cx="6" cy="6" r="3" stroke="currentColor" stroke-width="2"/><circle cx="6" cy="18" r="3" stroke="currentColor" stroke-width="2"/><line x1="20" y1="4" x2="8.12" y2="15.88" stroke="currentColor" stroke-width="2"/><line x1="14.47" y1="14.48" x2="20" y2="20" stroke="currentColor" stroke-width="2"/><line x1="8.12" y1="8.12" x2="12" y2="12" stroke="currentColor" stroke-width="2"/></svg></div>
        <h3>Split PDF</h3>
        <p>Pisah PDF jadi beberapa file</p>
      </div>
      <div class="tool-card" data-overlay="mergeOverlay" data-category="organize">
        <div class="tool-card-icon c-green"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><polygon points="12 2 2 7 12 12 22 7 12 2" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><polyline points="2 17 12 22 22 17" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><polyline points="2 12 12 17 22 12" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg></div>
        <h3>Merge PDF</h3>
        <p>Gabungkan beberapa PDF jadi satu</p>
      </div>
      <div class="tool-card" data-overlay="pdf2jpgOverlay" data-category="convert">
        <div class="tool-card-icon c-pink"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/><circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="2"/><path d="M21 15l-5-5L5 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        <h3>PDF ke JPG</h3>
        <p>Ubah tiap halaman PDF jadi gambar</p>
      </div>
      <div class="tool-card" data-overlay="jpg2pdfOverlay" data-category="convert">
        <div class="tool-card-icon c-pink"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="1.5"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="1.5"/><path d="M12 18v-6M9 15l3-3 3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        <h3>JPG ke PDF</h3>
        <p>Gabungkan gambar jadi satu PDF</p>
      </div>
      <div class="tool-card" data-overlay="pdf2wordOverlay" data-category="convert">
        <div class="tool-card-icon c-amber"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="1.5"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="1.5"/><path d="M9 13h6M9 17h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></div>
        <h3>PDF ke Word</h3>
        <p>Ekstrak teks PDF ke .docx</p>
      </div>
      <div class="tool-card" data-overlay="word2pdfOverlay" data-category="convert">
        <div class="tool-card-icon c-amber"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M4 7V4h16v3M9 20h6M12 4v16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></div>
        <h3>Word ke PDF</h3>
        <p>Konversi dokumen .docx ke PDF</p>
      </div>
      <div class="tool-card" data-overlay="compressOverlay" data-category="organize">
        <div class="tool-card-icon c-indigo"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><polyline points="4 14 10 14 10 20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="20 10 14 10 14 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="14" y1="10" x2="21" y2="3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="3" y1="21" x2="10" y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></div>
        <h3>Compress PDF</h3>
        <p>Perkecil ukuran file PDF</p>
      </div>
      <div class="tool-card" data-overlay="pagenumOverlay" data-category="organize">
        <div class="tool-card-icon c-green"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><line x1="4" y1="9" x2="20" y2="9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="4" y1="15" x2="20" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="10" y1="3" x2="8" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="16" y1="3" x2="14" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></div>
        <h3>Nomor Halaman</h3>
        <p>Tambah nomor halaman otomatis</p>
      </div>
      <div class="tool-card" data-overlay="excel2pdfOverlay" data-category="convert">
        <div class="tool-card-icon c-amber"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/><path d="M3 9h18M9 3v18" stroke="currentColor" stroke-width="2"/></svg></div>
        <h3>Excel ke PDF</h3>
        <p>Konversi spreadsheet .xlsx ke PDF</p>
      </div>
      <div class="tool-card" data-overlay="pdf2excelOverlay" data-category="convert">
        <div class="tool-card-icon c-amber"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/><path d="M3 9h18M9 3v18" stroke="currentColor" stroke-width="2"/><path d="M21 15l-5-5L5 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.5"/></svg></div>
        <h3>PDF ke Excel</h3>
        <p>Ekstrak teks PDF ke .xlsx</p>
      </div>
      <div class="tool-card" data-overlay="ppt2pdfOverlay" data-category="convert">
        <div class="tool-card-icon c-amber"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><rect x="2" y="4" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"/><path d="M8 21h8M12 18v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></div>
        <h3>PowerPoint ke PDF</h3>
        <p>Konversi .pptx ke PDF</p>
      </div>
      <div class="tool-card" data-overlay="pdf2pptOverlay" data-category="convert">
        <div class="tool-card-icon c-amber"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><rect x="2" y="4" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"/><path d="M8 21h8M12 18v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M21 15l-5-5L5 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.5"/></svg></div>
        <h3>PDF ke PowerPoint</h3>
        <p>Ekstrak teks PDF ke .pptx</p>
      </div>
      <div class="tool-card" data-overlay="editTextOverlay" data-category="sign">
        <div class="tool-card-icon c-indigo"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        <h3>Edit PDF</h3>
        <p>Ubah teks yang sudah ada di PDF</p>
      </div>
      <div class="tool-card" data-overlay="removePagesOverlay" data-category="organize">
        <div class="tool-card-icon c-green"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="1.5"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="1.5"/><line x1="9" y1="13" x2="15" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="15" y1="13" x2="9" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></div>
        <h3>Hapus Halaman</h3>
        <p>Buang halaman tertentu dari PDF</p>
      </div>
      <div class="tool-card" data-overlay="compareDocOverlay" data-category="analysis">
        <div class="tool-card-icon c-pink"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="8" height="18" rx="1" stroke="currentColor" stroke-width="2"/><rect x="13" y="3" width="8" height="18" rx="1" stroke="currentColor" stroke-width="2"/><path d="M11 8l2 4-2 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        <h3>Cek Kemiripan Dokumen</h3>
        <p>Bandingkan 2 dokumen yang diupload (bukan cek ke internet)</p>
      </div>
      <div class="tool-card" data-overlay="aiCheckOverlay" data-category="analysis">
        <div class="tool-card-icon c-pink"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 8v4l2.5 2.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        <h3>Cek Pola AI</h3>
        <p>Perkiraan kasar pola teks (bukan detektor akurat)</p>
      </div>
    </div>
    <p class="tool-empty-msg" id="toolEmptyMsg" style="display:none;">Tidak ada tool yang cocok. Coba kata kunci lain.</p>
  </section>

</div>

<!-- EDITOR MODAL (shown after upload) -->
<div class="editor-overlay" id="editorOverlay" style="display:none;">
  <div class="editor-layout">

    <!-- LEFT SIDEBAR: TOOLS -->
    <aside class="tools-panel">
      <div class="tools-header">
        <button class="back-btn" id="backBtn" title="Kembali">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="tools-logo">
          <span>Doc<em>YouMen</em></span>
        </div>
      </div>

      <!-- STEP 1: upload PDF -->
      <div id="signUploadStep" style="padding:16px;">
        <div class="upload-zone" id="uploadZone" style="padding:32px 20px;">
          <div class="upload-icon-wrap">
            <div class="upload-icon-ring"></div>
            <svg class="upload-icon-svg" width="32" height="32" viewBox="0 0 24 24" fill="none">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="1.5"/>
              <path d="M14 2v6h6" stroke="currentColor" stroke-width="1.5"/>
              <path d="M12 18v-6M9 15l3-3 3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3 class="upload-title" style="font-size:16px;">Drop PDF di sini</h3>
          <p class="upload-sub">Maks 50MB</p>
          <label class="upload-btn-label">
            <input type="file" id="pdfFileInput" accept=".pdf" hidden>
            <span class="btn-primary" style="padding:10px 20px;font-size:13px;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Pilih File PDF
            </span>
          </label>
          <div class="upload-progress" id="uploadProgress" style="display:none;">
            <div class="upload-progress-bar-wrap"><div class="upload-progress-bar" id="uploadProgressBar"></div></div>
            <span id="uploadProgressText">Mengupload...</span>
          </div>
        </div>
      </div>

      <!-- STEP 2: editor workspace (tampil setelah PDF dimuat) -->
      <div id="signWorkStep" style="display:none;flex:1;flex-direction:column;overflow:hidden;">
      <div class="tools-file-info" id="toolsFileInfo">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="2"/></svg>
        <span id="toolsFileName">document.pdf</span>
      </div>

      <!-- TOOL TABS -->
      <div class="tool-tabs">
        <button class="tool-tab active" data-tool="text">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 7V4h16v3M9 20h6M12 4v16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          Teks
        </button>
        <button class="tool-tab" data-tool="signature">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 17c3.333-5.333 6.667-8 10-8s6.667 2.667 10 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          Tanda Tangan
        </button>
        <button class="tool-tab" data-tool="stamp">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="5" stroke="currentColor" stroke-width="2"/><path d="M3 21h18M6 21v-3a6 6 0 0 1 12 0v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          Stempel
        </button>
      </div>

      <!-- TEXT TOOL PANEL -->
      <div class="tool-panel active" id="panelText">
        <div class="panel-section">
          <label class="panel-label">Teks</label>
          <textarea class="panel-textarea" id="textContent" placeholder="Ketik teks di sini...">Teks Contoh</textarea>
        </div>

        <div class="panel-section">
          <label class="panel-label">Font</label>
          <select class="panel-select" id="fontFamily">
            <option value="Arial">Arial</option>
            <option value="'Times New Roman'">Times New Roman</option>
            <option value="'Courier New'">Courier New</option>
            <option value="Georgia">Georgia</option>
            <option value="Verdana">Verdana</option>
            <option value="'Trebuchet MS'">Trebuchet MS</option>
            <option value="Impact">Impact</option>
          </select>
        </div>

        <div class="panel-row">
          <div class="panel-section half">
            <label class="panel-label">Ukuran</label>
            <div class="number-input-wrap">
              <button class="num-btn" onclick="adjustFontSize(-1)">−</button>
              <input type="number" class="panel-number" id="fontSize" value="16" min="6" max="120">
              <button class="num-btn" onclick="adjustFontSize(1)">+</button>
            </div>
          </div>
          <div class="panel-section half">
            <label class="panel-label">Warna</label>
            <div class="color-input-wrap">
              <input type="color" id="fontColor" value="#1a1a2e" class="color-picker">
              <span class="color-preview" id="colorPreview" style="background:#1a1a2e;"></span>
            </div>
          </div>
        </div>

        <div class="panel-section">
          <label class="panel-label">Gaya</label>
          <div class="style-btns">
            <button class="style-btn" id="btnBold" data-style="bold" title="Bold">
              <b>B</b>
            </button>
            <button class="style-btn" id="btnItalic" data-style="italic" title="Italic">
              <i>I</i>
            </button>
            <button class="style-btn" id="btnUnderline" data-style="underline" title="Underline">
              <u>U</u>
            </button>
          </div>
        </div>

        <div class="panel-section">
          <label class="panel-label">Opacity</label>
          <input type="range" class="panel-range" id="textOpacity" min="10" max="100" value="100">
          <span class="range-val" id="textOpacityVal">100%</span>
        </div>

        <button class="btn-add-element" id="addTextBtn">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          Klik PDF untuk tambah teks
        </button>
      </div>

      <!-- SIGNATURE TOOL PANEL -->
      <div class="tool-panel" id="panelSignature">
        <div class="sig-tabs">
          <button class="sig-tab active" data-sigtab="draw">✏️ Gambar</button>
          <button class="sig-tab" data-sigtab="upload">📁 Upload</button>
          <button class="sig-tab" data-sigtab="mobile">📱 HP</button>
        </div>

        <!-- Draw -->
        <div class="sig-panel active" id="sigDraw">
          <div class="panel-section">
            <label class="panel-label">Gambar Tanda Tangan</label>
            <div class="sig-canvas-wrap">
              <canvas id="sigCanvas" width="220" height="110"></canvas>
              <div class="sig-canvas-hint">Gambar di sini</div>
            </div>
            <div class="sig-canvas-actions">
              <div class="color-input-wrap" style="gap:6px;">
                <input type="color" id="sigColor" value="#1a1a2e" class="color-picker">
                <span style="font-size:12px;color:var(--muted);">Warna</span>
              </div>
              <div class="number-input-wrap" style="width:auto;gap:4px;">
                <input type="number" id="sigThickness" value="2" min="1" max="10" class="panel-number" style="width:50px;">
                <span style="font-size:12px;color:var(--muted);">Ketebalan</span>
              </div>
              <button class="btn-clear" onclick="clearSignatureCanvas()">Hapus</button>
            </div>
          </div>
          <button class="btn-add-element" onclick="useDrawnSignature()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Gunakan Tanda Tangan
          </button>
        </div>

        <!-- Upload -->
        <div class="sig-panel" id="sigUpload">
          <div class="panel-section">
            <label class="panel-label">Upload Tanda Tangan / Stempel</label>
            <label class="upload-sig-zone">
              <input type="file" id="sigImageInput" accept="image/*" hidden>
              <div class="upload-sig-inner" id="sigUploadPreview">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <span>Klik atau drag gambar</span>
                <small>PNG, JPG, SVG</small>
              </div>
            </label>
          </div>
          <div class="panel-section" id="sigUploadedPreviewWrap" style="display:none;">
            <img id="sigUploadedImg" src="" alt="Signature" style="max-width:100%;border-radius:8px;border:1px solid var(--border);">
          </div>
          <button class="btn-add-element" id="useUploadedSigBtn" style="display:none;" onclick="useUploadedSignature()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Gunakan Gambar Ini
          </button>
        </div>

        <!-- Mobile QR -->
        <div class="sig-panel" id="sigMobile">
          <div class="panel-section">
            <label class="panel-label">Tanda Tangan via Handphone</label>
            <p class="panel-desc">Scan QR code di bawah dengan HP, lalu tanda tangan di layar HP Anda. Tanda tangan akan langsung muncul di PDF.</p>
          </div>
          <div class="qr-wrap" id="qrWrap">
            <div class="qr-loading">
              <div class="spinner"></div>
              <span>Membuat QR Code...</span>
            </div>
          </div>
          <div class="qr-status" id="qrStatus">
            <div class="qr-status-dot" id="qrStatusDot"></div>
            <span id="qrStatusText">Menunggu tanda tangan...</span>
          </div>
          <button class="btn-secondary-sm" id="refreshQrBtn" onclick="generateMobileQR()">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Refresh QR
          </button>
        </div>
      </div>

      <!-- STAMP TOOL PANEL -->
      <div class="tool-panel" id="panelStamp">
        <div class="panel-section">
          <label class="panel-label">Upload Stempel</label>
          <label class="upload-sig-zone">
            <input type="file" id="stampImageInput" accept="image/*" hidden>
            <div class="upload-sig-inner" id="stampUploadPreview">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="5" stroke="currentColor" stroke-width="1.5"/><path d="M3 21h18M6 21v-3a6 6 0 0 1 12 0v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              <span>Upload Stempel</span>
              <small>PNG transparan lebih baik</small>
            </div>
          </label>
        </div>

        <div class="panel-section" id="stampPreviewWrap" style="display:none;">
          <img id="stampPreviewImg" src="" alt="Stamp" style="max-width:100%;border-radius:8px;border:1px solid var(--border);">
        </div>

        <div class="panel-section">
          <label class="panel-label">Opacity Stempel</label>
          <input type="range" class="panel-range" id="stampOpacity" min="10" max="100" value="80">
          <span class="range-val" id="stampOpacityVal">80%</span>
        </div>

        <div class="panel-section">
          <label class="panel-label">Ukuran</label>
          <input type="range" class="panel-range" id="stampSize" min="50" max="300" value="150">
          <span class="range-val" id="stampSizeVal">150px</span>
        </div>

        <button class="btn-add-element" id="useStampBtn" style="display:none;" onclick="useStamp()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Klik PDF untuk stempel
        </button>
        <p class="panel-desc" id="stampHint">Upload stempel terlebih dahulu</p>
      </div>

      <!-- ELEMENTS LIST -->
      <div class="elements-section">
        <div class="elements-header">
          <span>Elemen <span class="elements-count" id="elementsCount">0</span></span>
          <button class="btn-clear-all" id="clearAllBtn">Hapus Semua</button>
        </div>
        <div class="elements-list" id="elementsList">
          <p class="elements-empty">Belum ada elemen</p>
        </div>
      </div>
      </div><!-- /signWorkStep -->

    </aside>

    <!-- CENTER: PDF CANVAS AREA -->
    <main class="canvas-area" id="canvasArea">
      <div class="canvas-toolbar">
        <div class="canvas-toolbar-left">
          <button class="toolbar-btn" id="prevPageBtn" onclick="changePage(-1)" disabled>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </button>
          <span class="page-info">
            Halaman <strong id="currentPage">1</strong> dari <strong id="totalPages">1</strong>
          </span>
          <button class="toolbar-btn" id="nextPageBtn" onclick="changePage(1)" disabled>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </button>
        </div>
        <div class="canvas-toolbar-center">
          <button class="toolbar-btn" onclick="zoomOut()" title="Zoom out">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35M8 11h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </button>
          <span class="zoom-val" id="zoomVal">100%</span>
          <button class="toolbar-btn" onclick="zoomIn()" title="Zoom in">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35M11 8v6M8 11h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </button>
          <button class="toolbar-btn" onclick="resetZoom()" title="Reset zoom">↺</button>
        </div>
        <div class="canvas-toolbar-right">
          <button class="btn-undo" id="undoBtn" onclick="undoElement()" disabled title="Undo">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M3 7v6h6M3 13c0-4.97 4.03-9 9-9a9 9 0 0 1 0 18H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Undo
          </button>
          <button class="btn-save" id="saveBtn" onclick="savePDF()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Simpan PDF
          </button>
        </div>
      </div>

      <!-- PDF RENDER + OVERLAY -->
      <div class="pdf-viewport" id="pdfViewport">
        <div class="pdf-container" id="pdfContainer">
          <canvas id="pdfCanvas"></canvas>
          <div class="elements-overlay" id="elementsOverlay"></div>
          <!-- Cursor indicator -->
          <div class="cursor-indicator" id="cursorIndicator" style="display:none;"></div>
        </div>
      </div>

      <!-- Mode indicator -->
      <div class="mode-indicator" id="modeIndicator" style="display:none;">
        <span id="modeIndicatorText">Mode: Tambah Teks — Klik di PDF</span>
        <button onclick="cancelAddMode()">✕ Batal</button>
      </div>
    </main>

  </div>
</div>

<!-- ═══ SPLIT PDF ═══ -->
<div class="editor-overlay" id="splitOverlay" style="display:none;">
  <div class="editor-layout">
    <aside class="tools-panel">
      <div class="tools-header">
        <button class="back-btn" data-back-overlay="splitOverlay" title="Kembali">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="tools-logo"><span>Split <em>PDF</em></span></div>
      </div>
      <div class="tools-file-info" id="splitFileInfo" style="display:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="2"/></svg>
        <span id="splitFileName"></span>
      </div>
      <div class="tool-panel active" style="flex:1;">
        <div id="splitUploadStep">
          <div class="panel-section">
            <label class="panel-label">Upload PDF</label>
            <p class="panel-desc">Pisah PDF jadi beberapa file berdasarkan halaman.</p>
          </div>
          <label class="upload-sig-zone">
            <input type="file" id="splitFileInput" accept=".pdf" hidden>
            <div class="upload-sig-inner" id="splitDropzone">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              <span>Klik atau drag PDF di sini</span>
              <small>Maks 50MB</small>
            </div>
          </label>
        </div>
        <div id="splitWorkStep" style="display:none;">
          <div class="panel-section">
            <label class="panel-label">Mode Split</label>
            <div class="mode-tabs">
              <button class="mode-tab active" data-splitmode="range">Range Halaman</button>
              <button class="mode-tab" data-splitmode="each">Tiap Halaman</button>
            </div>
          </div>
          <div class="panel-section" id="splitRangeSection">
            <label class="panel-label">Halaman (mis. 1-3,5,7-9)</label>
            <input type="text" class="panel-textarea" id="splitRangeInput" style="min-height:auto;" placeholder="1-3,5,7-9">
          </div>
          <button class="btn-add-element" id="splitRunBtn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Split & Download
          </button>
        </div>
      </div>
    </aside>
    <main class="canvas-area">
      <div class="canvas-toolbar"><div class="canvas-toolbar-left"><span class="page-info" id="splitPageInfo"></span></div></div>
      <div class="pdf-viewport"><div class="thumb-grid" id="splitThumbGrid"></div></div>
    </main>
  </div>
</div>

<!-- ═══ MERGE PDF ═══ -->
<div class="editor-overlay" id="mergeOverlay" style="display:none;">
  <div class="editor-layout">
    <aside class="tools-panel">
      <div class="tools-header">
        <button class="back-btn" data-back-overlay="mergeOverlay" title="Kembali">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="tools-logo"><span>Merge <em>PDF</em></span></div>
      </div>
      <div class="tool-panel active" style="flex:0 0 auto;">
        <div class="panel-section">
          <label class="panel-label">Upload PDF (minimal 2)</label>
          <p class="panel-desc">Urutan file di antrian = urutan halaman hasil gabungan.</p>
        </div>
        <label class="upload-sig-zone">
          <input type="file" id="mergeFileInput" accept=".pdf" multiple hidden>
          <div class="upload-sig-inner" id="mergeDropzone">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <span>Klik atau drag PDF di sini</span>
            <small>Bisa pilih lebih dari satu file</small>
          </div>
        </label>
      </div>
      <div class="elements-section" style="max-height:none;flex:1;">
        <div class="elements-header">
          <span>Antrian <span class="elements-count" id="mergeQueueCount">0</span></span>
        </div>
        <div class="elements-list" id="mergeQueueList">
          <p class="elements-empty">Belum ada file</p>
        </div>
        <button class="btn-add-element" id="mergeRunBtn" disabled style="margin-top:12px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Gabungkan & Download
        </button>
      </div>
    </aside>
    <main class="canvas-area">
      <div class="canvas-toolbar"><div class="canvas-toolbar-left"><span class="page-info">Gabungkan Beberapa PDF</span></div></div>
      <div class="pdf-viewport"><div class="empty-hint"><span class="empty-hint-icon">📑</span><p>Tambahkan minimal 2 file PDF di panel kiri, lalu klik "Gabungkan &amp; Download".</p></div></div>
    </main>
  </div>
</div>

<!-- ═══ PDF → JPG ═══ -->
<div class="editor-overlay" id="pdf2jpgOverlay" style="display:none;">
  <div class="editor-layout">
    <aside class="tools-panel">
      <div class="tools-header">
        <button class="back-btn" data-back-overlay="pdf2jpgOverlay" title="Kembali">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="tools-logo"><span>PDF ke <em>JPG</em></span></div>
      </div>
      <div class="tools-file-info" id="pdf2jpgFileInfo" style="display:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="2"/></svg>
        <span id="pdf2jpgFileName"></span>
      </div>
      <div class="tool-panel active" style="flex:1;">
        <div id="pdf2jpgUploadStep">
          <div class="panel-section">
            <label class="panel-label">Upload PDF</label>
            <p class="panel-desc">Setiap halaman akan diubah jadi gambar JPG.</p>
          </div>
          <label class="upload-sig-zone">
            <input type="file" id="pdf2jpgFileInput" accept=".pdf" hidden>
            <div class="upload-sig-inner" id="pdf2jpgDropzone">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              <span>Klik atau drag PDF di sini</span>
              <small>Maks 50MB</small>
            </div>
          </label>
        </div>
        <div id="pdf2jpgWorkStep" style="display:none;">
          <div class="panel-section">
            <label class="panel-label">Kualitas JPG</label>
            <input type="range" class="panel-range" id="pdf2jpgQuality" min="30" max="100" value="90">
            <span class="range-val" id="pdf2jpgQualityVal">90%</span>
          </div>
          <button class="btn-add-element" id="pdf2jpgRunBtn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Convert & Download
          </button>
        </div>
      </div>
    </aside>
    <main class="canvas-area">
      <div class="canvas-toolbar"><div class="canvas-toolbar-left"><span class="page-info" id="pdf2jpgPageInfo"></span></div></div>
      <div class="pdf-viewport"><div class="thumb-grid" id="pdf2jpgThumbGrid"></div></div>
    </main>
  </div>
</div>

<!-- ═══ JPG → PDF ═══ -->
<div class="editor-overlay" id="jpg2pdfOverlay" style="display:none;">
  <div class="editor-layout">
    <aside class="tools-panel">
      <div class="tools-header">
        <button class="back-btn" data-back-overlay="jpg2pdfOverlay" title="Kembali">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="tools-logo"><span>JPG ke <em>PDF</em></span></div>
      </div>
      <div class="tool-panel active" style="flex:0 0 auto;">
        <div class="panel-section">
          <label class="panel-label">Upload Gambar</label>
          <p class="panel-desc">Urutan file di antrian = urutan halaman PDF hasil.</p>
        </div>
        <label class="upload-sig-zone">
          <input type="file" id="jpg2pdfFileInput" accept="image/*" multiple hidden>
          <div class="upload-sig-inner" id="jpg2pdfDropzone">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <span>Klik atau drag gambar di sini</span>
            <small>PNG, JPG — bisa lebih dari satu</small>
          </div>
        </label>
      </div>
      <div class="elements-section" style="max-height:none;flex:1;">
        <div class="elements-header">
          <span>Antrian <span class="elements-count" id="jpg2pdfQueueCount">0</span></span>
        </div>
        <div class="elements-list" id="jpg2pdfQueueList">
          <p class="elements-empty">Belum ada gambar</p>
        </div>
        <button class="btn-add-element" id="jpg2pdfRunBtn" disabled style="margin-top:12px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Buat PDF & Download
        </button>
      </div>
    </aside>
    <main class="canvas-area">
      <div class="canvas-toolbar"><div class="canvas-toolbar-left"><span class="page-info">Gabungkan Gambar jadi PDF</span></div></div>
      <div class="pdf-viewport"><div class="empty-hint"><span class="empty-hint-icon">🖼️</span><p>Tambahkan gambar di panel kiri, atur urutan, lalu klik "Buat PDF &amp; Download".</p></div></div>
    </main>
  </div>
</div>

<!-- ═══ KONVERSI SEDERHANA (upload → proses server → download) ═══
     Semua tool di sini punya shell identik, jadi digenerate dari 1 array
     konfigurasi + 1 loop, bukan ditulis manual per tool. -->
<?php
$simpleConverters = [
  [
    'id' => 'pdf2wordOverlay', 'titleHtml' => 'PDF ke <em>Word</em>', 'canvasLabel' => 'PDF ke Word',
    'uploadLabel' => 'Upload PDF',
    'desc' => 'Teks akan diekstrak & disusun ulang jadi dokumen Word (.docx). Gambar & tabel pada PDF asli tidak ikut terkonversi.',
    'accept' => '.pdf', 'dropLabel' => 'Klik atau drag PDF di sini', 'sizeLabel' => 'Maks 30MB',
    'btnLabel' => 'Convert ke Word', 'emptyIcon' => '📝', 'emptyText' => 'Upload PDF di panel kiri untuk memulai konversi ke Word.',
    'prefix' => 'pdf2word',
  ],
  [
    'id' => 'word2pdfOverlay', 'titleHtml' => 'Word ke <em>PDF</em>', 'canvasLabel' => 'Word ke PDF',
    'uploadLabel' => 'Upload Dokumen Word',
    'desc' => 'Heading, bold/italic, list, dan tabel sederhana akan dipertahankan. Layout kompleks (kolom, gambar mengambang) mungkin tidak sempurna.',
    'accept' => '.doc,.docx', 'dropLabel' => 'Klik atau drag .doc/.docx di sini', 'sizeLabel' => 'Maks 30MB',
    'btnLabel' => 'Convert ke PDF', 'emptyIcon' => '📄', 'emptyText' => 'Upload dokumen Word di panel kiri untuk memulai konversi ke PDF.',
    'prefix' => 'word2pdf',
  ],
  [
    'id' => 'excel2pdfOverlay', 'titleHtml' => 'Excel ke <em>PDF</em>', 'canvasLabel' => 'Excel ke PDF',
    'uploadLabel' => 'Upload Spreadsheet',
    'desc' => 'Isi sel & format dasar akan dipertahankan. Spreadsheet lebar mungkin terpotong/kecil di halaman PDF.',
    'accept' => '.xlsx,.xls', 'dropLabel' => 'Klik atau drag .xlsx/.xls di sini', 'sizeLabel' => 'Maks 30MB',
    'btnLabel' => 'Convert ke PDF', 'emptyIcon' => '📊', 'emptyText' => 'Upload spreadsheet di panel kiri untuk memulai konversi ke PDF.',
    'prefix' => 'excel2pdf',
  ],
  [
    'id' => 'pdf2excelOverlay', 'titleHtml' => 'PDF ke <em>Excel</em>', 'canvasLabel' => 'PDF ke Excel',
    'uploadLabel' => 'Upload PDF',
    'desc' => 'Tiap baris teks PDF ditulis ke 1 baris sel kolom A (1 sheet per halaman). Ini BUKAN rekonstruksi tabel — cuma dump teks apa adanya.',
    'accept' => '.pdf', 'dropLabel' => 'Klik atau drag PDF di sini', 'sizeLabel' => 'Maks 30MB',
    'btnLabel' => 'Convert ke Excel', 'emptyIcon' => '📊', 'emptyText' => 'Upload PDF di panel kiri untuk memulai konversi ke Excel.',
    'prefix' => 'pdf2excel',
  ],
  [
    'id' => 'ppt2pdfOverlay', 'titleHtml' => 'PowerPoint ke <em>PDF</em>', 'canvasLabel' => 'PowerPoint ke PDF',
    'uploadLabel' => 'Upload Presentasi',
    'desc' => '⚠️ Hanya teks, gambar, dan tabel yang ikut terkonversi. Shape, chart, grouped object, dan background/master slide TIDAK didukung — untuk deck yang banyak shape/chart, hasil bisa terlihat kosong.',
    'accept' => '.pptx,.ppt', 'dropLabel' => 'Klik atau drag .pptx/.ppt di sini', 'sizeLabel' => 'Maks 30MB',
    'btnLabel' => 'Convert ke PDF', 'emptyIcon' => '📽️', 'emptyText' => 'Upload presentasi di panel kiri untuk memulai konversi ke PDF.',
    'prefix' => 'ppt2pdf',
  ],
  [
    'id' => 'pdf2pptOverlay', 'titleHtml' => 'PDF ke <em>PowerPoint</em>', 'canvasLabel' => 'PDF ke PowerPoint',
    'uploadLabel' => 'Upload PDF',
    'desc' => '1 slide per halaman PDF, berisi teks halaman itu dalam 1 textbox. Layout & gambar asli PDF tidak dipertahankan.',
    'accept' => '.pdf', 'dropLabel' => 'Klik atau drag PDF di sini', 'sizeLabel' => 'Maks 30MB',
    'btnLabel' => 'Convert ke PowerPoint', 'emptyIcon' => '📽️', 'emptyText' => 'Upload PDF di panel kiri untuk memulai konversi ke PowerPoint.',
    'prefix' => 'pdf2ppt',
  ],
];
foreach ($simpleConverters as $c): ?>
<div class="editor-overlay" id="<?= $c['id'] ?>" style="display:none;">
  <div class="editor-layout">
    <aside class="tools-panel">
      <div class="tools-header">
        <button class="back-btn" data-back-overlay="<?= $c['id'] ?>" title="Kembali">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="tools-logo"><span><?= $c['titleHtml'] ?></span></div>
      </div>
      <div class="tools-file-info" id="<?= $c['prefix'] ?>FileInfo" style="display:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="2"/></svg>
        <span id="<?= $c['prefix'] ?>FileName"></span>
      </div>
      <div class="tool-panel active" style="flex:1;">
        <div class="panel-section">
          <label class="panel-label"><?= htmlspecialchars($c['uploadLabel']) ?></label>
          <p class="panel-desc"><?= $c['desc'] ?></p>
        </div>
        <label class="upload-sig-zone">
          <input type="file" id="<?= $c['prefix'] ?>FileInput" accept="<?= $c['accept'] ?>" hidden>
          <div class="upload-sig-inner" id="<?= $c['prefix'] ?>Dropzone">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <span><?= htmlspecialchars($c['dropLabel']) ?></span>
            <small><?= htmlspecialchars($c['sizeLabel']) ?></small>
          </div>
        </label>
        <button class="btn-add-element" id="<?= $c['prefix'] ?>RunBtn" style="display:none;margin-top:16px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <?= htmlspecialchars($c['btnLabel']) ?>
        </button>
        <div class="panel-section" id="<?= $c['prefix'] ?>ResultWrap" style="display:none;margin-top:16px;">
          <a class="btn-save" id="<?= $c['prefix'] ?>ResultLink" href="#" style="text-decoration:none;justify-content:center;width:100%;" download>Download</a>
        </div>
      </div>
    </aside>
    <main class="canvas-area">
      <div class="canvas-toolbar"><div class="canvas-toolbar-left"><span class="page-info"><?= htmlspecialchars($c['canvasLabel']) ?></span></div></div>
      <div class="pdf-viewport"><div class="empty-hint"><span class="empty-hint-icon"><?= $c['emptyIcon'] ?></span><p><?= htmlspecialchars($c['emptyText']) ?></p></div></div>
    </main>
  </div>
</div>
<?php endforeach; ?>

<!-- ═══ COMPRESS PDF ═══ -->
<div class="editor-overlay" id="compressOverlay" style="display:none;">
  <div class="editor-layout">
    <aside class="tools-panel">
      <div class="tools-header">
        <button class="back-btn" data-back-overlay="compressOverlay" title="Kembali">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="tools-logo"><span>Compress <em>PDF</em></span></div>
      </div>
      <div class="tools-file-info" id="compressFileInfo" style="display:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="2"/></svg>
        <span id="compressFileName"></span>
      </div>
      <div class="tool-panel active" style="flex:1;">
        <div id="compressUploadStep">
          <div class="panel-section">
            <label class="panel-label">Upload PDF</label>
            <p class="panel-desc">Perkecil ukuran file PDF. Diproses langsung di browser, file tidak diunggah ke server.</p>
          </div>
          <label class="upload-sig-zone">
            <input type="file" id="compressFileInput" accept=".pdf" hidden>
            <div class="upload-sig-inner" id="compressDropzone">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              <span>Klik atau drag PDF di sini</span>
              <small>Maks 50MB</small>
            </div>
          </label>
        </div>
        <div id="compressWorkStep" style="display:none;">
          <div class="panel-section">
            <label class="panel-label">Ukuran Asli</label>
            <p class="panel-desc" id="compressOrigSize" style="margin-bottom:0;">-</p>
          </div>
          <div class="panel-section">
            <label class="panel-label">Mode Kompres</label>
            <div class="mode-tabs">
              <button class="mode-tab active" data-compressmode="light">Ringan</button>
              <button class="mode-tab" data-compressmode="aggressive">Kuat</button>
            </div>
            <p class="panel-desc" id="compressModeDesc">Pertahankan kualitas & teks asli. Cocok untuk PDF berbasis teks — pengurangan ukuran moderat.</p>
          </div>
          <div class="panel-section" id="compressQualitySection" style="display:none;">
            <label class="panel-label">Kualitas Gambar</label>
            <input type="range" class="panel-range" id="compressQuality" min="30" max="95" value="70">
            <span class="range-val" id="compressQualityVal">70%</span>
            <p class="panel-desc">Tiap halaman diubah jadi gambar terkompresi — ukuran jauh lebih kecil, tapi teks tidak lagi bisa di-select/di-cari.</p>
          </div>
          <button class="btn-add-element" id="compressRunBtn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Compress & Download
          </button>
        </div>
      </div>
    </aside>
    <main class="canvas-area">
      <div class="canvas-toolbar"><div class="canvas-toolbar-left"><span class="page-info">Compress PDF</span></div></div>
      <div class="pdf-viewport"><div class="empty-hint"><span class="empty-hint-icon">🗜️</span><p>Upload PDF di panel kiri untuk melihat ukuran & mulai kompres.</p></div></div>
    </main>
  </div>
</div>

<!-- ═══ NOMOR HALAMAN ═══ -->
<div class="editor-overlay" id="pagenumOverlay" style="display:none;">
  <div class="editor-layout">
    <aside class="tools-panel">
      <div class="tools-header">
        <button class="back-btn" data-back-overlay="pagenumOverlay" title="Kembali">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="tools-logo"><span>Nomor <em>Halaman</em></span></div>
      </div>
      <div class="tools-file-info" id="pagenumFileInfo" style="display:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="2"/></svg>
        <span id="pagenumFileName"></span>
      </div>
      <div class="tool-panel active" style="flex:1;">
        <div id="pagenumUploadStep">
          <div class="panel-section">
            <label class="panel-label">Upload PDF</label>
            <p class="panel-desc">Tambahkan nomor halaman otomatis ke tiap halaman PDF.</p>
          </div>
          <label class="upload-sig-zone">
            <input type="file" id="pagenumFileInput" accept=".pdf" hidden>
            <div class="upload-sig-inner" id="pagenumDropzone">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              <span>Klik atau drag PDF di sini</span>
              <small>Maks 50MB</small>
            </div>
          </label>
        </div>
        <div id="pagenumWorkStep" style="display:none;">
          <div class="panel-section">
            <label class="panel-label">Posisi</label>
            <select class="panel-select" id="pagenumPosition">
              <option value="bottom-center" selected>Bawah Tengah</option>
              <option value="bottom-right">Bawah Kanan</option>
              <option value="bottom-left">Bawah Kiri</option>
              <option value="top-center">Atas Tengah</option>
              <option value="top-right">Atas Kanan</option>
              <option value="top-left">Atas Kiri</option>
            </select>
          </div>
          <div class="panel-section">
            <label class="panel-label">Format (gunakan {n} & {total})</label>
            <input type="text" class="panel-textarea" id="pagenumFormat" style="min-height:auto;" value="Halaman {n} dari {total}">
          </div>
          <div class="panel-section">
            <label class="panel-label">Mulai dari nomor</label>
            <input type="number" class="panel-textarea" id="pagenumStart" style="min-height:auto;" value="1" min="1">
          </div>
          <button class="btn-add-element" id="pagenumRunBtn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Tambah Nomor & Download
          </button>
        </div>
      </div>
    </aside>
    <main class="canvas-area">
      <div class="canvas-toolbar"><div class="canvas-toolbar-left"><span class="page-info" id="pagenumPageInfo">Nomor Halaman Otomatis</span></div></div>
      <div class="pdf-viewport"><div class="thumb-grid" id="pagenumThumbGrid"></div></div>
    </main>
  </div>
</div>

<!-- ═══ HAPUS HALAMAN ═══ -->
<div class="editor-overlay" id="removePagesOverlay" style="display:none;">
  <div class="editor-layout">
    <aside class="tools-panel">
      <div class="tools-header">
        <button class="back-btn" data-back-overlay="removePagesOverlay" title="Kembali">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="tools-logo"><span>Hapus <em>Halaman</em></span></div>
      </div>
      <div class="tools-file-info" id="removePagesFileInfo" style="display:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="2"/></svg>
        <span id="removePagesFileName"></span>
      </div>
      <div class="tool-panel active" style="flex:1;">
        <div id="removePagesUploadStep">
          <div class="panel-section">
            <label class="panel-label">Upload PDF</label>
            <p class="panel-desc">Klik halaman di panel kanan untuk menandai halaman yang ingin dihapus.</p>
          </div>
          <label class="upload-sig-zone">
            <input type="file" id="removePagesFileInput" accept=".pdf" hidden>
            <div class="upload-sig-inner" id="removePagesDropzone">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              <span>Klik atau drag PDF di sini</span>
              <small>Maks 50MB</small>
            </div>
          </label>
        </div>
        <div id="removePagesWorkStep" style="display:none;">
          <div class="panel-section">
            <label class="panel-label">Halaman Ditandai</label>
            <p class="panel-desc" style="margin-bottom:0;"><span id="removePagesMarkedCount">0</span> halaman akan dihapus. Klik thumbnail di kanan untuk menandai/batalkan.</p>
          </div>
          <button class="btn-add-element" id="removePagesRunBtn" disabled>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Hapus Halaman & Download
          </button>
        </div>
      </div>
    </aside>
    <main class="canvas-area">
      <div class="canvas-toolbar"><div class="canvas-toolbar-left"><span class="page-info" id="removePagesPageInfo"></span></div></div>
      <div class="pdf-viewport"><div class="thumb-grid" id="removePagesThumbGrid"></div></div>
    </main>
  </div>
</div>

<!-- ═══ EDIT PDF (ubah teks yang sudah ada) ═══ -->
<div class="editor-overlay" id="editTextOverlay" style="display:none;">
  <div class="editor-layout">
    <aside class="tools-panel">
      <div class="tools-header">
        <button class="back-btn" data-back-overlay="editTextOverlay" title="Kembali">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="tools-logo"><span>Edit <em>PDF</em></span></div>
      </div>
      <div class="tools-file-info" id="editTextFileInfo" style="display:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="2"/></svg>
        <span id="editTextFileName"></span>
      </div>
      <div class="tool-panel active" style="flex:1;">
        <div id="editTextUploadStep">
          <div class="panel-section">
            <label class="panel-label">Upload PDF</label>
            <p class="panel-desc">Klik langsung pada teks di PDF (kanan) untuk menggantinya. Teks lama ditutup kotak putih & diganti teks baru — bukan reflow paragraf, hasil terbaik untuk dokumen berlatar putih/polos.</p>
          </div>
          <label class="upload-sig-zone">
            <input type="file" id="editTextFileInput" accept=".pdf" hidden>
            <div class="upload-sig-inner" id="editTextDropzone">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              <span>Klik atau drag PDF di sini</span>
              <small>Maks 50MB</small>
            </div>
          </label>
        </div>
        <div id="editTextWorkStep" style="display:none;">
          <div class="panel-section">
            <label class="panel-label">Cara pakai</label>
            <p class="panel-desc">Klik teks di halaman PDF (kanan) untuk mengedit. Tekan Enter untuk simpan perubahan per-teks, Escape untuk batal.</p>
          </div>
          <div class="panel-section">
            <label class="panel-label">Perubahan</label>
            <p class="panel-desc" style="margin-bottom:0;"><span id="editTextEditCount">0</span> teks diubah.</p>
          </div>
          <button class="btn-save" id="editTextSaveBtn" disabled style="width:100%;justify-content:center;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Simpan PDF
          </button>
        </div>
      </div>
    </aside>
    <main class="canvas-area">
      <div class="canvas-toolbar">
        <div class="canvas-toolbar-left">
          <button class="toolbar-btn" id="editTextPrevBtn" disabled>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </button>
          <span class="page-info">Halaman <strong id="editTextCurrentPage">1</strong> dari <strong id="editTextTotalPages">1</strong></span>
          <button class="toolbar-btn" id="editTextNextBtn" disabled>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </button>
        </div>
      </div>
      <div class="pdf-viewport">
        <div class="pdf-container" id="editTextPdfContainer">
          <canvas id="editTextCanvas"></canvas>
          <div class="elements-overlay" id="editTextHitLayer" style="pointer-events:all;"></div>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- ═══ CEK KEMIRIPAN DOKUMEN ═══ -->
<div class="editor-overlay" id="compareDocOverlay" style="display:none;">
  <div class="editor-layout">
    <aside class="tools-panel">
      <div class="tools-header">
        <button class="back-btn" data-back-overlay="compareDocOverlay" title="Kembali">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="tools-logo"><span>Cek <em>Kemiripan</em></span></div>
      </div>
      <div class="tool-panel active" style="flex:1;">
        <div class="panel-section">
          <p class="panel-desc">Membandingkan 2 dokumen yang diupload satu sama lain saja — <strong>bukan</strong> pengecekan plagiarisme ke internet. Mendukung PDF & TXT. Diproses 100% di browser.</p>
        </div>
        <div class="panel-section">
          <label class="panel-label">Dokumen 1</label>
          <label class="upload-sig-zone">
            <input type="file" id="compareFileInputA" accept=".pdf,.txt" hidden>
            <div class="upload-sig-inner" id="compareDropzoneA">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              <span id="compareFileNameA">Klik atau drag PDF/TXT</span>
            </div>
          </label>
        </div>
        <div class="panel-section">
          <label class="panel-label">Dokumen 2</label>
          <label class="upload-sig-zone">
            <input type="file" id="compareFileInputB" accept=".pdf,.txt" hidden>
            <div class="upload-sig-inner" id="compareDropzoneB">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              <span id="compareFileNameB">Klik atau drag PDF/TXT</span>
            </div>
          </label>
        </div>
        <button class="btn-add-element" id="compareRunBtn" disabled>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Bandingkan Dokumen
        </button>
      </div>
    </aside>
    <main class="canvas-area">
      <div class="canvas-toolbar"><div class="canvas-toolbar-left"><span class="page-info">Cek Kemiripan Dokumen</span></div></div>
      <div class="pdf-viewport">
        <div class="empty-hint" id="compareResultEmpty"><span class="empty-hint-icon">📑</span><p>Upload 2 dokumen di panel kiri, lalu klik "Bandingkan Dokumen".</p></div>
        <div id="compareResultWrap" style="display:none;width:100%;max-width:640px;margin:auto;padding:24px;">
          <div style="text-align:center;margin-bottom:24px;">
            <div style="font-size:56px;font-weight:800;font-family:var(--font-display);color:var(--accent);" id="comparePercent">0%</div>
            <p style="color:var(--muted);font-size:13px;">Tingkat kemiripan berbasis potongan kalimat (5-word shingles)</p>
          </div>
          <label class="panel-label">Kalimat yang Mirip / Sama</label>
          <div id="compareMatchList" class="compare-match-list"></div>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- ═══ CEK POLA AI ═══ -->
<div class="editor-overlay" id="aiCheckOverlay" style="display:none;">
  <div class="editor-layout">
    <aside class="tools-panel">
      <div class="tools-header">
        <button class="back-btn" data-back-overlay="aiCheckOverlay" title="Kembali">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="tools-logo"><span>Cek Pola <em>AI</em></span></div>
      </div>
      <div class="tool-panel active" style="flex:1;">
        <div class="ai-warning-banner">
          ⚠️ Perkiraan kasar berbasis pola teks sederhana — <strong>BUKAN</strong> alat deteksi AI yang akurat. Jangan dipakai untuk keputusan akademik/hukum.
        </div>
        <div class="panel-section">
          <label class="panel-label">Upload PDF/TXT (opsional)</label>
          <label class="upload-sig-zone">
            <input type="file" id="aiCheckFileInput" accept=".pdf,.txt" hidden>
            <div class="upload-sig-inner" id="aiCheckDropzone">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              <span>Klik atau drag PDF/TXT — atau paste teks di bawah</span>
            </div>
          </label>
        </div>
        <div class="panel-section">
          <label class="panel-label">Teks</label>
          <textarea class="panel-textarea" id="aiCheckTextarea" style="min-height:160px;" placeholder="Paste teks di sini (minimal ~30 kata)..."></textarea>
        </div>
        <button class="btn-add-element" id="aiCheckRunBtn">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Analisis Teks
        </button>
      </div>
    </aside>
    <main class="canvas-area">
      <div class="canvas-toolbar"><div class="canvas-toolbar-left"><span class="page-info">Cek Pola AI</span></div></div>
      <div class="pdf-viewport">
        <div class="empty-hint" id="aiCheckResultEmpty"><span class="empty-hint-icon">🔎</span><p>Masukkan teks di panel kiri, lalu klik "Analisis Teks".</p></div>
        <div id="aiCheckResultWrap" style="display:none;width:100%;max-width:480px;margin:auto;padding:24px;">
          <div class="ai-warning-banner" style="margin-bottom:20px;">
            ⚠️ Skor ini perkiraan kasar, <strong>bukan</strong> verdict akurat.
          </div>
          <div style="text-align:center;margin-bottom:20px;">
            <div style="font-size:56px;font-weight:800;font-family:var(--font-display);color:var(--accent2);"><span id="aiCheckScore">0</span><span style="font-size:22px;color:var(--muted);">/100</span></div>
            <p style="color:var(--muted);font-size:13px;">Indikasi kasar pola teks ala-AI</p>
          </div>
          <div id="aiCheckDetail" class="ai-check-detail"></div>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- BUSY OVERLAY (dipakai tool Split/Merge/PDF↔JPG/PDF↔Word) -->
<div class="loading-overlay" id="busyOverlay">
  <div class="spinner-ring"></div>
  <span id="busyText" style="font-size:13px;color:var(--muted);">Memproses...</span>
</div>

<!-- MOBILE SIGNATURE PAGE (accessed via QR) -->
<?php if (isset($_GET['mobile_sign']) && isset($_GET['session'])): ?>
<script>window.isMobileSign = true; window.mobileSession = '<?= htmlspecialchars($_GET['session']) ?>';</script>
<?php endif; ?>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script>
  pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
</script>
<script src="assets/common.js"></script>
<script src="assets/editor.js"></script>
<script src="assets/tools.js"></script>
<script src="assets/convert.js"></script>
<script src="assets/editpdf.js"></script>
<script src="assets/textanalysis.js"></script>
</body>
</html>
