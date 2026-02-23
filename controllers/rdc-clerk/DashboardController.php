<?php
// RDC Clerk Dashboard controller
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/ProductStock.php';
require_once __DIR__ . '/../../models/Order.php';

session_start();

// Current user and RDC context (fall back to sensible defaults)
$userId = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? ($_SESSION['user_name'] ?? 'Clerk');
$role = $_SESSION['role'] ?? 'RDC_CLERK';
$rdcId = $_SESSION['rdc_id'] ?? $_SESSION['rdc_id'] ?? 1;
$rdcName = null;

// Try to fetch RDC name (graceful fallback)
try {
    $stmt = $pdo->prepare('SELECT rdc_name, rdc_code FROM rdcs WHERE rdc_id = :id');
    $stmt->execute(['id' => $rdcId]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) {
        $rdcName = $r['rdc_name'];
        $rdcCode = $r['rdc_code'] ?? '';
    } else {
        $rdcName = 'RDC';
        $rdcCode = '';
    }
} catch (Exception $e) {
    $rdcName = 'RDC';
    $rdcCode = '';
}

// Instantiate models
$productStock = new ProductStock($pdo);
$orderModel = new OrderModel($pdo);

// Fetch inventory stocks for the RDC
$inventory = $productStock->getStocksByRdc((int)$rdcId);

// Compute low stock count (current_stock <= minimum_level)
$lowStockCount = 0;
foreach ($inventory as $i) {
    $cur = (int)($i['current_stock'] ?? 0);
    $min = (int)($i['minimum_level'] ?? 0);
    if ($min > 0 && $cur <= $min) {
        $lowStockCount++;
    }
}

// Fetch pending orders for this RDC
try {
    $stmt = $pdo->prepare("SELECT o.*, rc.name as customer_name
                           FROM orders o
                           JOIN retail_customers rc ON o.customer_id = rc.id
                           LEFT JOIN users u ON o.placed_by = u.id
                           LEFT JOIN users u2 ON rc.user_id = u2.id
                           WHERE (u.rdc_id = :rdc OR u2.rdc_id = :rdc) AND o.status = 'pending'
                           ORDER BY o.created_at ASC");
    $stmt->execute(['rdc' => $rdcId]);
    $pendingOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $pendingOrders = [];
}

// Count processed/approved today (non-pending)
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders o
                           LEFT JOIN users u ON o.placed_by = u.id
                           WHERE u.rdc_id = :rdc AND o.status != 'pending' AND DATE(o.updated_at) = CURDATE()");
    $stmt->execute(['rdc' => $rdcId]);
    $processedToday = (int)$stmt->fetchColumn();
} catch (Exception $e) {
    $processedToday = 0;
}

// Recent orders for overview (use model)
$recentOrders = $orderModel->getRecentOrdersByRdc((int)$rdcId, 5);

// Fetch products (global list for CRUD) - limit 50 to match the view's previous behavior
try {
    $stmt = $pdo->query('SELECT * FROM products LIMIT 50');
    $allProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allProducts = [];
}

// Messages (controller-level)
$success_msg = '';
$error_msg = '';

// Active tab
$activeTab = $_GET['tab'] ?? 'dashboard';

// Provide variables expected by the view (names match view's expectations)
$user_id = $userId;
$rdc_id = $rdcId;
$clerk_name = $username;
$rdc_name = $rdcName;

$current_user = [
    'user_id' => $userId,
    'name' => $username,
    'role' => $role,
    'rdc_id' => $rdcId,
    'rdc_name' => $rdcName,
    'rdc_code' => $rdcCode ?? ''
];

// Decide which view to render. If an orders page is requested, prepare orders data and render orders view.
$page = $_GET['page'] ?? '';
$viewRequested = $_GET['view'] ?? '';

// Default: render dashboard view
require __DIR__ . '/../../views/rdc-clerk/dashboard.php';

?>
