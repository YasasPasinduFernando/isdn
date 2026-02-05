<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ShoppingCart.php';

$userId = $_SESSION['user_id'] ?? 1; // logged user

$cart = new ShoppingCart($pdo);
$action = $_GET['action'] ?? 'view';

$data = json_decode(file_get_contents("php://input"), true);

switch ($action) {

    case 'add':
        $cart->addToCart($userId, (int)$data['product_id'], (int)$data['qty']);
        echo json_encode(['success' => true]);
        break;

    case 'update':
        $cart->updateQty($userId, (int)$data['product_id'], (int)$data['qty']);
        echo json_encode(['success' => true]);
        break;

    case 'remove':
        $cart->removeItem($userId, (int)$data['product_id']);
        echo json_encode(['success' => true]);
        break;

    case 'clear':
        $cart->clearCart($userId);
        echo json_encode(['success' => true]);
        break;

    default:
        $cartItems = $cart->getUserCart($userId);
        require __DIR__ . '/../views/customer/cart.php';
}

?>
