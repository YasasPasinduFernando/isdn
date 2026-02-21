<?php
// Controller for Request Product Units page
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/ProductStock.php';
require_once __DIR__ . '/../../models/StockTransfer.php';

// Start session if not already
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Current user from session
$current_user = [
    'user_id' => $_SESSION['user_id'] ?? null,
    'name' => $_SESSION['username'] ?? 'User',
    'role' => strtoupper($_SESSION['role'] ?? 'rdc_clerk'),
    'rdc_id' => $_SESSION['rdc_id'] ?? null
];

try {
    $stmt = $pdo->prepare('SELECT rdc_name, rdc_code FROM rdcs WHERE rdc_id = :id');
    $stmt->execute(['id' => $current_user['rdc_id']]);
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

$current_user['rdc_name'] = $rdcName;
$current_user['rdc_code'] = $rdcCode;

// Simple helpers
function redirect_back($msg = null)
{
    $url = '/index.php?page=request-product-units';
    if ($msg) $url .= '&msg=' . urlencode($msg);
    header('Location: ' . $url);
    exit;
}

// Handle POST: create transfer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // If this is an AJAX status update request, handle separately and return JSON
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        header('Content-Type: application/json');

        $transfer_id = (int)($_POST['transfer_id'] ?? 0);
        $new_status = strtoupper(trim($_POST['new_status'] ?? ''));
        $remarks = trim($_POST['remarks'] ?? '');

        // Basic validations
        if (!$transfer_id || !$new_status) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing transfer id or new status.']);
            exit;
        }

        // Only RDC_MANAGER can perform this change
        if ($current_user['role'] !== 'RDC_MANAGER') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Permission denied.']);
            exit;
        }

        // Only allow moving CLERK_REQUESTED -> PENDING or CANCELLED
        $allowed = ['PENDING', 'CANCELLED'];
        if (!in_array($new_status, $allowed)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid new status.']);
            exit;
        }

        try {
            // fetch current transfer status
            $q = $pdo->prepare('SELECT approval_status FROM stock_transfers WHERE transfer_id = ?');
            $q->execute([$transfer_id]);
            $row = $q->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Transfer not found.']);
                exit;
            }

            $prev_status = $row['approval_status'];
            if ($prev_status !== 'CLERK_REQUESTED') {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Only transfers in CLERK_REQUESTED state can be changed by RDC manager.']);
                exit;
            }

            // validate remarks for these transitions
            if (in_array($new_status, ['PENDING', 'CANCELLED']) && $remarks === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Please add remarks before updating status.']);
                exit;
            }

            // perform update and insert log
            $pdo->beginTransaction();

            $upd = $pdo->prepare('UPDATE stock_transfers SET approval_status = ? WHERE transfer_id = ?');
            $upd->execute([$new_status, $transfer_id]);

            $logStmt = $pdo->prepare("INSERT INTO transfer_status_logs (transfer_id, previous_status, new_status, changed_by, change_by_role, change_by_name) VALUES (?, ?, ?, ?, ?, ?)");
            $logStmt->execute([$transfer_id, $prev_status, $new_status, $current_user['user_id'], $current_user['role'], $current_user['name']]);

            $pdo->commit();

            echo json_encode(['success' => true, 'message' => "Transfer status updated to $new_status."]);
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update status: ' . $e->getMessage()]);
            exit;
        }
    }
    $sel = $_POST['selected_products'] ?? [];
    $source_rdc_id = (int)($_POST['source_rdc_id'] ?? 0);
    $is_urgent = isset($_POST['is_urgent']) ? (int)$_POST['is_urgent'] : 0;
    $reason = trim($_POST['reason'] ?? '');

    if (empty($sel) || !$source_rdc_id || empty($reason)) {
        redirect_back('Please select products, source RDC and a reason.');
    }

    // Determine approval status based on role
    $role = $current_user['role'];
    $approval_status = 'PENDING';
    if ($role === 'RDC_CLERK') $approval_status = 'CLERK_REQUESTED';
    if ($role === 'RDC_MANAGER') $approval_status = 'PENDING';

    // Prepare transfer_number
    $transfer_number = 'TR-' . date('YmdHis') . '-' . random_int(100, 999);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO stock_transfers
            (transfer_number, source_rdc_id, destination_rdc_id, requested_by, requested_by_role, request_reason, is_urgent, approval_status, requested_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->execute([
            $transfer_number,
            $source_rdc_id,
            $current_user['rdc_id'],
            $current_user['user_id'],
            $role,
            $reason,
            $is_urgent,
            $approval_status
        ]);

        $transferId = (int)$pdo->lastInsertId();

        // Insert items
        $itemStmt = $pdo->prepare("INSERT INTO stock_transfer_items (transfer_id, product_id, requested_quantity) VALUES (?, ?, ?)");
        foreach ($sel as $pid) {
            $pid = (int)$pid;
            $qtyField = 'request_qty_' . $pid;
            $qty = (int)($_POST[$qtyField] ?? 0);
            if ($qty <= 0) continue;
            $itemStmt->execute([$transferId, $pid, $qty]);
        }

        // Log initial status
        $logStmt = $pdo->prepare("INSERT INTO transfer_status_logs (transfer_id, previous_status, new_status, changed_by, change_by_role, change_by_name) VALUES (?, ?, ?, ?, ?, ?)");
        $logStmt->execute([$transferId, null, $approval_status, $current_user['user_id'], $role, $current_user['name']]);

        $pdo->commit();
        redirect_back('Transfer request submitted successfully.');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        redirect_back('Failed to create transfer: ' . $e->getMessage());
    }
}

