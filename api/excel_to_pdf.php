<?php
/**
 * DocYouMen — Excel (.xlsx/.xls) to PDF
 * phpoffice/phpspreadsheet membaca dokumen lalu merender via dompdf.
 * Layout kompleks (banyak kolom, grafik) mungkin terpotong di halaman PDF.
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

if (!isset($_FILES['excel']) || $_FILES['excel']['error'] !== UPLOAD_ERR_OK) {
    fail('Upload dokumen gagal atau tidak ada file');
}

$file = $_FILES['excel'];

if ($file['size'] > 30 * 1024 * 1024) {
    fail('File terlalu besar (maks 30MB)');
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExt = ['xlsx', 'xls'];
$allowedMime = [
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
    'application/vnd.ms-excel', // .xls
    'application/zip', // .xlsx terdeteksi sbg zip container di beberapa sistem
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($ext, $allowedExt, true) || !in_array($mimeType, $allowedMime, true)) {
    fail('File harus berupa .xlsx atau .xls');
}

$tempDir = __DIR__ . '/../temp/';
if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

// Simpan dengan ekstensi asli — IOFactory::load() butuh ekstensi valid untuk deteksi format.
$tmpUploadPath = $tempDir . bin2hex(random_bytes(16)) . '.' . $ext;
if (!move_uploaded_file($file['tmp_name'], $tmpUploadPath)) {
    fail('Gagal menyimpan file upload');
}

$outPath = $tempDir . bin2hex(random_bytes(16)) . '.pdf';

try {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpUploadPath);
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Dompdf');
    $writer->save($outPath);
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
