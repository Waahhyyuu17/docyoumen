<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (!isset($_FILES['pdf'])) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['pdf'];

// Validate
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Upload error: ' . $file['error']]);
    exit;
}

// Check file type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if ($mimeType !== 'application/pdf') {
    // Also allow by extension
    if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'pdf') {
        echo json_encode(['success' => false, 'error' => 'File harus berupa PDF']);
        exit;
    }
}

// Size check (50MB)
if ($file['size'] > 50 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'File terlalu besar (maks 50MB)']);
    exit;
}

// Generate unique filename
$sessionId = bin2hex(random_bytes(16));
$filename = $sessionId . '.pdf';
$targetPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success' => false, 'error' => 'Gagal menyimpan file']);
    exit;
}

echo json_encode([
    'success'   => true,
    'session'   => $sessionId,
    'filename'  => $filename,
    'url'       => 'uploads/' . $filename,
    'name'      => $file['name'],
    'size'      => $file['size'],
]);
