<?php
// RDC Manager Dashboard controller
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/ProductStock.php';
require_once __DIR__ . '/../../models/StockTransfer.php';
require_once __DIR__ . '/../../models/Order.php';
require_once __DIR__ . '/../../models/StockMovementLog.php';

session_start();

// Determine current user and rdc (fall back to session or defaults)
$userId = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? 'Manager';
$role = $_SESSION['role'] ?? 'RDC_MANAGER';
$rdcId = $_SESSION['rdc_id'] ?? 1;
$rdcName = null;

// Try to fetch rdc name
try {
    $stmt = $pdo->prepare('SELECT rdc_name, rdc_code FROM rdcs WHERE rdc_id = :id');
    $stmt->execute(['id' => $rdcId]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) {
        $rdcName = $r['rdc_name'];
        $rdcCode = $r['rdc_code'];
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
$transferModel = new StockTransfer($pdo);
$orderModel = new OrderModel($pdo);
$movementModel = new StockMovementLog($pdo);

// Fetch data
$current_stock = $productStock->getStocksByRdc((int)$rdcId);
$pending_transfers_raw = $transferModel->getPendingTransfersForRdc((int)$rdcId, 10);
$recent_orders_raw = $orderModel->getRecentOrdersByRdc((int)$rdcId, 5);
$recent_movements_raw = $movementModel->getRecentMovementsByRdc((int)$rdcId, 6);

// Map/format for the existing view expectations
$pending_transfers = array_map(function ($t) {
    return [
        'transfer_number' => $t['transfer_number'],
        'source_rdc' => $t['source_rdc'],
        'items' => (int)$t['items'],
        'status' => $t['status'],
        'is_urgent' => (int)$t['is_urgent'] === 1,
        'date' => $t['requested_date'] ?? $t['request_date'] ?? null
    ];
}, $pending_transfers_raw);

$recent_orders = array_map(function ($o) {
    return [
        'order_number' => $o['order_number'],
        'customer' => $o['customer'] ?? 'Unknown',
        'total' => (float)$o['total'],
        'status' => $o['status'],
        'date' => $o['date']
    ];
}, $recent_orders_raw);

$recent_movements = array_map(function ($m) {
    return [
        'product' => $m['product'] ?? 'Unknown',
        'type' => $m['type'],
        'quantity' => (int)$m['quantity'],
        'date' => $m['date'],
        'user' => $m['user'] ?? 'System'
    ];
}, $recent_movements_raw);

// Current user info for view
$current_user = [
    'user_id' => $userId,
    'name' => $username,
    'role' => $role,
    'rdc_id' => $rdcId,
    'rdc_name' => $rdcName,
    'rdc_code' => $rdcCode
];

// Compute KPIs in the view like before — include the view which uses these variables.
require __DIR__ . '/../../views/rdc-manager/dashboard.php';

?>
