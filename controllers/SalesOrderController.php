<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SalesOrder.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_GET['action'])
    && $_GET['action'] === 'place') {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || empty($data['items'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart data']);
        exit;
    }

    $userId = $_SESSION['user_id'] ?? 1; // demo user
    $items  = $data['items'];

    try {
        $orderModel = new SalesOrder($pdo);
        $orderId = $orderModel->placeOrder($userId, $userId, $items);

        echo json_encode([
            'success' => true,
            'order_id'=> $orderId
        ]);
        exit;

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Order processing failed'
        ]);
        exit;
    }
}
