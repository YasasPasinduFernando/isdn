<?php
// Application Configuration
define('APP_NAME', 'ISDN - IslandLink Sales Distribution Network');
// Base URL/path auto-detected for shared hosting (e.g., InfinityFree).
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
$scheme = $isHttps ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$scriptDir = isset($_SERVER['SCRIPT_NAME']) ? rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') : '';
$basePath = $scriptDir === '/' ? '' : $scriptDir;

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
