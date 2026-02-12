<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Simple routing
$page = $_GET['page'] ?? 'home';

// Check if user is logged in for protected pages
$protected_pages = [
    'dashboard',
    'profile',
    'products',
    'cart',
    'orders',
    'tracking',
    'payment',
    'rdc-manager-dashboard',
    'rdc-clerk-dashboard',
    'rdc-clerk-promotions',
    'rdc-sales-ref-dashboard',
    'logistics-officer-dashboard',
    'rdc-driver-dashboard',
    'head-office-manager-dashboard',
    'system-admin-dashboard',
    'system-admin-users',
    'system-admin-products',
    'system-admin-promotions',
    'system-admin-profile',
    'system-admin-audit',
    'delivery-report',
    'request-product-units',
    'send-product-units',
    'stock-reports'
];
if (in_array($page, $protected_pages) && !is_logged_in()) {
    redirect('/index.php?page=login');
}
if (in_array($page, $protected_pages) && is_logged_in()) {
    $role = current_user_role();
    if (!is_page_allowed_for_role($role, $page)) {
        $dashboard = dashboard_page_for_role($role);
        redirect('/index.php?page=' . $dashboard);
    }
}

switch ($page) {
    case 'home':
        if (is_logged_in()) {
            $dashboard = dashboard_page_for_role(current_user_role());
            redirect('/index.php?page=' . $dashboard);
        }
        require __DIR__ . '/views/auth/login.php';
        break;
    case 'login':
        require __DIR__ . '/views/auth/login.php';
        break;
    case 'register':
        require __DIR__ . '/views/auth/register.php';
        break;
    case 'forgot-password':
        require __DIR__ . '/views/auth/forgot_password.php';
        break;
    case 'reset-password':
        require __DIR__ . '/views/auth/reset_password.php';
        break;
    case 'dashboard':
        require __DIR__ . '/views/customer/dashboard.php';
        break;
    case 'products':
        require_once __DIR__ . '/controllers/ProductController.php';
        break;
    case 'cart':
        require_once __DIR__ . '/controllers/CartController.php';
        break;
    case 'tracking':
        require __DIR__ . '/views/customer/tracking.php';
        break;
    case 'payment':
        require __DIR__ . '/views/customer/payment.php';
        break;
    case 'rdc-manager-dashboard':
        require __DIR__ . '/views/rdc-manager/dashboard.php';
        break;
    case 'rdc-clerk-dashboard':
        break;
    case 'clerk':
        require __DIR__ . '/views/rdc/clerk_dashboard.php';
        break;
    case 'rdc-clerk-promotions':
        require __DIR__ . '/views/rdc-clerk/promotions.php';
        break;
    case 'rdc-dashboard':
        require __DIR__ . '/views/rdc/dashboard.php';
        break;
    case 'rep':
        require __DIR__ . '/views/rdc/rep_dashboard.php';
        break;
    case 'driver':
        require __DIR__ . '/views/rdc/driver_dashboard.php';
        break;
    case 'ho':
        require __DIR__ . '/views/ho/dashboard.php';
        break;
    case 'rdc-sales-ref-dashboard':
        require __DIR__ . '/views/rdc-sales-ref/dashboard.php';
        break;
    case 'logistics-officer-dashboard':
        require __DIR__ . '/views/logistics-officer/dashboard.php';
        break;
    case 'rdc-driver-dashboard':
        require __DIR__ . '/views/rdc-driver/dashboard.php';
        break;
    case 'head-office-manager-dashboard':
        require __DIR__ . '/views/head-office-manager/dashboard.php';
        break;
    case 'system-admin-dashboard':
        require __DIR__ . '/views/system-admin/dashboard.php';
        break;
    case 'system-admin-users':
        require __DIR__ . '/views/system-admin/users.php';
        break;
    case 'system-admin-products':
        require __DIR__ . '/views/system-admin/products.php';
        break;
    case 'system-admin-promotions':
        require __DIR__ . '/views/system-admin/promotions.php';
        break;
    case 'system-admin-profile':
        require __DIR__ . '/views/system-admin/profile.php';
        break;
    case 'system-admin-audit':
        require __DIR__ . '/views/system-admin/audit_logs.php';
        break;
    case 'delivery-report':
        require __DIR__ . '/views/reports/delivery_efficiency.php';
        break;
    case 'request-product-units':
        require __DIR__ . '/views/stock-management/request_product_units.php';
        break;
    case 'send-product-units':
        require __DIR__ . '/views/stock-management/send_product_units.php';
        break;
    case 'stock-reports':
        require __DIR__ . '/views/stock-management/stock_reports.php';
        break;
    case 'customer-sales-orders':
        require __DIR__ . '/controllers/SalesOrderController.php';
        break;
    case 'rdc-sales-ref-sales-orders':
        require __DIR__ . '/controllers/SalesOrderController.php';
        break;
    case 'rdc-clerk-sales-orders':
        require __DIR__ . '/controllers/SalesOrderController.php';
        break;
    case 'head-office-manager-sales-orders':
        require __DIR__ . '/controllers/SalesOrderController.php';
        break;
    case 'order-info':
        require __DIR__ . '/views/customer/order_info.php';
        break;
    case 'register-product':
        require __DIR__ . '/views/customer/register_product.php';
        break;
    case 'products-list':
        require __DIR__ . '/views/customer/products_list.php';
        break;
    case 'checkout':
        require __DIR__ . '/views/customer/order_checkout.php';
        break;
    default:
        require __DIR__ . '/views/shared/404.php';
        break;
}
?>