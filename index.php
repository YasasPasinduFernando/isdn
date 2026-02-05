<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Simple routing
$page = $_GET['page'] ?? 'home';

// Check if user is logged in for protected pages
$protected_pages = ['dashboard', 'products', 'cart', 'orders', 'tracking'];
if (in_array($page, $protected_pages) && !is_logged_in()) {
    redirect('/index.php?page=login');
}

switch ($page) {
    case 'home':
        require __DIR__ . '/views/auth/login.php';
        break;
    case 'login':
        require __DIR__ . '/views/auth/login.php';
        break;
    case 'register':
        require __DIR__ . '/views/auth/register.php';
        break;
    case 'dashboard':
        require __DIR__ . '/views/customer/dashboard.php';
        break;
    case 'products':
        require_once __DIR__ . '/controllers/ProductController.php';
    case 'cart':
        require_once __DIR__ . '/controllers/CartController.php';
        break;
    case 'orders':
        require __DIR__ . '/views/customer/orders.php';
        break;
    case 'tracking':
        require __DIR__ . '/views/customer/tracking.php';
        break;
    case 'payment':
        require __DIR__ . '/views/customer/payment.php';
        break;
    default:
        require __DIR__ . '/views/shared/404.php';
        break;
}
?>
