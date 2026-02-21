<?php
// Controller for Send Product Units (Approval) page
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/StockTransfer.php';

// Start session if not already
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Current user from session
$current_user = [
    'user_id' => $_SESSION['user_id'] ?? null,
    'name' => $_SESSION['username'] ?? 'User',
    'role' => strtoupper($_SESSION['role'] ?? 'rdc_manager'),
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

// Helper to send JSON
function json_error($msg, $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

function json_success($msg, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => true, 'message' => $msg], $data));
    exit;
}

// Only handle POST for approval actions here
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_approval') {
    // Accepts: transfer_id, new_status (APPROVED|REJECTED), remarks
    $transfer_id = (int)($_POST['transfer_id'] ?? 0);
    $new_status = strtoupper(trim($_POST['new_status'] ?? ''));
    $remarks = trim($_POST['remarks'] ?? '');

    if (!$transfer_id || !$new_status) json_error('Missing required fields.', 400);

    // Only RDC_MANAGER allowed
    if ($current_user['role'] !== 'RDC_MANAGER') json_error('Permission denied.', 403);

    if (!in_array($new_status, ['APPROVED', 'REJECTED'])) json_error('Invalid status.', 400);

    if ($remarks === '') json_error('Please add approval remarks before submitting!', 400);

    try {
        // Fetch current transfer
        $q = $pdo->prepare('SELECT approval_status, destination_rdc_id FROM stock_transfers WHERE transfer_id = ?');
        $q->execute([$transfer_id]);
        $row = $q->fetch(PDO::FETCH_ASSOC);
        if (!$row) json_error('Transfer not found.', 404);

        // Ensure this transfer is intended for this RDC
        if ((int)$row['destination_rdc_id'] !== (int)$current_user['rdc_id']) json_error('Transfer does not belong to your RDC.', 403);

        // Only process if current status is PENDING (awaiting source RDC manager approval)
        $prev = $row['approval_status'];
        if ($prev !== 'PENDING') json_error('Only transfers with status PENDING can be approved/rejected.', 409);

        $pdo->beginTransaction();

        // Update stock_transfers
        $upd = $pdo->prepare('UPDATE stock_transfers SET approval_status = ?, approved_by = ?, approval_date = NOW(), approval_remarks = ? WHERE transfer_id = ?');
        $upd->execute([$new_status, $current_user['user_id'], $remarks, $transfer_id]);

        // Insert into transfer_status_logs
        $log = $pdo->prepare('INSERT INTO transfer_status_logs (transfer_id, previous_status, new_status, changed_by, change_by_role, change_by_name) VALUES (?, ?, ?, ?, ?, ?)');
        $log->execute([$transfer_id, $prev, $new_status, $current_user['user_id'], $current_user['role'], $current_user['name']]);

        $pdo->commit();

        json_success('Decision recorded successfully.');

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        json_error('Failed to update transfer: ' . $e->getMessage(), 500);
    }
}

// GET: prepare data for view
$stockTransfer = new StockTransfer($pdo);
$currentRdc = (int)$current_user['rdc_id'];

// Pending transfers destined to this RDC (detailed)
$pending_transfers = $stockTransfer->getPendingTransfersDetailedForRdc($currentRdc, 50);
echo '<pre>';
print_r($pending_transfers);
echo '</pre>';

// Processed transfers (history) for this RDC
$stmt = $pdo->prepare("SELECT st.transfer_id, st.transfer_number, r1.rdc_name AS source_rdc_name, st.requested_date, st.approval_status, u.username AS approved_by, st.approval_date, COALESCE((SELECT COUNT(*) FROM stock_transfer_items sti WHERE sti.transfer_id = st.transfer_id),0) AS product_count, COALESCE((SELECT SUM(requested_quantity) FROM stock_transfer_items sti WHERE sti.transfer_id = st.transfer_id),0) AS total_items
    FROM stock_transfers st
    JOIN rdcs r1 ON st.source_rdc_id = r1.rdc_id
    LEFT JOIN users u ON st.approved_by = u.id
    WHERE st.destination_rdc_id = :rdc_id
      AND st.approval_status IN ('APPROVED','REJECTED','CANCELLED','RECEIVED')
    ORDER BY st.approval_date DESC
    LIMIT 100");
$stmt->execute(['rdc_id' => $currentRdc]);
$processed_transfers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include the view (it expects $pending_transfers and $processed_transfers)
require __DIR__ . '/../../views/stock-management/send_product_units.php';

?>
