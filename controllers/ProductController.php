<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../dummydata/Products.php';


$productModel = new Product($pdo);



if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $page = $_GET['page'] ?? '';

    if ($page === 'products') {
        $products = $retail_products;//$productModel->findAllProducts();
        require_once __DIR__ . '/../views/customer/products.php';
    }

    if ($page === 'admin-products-list') {
        $products = $retail_products;//$productModel->findAllProducts();
        require_once __DIR__ . '/../views/system-admin/products.php';
    }
}

?>
