<?php
// Application Configuration
define('APP_NAME', 'ISDN - IslandLink Sales Distribution Network');
// Base URL/path auto-detected for shared hosting (e.g., InfinityFree).
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
$scheme = $isHttps ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

$docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
$appRoot = realpath(__DIR__ . '/..');
if ($docRoot && $appRoot && strpos($appRoot, $docRoot) === 0) {
    $basePath = str_replace('\\', '/', substr($appRoot, strlen($docRoot)));
} else {
    $scriptDir = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '';
    $basePath = str_replace('\\', '/', $scriptDir);
}
$basePath = rtrim($basePath, '/');

define('APP_URL', $scheme . '://' . $host . $basePath);
define('BASE_PATH', $basePath);
define('SITE_KEY', 'your-secret-key-here');

// Timezone
date_default_timezone_set('Asia/Colombo');

// Session Configuration
ini_set('session.cookie_httponly', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
