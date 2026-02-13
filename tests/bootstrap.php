<?php
/**
 * PHPUnit bootstrap for ISDN test suite
 * Loads minimal config so tests can run without full app context
 */
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', '1');

$_SERVER = $_SERVER ?? [];
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['HTTPS'] = $_SERVER['HTTPS'] ?? 'off';

define('APP_ROOT', dirname(__DIR__));
define('BASE_PATH', '');
define('APP_URL', 'http://localhost');

require_once APP_ROOT . '/includes/functions.php';
