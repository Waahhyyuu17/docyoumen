<?php
/**
 * DocYouMen — Configuration
 *
 * Ubah BASE_URL sesuai dengan domain/URL server Anda.
 * Ini digunakan untuk generate QR code mobile signature.
 * 
 * Contoh:
 *   define('BASE_URL', 'https://mydomain.com/pdf-editor');
 *   define('BASE_URL', 'http://localhost/pdf-editor');
 */

// Otomatis deteksi jika tidak diset manual
if (!defined('BASE_URL')) {
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host   = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $dir    = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    define('BASE_URL', $scheme . '://' . $host . $dir);
}

// Upload limits
define('MAX_PDF_SIZE', 50 * 1024 * 1024); // 50MB
define('SESSION_EXPIRE', 3600); // 1 hour

// Cleanup old files (optional - can be run via cron)
function cleanupOldFiles() {
    $dirs = [__DIR__ . '/uploads/', __DIR__ . '/signatures/', __DIR__ . '/temp/'];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) continue;
        $files = glob($dir . '*');
        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file)) > SESSION_EXPIRE) {
                unlink($file);
            }
        }
    }
}
