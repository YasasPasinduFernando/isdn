<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SalesOrder.php';
require_once __DIR__ . '/../models/OrderItem.php';


if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $page = $_GET['page'] ?? '';
    $role = current_user_role();

    if ($page === 'order-info' && $role === 'customer') {
        $orderId = (int) $_GET['id'] ?? '0';
        $orderModel = new SalesOrder($pdo);
        $orderItem = new OrderItem($pdo);

        $customer_order_info = $orderModel->getOrderbyId($orderId);
        $order_items = $orderItem->getOrderItems($orderId);
        $order_totals = $orderItem->calculateOrderTotals($orderId);

        require_once __DIR__ . '/../views/customer/order_info.php';
    } else if ($page === 'order-info' && $role === 'rdc_clerk') {
        $orderId = (int) $_GET['id'] ?? '0';
        $rdcId = (int) $_SESSION['rdc_id'] ?? '0';
        $orderModel = new SalesOrder($pdo);
        $orderItem = new OrderItem($pdo);

        $customer_order_info = $orderModel->getOrderbyId($orderId);
        $order_items = $orderItem->getOrderItemsWithStocks($orderId, $rdcId);
        $order_totals = $orderItem->calculateOrderTotals($orderId);

        require_once __DIR__ . '/../views/rdc-clerk/order_info.php';
    } else if ($page === 'order-info' && $role === 'rdc_sales_ref') {
        $orderId = (int) $_GET['id'] ?? '0';
        $rdcId = (int) $_SESSION['rdc_id'] ?? '0';
        $orderModel = new SalesOrder($pdo);
        $orderItem = new OrderItem($pdo);

        $customer_order_info = $orderModel->getOrderbyId($orderId);
        $order_items = $orderItem->getOrderItemsWithStocks($orderId, $rdcId);
        $order_totals = $orderItem->calculateOrderTotals($orderId);

        require_once __DIR__ . '/../views/rdc-sales-ref/order_info.php';

    }
}