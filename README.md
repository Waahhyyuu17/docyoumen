# DocYouMen — PDF Editor
### Editor PDF dengan Tanda Tangan & Stempel

---

## 🚀 Fitur

Landing page berupa menu pilihan 17 tool, dengan search bar & filter kategori (Tanda Tangan & Anotasi, Organisir PDF, Konversi File, Analisis Teks):

**Tanda Tangan & Anotasi**
- **Tanda Tangan & Edit PDF** — Tambah teks, tanda tangan (gambar/upload/QR-HP), stempel, drag/resize/rotate elemen, zoom, multi-halaman, undo, simpan PDF
- **Edit PDF** — Ubah teks yang SUDAH ADA di PDF: klik teks di halaman, ganti isinya. Teknik "tutup & timpa" (teks lama ditutup kotak putih, teks baru digambar di posisi sama) — teks baru bisa di-select/dicari, tapi ini bukan reflow paragraf; hasil terbaik untuk dokumen berlatar putih/polos

**Organisir PDF**
- **Split PDF** — Pisah PDF jadi beberapa file (per range halaman atau tiap halaman terpisah)
- **Merge PDF** — Gabungkan beberapa PDF jadi satu, urutan sesuai antrian
- **Hapus Halaman** — Tandai halaman di thumbnail untuk dibuang dari PDF
- **Compress PDF** — Perkecil ukuran file: mode Ringan (pertahankan teks & kualitas) atau Kuat (rasterize per halaman, ukuran lebih kecil tapi teks tidak lagi bisa di-select)
- **Nomor Halaman** — Tambah nomor halaman otomatis (posisi & format custom)

**Konversi File**
- **PDF ↔ JPG** — Ubah tiap halaman PDF jadi gambar JPG dan sebaliknya
- **PDF ↔ Word** — Ekstrak teks PDF ke .docx dan sebaliknya. Gambar/tabel kompleks tidak ikut terkonversi
- **PDF ↔ Excel** — Excel ke PDF pakai renderer dompdf; PDF ke Excel cuma dump teks per baris ke kolom A (bukan rekonstruksi tabel)
- **PDF ↔ PowerPoint** — ⚠️ PowerPoint ke PDF hanya render teks/gambar/tabel — shape, chart, dan background/master slide TIDAK didukung (keterbatasan library `phpoffice/phppresentation`, bukan bug). PDF ke PowerPoint = rekonstruksi teks per halaman ke 1 slide + textbox

**Analisis Teks**
- **Cek Kemiripan Dokumen** — Bandingkan 2 dokumen (PDF/TXT) yang diupload satu sama lain via Jaccard similarity atas 5-word shingle. **Bukan** pengecekan plagiarisme ke internet — murni perbandingan antar-dokumen lokal
- **Cek Pola AI** — Heuristik kasar (variasi panjang kalimat, keragaman kosakata, frasa umum ala-AI) menghasilkan skor 0-100. **Bukan** detektor AI yang akurat — selalu tampil banner peringatan, jangan dipakai untuk keputusan akademik/hukum

### Privasi & penyimpanan file

Aplikasi ini **tidak menggunakan database sama sekali** — tidak ada data yang disimpan permanen di server.

- **Split, Merge, Hapus Halaman, PDF↔JPG, Compress, Nomor Halaman, Edit PDF, Cek Kemiripan, Cek Pola AI** berjalan 100% di browser (pdf-lib/pdf.js/jsPDF/JSZip) — file tidak pernah diunggah ke server.
- **PDF↔Word, PDF↔Excel, PDF↔PowerPoint** perlu diproses di server (butuh library PHP: `smalot/pdfparser`, `phpoffice/phpword`, `phpoffice/phpspreadsheet`, `phpoffice/phppresentation`, `dompdf`). File hasil konversi **langsung dikirim sebagai respons** (bukan disimpan dengan URL yang bisa diakses lagi), dan file sementara di server dihapus segera setelah terkirim — tidak ada file yang tersisa menunggu.
- Tool **Tanda Tangan** tetap mengunggah PDF ke folder `uploads/` (dibutuhkan untuk fitur QR/tanda tangan HP) dan tanda tangan mobile ke `signatures/` — keduanya auto-terhapus setelah 1 jam (lihat bagian Keamanan).

---

## 📋 Persyaratan

- **PHP** 8.0 atau lebih baru (dibutuhkan oleh `phpoffice/phpword` ^1.4)
- **Web Server** — Apache, Nginx, atau PHP built-in server
- Ekstensi PHP: `fileinfo`, `json`, `dom`, `gd`, `zip`, `xml` (biasanya sudah aktif)
- **Composer** — untuk install dependency fitur PDF↔Word/Excel/PowerPoint:
  ```bash
  composer install
  ```
  Catatan versi: `phpoffice/phppresentation ^1.2` mensyaratkan `phpoffice/phpspreadsheet` di rentang `^1.9 || ^2.0 || ^3.0 || ^4.0` (belum mendukung ^5.0) — `composer.json` proyek ini sudah mengunci ke `^3.10` (versi aman, tanpa advisory keamanan) supaya kompatibel. Jangan jalankan `composer require phpoffice/phpspreadsheet` tanpa versi spesifik karena Composer akan menariknya ke versi 5.x terbaru dan diam-diam menurunkan phpPresentation ke versi lama (0.9.0) yang tidak punya PDF writer.

