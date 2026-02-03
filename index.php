<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Simple routing
$page = $_GET['page'] ?? 'home';

// Check if user is logged in for protected pages
$protected_pages = ['dashboard', 'products', 'cart', 'orders', 'tracking', 'rdc-clerk-dashboard', 'request-product-units'];
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
        require __DIR__ . '/views/customer/products.php';
        break;
    case 'cart':
        require __DIR__ . '/views/customer/cart.php';
        break;
    case 'orders':
        require __DIR__ . '/views/customer/orders.php';
        break;
    case 'tracking':
        require __DIR__ . '/views/customer/tracking.php';
        break;
    case 'rdc-clerk-dashboard':
        require __DIR__ . '/views/rdc-clerk/dashboard.php';
        break;
    case 'request-product-units':
        require __DIR__ . '/views/stock-management/request_product_unit.php';
        break;
    default:
        require __DIR__ . '/views/shared/404.php';
        break;
}
?>
