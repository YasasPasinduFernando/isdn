<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SalesOrder.php';
require_once __DIR__ . '/../models/ShoppingCart.php';
require_once __DIR__ . '/../models/RetailCustomer.php';
require_once __DIR__ . '/../dummydata/Orders.php';

$page = $_GET['page'] ?? '';
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


if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && $page === 'sales-orders'
    && isset($_GET['method'])
) {

    $method = $_GET['method'] ?? '';
    $userId = $_SESSION['user_id'] ?? 1; // demo user

    if ($method === 'cash') {

        // Read JSON body (since fetch sends JSON)
        $input = json_decode(file_get_contents("php://input"), true);
        $deliveryNotes = $input['delivery_notes'] ?? '';

        // TODO: Save order here using $deliveryNotes

        // Example response data
        $orderModel = new SalesOrder($pdo);
        $retail_customer = new RetailCustomer(pdo: $pdo);
        $userCartItems = new ShoppingCart($pdo);
        $userCartItems = $userCartItems->getUserCart($userId);
        $customer_info = $retail_customer->findByUserId($userId);

        $orderId = $orderModel->placeOrder($customer_info['id'], $userId, $userCartItems);
        $order_info = $orderModel->getOrderbyId($orderId)[0];
        $date = new DateTime($order_info['estimated_date']);
        $payment_date = $date->format('d M, Y');
        $cash_payment_info = [
            "invoice_no" => "INV-" . $order_info['order_number'],
            "customer_name" => $order_info['name'],
            "payment_amount" => number_format($order_info['total_amount'], 2),
            "payment_date_label" => "Payment Due Date",
            "payment_date" => $payment_date,
        ];

        // Store in session to use in success page
        $_SESSION['cash_payment_info'] = $cash_payment_info;

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'redirect' => 'index.php?page=payment-success'
        ]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $page = $_GET['page'] ?? '';
    $method = $_GET['method'] ?? '';

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
    } else if ($page === 'rdc-clerk-sales-orders') {
        $userId = $_SESSION['user_id'] ?? 1;
        $orderModel = new SalesOrder($pdo);
        $userOrders = $clerkOrders;//$orderModel->getUserOrders($userId);
        require_once __DIR__ . '/../views/rdc-clerk/orders.php';
    } else if ($page === 'head-office-manager-sales-orders') {
        $userId = $_SESSION['user_id'] ?? 1;
        $orderModel = new SalesOrder($pdo);
        $userOrders = $headOfficeOrders;//$orderModel->getUserOrders($userId);
        require_once __DIR__ . '/../views/head-office-manager/orders.php';
    }
    /*else if ($page === 'sales-orders' && $method === 'cash') {
        $cash_payment_info = [
            "invoice_no" => "INV-ORD-RDCS-260213-1025",
            "customer_name" => "Vijya Stores",
            "payment_amount" => "18,750.00",
            "payment_date_label" => "Payment Due Date",
            "payment_date" => "15 Feb, 2026",
        ];
        /// save order and display success page
        require_once __DIR__ . '/../views/shared/payment_success.php';
    } else if ($page === 'sales-orders' && $method === 'card') {

        require_once __DIR__ . '/../views/customer/payment.php';
    }*/
}