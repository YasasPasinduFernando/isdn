<?php
/**
 * Dashboard Controller — Head Office Manager
 *
 * NOTE: $pdo, config.php, and functions.php are already loaded by index.php.
 * We only require constants.php and the Dashboard model here.
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Dashboard.php';

// Role guard
if (!is_logged_in() || current_user_role() !== USER_ROLE_HEAD_OFFICE_MANAGER) {
    redirect('/index.php?page=login');
}

// Handle stock transfer approve/reject (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_transfer_status'])) {
    $transferId = (int) ($_POST['transfer_id'] ?? 0);
    $status     = $_POST['status'] ?? '';
    $remarks    = trim($_POST['remarks'] ?? '');

    if ($transferId && in_array($status, ['APPROVED', 'REJECTED'])) {
        try {
            $dash = new Dashboard($pdo);
            $pdo->beginTransaction();
            $dash->updateTransferStatus($transferId, $status, (int) $_SESSION['user_id'], $remarks);
            $dash->logTransferStatusChange($transferId, $status, (int) $_SESSION['user_id'], 'HEAD_OFFICE_MANAGER', $_SESSION['username'] ?? 'HO Manager');
            $pdo->commit();
            flash_message("Transfer #{$transferId} has been {$status}.", 'success');
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash_message('Operation failed: ' . $e->getMessage(), 'error');
        }
    }
    redirect('/index.php?page=head-office-manager-dashboard');
}

// Sanitize filters
$filters = [];
if (!empty($_GET['start_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['start_date'])) {
    $filters['start_date'] = $_GET['start_date'];
}
if (!empty($_GET['end_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['end_date'])) {
    $filters['end_date'] = $_GET['end_date'];
}
if (!empty($_GET['rdc_id']) && is_numeric($_GET['rdc_id'])) {
    $filters['rdc_id'] = (int) $_GET['rdc_id'];
}
if (!empty($_GET['category_id']) && is_numeric($_GET['category_id'])) {
    $filters['category_id'] = (int) $_GET['category_id'];
}

// Fetch all dashboard data (wrapped in try-catch so it never white-screens)
try {
    $dashboard = new Dashboard($pdo);

    $totalRevenue      = $dashboard->getTotalRevenue($filters);
    $totalOrders       = $dashboard->getTotalOrders($filters);
    $pendingOrders     = $dashboard->getOrderCountByStatus('pending', $filters);
    $deliveredOrders   = $dashboard->getOrderCountByStatus('delivered', $filters);
    $monthlyGrowth     = $dashboard->getMonthlyGrowth();
    $lowStockAlerts    = $dashboard->getLowStockAlerts();
    $rdcSales          = $dashboard->getRdcSalesComparison($filters);
    $topProducts       = $dashboard->getTopSellingProducts($filters);
    $monthlyTrend      = $dashboard->getMonthlyRevenueTrend(6);
    $statusDistribution = $dashboard->getOrderStatusDistribution($filters);
    $deliveryStats     = $dashboard->getDeliveryEfficiency($filters);
    $allRdcs           = $dashboard->getAllRdcs();
    $allCategories     = $dashboard->getAllCategories();
    $pendingTransfers  = $dashboard->getPendingTransfers();
    $dashboardError    = null;

} catch (Exception $e) {
    // Provide defaults so the view still renders
    $dashboardError     = $e->getMessage();
    $totalRevenue       = 0;
    $totalOrders        = 0;
    $pendingOrders      = 0;
    $deliveredOrders    = 0;
    $monthlyGrowth      = ['current_month' => 0, 'previous_month' => 0, 'growth_pct' => 0];
    $lowStockAlerts     = [];
    $rdcSales           = [];
    $topProducts        = [];
    $monthlyTrend       = [];
    $statusDistribution = [];
    $deliveryStats      = ['total_deliveries' => 0, 'completed' => 0, 'avg_hours' => 0, 'completion_rate' => 0];
    $allRdcs            = [];
    $allCategories      = [];
    $pendingTransfers   = [];
}

$activeFilters = $filters;

require __DIR__ . '/../views/head-office-manager/dashboard.php';
