<?php
// Controller for Stock Reports
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/ProductStock.php';
require_once __DIR__ . '/../../models/StockTransfer.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Build current user info from session (keep role lowercase to match views)
$current_user = [
    'user_id' => $_SESSION['user_id'] ?? null,
    'name' => $_SESSION['username'] ?? ($_SESSION['name'] ?? 'User'),
    'role' => strtolower($_SESSION['role'] ?? 'rdc_clerk'),
    'rdc_id' => $_SESSION['rdc_id'] ?? null,
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

// Role permissions (same mapping used in view)
$role_permissions = [
    'rdc_manager' => [
        'reports' => ['current_stock', 'low_stock_alerts', 'transfer_summary'],
        'view_scope' => 'own_rdc',
        'can_export' => true
    ],
    'head_office_manager' => [
        'reports' => ['current_stock', 'low_stock_alerts', 'transfer_summary', 'stock_valuation'],
        'view_scope' => 'all_rdcs',
        'can_export' => true
    ],
    'rdc_clerk' => [
        'reports' => ['current_stock', 'low_stock_alerts'],
        'view_scope' => 'own_rdc',
        'can_export' => false
    ],
    'logistics_officer' => [
        'reports' => ['current_stock'],
        'view_scope' => 'own_rdc',
        'can_export' => false
    ],
    'system_admin' => [
        'reports' => ['current_stock', 'low_stock_alerts', 'transfer_summary'],
        'view_scope' => 'all_rdcs',
        'can_export' => true
    ]
];

$user_permissions = $role_permissions[$current_user['role']] ?? $role_permissions['rdc_clerk'];

// Selected RDC: allow head office manager to pass rdc_id via GET; otherwise use user's rdc
$selected_rdc = null;
if (isset($_GET['rdc_id']) && is_numeric($_GET['rdc_id']) && (int)$_GET['rdc_id'] > 0) {
    $selected_rdc = (int)$_GET['rdc_id'];
}
// If role is limited to own RDC, force selection to user's RDC
if ($user_permissions['view_scope'] === 'own_rdc') {
    $selected_rdc = (int)$current_user['rdc_id'];
}
// If view_scope is all_rdcs and no rdc selected, keep $selected_rdc as null to indicate 'ALL'

// Fetch list of RDCs (for head office manager selector)
$all_rdcs = [];
try {
    $stmt = $pdo->prepare('SELECT rdc_id, rdc_name, rdc_code FROM rdcs ORDER BY rdc_name');
    $stmt->execute();
    $all_rdcs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // leave $all_rdcs empty on error
}

// Fetch categories
$categories = [];
try {
    $stmt = $pdo->prepare('SELECT name FROM product_categories ORDER BY name');
    $stmt->execute();
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cats as $c) $categories[] = $c['name'];
} catch (Exception $e) {
    // ignore
}

// Instantiate models
$productStock = new ProductStock($pdo);
$stockTransfer = new StockTransfer($pdo);

// Current stock data for selected RDC
// Current stock data for selected RDC (or ALL RDCs when $selected_rdc is null)
$current_stock_data = [];
try {
    if ($selected_rdc) {
        $stocks = $productStock->getStocksByRdc($selected_rdc);
        // attach rdc info for each row
        $rdc_name = null;
        foreach ($all_rdcs as $r) {
            if ((int)$r['rdc_id'] === (int)$selected_rdc) { $rdc_name = $r['rdc_name']; break; }
        }
        foreach ($stocks as $s) {
            $s['rdc_id'] = $selected_rdc;
            $s['rdc_name'] = $rdc_name ?? ('RDC ' . $selected_rdc);
            $current_stock_data[] = $s;
        }
    } else {
        // All RDCs: iterate and collect stocks for each rdc
        foreach ($all_rdcs as $rdc) {
            $rid = (int)$rdc['rdc_id'];
            $stocks = $productStock->getStocksByRdc($rid);
            foreach ($stocks as $s) {
                $s['rdc_id'] = $rid;
                $s['rdc_name'] = $rdc['rdc_name'];
                $current_stock_data[] = $s;
            }
        }
    }
} catch (Exception $e) {
    // ignore and leave empty
}

// Transfer summary for the RDC (incoming or outgoing) - include transfers where RDC is source or destination
$transfer_summary_data = [];
try {
    if ($selected_rdc) {
        $transfer_summary_data = $stockTransfer->getTransfersForRdc($selected_rdc, 200);
    } else {
        // All RDCs: fetch transfers for each RDC and merge unique transfer_ids
        $seen = [];
        foreach ($all_rdcs as $rdc) {
            $rid = (int)$rdc['rdc_id'];
            $rows = $stockTransfer->getTransfersForRdc($rid, 200);
            foreach ($rows as $row) {
                $tid = (int)$row['transfer_id'];
                if (!isset($seen[$tid])) {
                    $seen[$tid] = true;
                    $transfer_summary_data[] = $row;
                }
            }
        }
        // sort merged transfers by is_urgent then requested_date desc
        usort($transfer_summary_data, function($a, $b) {
            if ($a['is_urgent'] !== $b['is_urgent']) return $b['is_urgent'] <=> $a['is_urgent'];
            return strtotime($b['requested_date']) <=> strtotime($a['requested_date']);
        });
    }
} catch (Exception $e) {
    // ignore
}

// KPIs
$kpi_total_products = count($current_stock_data);
$kpi_below_minimum = 0;
$kpi_total_stock_value = 0.0;
foreach ($current_stock_data as $r) {
    $kpi_total_stock_value += ((float)($r['unit_price'] ?? 0) * (int)($r['current_stock'] ?? 0));
    if (isset($r['minimum_level']) && (int)$r['minimum_level'] > 0 && (int)$r['current_stock'] < (int)$r['minimum_level']) {
        $kpi_below_minimum++;
    }
}

// Provide variables to the view
require __DIR__ . '/../../views/stock-management/stock_reports.php';

?>
