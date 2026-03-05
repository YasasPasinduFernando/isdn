<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/ShoppingCart.php';
require_once __DIR__ . '/../../models/RetailCustomer.php';


$userId = $_SESSION['user_id'] ?? 1; // logged user
$rdc_id = $_SESSION['rdc_id']?? 1; 

$cart = new ShoppingCart($pdo);
$retail_customer = new RetailCustomer(pdo: $pdo);
$action = $_GET['action'] ?? 'view';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $page = $_GET['page'] ?? '';

    if ($page === 'checkout') {
        $order_items = $cart->getUserCart($userId);
        $products_count = $cart->getUserCartCount($userId);
        $customer_list = $retail_customer->findByRdcId($rdc_id);
        $cart_count = $products_count['product_count'];
        require __DIR__ . '/../../views/rdc-sales-ref/order_checkout.php';
    }
}