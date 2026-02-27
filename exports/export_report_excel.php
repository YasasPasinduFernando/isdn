<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ProductStock.php';
require_once __DIR__ . '/../models/StockTransfer.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$report = $_GET['report'] ?? 'current_stock';
$rdc = isset($_GET['rdc_id']) && is_numeric($_GET['rdc_id']) ? (int)$_GET['rdc_id'] : null;
$date = $_GET['date'] ?? '30';

$productStock = new ProductStock($pdo);
$stockTransfer = new StockTransfer($pdo);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $report . '-' . date('Ymd-His') . '.csv"');

$out = fopen('php://output', 'w');
// BOM for Excel to recognize UTF-8
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

if ($report === 'current_stock') {
    fputcsv($out, ['RDC', 'Product Code', 'Product Name', 'Category', 'Current Stock', 'Minimum Level', 'Status']);
    if ($rdc) {
        $rows = $productStock->getStocksByRdc($rdc);
        // attach rdc name
        $stmt = $pdo->prepare('SELECT rdc_name FROM rdcs WHERE rdc_id = :id');
        $stmt->execute(['id' => $rdc]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $rdcName = $r['rdc_name'] ?? 'RDC ' . $rdc;
        foreach ($rows as $row) {
            fputcsv($out, [$rdcName, $row['product_code'], $row['product_name'], $row['category'] ?? '', $row['current_stock'], $row['minimum_level'], $row['status']]);
        }
    } else {
        $stmt = $pdo->query('SELECT rdc_id, rdc_name FROM rdcs');
        $rdcs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rdcs as $r) {
            $rows = $productStock->getStocksByRdc((int)$r['rdc_id']);
            foreach ($rows as $row) fputcsv($out, [$r['rdc_name'], $row['product_code'], $row['product_name'], $row['category'] ?? '', $row['current_stock'], $row['minimum_level'], $row['status']]);
        }
    }
    exit;
} elseif ($report === 'low_stock_alerts') {
    fputcsv($out, ['Priority', 'RDC', 'Product', 'Category', 'Current', 'Required', 'Shortage']);
    $stmt = $pdo->query('SELECT rdc_id, rdc_name FROM rdcs');
    $rdcs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rdcs as $r) {
        $rows = $productStock->getStocksByRdc((int)$r['rdc_id']);
        foreach ($rows as $row) {
            if (!in_array($row['status'], ['LOW', 'CRITICAL', 'OUT_OF_STOCK'])) continue;
            $priority = $row['status'];
            $shortage = ((int)$row['minimum_level']) - ((int)$row['current_stock']);
            fputcsv($out, [$priority, $r['rdc_name'], $row['product_name'], $row['category'] ?? '', $row['current_stock'], $row['minimum_level'], $shortage]);
        }
    }
    exit;
} elseif ($report === 'transfer_summary') {
    fputcsv($out, ['Transfer #', 'Date', 'Source RDC', 'Destination RDC', 'Products', 'Total Items', 'Status', 'Priority']);
    if ($rdc) {
        $rows = $stockTransfer->getTransfersForRdc($rdc, 1000);
    } else {
        // all transfers - fetch all rdcs and merge
        $rows = [];
        $stmt = $pdo->query('SELECT rdc_id FROM rdcs');
        $rdcs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $seen = [];
        foreach ($rdcs as $r) {
            $res = $stockTransfer->getTransfersForRdc((int)$r['rdc_id'], 1000);
            foreach ($res as $rr) {
                if (!isset($seen[$rr['transfer_id']])) { $seen[$rr['transfer_id']] = true; $rows[] = $rr; }
            }
        }
    }
    // Apply date filter if provided (days)
    if ($date !== 'all' && is_numeric($date)) {
        $days = (int)$date;
        $cutoff = strtotime("-{$days} days");
        $rows = array_filter($rows, fn($r) => strtotime($r['requested_date']) >= $cutoff);
    }
    foreach ($rows as $row) {
        fputcsv($out, [$row['transfer_number'], $row['requested_date'], $row['source_rdc'], $row['destination_rdc'], $row['product_count'], $row['total_items'], $row['status'], $row['is_urgent'] ? 'URGENT' : 'Normal']);
    }
    exit;
} elseif ($report === 'stock_valuation') {
    fputcsv($out, ['RDC', 'Product', 'Category', 'Quantity', 'Unit Price', 'Total Value']);
    $stmt = $pdo->query('SELECT rdc_id, rdc_name FROM rdcs');
    $rdcs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rdcs as $r) {
        $rows = $productStock->getStocksByRdc((int)$r['rdc_id']);
        foreach ($rows as $row) {
            $val = (float)$row['current_stock'] * (float)$row['unit_price'];
            fputcsv($out, [$r['rdc_name'], $row['product_name'], $row['category'] ?? '', $row['current_stock'], number_format($row['unit_price'], 2), number_format($val, 2)]);
        }
    }
    exit;
}

fclose($out);

?>
