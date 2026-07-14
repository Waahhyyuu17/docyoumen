<?php
/**
 * DocYouMen — PowerPoint (.ppt/.pptx) to PDF
 * phpoffice/phppresentation membaca dokumen lalu merender via dompdf.
 *
 * KETERBATASAN PENTING (phppresentation 1.2.0's PDF writer hanya
 * merender teks, gambar, dan tabel — dikonfirmasi dari source-nya):
 * AutoShape, Chart, Group, background/master slide TIDAK ikut
 * terkonversi sama sekali. Untuk slide yang banyak shape/chart,
 * hasilnya akan terlihat kosong/rusak, bukan cuma "flat". UI wajib
 * menampilkan peringatan ini dengan jelas.
 *
 * Writer bawaan hardcode ukuran kertas A4 landscape — di sini dipatch
 * pakai class turunan supaya ukuran kertas ikut ukuran slide asli.
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

if (!isset($_FILES['ppt']) || $_FILES['ppt']['error'] !== UPLOAD_ERR_OK) {
    fail('Upload dokumen gagal atau tidak ada file');
}

$file = $_FILES['ppt'];

if ($file['size'] > 30 * 1024 * 1024) {
    fail('File terlalu besar (maks 30MB)');
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExt = ['pptx', 'ppt'];
$allowedMime = [
    'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
    'application/vnd.ms-powerpoint', // .ppt
    'application/zip', // .pptx terdeteksi sbg zip container di beberapa sistem
    'application/octet-stream', // fileinfo di sebagian sistem tidak kenali struktur zip pptx secara spesifik
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($ext, $allowedExt, true) || !in_array($mimeType, $allowedMime, true)) {
    fail('File harus berupa .pptx atau .ppt');
}

$tempDir = __DIR__ . '/../temp/';
if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

$tmpUploadPath = $tempDir . bin2hex(random_bytes(16)) . '.' . $ext;
if (!move_uploaded_file($file['tmp_name'], $tmpUploadPath)) {
    fail('Gagal menyimpan file upload');
}

// Writer custom: sama seperti Writer\PDF\DomPDF bawaan, tapi ukuran kertas
// ikut dimensi slide asli (bawaan hardcode A4 landscape untuk semua deck).
class DocYouMenPresentationPdfWriter extends \PhpOffice\PhpPresentation\Writer\PDF\DomPDF
{
    public function save(string $filename): void
    {
        $this->isPDF = true;
        $html = $this->getHtmlContent();
        $html = str_replace(PHP_EOL, '', $html);

        $layout = $this->getPhpPresentation()->getLayout();
        $widthPt = $layout->getCX(\PhpOffice\PhpPresentation\DocumentLayout::UNIT_POINT);
        $heightPt = $layout->getCY(\PhpOffice\PhpPresentation\DocumentLayout::UNIT_POINT);

        $domPdf = new \Dompdf\Dompdf(new \Dompdf\Options());
        $domPdf->loadHtml($html);
        $domPdf->setPaper([0, 0, $widthPt, $heightPt]);
        $domPdf->render();
        file_put_contents($filename, $domPdf->output());
    }
}

$outPath = $tempDir . bin2hex(random_bytes(16)) . '.pdf';

try {
    $presentation = \PhpOffice\PhpPresentation\IOFactory::load($tmpUploadPath);
    $writer = new DocYouMenPresentationPdfWriter($presentation);
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