---

## ⚙️ Instalasi

### 1. Upload ke Server

Upload seluruh folder `pdf-editor` ke server Anda:

```
/var/www/html/pdf-editor/   ← Apache/Nginx
```

### 2. Set Permission

```bash
chmod 755 uploads/ signatures/ temp/
# Atau
chmod 777 uploads/ signatures/ temp/
```

### 3. Konfigurasi Base URL (Wajib untuk QR Mobile)

Edit file `config.php`:

```php
define('BASE_URL', 'https://domainanda.com/pdf-editor');
```

Ganti dengan URL aktual server Anda.

### 4. Konfigurasi Web Server

**Apache** — Buat `.htaccess` di root folder:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    Options -Indexes
</IfModule>

<FilesMatch "\.(pdf|png|jpg)$">
    Header set Cache-Control "no-cache"
</FilesMatch>
```

**Nginx** — Tambahkan ke config:

```nginx
location /pdf-editor {
    try_files $uri $uri/ /pdf-editor/index.php?$query_string;
}
```

### 5. PHP Built-in Server (Development)

```bash
cd pdf-editor
php -S localhost:8080
```

Buka: `http://localhost:8080`

---

## 📱 Fitur QR Mobile

Untuk menggunakan tanda tangan via HP:

1. Server **harus bisa diakses dari HP** (IP/domain yang sama)
2. Set `BASE_URL` di `config.php` ke alamat yang bisa diakses HP
3. HP dan komputer **harus di jaringan yang sama** (atau server publik)

Contoh dengan IP lokal:
```php
define('BASE_URL', 'http://192.168.1.100/pdf-editor');
```

---

## 📁 Struktur Folder

```
pdf-editor/
├── index.php              # Landing (menu 17 tool, search & filter) + overlay tiap tool
├── mobile_sign.php        # Halaman tanda tangan HP
├── config.php             # Konfigurasi
├── composer.json          # Dependency PHP (phpword, phpspreadsheet, phppresentation, dompdf, pdfparser)
├── vendor/                # Composer packages (hasil `composer install`)
├── api/
│   ├── upload_pdf.php     # Upload handler (tool Tanda Tangan)
│   ├── generate_qr.php    # Generate QR session
│   ├── mobile_signature.php  # Terima & poll tanda tangan
│   ├── pdf_to_word.php    # Konversi PDF → Word (server-side)
│   ├── word_to_pdf.php    # Konversi Word → PDF (server-side)
│   ├── excel_to_pdf.php   # Konversi Excel → PDF (server-side)
│   ├── pdf_to_excel.php   # Konversi PDF → Excel (server-side)
│   ├── ppt_to_pdf.php     # Konversi PowerPoint → PDF (server-side)
│   └── pdf_to_ppt.php     # Konversi PDF → PowerPoint (server-side)
├── assets/
│   ├── style.css          # Stylesheet
│   ├── common.js          # Util bersama (toast, busy overlay, navigasi tool, search/filter, queue)
│   ├── editor.js          # Tool Tanda Tangan
│   ├── tools.js           # Tool Split/Merge/PDF↔JPG/Compress/Nomor Halaman/Hapus Halaman (client-side)
│   ├── convert.js         # Tool PDF↔Word/Excel/PowerPoint (panggil API server, data-driven CONVERTERS[])
│   ├── editpdf.js         # Tool Edit PDF (ubah teks yang sudah ada)
│   └── textanalysis.js    # Tool Cek Kemiripan Dokumen & Cek Pola AI (client-side)
├── uploads/               # PDF yang diupload (auto-cleanup)
├── signatures/            # Tanda tangan mobile temp (auto-cleanup)
└── temp/                  # File sementara & hasil konversi server-side (auto-cleanup)
```

---

## ⌨️ Keyboard Shortcuts

| Shortcut | Fungsi |
|----------|--------|
| `Ctrl+S` | Simpan PDF |
| `Ctrl+Z` | Undo |
| `Delete` | Hapus elemen terpilih |
| `Escape` | Batalkan mode tambah |
| `←/→` | Navigasi halaman |
| `+/-` | Zoom in/out |

---

## 🔒 Keamanan

- File PDF disimpan sementara dengan nama random
- Session ID menggunakan `random_bytes` (kriptografis aman)
- Validasi MIME type file upload
- Batas ukuran file 50MB
- File otomatis terhapus setelah 1 jam (lihat `config.php`)

Untuk production, tambahkan cleanup cron:
```bash
# Cron job - hapus file lama setiap jam
0 * * * * php /path/to/pdf-editor/cleanup.php
```

---

## 📝 Lisensi

Free to use — DocYouMen
