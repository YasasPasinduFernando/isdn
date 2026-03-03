<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/SalesOrder.php';
require_once __DIR__ . '/../../models/ShoppingCart.php';
require_once __DIR__ . '/../../models/RetailCustomer.php';
require_once __DIR__ . '/../../models/OrderItem.php';

$page = $_GET['page'] ?? '';
$userId = $_SESSION['user_id'] ?? 1;

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';
$action = $_GET['action'] ?? '';



if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $page = $_GET['page'] ?? '';
    $method = $_GET['method'] ?? '';

    if ($page === 'rdc-clerk-sales-orders') {
        $rdc_id = $_SESSION['rdc_id'] ?? 1;
        $orderModel = new SalesOrder($pdo);
        $userOrders = $orderModel->getRdcOrders($rdc_id);
        require_once __DIR__ . '/../../views/rdc-clerk/orders.php';
    }
}

// inside your router/controller
if ($requestMethod === 'POST' && $page === 'rdc-clerk-sales-orders' && $action === 'update') {

    header('Content-Type: application/json');

    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            throw new InvalidArgumentException('Invalid request body.');
        }
        $rdc_id = (int) $_SESSION['rdc_id'] ?? 1;
        $orderId = (int) ($input['order_id'] ?? 0);
        $orderStatus = strtolower(trim($input['order_status'] ?? ''));

        if ($orderId <= 0) {
            throw new InvalidArgumentException('Invalid order_id.');
        }

        $allowedStatuses = ['processing', 'in_transit', 'delivered', 'cancelled'];
        if (!in_array($orderStatus, $allowedStatuses, true)) {
            throw new InvalidArgumentException('Invalid order status.');
        }

        // Build models
        $orderModel = new SalesOrder($pdo);
        $orderItem = new OrderItem($pdo);

        // IMPORTANT: $rdc_id must be set securely from session/user context
        // Example:
        // $rdc_id = (int)($_SESSION['user']['rdc_id'] ?? 0);

        if (empty($rdc_id)) {
            throw new RuntimeException('RDC not identified.');
        }

        if ($orderStatus === 'processing') {
            // Validate stock first
            if (!$orderItem->validateStockAvailability($orderId, $rdc_id)) {
                echo json_encode(['success' => false, 'message' => 'Insufficient stocks.']);
                exit;
            }

            // Update status
            $orderModel->updateOrderStatus($orderId, $orderStatus);

            // Deduct stock
            $items = $orderItem->getOrderItems($orderId);
            $orderItem->updateProductStocks($items, $rdc_id, 'deduct');

        } elseif ($orderStatus === 'cancelled') {
            // Update status
            $orderModel->updateOrderStatus($orderId, $orderStatus);

            // Usually, cancelled orders should restore stock if it was deducted earlier.
            // If your business rule is different, tell me.
            $items = $orderItem->getOrderItems($orderId);
            $orderItem->updateProductStocks($items, $rdc_id, 'add');

        } else {
            // in_transit / delivered: status update only
            $orderModel->updateOrderStatus($orderId, $orderStatus);
        }

        // Set flash message to show after redirect
        $_SESSION['flash_success'] = "Order status updated to " . ucwords(str_replace('_', ' ', $orderStatus)) . ".";

        echo json_encode(['success' => true]);
        exit;

    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}