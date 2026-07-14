<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$nama     = trim($_POST['nama'] ?? '');
$email    = trim($_POST['email'] ?? '');
$pesan    = trim($_POST['pesan'] ?? '');
$honeypot = trim($_POST['website'] ?? '');

// Bot caught by honeypot — pretend success, do nothing
if ($honeypot !== '') {
    echo json_encode(['success' => true]);
    exit;
}

if ($nama === '' || $email === '' || $pesan === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Format email tidak valid.']);
    exit;
}

if (mb_strlen($nama) > 100 || mb_strlen($pesan) > 5000) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Isian terlalu panjang.']);
    exit;
}

// Strip newlines to prevent email header injection
$cleanNama  = str_replace(["\r", "\n"], '', $nama);
$cleanEmail = str_replace(["\r", "\n"], '', $email);

$to      = 'wahyuardiansyah1701@gmail.com';
$subject = '[DocYouMen] Pesan baru dari ' . mb_substr($cleanNama, 0, 80);
$body    = "Nama: $cleanNama\r\nEmail: $cleanEmail\r\n\r\nPesan:\r\n$pesan\r\n";

$host    = preg_replace('/[^a-zA-Z0-9.\-]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
$headers = "From: DocYouMen Contact Form <no-reply@$host>\r\n";
$headers .= "Reply-To: $cleanEmail\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$sent = @mail($to, $subject, $body, $headers);

if ($sent) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal mengirim pesan. Coba lagi lewat WhatsApp/Email langsung.']);
}
