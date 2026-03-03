<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SalesOrder.php';
require_once __DIR__ . '/../models/ShoppingCart.php';
require_once __DIR__ . '/../models/RetailCustomer.php';
require_once __DIR__ . '/../models/OrderItem.php';
require_once __DIR__ . '/../includes/MailSender.php';

$page = $_GET['page'] ?? '';
$userId = $_SESSION['user_id'] ?? 1;

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';
$action = $_GET['action'] ?? '';

if ($requestMethod === 'POST' && $page === 'sales-orders' && $action === 'place') {

    $method = $_GET['method'] ?? '';

    $deliveryNotes = '';
    $payment_date_label = '';
    $payment_date = '';

    $input = null;

    if ($method === 'cash') {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $deliveryNotes = $input['delivery_notes'] ?? '';
        $payment_date_label = 'Payment Due Date';

        $date = new DateTime();
        $date->modify('+2 days');
        $payment_date = $date->format('d M, Y');

    } else if ($method === 'card') {
        $deliveryNotes = $_SESSION['delivery_notes'] ?? '';
        $payment_date_label = 'Payment Date';

        $payment_date = (new DateTime())->format('d M, Y');
    }

    $orderModel = new SalesOrder($pdo);
    $retail_customer = new RetailCustomer(pdo: $pdo);
    $shopping_cart = new ShoppingCart($pdo);
    $orderItem = new OrderItem($pdo);

    $userCartItems = $shopping_cart->getUserCart($userId);
    $customer_info = $retail_customer->findByUserId($userId);


    $orderId = $orderModel->placeOrder($customer_info['id'], $userId, $userCartItems);

    $order_info = $orderModel->getOrderbyId($orderId);
    $order_items = $orderItem->getOrderItems($orderId);
    $order_totals = $orderItem->calculateOrderTotals($orderId);

    $invoice_location = '/invoices/ISDN-Invoice-' . $order_info['order_number'] . '.pdf';

    Mailsender::sendMailAndGenerateInvoice([
        'delivery_notes' => $deliveryNotes,
        'order_info' => $order_info,
        'order_items' => $order_items,
        'order_totals' => $order_totals
    ]);

    $_SESSION['cash_payment_info'] = [
        'invoice_no' => 'INV-' . $order_info['order_number'],
        'customer_name' => $order_info['customer'],
        'payment_amount' => number_format($order_info['total_amount'], 2),
        'payment_date_label' => $payment_date_label,
        'payment_date' => $payment_date,
        'invoice_path' => $invoice_location,
    ];

    $shopping_cart->clearCart($userId);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'redirect' => 'index.php?page=payment-success'
    ]);
    exit;

} else if ($requestMethod === 'POST' && $page === 'sales-orders' && $action === 'pay') {

    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $deliveryNotes = $input['delivery_notes'] ?? '';

    $retail_customer = new RetailCustomer(pdo: $pdo);
    $shopping_cart = new ShoppingCart($pdo);
    $orderModel = new SalesOrder($pdo);
    $orderItem = new OrderItem($pdo);

    $customer_info = $retail_customer->findByUserId($userId);
    $userCartItems = $shopping_cart->getUserCart($userId);
    $cartAmount = $shopping_cart->getUserCartAmount($userId);

    $orderId = $orderModel->placeOrder($customer_info['id'], $userId, $userCartItems);
    $shopping_cart->clearCart($userId);
    $order_info = $orderModel->getOrderbyId($orderId);
    $order_totals = $orderItem->calculateOrderTotals($orderId);

    $_SESSION['checkout'] = [
        'order_id' => $orderId,
        'delivery_notes' => $deliveryNotes,
        'cart_grand_total' => $order_totals['grand_total']
    ];

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'redirect' => 'index.php?page=payment'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $page = $_GET['page'] ?? '';
    $method = $_GET['method'] ?? '';

    if ($page === 'customer-sales-orders') {
        $userId = $_SESSION['user_id'] ?? 1;
        $retail_customer = new RetailCustomer(pdo: $pdo);
        $customer_info = $retail_customer->findByUserId($userId);
        //get orders by customers
        $orderModel = new SalesOrder($pdo);
        $userOrders = $orderModel->getCustomerOrders($customer_info['id']);
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