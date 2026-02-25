<?php
require_once __DIR__ . '/../config/database.php';

echo "Checking product_categories table..." . PHP_EOL;

$pdo->exec("
CREATE TABLE IF NOT EXISTS product_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
");

echo "Table ready." . PHP_EOL;

$count = (int) $pdo->query("SELECT COUNT(*) FROM product_categories")->fetchColumn();
if ($count === 0) {
    echo "Seeding default categories..." . PHP_EOL;

    $defaults = [
        ['Construction', 'Construction products'],
        ['Finishing', 'Finishing products'],
        ['Plumbing', 'Plumbing products'],
        ['Raw Material', 'Raw material products'],
    ];

    $ins = $pdo->prepare("
        INSERT INTO product_categories (name, description)
        VALUES (:name, :description)
        ON DUPLICATE KEY UPDATE description = VALUES(description)
    ");

    foreach ($defaults as [$name, $description]) {
        $ins->execute([
            'name' => $name,
            'description' => $description,
        ]);
    }
}

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = :table
    ");
    $stmt->execute(['table' => $table]);
    return (bool) $stmt->fetchColumn();
}

if (tableExists($pdo, 'categories') && (int) $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() > 0) {
    echo "Copying categories -> product_categories (if missing)..." . PHP_EOL;
    $pdo->exec("
        INSERT INTO product_categories (name, description)
        SELECT c.name, c.description
        FROM categories c
        LEFT JOIN product_categories pc ON pc.name = c.name
        WHERE pc.category_id IS NULL
    ");
}

echo "Done." . PHP_EOL;
