<?php
// Controller for Stock Movement management
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/ProductStock.php';
require_once __DIR__ . '/../../models/StockMovementLog.php';
require_once __DIR__ . '/../../models/Product.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$current_user = [
    'user_id' => $_SESSION['user_id'] ?? null,
    'name' => $_SESSION['username'] ?? ($_SESSION['name'] ?? 'User'),
    // Store role uppercase to match other controllers
    'role' => strtoupper($_SESSION['role'] ?? 'rdc_clerk'),
    'rdc_id' => $_SESSION['rdc_id'] ?? null
];

// Fetch RDC name/code for display
try {
    $stmt = $pdo->prepare('SELECT rdc_name, rdc_code FROM rdcs WHERE rdc_id = :id');
    $stmt->execute(['id' => $current_user['rdc_id']]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) {
        $current_user['rdc_name'] = $r['rdc_name'];
        $current_user['rdc_code'] = $r['rdc_code'];
    } else {
        $current_user['rdc_name'] = 'RDC';
        $current_user['rdc_code'] = '';
    }
} catch (Exception $e) {
    $current_user['rdc_name'] = 'RDC';
    $current_user['rdc_code'] = '';
}

// Allowed roles
$allowed = ['RDC_MANAGER', 'HEAD_OFFICE_MANAGER'];
if (!in_array($current_user['role'], $allowed)) {
    // For non-AJAX calls, stop; for AJAX return JSON
    if (php_sapi_name() !== 'cli' && (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Permission denied.']);
    } else {
        die('Access Denied: You do not have permission to manage stock movements.');
    }
    exit;
}

// Helper: redirect back to the view page
function redirect_back($msg = null)
{
    $url = '/index.php?page=stock-movement-management';
    if ($msg) $url .= '&msg=' . urlencode($msg);
    header('Location: ' . $url);
    exit;
}

// Handle POST (expect JSON or form-encoded). Returns JSON on POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // read input (support both form-encoded and JSON)
    $input = $_POST;
    $raw = file_get_contents('php://input');
    if (empty($input) && $raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) $input = $decoded;
    }

    $movement_type = strtoupper(trim($input['movement_type'] ?? ''));
    $product_id = (int)($input['product_id'] ?? 0);
    $quantity = (int)($input['quantity'] ?? 0);
    $note = trim($input['note'] ?? '');
    // rdc_id may be provided by head office manager; otherwise use user's rdc
    $rdc_id = isset($input['rdc_id']) && (int)$input['rdc_id'] > 0 ? (int)$input['rdc_id'] : (int)$current_user['rdc_id'];

    // Basic validation
    if (!$movement_type || !$product_id || $quantity <= 0 || $note === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing or invalid required fields.']);
        exit;
    }

    // Movement direction mapping - mirror front-end assumptions
    $positiveTypes = ['STOCK_IN', 'RETURNED', 'TRANSFER_IN'];
    $negativeTypes = ['STOCK_OUT', 'TRANSFER_OUT', 'DAMAGED', 'EXPIRED'];

    $actualChange = $quantity;
    if (in_array($movement_type, $negativeTypes)) {
        $actualChange = -$quantity;
    }
    // ADJUSTMENT or others are treated as positive by default unless included in negative set

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Lock product stock row for this rdc/product
        $select = $pdo->prepare('SELECT id, available_quantity FROM product_stocks WHERE rdc_id = ? AND product_id = ? FOR UPDATE');
        $select->execute([$rdc_id, $product_id]);
        $row = $select->fetch(PDO::FETCH_ASSOC);

        $previous = $row ? (int)($row['available_quantity'] ?? 0) : 0;
        $new = $previous + $actualChange;

        if ($new < 0) {
            // cannot make negative stock
            if ($pdo->inTransaction()) $pdo->rollBack();
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Operation would result in negative stock.']);
            exit;
        }

        if ($row) {
            $update = $pdo->prepare('UPDATE product_stocks SET available_quantity = ?, last_updated = NOW() WHERE id = ?');
            $update->execute([$new, $row['id']]);
        } else {
            // Insert new stock record (only allowed if change is positive)
            if ($new < 0) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Insufficient stock.']);
                exit;
            }
            $ins = $pdo->prepare('INSERT INTO product_stocks (product_id, rdc_id, available_quantity, last_updated) VALUES (?, ?, ?, NOW())');
            $ins->execute([$product_id, $rdc_id, $new]);
        }

        // Insert movement log
        $log = $pdo->prepare('INSERT INTO stock_movement_logs (rdc_id, product_id, movement_type, quantity, previous_quantity, new_quantity, created_by, created_by_role, created_by_name, note, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $log->execute([
            $rdc_id,
            $product_id,
            $movement_type,
            $actualChange,
            $previous,
            $new,
            $current_user['user_id'],
            $current_user['role'],
            $current_user['name'],
            $note
        ]);

        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Stock movement recorded.', 'previous' => $previous, 'new' => $new]);
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to record movement: ' . $e->getMessage()]);
        exit;
    }
}

// GET: prepare data for view
$productStock = new ProductStock($pdo);
$movementLog = new StockMovementLog($pdo);

$currentRdc = (int)$current_user['rdc_id'];

// If head office manager, supply all RDCs for selector
$all_rdcs = [];
if ($current_user['role'] === 'HEAD_OFFICE_MANAGER') {
    $stmt = $pdo->prepare('SELECT rdc_id, rdc_name, rdc_code FROM rdcs ORDER BY rdc_name');
    $stmt->execute();
    $all_rdcs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Products for the selected RDC
$products = $productStock->getStocksByRdc($currentRdc);

// Recent stock movements for the RDC
$recent_movements = $movementLog->getRecentMovementsByRdc($currentRdc, 50);

// Pass to view
require __DIR__ . '/../../views/stock-management/stock_movement_management.php';

?>
