<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ShoppingCart.php';
require_once __DIR__ . '/../models/SalesOrder.php';
require_once __DIR__ . '/../models/OrderItem.php';
require_once __DIR__ . '/../models/RetailCustomer.php';
require_once __DIR__ . '/../models/OrderPayment.php';
require_once __DIR__ . '/../includes/MailSender.php';



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
        // generate invoice and send mail
        $orderModel = new SalesOrder($pdo);
        $retail_customer = new RetailCustomer(pdo: $pdo);
        $shopping_cart = new ShoppingCart($pdo);
        $orderItem = new OrderItem($pdo);
        $orderPayment = new OrderPayment($pdo);
        $card_payment_info = $_SESSION['checkout'];

        $orderId = $card_payment_info['order_id'];
        $order_info = $orderModel->getOrderbyId($orderId);
        $order_items = $orderItem->getOrderItems($orderId);
        $order_totals = $orderItem->calculateOrderTotals($orderId);

        $payment_info = [
            'order_id' => $orderId,
            'payment_method' => 'CARD',
            'order_total' => $order_totals['grand_total']
        ];
        $orderPayment->savePayment($payment_info);

        $invoice_location = '/invoices/ISDN-Invoice-' . $order_info['order_number'] . '.pdf';

        Mailsender::sendMailAndGenerateInvoice([
            'delivery_notes' => $card_payment_info['delivery_notes'],
            'order_info' => $order_info,
            'order_items' => $order_items,
            'order_totals' => $order_totals
        ]);

        $payment_date_label = 'Payment Date';
        $payment_date = (new DateTime())->format('d M, Y');
        $_SESSION['cash_payment_info'] = [
            'invoice_no' => 'INV-' . $order_info['order_number'],
            'customer_name' => $order_info['customer'],
            'payment_amount' => number_format($order_info['total_amount'], 2),
            'payment_date_label' => $payment_date_label,
            'payment_date' => $payment_date,
            'invoice_path' => $invoice_location,
        ];
        echo json_encode([
            'success' => true,
            'redirect' => 'index.php?page=payment-success'

        ]);
        exit;
    } else if ($page === 'payment' && $action === 'proceed') {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        header('Content-Type: application/json');

        // If not JSON, fallback to $_POST
        if (!is_array($input) || empty($input)) {
            $input = $_POST;
        }

        // Sanitize inputs
        $mobileNumber = trim($input['mobile_number'] ?? '');
        $email = trim($input['email'] ?? '');
        $paymentMethod = strtolower(trim($input['payment_method'] ?? ''));

        // Basic validation (server-side validation is mandatory)
        if (empty($mobileNumber) || empty($email) || empty($paymentMethod)) {
            echo json_encode([
                'success' => false,
                'message' => 'All fields are required.'
            ]);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid email address.'
            ]);
            exit;
        }

        // Optional: Validate payment method strictly
        $allowedMethods = ['visa', 'mastercard'];
        if (!in_array($paymentMethod, $allowedMethods, true)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid payment method.'
            ]);
            exit;
        }

        // Save into session (merge with existing checkout data)
        $_SESSION['checkout'] = array_merge(
            $_SESSION['checkout'] ?? [],
            [
                'mobile_number' => $mobileNumber,
                'email' => $email,
                'payment_method' => $paymentMethod,
                // keep previously stored values like:
                // 'delivery_notes'
                // 'cart_grand_total'
            ]
        );

        echo json_encode([
            'success' => true
        ]);

        exit;
    }
}