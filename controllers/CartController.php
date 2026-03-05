<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ShoppingCart.php';
require_once __DIR__ . '/../dummydata/ShoppingCart.php';


$userId = $_SESSION['user_id'] ?? 1; // logged user

$cart = new ShoppingCart($pdo);
$action = $_GET['action'] ?? 'view';

$data = json_decode(file_get_contents("php://input"), true);

switch ($action) {

    case 'add':
        $cart->addToCart($userId, (int) ($data['product_id'] ?? 0), (int) ($data['qty'] ?? 0));

        $cartCountRow = $cart->getUserCartCount($userId);
        $cartAmountRow = $cart->getUserCartAmount($userId);

        // Safe defaults
        $cartCount = (int) ($cartCountRow['product_count'] ?? 0);
        $subTotal = (float) ($cartAmountRow['cart_total'] ?? 0.0);

        // Calculate totals
        $taxRate = 0.15;
        $deliveryCharges = 1450;

        $taxAmount = $subTotal * $taxRate;
        $grandTotal = $subTotal + $taxAmount + $deliveryCharges;

        // (Optional) format to 2 decimals (keep as number, not string)
        $grandTotal = round($grandTotal, 2);

        echo json_encode([
            'success' => true,
            'cart_count' => $cartCount,
            'cart_total' => $grandTotal,
        ]);

        break;
    case 'update':
        $cart->updateQty($userId, (int) $data['product_id'], (int) $data['qty']);
        echo json_encode(['success' => true]);
        break;

    case 'remove':
        $cart->removeItem($userId, (int) $data['product_id']);
        echo json_encode(['success' => true]);
        break;

    case 'clear':
        $cart->clearCart($userId);
        echo json_encode(['success' => true]);
        break;

    default:
        //$cartItems = $shopping_cart;//$cart->getUserCart($userId);
        $cartItems = $cart->getUserCart($userId);
        $products_count = $cart->getUserCartCount($userId);
        $cart_count = (int) ($products_count['product_count'] ?? 0);
        require __DIR__ . '/../views/customer/cart.php';
}

?>