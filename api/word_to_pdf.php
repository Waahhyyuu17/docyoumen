<?php
/**
 * DocYouMen — Word (.doc/.docx) to PDF
 * phpoffice/phpword membaca dokumen lalu merender via dompdf.
 * Layout kompleks (kolom, gambar mengambang, dsb) mungkin tidak sempurna.
 *
 * Privasi: hasil konversi langsung dikirim sebagai respons (bukan
 * disimpan dengan URL yang bisa diakses lagi) dan semua file sementara
 * (upload asli + hasil) dihapus segera setelah terkirim.
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

if (!isset($_FILES['docx']) || $_FILES['docx']['error'] !== UPLOAD_ERR_OK) {
    fail('Upload dokumen gagal atau tidak ada file');
}

$file = $_FILES['docx'];

if ($file['size'] > 30 * 1024 * 1024) {
    fail('File terlalu besar (maks 30MB)');
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExt = ['doc', 'docx'];
$allowedMime = [
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
    'application/msword', // .doc
    'application/zip', // .docx terdeteksi sbg zip container di beberapa sistem
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($ext, $allowedExt, true) || !in_array($mimeType, $allowedMime, true)) {
    fail('File harus berupa .doc atau .docx');
}

$tempDir = __DIR__ . '/../temp/';
if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

// Simpan dengan ekstensi asli — PhpWord\IOFactory::load() butuh ekstensi valid untuk deteksi format.
$tmpUploadPath = $tempDir . bin2hex(random_bytes(16)) . '.' . $ext;
if (!move_uploaded_file($file['tmp_name'], $tmpUploadPath)) {
    fail('Gagal menyimpan file upload');
}

$outPath = $tempDir . bin2hex(random_bytes(16)) . '.pdf';

try {
    \PhpOffice\PhpWord\Settings::setPdfRendererPath(__DIR__ . '/../vendor/dompdf/dompdf');
    \PhpOffice\PhpWord\Settings::setPdfRendererName(\PhpOffice\PhpWord\Settings::PDF_RENDERER_DOMPDF);

    $phpWord = \PhpOffice\PhpWord\IOFactory::load($tmpUploadPath);
    \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF')->save($outPath);
} catch (\Throwable $e) {
    @unlink($tmpUploadPath);
    @unlink($outPath);
    fail('Gagal konversi: ' . $e->getMessage(), 500);
}

@unlink($tmpUploadPath);

$downloadName = pathinfo($file['name'], PATHINFO_FILENAME) . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($outPath));
readfile($outPath);
unlink($outPath);
