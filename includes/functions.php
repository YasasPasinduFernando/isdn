<?php
// Helper Functions
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user_role() {
    return $_SESSION['role'] ?? null;
}

function dashboard_page_for_role($role) {
    $map = [
        'customer' => 'dashboard',
        'rdc_manager' => 'rdc-manager-dashboard',
        'rdc_clerk' => 'rdc-clerk-dashboard',
        'rdc_sales_ref' => 'rdc-sales-ref-dashboard',
        'logistics_officer' => 'logistics-officer-dashboard',
        'rdc_driver' => 'rdc-driver-dashboard',
        'head_office_manager' => 'head-office-manager-dashboard',
        'system_admin' => 'system-admin-dashboard'
    ];

    return $map[$role] ?? 'dashboard';
}

function get_allowed_pages_for_role($role) {
    $map = [
        'customer' => [
            'dashboard', 'products', 'cart', 'orders', 'tracking', 'payment'
        ],
        'rdc_manager' => [
            'rdc-manager-dashboard', 'request-product-units', 'send-product-units', 'stock-reports'
        ],
        'rdc_clerk' => [
            'rdc-clerk-dashboard', 'request-product-units', 'stock-reports'
        ],
        'rdc_sales_ref' => [
            'rdc-sales-ref-dashboard', 'products', 'orders'
        ],
        'logistics_officer' => [
            'logistics-officer-dashboard', 'stock-reports', 'tracking'
        ],
        'rdc_driver' => [
            'rdc-driver-dashboard', 'tracking'
        ],
        'head_office_manager' => [
            'head-office-manager-dashboard', 'stock-reports'
        ],
        'system_admin' => [
            'system-admin-dashboard', 'stock-reports'
        ]
    ];

    return $map[$role] ?? ['dashboard'];
}

function is_page_allowed_for_role($role, $page) {
    $allowed = get_allowed_pages_for_role($role);
    return in_array($page, $allowed, true);
}

function get_nav_items_for_role($role) {
    $map = [
        'customer' => [
            'dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
            'products' => ['icon' => 'shopping_bag', 'label' => 'Products'],
            'orders' => ['icon' => 'receipt_long', 'label' => 'Orders'],
            'cart' => ['icon' => 'shopping_cart', 'label' => 'Cart'],
            'tracking' => ['icon' => 'location_on', 'label' => 'Tracking']
        ],
        'rdc_manager' => [
            'rdc-manager-dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
            'request-product-units' => ['icon' => 'inbox', 'label' => 'Requests'],
            'send-product-units' => ['icon' => 'local_shipping', 'label' => 'Dispatch'],
            'stock-reports' => ['icon' => 'bar_chart', 'label' => 'Reports']
        ],
        'rdc_clerk' => [
            'rdc-clerk-dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
            'request-product-units' => ['icon' => 'inbox', 'label' => 'Requests'],
            'stock-reports' => ['icon' => 'bar_chart', 'label' => 'Reports']
        ],
        'rdc_sales_ref' => [
            'rdc-sales-ref-dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
            'products' => ['icon' => 'shopping_bag', 'label' => 'Products'],
            'orders' => ['icon' => 'receipt_long', 'label' => 'Orders']
        ],
        'logistics_officer' => [
            'logistics-officer-dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
            'stock-reports' => ['icon' => 'bar_chart', 'label' => 'Reports'],
            'tracking' => ['icon' => 'location_on', 'label' => 'Tracking']
        ],
        'rdc_driver' => [
            'rdc-driver-dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
            'tracking' => ['icon' => 'location_on', 'label' => 'Tracking']
        ],
        'head_office_manager' => [
            'head-office-manager-dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
            'stock-reports' => ['icon' => 'bar_chart', 'label' => 'Reports']
        ],
        'system_admin' => [
            'system-admin-dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
            'stock-reports' => ['icon' => 'bar_chart', 'label' => 'Reports']
        ]
    ];

    return $map[$role] ?? [
        'dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard']
    ];
}

function redirect($url) {
    header("Location: " . BASE_PATH . $url);
    exit();
}

function flash_message($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

function display_flash() {
    $flash = get_flash_message();
    if ($flash) {
        $bgColor = $flash['type'] === 'success' ? 'bg-green-500' : 'bg-red-500';
        echo "<div class='$bgColor text-white px-6 py-4 rounded-lg mb-4'>{$flash['message']}</div>";
    }
}
?>
