<?php
/**
 * DocYouMen — Mobile Signature API
 * POST: Terima tanda tangan dari HP
 * GET:  Poll / ambil tanda tangan dari desktop
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Cache-Control: no-store, no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$sigDir = __DIR__ . '/../signatures/';
if (!is_dir($sigDir)) {
    if (!mkdir($sigDir, 0755, true)) {
        echo json_encode(['success' => false, 'error' => 'Cannot create signatures dir']);
        exit;
    }
}

function sanitizeSession($s) {
    return preg_replace('/[^a-f0-9]/', '', (string)$s);
}

// POST: Terima dari HP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true);

    if (!$body || empty($body['session']) || empty($body['signature'])) {
        echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
        exit;
    }

    $session = sanitizeSession($body['session']);
    if (strlen($session) !== 32) {
        echo json_encode(['success' => false, 'error' => 'Session ID tidak valid']);
        exit;
    }

    $sig = $body['signature'];
    if (!preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,(.+)$/', $sig, $m)) {
        echo json_encode(['success' => false, 'error' => 'Format gambar tidak valid']);
        exit;
    }

    $imgData = base64_decode($m[2], true);
    if (!$imgData || strlen($imgData) < 50) {
        echo json_encode(['success' => false, 'error' => 'Data gambar kosong']);
        exit;
    }

    $imgPath = $sigDir . $session . '_mobile.png';
    $written = file_put_contents($imgPath, $imgData);

    if ($written === false) {
        echo json_encode(['success' => false, 'error' => 'Gagal simpan. Cek permission folder signatures/']);
        exit;
    }

    file_put_contents($sigDir . $session . '_meta.json', json_encode([
        'session' => $session, 'timestamp' => time(), 'status' => 'completed'
    ]));

    echo json_encode(['success' => true, 'message' => 'Tanda tangan tersimpan']);
    exit;
}

// GET: Poll dari desktop
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $session = sanitizeSession($_GET['session'] ?? '');

    if (strlen($session) !== 32) {
        echo json_encode(['success' => false, 'status' => 'invalid']);
        exit;
    }

    $imgPath  = $sigDir . $session . '_mobile.png';
    $metaPath = $sigDir . $session . '_meta.json';

    if (file_exists($imgPath) && filesize($imgPath) > 0) {
        $imgData = file_get_contents($imgPath);
        $dataUrl = 'data:image/png;base64,' . base64_encode($imgData);

        @unlink($imgPath);
        @unlink($metaPath);

        echo json_encode(['success' => true, 'status' => 'completed', 'signature' => $dataUrl]);
    } else {
        echo json_encode(['success' => true, 'status' => 'pending']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
