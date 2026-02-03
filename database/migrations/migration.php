<?php

require_once __DIR__ . '/../../config/database.php';

if (!isset($pdo) || !$pdo instanceof PDO) {
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER')) {
        die("Database constants not found. Ensure config/database.php defines DB_HOST, DB_NAME, DB_USER.");
    }

    $dsn = "mysql:host=" . DB_HOST;
    if (defined('DB_PORT') && DB_PORT !== '') {
        $dsn .= ";port=" . DB_PORT;
    }
    $dsn .= ";dbname=" . DB_NAME . ";charset=" . (defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4');

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
}

$migrationDir = __DIR__;

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL UNIQUE,
        applied_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$files = glob($migrationDir . "/*.sql");
sort($files);

foreach ($files as $file) {
    $base = basename($file);
    if ($base === 'schema.sql') continue;

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM migrations WHERE filename = :filename');
    $stmt->execute([':filename' => $base]);
    $already = (int)$stmt->fetchColumn();
    if ($already) {
        echo "Skipping (already applied): $base" . PHP_EOL;
        continue;
    }

    echo "Running: $base" . PHP_EOL;

    $sql = file_get_contents($file);
    try {
        $pdo->exec($sql);

        $ins = $pdo->prepare('INSERT INTO migrations (filename, applied_at) VALUES (:f, NOW())');
        $ins->execute([':f' => $base]);

        echo "Applied: $base" . PHP_EOL;
    } catch (PDOException $e) {
        echo "Error applying $base: " . $e->getMessage() . PHP_EOL;
        exit(1);
    }
}

echo "Migration run finished." . PHP_EOL;
