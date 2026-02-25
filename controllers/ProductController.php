<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/SystemAdmin.php';

$adminModel = new SystemAdmin($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page = $_GET['page'] ?? '';

    if ($page === 'products') {
        // Use the same DB source as admin products to keep customer/admin data consistent.
        $rows = $adminModel->getProducts(1, 500, '', '');

        $products = array_map(static function (array $p): array {
            $stock = (int) ($p['total_stock'] ?? 0);
            $minStock = (int) ($p['minimum_stock_level'] ?? 0);
            $isActive = (int) ($p['is_active'] ?? 1) === 1;

            $availability = 'In Stock';
            if (!$isActive || $stock <= 0) {
                $availability = 'Out of Stock';
            } elseif ($minStock > 0 && $stock < $minStock) {
                $availability = 'Low Stock';
            }

            $imageUrl = trim((string) ($p['image_url'] ?? ''));
            if ($imageUrl !== '' && $imageUrl[0] !== '/') {
                $imageUrl = '/' . $imageUrl;
            }

            return [
                'product_id' => $p['product_id'] ?? null,
                'product_code' => $p['product_code'] ?? '',
                'product_name' => $p['product_name'] ?? '',
                'promotion' => '',
                'category' => $p['category'] ?? '',
                'unit_price' => (float) ($p['unit_price'] ?? 0),
                'minimum_stock_level' => $minStock,
                'image_url' => $imageUrl,
                'is_active' => $isActive ? 1 : 0,
                'description' => (string) ($p['description'] ?? ''),
                'rating' => 4.5,
                'available_quantity' => $stock,
                'availability' => $availability,
            ];
        }, $rows);

        require_once __DIR__ . '/../views/customer/products.php';
    }

    if ($page === 'admin-products-list') {
        // Legacy route support: forward to the current admin products page.
        redirect('/index.php?page=system-admin-products');
    }
}
?>
