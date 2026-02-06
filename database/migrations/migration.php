<?php

require_once __DIR__ . '/../../config/database.php';

if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER')) {
    die("Database constants not found. Ensure config/database.php defines DB_HOST, DB_NAME, DB_USER.");
}

$serverDsn = "mysql:host=" . DB_HOST;
if (defined('DB_PORT') && DB_PORT !== '') {
    $serverDsn .= ";port=" . DB_PORT;
}
if (defined('DB_CHARSET') && DB_CHARSET !== '') {
    $serverDsn .= ";charset=" . DB_CHARSET;
}

try {
    $serverPdo = new PDO($serverDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Could not connect to MySQL server: ' . $e->getMessage());
}

$dbname = DB_NAME;

$stmt = $serverPdo->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :db');
$stmt->execute([':db' => $dbname]);
$exists = (bool)$stmt->fetchColumn();

if ($exists) {
    echo "Database '$dbname' exists — dropping all tables..." . PHP_EOL;

    $serverPdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    $tblStmt = $serverPdo->prepare('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :db AND TABLE_TYPE = "BASE TABLE"');
    $tblStmt->execute([':db' => $dbname]);
    $tables = $tblStmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($tables)) {
        $qualified = array_map(function ($t) use ($dbname) {
            return "`" . $dbname . "`.`" . $t . "`";
        }, $tables);
        $dropSql = 'DROP TABLE IF EXISTS ' . implode(', ', $qualified);
        $serverPdo->exec($dropSql);
    }

    $viewStmt = $serverPdo->prepare('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = :db');
    $viewStmt->execute([':db' => $dbname]);
    $views = $viewStmt->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($views)) {
        $qualifiedViews = array_map(function ($v) use ($dbname) {
            return "`" . $dbname . "`.`" . $v . "`";
        }, $views);
        $serverPdo->exec('DROP VIEW IF EXISTS ' . implode(', ', $qualifiedViews));
    }

    $serverPdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    echo "All tables dropped." . PHP_EOL;
} else {
    echo "Database '$dbname' does not exist — creating..." . PHP_EOL;
    $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
    $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `" . $dbname . "` CHARACTER SET " . $serverPdo->quote($charset));
    echo "Database created." . PHP_EOL;
}

$dsn = "mysql:host=" . DB_HOST;
if (defined('DB_PORT') && DB_PORT !== '') {
    $dsn .= ";port=" . DB_PORT;
}
$dsn .= ";dbname=" . $dbname . ";charset=" . (defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4');

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

$migrationDir = __DIR__;

// Parse CLI args for optional seed file: --seed=filename.sql or --seed=/full/path/to/file.sql
$seedArg = null;
if (isset($argv) && is_array($argv)) {
    foreach ($argv as $arg) {
        if (strpos($arg, '--seed=') === 0) {
            $seedArg = substr($arg, strlen('--seed='));
            break;
        }
    }
}

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

    echo "Running: $base" . PHP_EOL;

    $sql = file_get_contents($file);
    try {
        $pdo->exec($sql);

        $ins = $pdo->prepare('INSERT IGNORE INTO migrations (filename, applied_at) VALUES (:f, NOW())');
        $ins->execute([':f' => $base]);

        echo "Applied: $base" . PHP_EOL;
    } catch (PDOException $e) {
        echo "Error applying $base: " . $e->getMessage() . PHP_EOL;
        exit(1);
    }
}

echo "Migration run finished." . PHP_EOL;

if ($seedArg) {
    $seedPath = null;
    if (strpos($seedArg, '/') !== false || strpos($seedArg, '\\') !== false) {
        $seedPath = realpath($seedArg) ?: $seedArg;
    } else {
        $candidate = dirname(__DIR__) . '/' . $seedArg;
        if (file_exists($candidate)) {
            $seedPath = $candidate;
        } else {
            $candidate2 = dirname(__DIR__) . '/../' . $seedArg;
            if (file_exists($candidate2)) {
                $seedPath = $candidate2;
            }
        }
    }

    if ($seedPath && file_exists($seedPath)) {
        echo "Running seed file: $seedPath" . PHP_EOL;
        $seedSql = file_get_contents($seedPath);
        try {
            $pdo->exec($seedSql);
            echo "Seed applied: $seedPath" . PHP_EOL;
        } catch (PDOException $e) {
            echo "Error applying seed $seedPath: " . $e->getMessage() . PHP_EOL;
            exit(1);
        }
    } else {
        echo "Seed file not found: $seedArg" . PHP_EOL;
        exit(1);
    }
}
