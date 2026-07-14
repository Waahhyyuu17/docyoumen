<?php
/**
 * DocYouMen — Cleanup Script
 * Jalankan via cron job setiap jam:
 * 0 * * * * php /path/to/pdf-editor/cleanup.php
 */

define('MAX_AGE', 3600); // 1 jam

$dirs = [
    __DIR__ . '/uploads/',
    __DIR__ . '/signatures/',
    __DIR__ . '/temp/',
];

$deleted = 0;
foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $files = glob($dir . '*');
    if (!$files) continue;
    foreach ($files as $file) {
        if (!is_file($file)) continue;
        if ((time() - filemtime($file)) > MAX_AGE) {
            if (unlink($file)) $deleted++;
        }
    }
}

echo date('Y-m-d H:i:s') . " — Cleaned up {$deleted} file(s)\n";
