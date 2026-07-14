<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Generate a unique session for mobile signature
$session = bin2hex(random_bytes(16));

// Get base URL dynamically
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$basePath = dirname(dirname($_SERVER['SCRIPT_NAME']));
$basePath = rtrim($basePath, '/');

$mobileUrl = $scheme . '://' . $host . $basePath . '/mobile_sign.php?session=' . $session;

// If a custom base URL is provided via config
$configPath = __DIR__ . '/../config.php';
if (file_exists($configPath)) {
    include $configPath;
    if (defined('BASE_URL')) {
        $mobileUrl = rtrim(BASE_URL, '/') . '/mobile_sign.php?session=' . $session;
    }
}

echo json_encode([
    'success'    => true,
    'session'    => $session,
    'mobile_url' => $mobileUrl,
]);