// GET: prepare data for view
$productStock = new ProductStock($pdo);
$stockTransfer = new StockTransfer($pdo);

$currentRdc = (int)$current_user['rdc_id'];

// Other RDCs (exclude current)
$other_rdcs = $pdo->prepare('SELECT rdc_id, rdc_name, rdc_code FROM rdcs WHERE rdc_id != ? ORDER BY rdc_name');
$other_rdcs->execute([$currentRdc]);
$other_rdcs = $other_rdcs->fetchAll(PDO::FETCH_ASSOC);

// Low stock products at current RDC
$allStocks = $productStock->getStocksByRdc($currentRdc);



$low_stock_products = array_filter($allStocks, fn($p) => in_array($p['status'], ['LOW','CRITICAL']));

// Map to view expected keys (status lowercased, unit default)
$low_stock_products = array_map(function($p){
    return [
        'product_id' => $p['product_id'],
        'product_code' => $p['product_code'],
        'product_name' => $p['product_name'],
        'category' => $p['category'],
        'current_stock' => $p['current_stock'],
        'minimum_level' => $p['minimum_level'],
        'unit' => 'units',
        'status' => strtolower($p['status']),
        'unit_price' => $p['unit_price']
    ];
}, $low_stock_products);

// Pending transfers for this RDC (as destination) with details
$pending_transfers = $stockTransfer->getPendingTransfersDetailedForRdc($currentRdc, 10);

// Build other RDC stocks map for JS: rdc_id => { product_id: available }
$other_rdc_stocks = [];
if (!empty($other_rdcs)) {
    $placeholders = implode(',', array_fill(0, count($other_rdcs), '?'));
    $rids = array_map(fn($r) => $r['rdc_id'], $other_rdcs);
    $sql = "SELECT ps.rdc_id, ps.product_id, COALESCE(ps.available_quantity,0) AS available_quantity
            FROM product_stocks ps
            WHERE ps.rdc_id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $i = 1;
    foreach ($rids as $rid) { $stmt->bindValue($i, $rid, PDO::PARAM_INT); $i++; }
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $rid = (int)$row['rdc_id'];
        if (!isset($other_rdc_stocks[$rid])) $other_rdc_stocks[$rid] = [];
        $other_rdc_stocks[$rid][(int)$row['product_id']] = (int)$row['available_quantity'];
    }
}

// Include the view (it expects variables: current_user, other_rdcs, low_stock_products, pending_transfers, other_rdc_stocks)
require __DIR__ . '/../../views/stock-management/request_product_units.php';

?>
