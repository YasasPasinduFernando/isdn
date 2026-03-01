<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ShoppingCart.php';
require_once __DIR__ . '/../models/RetailCustomer.php';


$userId = $_SESSION['user_id'] ?? 1; // logged user

$retail_customer = new RetailCustomer($pdo);
$action = $_GET['action'] ?? 'view';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $page = $_GET['page'] ?? '';

    if ($page === 'payment-success') {
        $cash_payment_info = $_SESSION['cash_payment_info'] ?? [];
        require __DIR__ . '/../views/shared/payment_success.php';
    } else if ($page === 'payment') {

        //get cart total and show 
        $checkout_info = $_SESSION['checkout'] ?? [];
        require __DIR__ . '/../views/shared/payment_info.php';

    } else if ($page === 'payment-gateway') {
         $checkout_info = $_SESSION['checkout'] ?? [];
        require __DIR__ . '/../views/shared/payment_gateway.php';

    }
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === 'payment' && $action === 'pay') {
    header('Content-Type: application/json');
    // Save Payment
    echo json_encode([
        'success' => true,
    ]);
    exit;
    }
}