<?php
require_once __DIR__ . '/../config/database.php';

$images = [
    'assets/images/products/bcbt.jpg',
    'assets/images/products/clogard.jpg',
    'assets/images/products/cocacola.jpeg',
    'assets/images/products/cocacola2.jpeg',
    'assets/images/products/dettol-bw.jpg',
    'assets/images/products/harpic.jpg',
    'assets/images/products/nestomalt.jpg',
    'assets/images/products/ns-fw.jpg',
    'assets/images/products/strepsils.jpg',
    'assets/images/products/sunlight.jpeg',
    'assets/images/products/sunlight.jpg',
];

$available = [];
foreach ($images as $img) {
    if (file_exists(__DIR__ . '/../' . $img)) {
        $available[] = $img;
    }
}

if (empty($available)) {
    echo "No product images found in assets/images/products" . PHP_EOL;
    exit(1);
}

$products = $pdo->query("SELECT product_id FROM products ORDER BY product_id")->fetchAll(PDO::FETCH_ASSOC);
$update = $pdo->prepare("UPDATE products SET image_url = :image WHERE product_id = :id");

$i = 0;
foreach ($products as $product) {
    $img = $available[$i % count($available)];
    $update->execute([
        'image' => $img,
        'id' => (int) $product['product_id'],
    ]);
    $i++;
}

echo "Backfilled image_url for {$i} products." . PHP_EOL;
