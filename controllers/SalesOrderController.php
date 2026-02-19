<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SalesOrder.php';
require_once __DIR__ . '/../models/ShoppingCart.php';
require_once __DIR__ . '/../dummydata/Orders.php';


if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_GET['action'])
    && $_GET['action'] === 'place'
) {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || empty($data['items'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart data']);
        exit;
    }

    $userId = $_SESSION['user_id'] ?? 1; // demo user


    try {
        $orderModel = new SalesOrder($pdo);
        $userCartItems = new ShoppingCart($pdo);
        $userCartItems = $userCartItems->getUserCart($userId);

        $orderId = $orderModel->placeOrder($userId, $userId, $userCartItems);

        echo json_encode([
            'success' => true,
            'order_id' => $orderId
        ]);
        exit;

    } catch (Exception $e) {
        echo $e;
        echo json_encode([
            'success' => false,
            'message' => 'Order processing failed'
        ]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $page = $_GET['page'] ?? '';

    if ($page === 'customer-sales-orders') {
        $userId = $_SESSION['user_id'] ?? 1;
        $orderModel = new SalesOrder($pdo);
        $userOrders = $orders;//$orderModel->getUserOrders($userId);
        require_once __DIR__ . '/../views/customer/orders.php';
    } else if ($page === 'rdc-sales-ref-sales-orders') {
        $userId = $_SESSION['user_id'] ?? 1;
        $orderModel = new SalesOrder($pdo);
        $userOrders = $refOrders;//$orderModel->getUserOrders($userId);
        require_once __DIR__ . '/../views/rdc-sales-ref/orders.php';
    }else if ($page === 'rdc-clerk-sales-orders') {
        $userId = $_SESSION['user_id'] ?? 1;
        $orderModel = new SalesOrder($pdo);
        $userOrders = $clerkOrders;//$orderModel->getUserOrders($userId);
        require_once __DIR__ . '/../views/rdc-clerk/orders.php';
    }else if ($page === 'head-office-manager-sales-orders') {
        $userId = $_SESSION['user_id'] ?? 1;
        $orderModel = new SalesOrder($pdo);
        $userOrders = $headOfficeOrders;//$orderModel->getUserOrders($userId);
        require_once __DIR__ . '/../views/head-office-manager/orders.php';
    }
}