<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SalesOrder.php';
require_once __DIR__ . '/../models/OrderItem.php';


if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $orderId = (int) $_GET['id'] ?? '0';
    $orderModel = new SalesOrder($pdo);
    $orderItem = new OrderItem($pdo);

    $customer_order_info = $orderModel->getOrderbyId($orderId);
    $order_items = $orderItem->getOrderItems($orderId);
    $order_totals = $orderItem->calculateOrderTotals($orderId);

    require_once __DIR__ . '/../views/customer/order_info.php';
}