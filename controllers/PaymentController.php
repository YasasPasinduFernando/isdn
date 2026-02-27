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
    }
}