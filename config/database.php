<?php
// Database Configuration
define('DB_HOST', '192.168.8.199');
define('DB_PORT', '3310');
define('DB_NAME', 'isdn_db');
define('DB_USER', 'root');
define('DB_PASS', 'yasas');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=" . DB_HOST;
    if (defined('DB_PORT') && DB_PORT !== '') {
        $dsn .= ";port=" . DB_PORT;
    }
    $dsn .= ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
