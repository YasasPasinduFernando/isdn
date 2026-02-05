<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Product.php';


$productModel = new Product($pdo);



if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $page = $_GET['page'] ?? '';

    if ($page === 'products') {
        $products = $productModel->findAllProducts();
        require_once __DIR__ . '/../views/customer/products.php';
    }
}

?>
