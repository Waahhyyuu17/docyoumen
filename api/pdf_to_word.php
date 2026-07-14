<?php
/**
 * DocYouMen — PDF to Word (.docx)
 * Ekstraksi teks per halaman (smalot/pdfparser) lalu dibangun ulang
 * jadi dokumen Word (phpoffice/phpword). Hasil berupa teks polos —
 * gambar & tabel pada PDF asli tidak ikut terkonversi.
 *
 * Privasi: hasil konversi langsung dikirim sebagai respons (bukan
 * disimpan dengan URL yang bisa diakses lagi) dan file sementara
 * dihapus segera setelah terkirim — tidak ada file yang tersisa
 * menunggu di server.
 */
require __DIR__ . '/../vendor/autoload.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

function fail(string $msg, int $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed', 405);
}

if (!isset($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
    fail('Upload PDF gagal atau tidak ada file');
}

$file = $_FILES['pdf'];

if ($file['size'] > 30 * 1024 * 1024) {
    fail('File terlalu besar (maks 30MB)');
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
$isPdfExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) === 'pdf';

if ($mimeType !== 'application/pdf' || !$isPdfExt) {
    fail('File harus berupa PDF');
}

$tempDir = __DIR__ . '/../temp/';
if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

// $outPath belum dibuat sampai titik ini, jadi fail() sebelum save() aman
// tanpa perlu cleanup (exit() di dalam try tidak menjalankan finally di PHP).
$outPath = $tempDir . bin2hex(random_bytes(16)) . '.docx';

try {
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($file['tmp_name']);
    $pages = $pdf->getPages();

    if (!count($pages)) {
        fail('PDF tidak berisi teks yang bisa diekstrak');
    }

    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();

    foreach ($pages as $i => $page) {
        if ($i > 0) $section->addPageBreak();
        $section->addTitle('Halaman ' . ($i + 1), 2);
        $lines = preg_split('/\r\n|\r|\n/', $page->getText());
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') { $section->addTextBreak(); continue; }
            $section->addText(htmlspecialchars($line, ENT_QUOTES, 'UTF-8'));
        }
    }

    \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($outPath);
} catch (\Throwable $e) {
    @unlink($outPath);
    fail('Gagal konversi: ' . $e->getMessage(), 500);
}

$downloadName = pathinfo($file['name'], PATHINFO_FILENAME) . '.docx';
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($outPath));
readfile($outPath);
unlink($outPath);
