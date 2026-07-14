<?php
/**
 * DocYouMen — PDF to Excel (.xlsx)
 * Ekstraksi teks per halaman (smalot/pdfparser), tiap baris teks PDF
 * ditulis ke 1 baris sel kolom A pada 1 sheet per halaman. Ini BUKAN
 * rekonstruksi tabel — cuma dump teks per baris apa adanya.
 *
 * Privasi: hasil konversi langsung dikirim sebagai respons dan file
 * sementara dihapus segera setelah terkirim.
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

$outPath = $tempDir . bin2hex(random_bytes(16)) . '.xlsx';

try {
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($file['tmp_name']);
    $pages = $pdf->getPages();

    if (!count($pages)) {
        fail('PDF tidak berisi teks yang bisa diekstrak');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $spreadsheet->removeSheetByIndex(0);

    foreach ($pages as $i => $page) {
        $sheet = $spreadsheet->createSheet();
        $title = 'Halaman ' . ($i + 1);
        $sheet->setTitle($title);

        $lines = preg_split('/\r\n|\r|\n/', $page->getText());
        $row = 1;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $sheet->setCellValueExplicit('A' . $row, $line, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $row++;
        }
        $sheet->getColumnDimension('A')->setWidth(100);
    }
    $spreadsheet->setActiveSheetIndex(0);

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($outPath);
} catch (\Throwable $e) {
    @unlink($outPath);
    fail('Gagal konversi: ' . $e->getMessage(), 500);
}

$downloadName = pathinfo($file['name'], PATHINFO_FILENAME) . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($outPath));
readfile($outPath);
unlink($outPath);
