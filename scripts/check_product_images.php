<?php
require_once __DIR__ . '/../config/database.php';

$total = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$withImage = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE image_url IS NOT NULL AND image_url <> ''")->fetchColumn();

echo "total={$total} with_image={$withImage}" . PHP_EOL;

$stmt = $pdo->query('SELECT product_id, product_code, product_name, image_url FROM products ORDER BY product_id LIMIT 20');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['product_id']} | {$row['product_code']} | {$row['product_name']} | {$row['image_url']}" . PHP_EOL;
}
