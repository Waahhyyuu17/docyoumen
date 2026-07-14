<?php
/**
 * DocYouMen — PDF to PowerPoint (.pptx)
 * Rekonstruksi berbasis teks: 1 slide per halaman PDF, isi 1 textbox
 * berisi teks halaman itu (smalot/pdfparser). Layout & gambar asli
 * TIDAK dipertahankan — sama level "best-effort" dengan PDF→Word.
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

$outPath = $tempDir . bin2hex(random_bytes(16)) . '.pptx';

try {
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($file['tmp_name']);
    $pages = $pdf->getPages();

    if (!count($pages)) {
        fail('PDF tidak berisi teks yang bisa diekstrak');
    }

    $presentation = new \PhpOffice\PhpPresentation\PhpPresentation();
    // Presentation baru sudah punya 1 slide kosong — pakai untuk halaman pertama.
    $slides = [$presentation->getSlide(0)];
    for ($i = 1; $i < count($pages); $i++) {
        $slides[] = $presentation->createSlide();
    }

    $layout = $presentation->getLayout();
    $widthPt = $layout->getCX(\PhpOffice\PhpPresentation\DocumentLayout::UNIT_POINT);
    $heightPt = $layout->getCY(\PhpOffice\PhpPresentation\DocumentLayout::UNIT_POINT);

    foreach ($pages as $i => $page) {
        $slide = $slides[$i];
        $richText = $slide->createRichTextShape();
        $richText->setOffsetX(20)->setOffsetY(20);
        $richText->setWidth((int) $widthPt - 40)->setHeight((int) $heightPt - 40);
        $richText->setWrap(\PhpOffice\PhpPresentation\Shape\RichText::WRAP_SQUARE);

        $titleParagraph = $richText->createParagraph();
        $titleRun = $titleParagraph->createTextRun('Halaman ' . ($i + 1));
        $titleRun->getFont()->setBold(true)->setSize(16);

        $lines = preg_split('/\r\n|\r|\n/', $page->getText());
        foreach ($lines as $line) {
            $line = trim($line);
            $paragraph = $richText->createParagraph();
            if ($line !== '') $paragraph->createTextRun($line)->getFont()->setSize(12);
        }
    }

    $writer = \PhpOffice\PhpPresentation\IOFactory::createWriter($presentation, 'PowerPoint2007');
    $writer->save($outPath);
} catch (\Throwable $e) {
    @unlink($outPath);
    fail('Gagal konversi: ' . $e->getMessage(), 500);
}

$downloadName = pathinfo($file['name'], PATHINFO_FILENAME) . '.pptx';
header('Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($outPath));
readfile($outPath);
unlink($outPath);
