<?php
// Controller: returns transfer summary and transfer status logs for tracking modal
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/StockTransfer.php';
require_once __DIR__ . '/../../models/StockTransferLog.php';

// Start session if not already
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Helper to send JSON
function json_error($msg, $code = 400)
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

function json_success($data = [])
{
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

// GET endpoint: ?action=get_tracking&transfer_id=123 or transfer_number=TR-001
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['action']) && $_GET['action'] === 'get_tracking')) {
    $transferId = isset($_GET['transfer_id']) ? (int)$_GET['transfer_id'] : null;
    $transferNumber = isset($_GET['transfer_number']) ? trim($_GET['transfer_number']) : null;

    if (!$transferId && !$transferNumber) json_error('Missing transfer identifier (transfer_id or transfer_number)', 400);

    try {
        // Fetch transfer summary
        if ($transferId) {
            $stmt = $pdo->prepare("SELECT st.transfer_id, st.transfer_number, st.approval_status AS status, st.requested_date, r1.rdc_name AS source_rdc, r2.rdc_name AS destination_rdc,
                COALESCE((SELECT COUNT(*) FROM stock_transfer_items sti WHERE sti.transfer_id = st.transfer_id),0) AS product_count,
                COALESCE((SELECT SUM(requested_quantity) FROM stock_transfer_items sti WHERE sti.transfer_id = st.transfer_id),0) AS total_items
                FROM stock_transfers st
                JOIN rdcs r1 ON st.source_rdc_id = r1.rdc_id
                JOIN rdcs r2 ON st.destination_rdc_id = r2.rdc_id
                WHERE st.transfer_id = ? LIMIT 1");
            $stmt->execute([$transferId]);
        } else {
            $stmt = $pdo->prepare("SELECT st.transfer_id, st.transfer_number, st.approval_status AS status, st.requested_date, r1.rdc_name AS source_rdc, r2.rdc_name AS destination_rdc,
                COALESCE((SELECT COUNT(*) FROM stock_transfer_items sti WHERE sti.transfer_id = st.transfer_id),0) AS product_count,
                COALESCE((SELECT SUM(requested_quantity) FROM stock_transfer_items sti WHERE sti.transfer_id = st.transfer_id),0) AS total_items
                FROM stock_transfers st
                JOIN rdcs r1 ON st.source_rdc_id = r1.rdc_id
                JOIN rdcs r2 ON st.destination_rdc_id = r2.rdc_id
                WHERE st.transfer_number = ? LIMIT 1");
            $stmt->execute([$transferNumber]);
        }

        $transfer = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$transfer) json_error('Transfer not found', 404);

        // Normalize
        $transferData = [
            'transfer_id' => (int)$transfer['transfer_id'],
            'transfer_number' => $transfer['transfer_number'],
            'source_rdc' => $transfer['source_rdc'],
            'destination_rdc' => $transfer['destination_rdc'],
            'product_count' => (int)$transfer['product_count'],
            'total_items' => (int)$transfer['total_items'],
            'status' => $transfer['status'],
            'requested_date' => $transfer['requested_date']
        ];
   
        // Fetch status history using model
        $logModel = new StockTransferLog($pdo);
        $logs = $logModel->getLogsByTransferId((int)$transferData['transfer_id']);

        json_success(['transfer' => $transferData, 'status_history' => $logs]);

    } catch (Exception $e) {
        json_error('Failed to load transfer: ' . $e->getMessage(), 500);
    }

}

// If not called as endpoint, just show 400
http_response_code(400);
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;

?>