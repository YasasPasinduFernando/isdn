<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SalesOrder.php';
require_once __DIR__ . '/../models/ShoppingCart.php';
require_once __DIR__ . '/../models/RetailCustomer.php';
require_once __DIR__ . '/../models/OrderItem.php';
require_once __DIR__ . '/../includes/MailSender.php';

$page = $_GET['page'] ?? '';
$userId = $_SESSION['user_id'] ?? 1;

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && $page === 'sales-orders'
    && isset($_GET['action'])
    && $_GET['action'] === 'place'
) {

    $method = $_GET['method'] ?? '';
    $deliveryNotes = '';
    $payment_date_label = '';
    $payment_date = '';
    $orderModel = new SalesOrder($pdo);
    $retail_customer = new RetailCustomer(pdo: $pdo);
    $shopping_cart = new ShoppingCart($pdo);
    $orderItem = new OrderItem($pdo);
    if ($method === 'cash') {
        // Read JSON body (since fetch sends JSON)
        $input = json_decode(file_get_contents("php://input"), true);
        $deliveryNotes = $input['delivery_notes'] ?? '';
        $payment_date_label = "Payment Due Date";
        $date = new DateTime();
        $date->modify('+2 days');
        $payment_date = $date->format('d M, Y');

    } else if ($method === 'card') {
        $deliveryNotes = $_SESSION['delivery_notes'] ?? '';
        $payment_date_label = "Payment Date";
        $date = new DateTime();
        $payment_date = $date->format('d M, Y');
    }

    $userCartItems = $shopping_cart->getUserCart($userId);
    $customer_info = $retail_customer->findByUserId($userId);
    $orderId = $orderModel->placeOrder($customer_info['id'], $userId, $userCartItems);
    $order_info = $orderModel->getOrderbyId($orderId);
    $order_items = $orderItem->getOrderItems($orderId);
    $order_totals = $orderItem->calculateOrderTotals($orderId);
    $invoice_location = "/invoices/ISDN-Invoice-" . $order_info['order_number'] . ".pdf";
    Mailsender::sendMailAndGenerateInvoice([
        'delivery_notes' => $deliveryNotes,
        'order_info' => $order_info,
        'order_items' => $order_items,
        'order_totals' => $order_totals
    ]);

    $cash_payment_info = [
        "invoice_no" => "INV-" . $order_info['order_number'],
        "customer_name" => $order_info['name'],
        "payment_amount" => number_format($order_info['total_amount'], 2),
        "payment_date_label" => $payment_date_label,
        "payment_date" => $payment_date,
        "invoice_path" => $invoice_location,
    ];
    // Store in session to use in success page
    $_SESSION['cash_payment_info'] = $cash_payment_info;
    $shopping_cart->clearCart($userId);

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'redirect' => 'index.php?page=payment-success'
    ]);
    exit;

} else if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && $page === 'sales-orders'
    && isset($_GET['action'])
    && $_GET['action'] === 'pay'
) {
    $input = json_decode(file_get_contents("php://input"), true);
    $deliveryNotes = $input['delivery_notes'] ?? '';
    $shopping_cart = new ShoppingCart($pdo);
    $cartAmount = $shopping_cart->getUserCartAmount($userId);
    $tax_percentage = 15;
    $delivery_fee = 1450;

    // Safely extract total (default 0)
    $cartTotal = (float) ($cartAmount['cart_total'] ?? 0);

    $taxPercentage = (float) $tax_percentage;
    $deliveryFee = (float) $delivery_fee;

    $cartTax = round($cartTotal * $taxPercentage / 100, 2);
    $cartGrandTotal = round($cartTotal + $cartTax + $deliveryFee, 2);
    $_SESSION['checkout'] = [
        'delivery_notes' => $deliveryNotes,
        'cart_grand_total' => $cartGrandTotal
    ];

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'redirect' => 'index.php?page=payment'
    ]);
    exit;

}

  $orderData = [
    'order_no' => 'ORD-RDCS-260213-1025',
    'order_date' => '13 Feb 2026',
    'customer' => 'Vijaya Stores - Galle',
    'payment_method' => 'Card Payment',
    'status' => 'Pending',
    'grand_total' => 'Rs. 18,751.46',
    'subtotal' => 'Rs. 15,044.75',
    'discount' => 'Rs. 310.25',
    'vat' => '15%',
    'delivery_fee' => 'Rs. 1,450.00',
    'address' => 'No. 62, Matara Road, Galle, Sri Lanka'
  ];

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
        $invoicePath = InvoiceGenerator::generate($orderData);
        Mailsender::sendMail();

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