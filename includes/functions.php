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

/** Profile page key for header link; system_admin uses system-admin-profile, others use profile. */
function get_profile_page_for_role($role) {
    if ($role === 'system_admin') {
        return 'system-admin-profile';
    }
    return 'profile';
}

function get_allowed_pages_for_role($role) {
    $map = [
        'customer' => [
            'dashboard', 'products', 'cart', 'customer-sales-orders', 'tracking', 'payment', 'profile'
        ],
        'rdc_manager' => [
            'rdc-manager-dashboard', 'request-product-units', 'send-product-units',  'stock-movement-management', 'stock-reports', 'profile'
        ],
        'rdc_clerk' => [
            'rdc-clerk-dashboard', 'rdc-clerk-promotions', 'request-product-units', 'stock-reports', 'profile'
        ],
        'rdc_sales_ref' => [
            'rdc-sales-ref-dashboard', 'products', 'orders', 'profile'
        ],
        'logistics_officer' => [
            'logistics-officer-dashboard', 'stock-reports', 'tracking', 'profile'
        ],
        'rdc_driver' => [
            'rdc-driver-dashboard', 'tracking', 'profile'
        ],
        'head_office_manager' => [
            'head-office-manager-dashboard', 'stock-reports', 'delivery-report', 'sales-report', 'profile'
        ],
        'system_admin' => [
            'system-admin-dashboard', 'system-admin-users', 'system-admin-products',
            'system-admin-promotions', 'system-admin-profile', 'system-admin-audit',
            'stock-reports', 'delivery-report', 'sales-report'
        ]
    ];

    return $map[$role] ?? ['dashboard'];
}

function is_page_allowed_for_role($role, $page) {
    $allowed = get_allowed_pages_for_role($role);
    return in_array($page, $allowed, true);
}

/**
 * Page display info (icon + label) for nav. Only pages listed here can appear in the menu.
 * Nav is built from allowed pages for the role, so users only see links they can access.
 */
function get_nav_page_labels() {
    return [
        'dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
        'products' => ['icon' => 'shopping_bag', 'label' => 'Products'],
        'customer-sales-orders' => ['icon' => 'receipt_long', 'label' => 'Orders'],
        'cart' => ['icon' => 'shopping_cart', 'label' => 'Cart'],
        'tracking' => ['icon' => 'location_on', 'label' => 'Tracking'],
        'payment' => ['icon' => 'payment', 'label' => 'Payment'],
        'rdc-manager-dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
        'request-product-units' => ['icon' => 'inbox', 'label' => 'Requests'],
        'send-product-units' => ['icon' => 'local_shipping', 'label' => 'Dispatch'],
        'stock-reports' => ['icon' => 'bar_chart', 'label' => 'Stock Reports'],
        'rdc-clerk-dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
        'rdc-clerk-promotions' => ['icon' => 'loyalty', 'label' => 'Promotions'],
        'rdc-sales-ref-dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
        'logistics-officer-dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
        'rdc-driver-dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
        'head-office-manager-dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
        'delivery-report' => ['icon' => 'local_shipping', 'label' => 'Delivery Report'],
        'sales-report' => ['icon' => 'trending_up', 'label' => 'Sales Report'],
        'system-admin-dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
        'system-admin-users' => ['icon' => 'group', 'label' => 'Manage Users'],
        'system-admin-products' => ['icon' => 'inventory_2', 'label' => 'Manage Products'],
        'system-admin-promotions' => ['icon' => 'loyalty', 'label' => 'Promotions'],
        'system-admin-audit' => ['icon' => 'history', 'label' => 'Audit Log'],
        'stock-movement-management' => ['icon' => 'swap_horiz', 'label' => 'Stock Movement']
    ];
}

/**
 * Nav items for a role: only pages the role is allowed to access, with icon and label.
 * Order is determined by the role's allowed list so nav matches access (e.g. HO manager: Dashboard, Reports, Delivery Report).
 */
function get_nav_items_for_role($role) {
    $labels = get_nav_page_labels();
    // Profile is in header dropdown; exclude from main nav to avoid clutter
    $exclude = ['system-admin-profile', 'profile', 'payment'];

    // System admin: nav shows Dashboard, Manage Users, Manage Products, Audit Log
    if ($role === 'system_admin') {
        $navPages = ['system-admin-dashboard', 'system-admin-users', 'system-admin-products', 'system-admin-audit'];
        $order = [];
        foreach ($navPages as $page) {
            if (isset($labels[$page])) {
                $order[$page] = $labels[$page];
            }
        }
        return $order ?: ['system-admin-users' => $labels['system-admin-users']];
    }

    $allowed = get_allowed_pages_for_role($role);
    $allowed = array_diff($allowed, $exclude);
    $order = [];
    foreach ($allowed as $page) {
        if (isset($labels[$page])) {
            $order[$page] = $labels[$page];
        }
    }
    return $order ?: ['dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard']];
}

function redirect($url) {
    $target = BASE_PATH . $url;
    if (!headers_sent()) {
        header("Location: " . $target);
    } else {
        $safe = htmlspecialchars($target, ENT_QUOTES, 'UTF-8');
        echo "<script>window.location.href='{$safe}';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url={$safe}'></noscript>";
    }
    exit();
}

/**
 * Build an absolute in-app URL path safely.
 * Example: BASE_PATH='/isdn' and input '/assets/a.png' => '/isdn/assets/a.png'
 */
function app_url_path(string $path): string {
    $base = rtrim((string) BASE_PATH, '/');
    $cleanPath = '/' . ltrim($path, '/');
    return $base . $cleanPath;
}

/**
 * Write an audit log entry. Use for login, logout, and any tracked action.
 */
function audit_log(PDO $pdo, int $userId, string $action, string $entityType, ?int $entityId = null, string $details = ''): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $entityType, $entityId, $details, $ip]);
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
        $isSuccess = $flash['type'] === 'success';
        $bgColor = $isSuccess ? 'bg-emerald-600' : 'bg-red-600';
        $icon = $isSuccess ? 'check_circle' : 'error';
        $message = htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8');

        echo "
        <div id='flash-toast' class='fixed top-20 right-4 z-[9999] {$bgColor} text-white px-5 py-3 rounded-xl shadow-xl flex items-center gap-2 max-w-md'>
            <span class='material-symbols-rounded text-[20px]'>{$icon}</span>
            <span class='text-sm font-semibold'>{$message}</span>
        </div>
        <script>
        (function () {
            var toast = document.getElementById('flash-toast');
            if (!toast) return;
            setTimeout(function () {
                toast.style.transition = 'opacity .35s ease, transform .35s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-8px)';
                setTimeout(function () { toast.remove(); }, 380);
            }, 3200);
        })();
        </script>";
    }
}
?>
