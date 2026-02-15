<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Dashboard.php';

// ── Data Fetching ─────────────────────────────────────────────────
$dashboardError = null;
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
if (!empty($_GET['category'])) {
    $filters['category'] = trim($_GET['category']);
}

// Handle POST: stock transfer approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_transfer_status'])) {
    $transferId = (int) ($_POST['transfer_id'] ?? 0);
    $status     = $_POST['status'] ?? '';
    $remarks    = trim($_POST['remarks'] ?? '');
    if ($transferId && in_array($status, ['APPROVED', 'REJECTED'])) {
        try {
            $d = new Dashboard($pdo);
            $pdo->beginTransaction();
            $d->updateTransferStatus($transferId, $status, (int) $_SESSION['user_id'], $remarks);
            $d->logTransferStatusChange($transferId, $status, (int) $_SESSION['user_id']);
            $pdo->commit();
            audit_log($pdo, (int) $_SESSION['user_id'], 'UPDATE', 'transfer', $transferId, "Stock transfer #{$transferId} {$status}");
            flash_message("Transfer #{$transferId} has been {$status}.", 'success');
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash_message('Operation failed: ' . $e->getMessage(), 'error');
        }
    }
    redirect('/index.php?page=head-office-manager-dashboard');
}

try {
    $dashboard = new Dashboard($pdo);

    $totalRevenue       = $dashboard->getTotalRevenue($filters);
    $totalOrders        = $dashboard->getTotalOrders($filters);
    $pendingOrders      = $dashboard->getOrderCountByStatus('pending', $filters);
    $deliveredOrders    = $dashboard->getOrderCountByStatus('delivered', $filters);
    $monthlyGrowth      = $dashboard->getMonthlyGrowth();
    $lowStockAlerts     = $dashboard->getLowStockAlerts();
    $rdcSales           = $dashboard->getRdcSalesComparison($filters);
    $topProducts        = $dashboard->getTopSellingProducts($filters);
    $monthlyTrend       = $dashboard->getMonthlyRevenueTrend(6);
    $statusDistribution = $dashboard->getOrderStatusDistribution($filters);
    $deliveryStats      = $dashboard->getDeliveryEfficiency($filters);
    $allRdcs            = $dashboard->getAllRdcs();
    $allCategories      = $dashboard->getAllCategories();
    $pendingTransfers   = $dashboard->getPendingTransfers();
} catch (Exception $e) {
    $dashboardError      = $e->getMessage();
    $totalRevenue        = 0;
    $totalOrders         = 0;
    $pendingOrders       = 0;
    $deliveredOrders     = 0;
    $monthlyGrowth       = ['current_month' => 0, 'previous_month' => 0, 'growth_pct' => 0];
    $lowStockAlerts      = [];
    $rdcSales            = [];
    $topProducts         = [];
    $monthlyTrend        = [];
    $statusDistribution  = [];
    $deliveryStats       = ['total_deliveries' => 0, 'completed' => 0, 'avg_hours' => 0, 'completion_rate' => 0];
    $allRdcs             = [];
    $allCategories       = [];
    $pendingTransfers    = [];
}

$activeFilters = $filters;

// Chart data
$rdcChartData = [
    'labels'   => array_column($rdcSales, 'rdc_name'),
    'revenues' => array_map('floatval', array_column($rdcSales, 'total_revenue')),
];
$monthlyChartData = [
    'labels'   => array_column($monthlyTrend, 'month_label'),
    'revenues' => array_map('floatval', array_column($monthlyTrend, 'revenue')),
];
$statusChartData = [
    'labels' => array_map('ucfirst', array_column($statusDistribution, 'status')),
    'counts' => array_map('intval', array_column($statusDistribution, 'count')),
];
$statusColors = ['Pending'=>'#f59e0b','Confirmed'=>'#3b82f6','Processing'=>'#8b5cf6','Delivered'=>'#10b981','Cancelled'=>'#ef4444'];
$pieColors = [];
foreach ($statusChartData['labels'] as $label) {
    $pieColors[] = $statusColors[$label] ?? '#6b7280';
}
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        <?php display_flash(); ?>

        <?php if ($dashboardError): ?>
            <div class="glass-card rounded-2xl p-5 mb-6 border-l-4 border-red-500">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-rounded text-red-500 text-2xl">error</span>
                    <div>
                        <p class="font-bold text-gray-800 font-['Outfit']">Dashboard Error</p>
                        <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($dashboardError); ?></p>
                        <p class="text-xs text-gray-400 mt-2">Run <code class="bg-gray-100 px-1.5 py-0.5 rounded">seeder.sql</code> then <code class="bg-gray-100 px-1.5 py-0.5 rounded">seed_dashboard_demo.sql</code></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Welcome Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-10">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 font-['Outfit']">Head Office Dashboard</h1>
                <p class="text-gray-500 mt-1">Welcome back, <span class="text-teal-600 font-semibold"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Manager'); ?></span> &mdash; <?php echo date('l, F j, Y'); ?></p>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" action="<?php echo BASE_PATH; ?>/index.php" class="mb-8">
            <input type="hidden" name="page" value="head-office-manager-dashboard">
            <div class="glass-card rounded-2xl p-5 flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">From</label>
                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($activeFilters['start_date'] ?? ''); ?>"
                           class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent transition shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">To</label>
                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($activeFilters['end_date'] ?? ''); ?>"
                           class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent transition shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">RDC</label>
                    <select name="rdc_id" class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 text-sm min-w-[150px] focus:ring-2 focus:ring-teal-500 focus:border-transparent transition shadow-sm">
                        <option value="">All RDCs</option>
                        <?php foreach ($allRdcs as $r): ?>
                            <option value="<?php echo $r['rdc_id']; ?>" <?php echo (isset($activeFilters['rdc_id']) && $activeFilters['rdc_id'] == $r['rdc_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($r['rdc_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Category</label>
                    <select name="category" class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 text-sm min-w-[150px] focus:ring-2 focus:ring-teal-500 focus:border-transparent transition shadow-sm">
                        <option value="">All Categories</option>
                        <?php foreach ($allCategories as $c): ?>
                            <option value="<?php echo htmlspecialchars($c['category']); ?>" <?php echo (isset($activeFilters['category']) && $activeFilters['category'] === $c['category']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white font-bold text-sm shadow-lg shadow-teal-200/50 hover:scale-[1.02] transition flex items-center gap-1.5">
                    <span class="material-symbols-rounded text-base">filter_list</span> Apply
                </button>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=head-office-manager-dashboard" class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-white/40 transition text-sm flex items-center gap-1">
                    <span class="material-symbols-rounded text-base">refresh</span> Reset
                </a>
            </div>
        </form>

        <!-- KPI Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-10">
            <!-- Total Revenue -->
            <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-teal-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Revenue</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-2 font-['Outfit']">Rs. <?php echo number_format($totalRevenue); ?></h3>
                        <p class="text-teal-600 text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">payments</span> All time sales
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-100/50 flex items-center justify-center text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">account_balance</span>
                    </div>
                </div>
            </div>
            <!-- Total Orders -->
            <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-blue-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Orders</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']"><?php echo number_format($totalOrders); ?></h3>
                        <p class="text-blue-600 text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">shopping_cart</span> Island-wide
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-100/50 flex items-center justify-center text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">receipt_long</span>
                    </div>
                </div>
            </div>
            <!-- Pending -->
            <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-yellow-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pending</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']"><?php echo number_format($pendingOrders); ?></h3>
                        <p class="text-gray-500 text-xs font-medium mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">hourglass_top</span> Awaiting processing
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-yellow-100/50 flex items-center justify-center text-yellow-600 group-hover:bg-yellow-400 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">pending</span>
                    </div>
                </div>
            </div>
            <!-- Delivered -->
            <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-green-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Delivered</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']"><?php echo number_format($deliveredOrders); ?></h3>
                        <p class="text-green-600 text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">check_circle</span> Successfully delivered
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-green-100/50 flex items-center justify-center text-green-600 group-hover:bg-green-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">done_all</span>
                    </div>
                </div>
            </div>
            <!-- Monthly Growth -->
            <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-purple-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Monthly Growth</p>
                        <h3 class="text-3xl font-bold mt-2 font-['Outfit'] <?php echo $monthlyGrowth['growth_pct'] >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo ($monthlyGrowth['growth_pct'] >= 0 ? '+' : '') . $monthlyGrowth['growth_pct']; ?>%
                        </h3>
                        <p class="<?php echo $monthlyGrowth['growth_pct'] >= 0 ? 'text-green-600' : 'text-red-600'; ?> text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1"><?php echo $monthlyGrowth['growth_pct'] >= 0 ? 'trending_up' : 'trending_down'; ?></span> vs last month
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-purple-100/50 flex items-center justify-center text-purple-600 group-hover:bg-purple-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">insights</span>
                    </div>
                </div>
            </div>
            <!-- Low Stock -->
            <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-red-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Low Stock</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']"><?php echo count($lowStockAlerts); ?></h3>
                        <p class="text-red-500 text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">warning</span> <?php echo count($lowStockAlerts) > 0 ? 'Need attention' : 'All healthy'; ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-red-100/50 flex items-center justify-center text-red-500 group-hover:bg-red-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">inventory</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Stock Transfer Approvals -->
        <?php if (!empty($pendingTransfers)): ?>
        <div class="glass-panel rounded-3xl p-6 sm:p-8 mb-10">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-rounded text-yellow-500 text-2xl">approval_delegation</span>
                    <h2 class="text-xl font-bold text-gray-800 font-['Outfit']">Pending Approvals</h2>
                    <span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-3 py-1 rounded-full"><?php echo count($pendingTransfers); ?> pending</span>
                </div>
            </div>
            <div class="space-y-4">
                <?php foreach ($pendingTransfers as $st): ?>
                    <div class="bg-white/40 border border-white/60 backdrop-blur-sm rounded-2xl p-5 hover:bg-white/60 transition duration-300 group shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-xl <?php echo $st['is_urgent'] ? 'bg-red-100/50 text-red-600 border border-red-100' : 'bg-blue-100/50 text-blue-600 border border-blue-100'; ?> flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition duration-300">
                                    <span class="material-symbols-rounded"><?php echo $st['is_urgent'] ? 'priority_high' : 'swap_horiz'; ?></span>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 font-['Outfit']"><?php echo htmlspecialchars($st['transfer_number']); ?></h3>
                                    <div class="flex items-center text-xs text-gray-600 mt-1 space-x-2">
                                        <span><?php echo htmlspecialchars($st['source_rdc']); ?></span>
                                        <span class="material-symbols-rounded text-gray-400" style="font-size:14px">arrow_forward</span>
                                        <span><?php echo htmlspecialchars($st['dest_rdc']); ?></span>
                                        <?php if ($st['is_urgent']): ?>
                                            <span class="bg-red-100 text-red-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full uppercase">Urgent</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center text-xs text-gray-500 mt-1">
                                        <span class="material-symbols-rounded text-sm mr-1">person</span> <?php echo htmlspecialchars($st['requester']); ?> &mdash; <?php echo htmlspecialchars($st['request_reason'] ?? 'No reason'); ?>
                                    </div>
                                </div>
                            </div>
                            <form method="POST" action="<?php echo BASE_PATH; ?>/index.php?page=head-office-manager-dashboard" class="flex items-center gap-2 flex-shrink-0">
                                <input type="hidden" name="transfer_id" value="<?php echo $st['transfer_id']; ?>">
                                <input type="hidden" name="update_transfer_status" value="1">
                                <input type="text" name="remarks" placeholder="Remarks..." class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 text-sm w-28 focus:ring-2 focus:ring-teal-500 outline-none shadow-sm">
                                <button type="submit" name="status" value="REJECTED" class="px-4 py-2 rounded-xl border border-red-200 bg-red-50/50 text-red-600 text-sm font-bold hover:bg-red-100 transition">Reject</button>
                                <button type="submit" name="status" value="APPROVED" class="px-4 py-2 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-bold shadow-lg shadow-green-200/50 hover:scale-[1.02] transition">Approve</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
            <div class="glass-panel rounded-3xl p-6 sm:p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <span class="material-symbols-rounded text-blue-500 text-2xl">bar_chart</span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">RDC Sales</h2>
                </div>
                <div class="h-60">
                    <?php if (empty($rdcChartData['labels'])): ?>
                        <div class="flex flex-col items-center justify-center h-full">
                            <div class="w-16 h-16 rounded-full bg-blue-100/50 flex items-center justify-center text-blue-300 mb-3"><span class="material-symbols-rounded text-3xl">bar_chart</span></div>
                            <p class="text-sm text-gray-400">No RDC sales data yet</p>
                        </div>
                    <?php else: ?>
                        <canvas id="rdcBarChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
            <div class="glass-panel rounded-3xl p-6 sm:p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <span class="material-symbols-rounded text-purple-500 text-2xl">show_chart</span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Monthly Revenue</h2>
                </div>
                <div class="h-60">
                    <?php if (empty($monthlyChartData['labels'])): ?>
                        <div class="flex flex-col items-center justify-center h-full">
                            <div class="w-16 h-16 rounded-full bg-purple-100/50 flex items-center justify-center text-purple-300 mb-3"><span class="material-symbols-rounded text-3xl">show_chart</span></div>
                            <p class="text-sm text-gray-400">No monthly revenue data</p>
                        </div>
                    <?php else: ?>
                        <canvas id="monthlyLineChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
            <div class="glass-panel rounded-3xl p-6 sm:p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <span class="material-symbols-rounded text-green-500 text-2xl">pie_chart</span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Order Status</h2>
                </div>
                <div class="h-60">
                    <?php if (empty($statusChartData['labels'])): ?>
                        <div class="flex flex-col items-center justify-center h-full">
                            <div class="w-16 h-16 rounded-full bg-green-100/50 flex items-center justify-center text-green-300 mb-3"><span class="material-symbols-rounded text-3xl">pie_chart</span></div>
                            <p class="text-sm text-gray-400">No orders yet</p>
                        </div>
                    <?php else: ?>
                        <canvas id="statusPieChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Bottom: Top Products + Delivery + Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
            <!-- Top 5 Products -->
            <div class="lg:col-span-2">
                <div class="glass-panel rounded-3xl p-6 sm:p-8">
                    <div class="flex items-center space-x-3 mb-8">
                        <span class="material-symbols-rounded text-teal-500 text-2xl">trending_up</span>
                        <h2 class="text-xl font-bold text-gray-800 font-['Outfit']">Top Selling Products</h2>
                    </div>
                    <?php if (empty($topProducts)): ?>
                        <div class="flex flex-col items-center justify-center py-12">
                            <div class="w-16 h-16 rounded-full bg-gray-100/50 flex items-center justify-center text-gray-300 mb-3"><span class="material-symbols-rounded text-3xl">inventory_2</span></div>
                            <p class="text-sm text-gray-400">No product sales data yet</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php
                            $rankColors = [
                                ['bg'=>'bg-yellow-100/50','text'=>'text-yellow-600','border'=>'border-yellow-100','bar'=>'from-yellow-400 to-yellow-500'],
                                ['bg'=>'bg-gray-100/50','text'=>'text-gray-500','border'=>'border-gray-100','bar'=>'from-gray-400 to-gray-500'],
                                ['bg'=>'bg-orange-100/50','text'=>'text-orange-600','border'=>'border-orange-100','bar'=>'from-orange-400 to-orange-500'],
                                ['bg'=>'bg-blue-100/50','text'=>'text-blue-600','border'=>'border-blue-100','bar'=>'from-blue-400 to-blue-500'],
                                ['bg'=>'bg-purple-100/50','text'=>'text-purple-600','border'=>'border-purple-100','bar'=>'from-purple-400 to-purple-500'],
                            ];
                            $maxSales = $topProducts[0]['total_sales'] ?: 1;
                            foreach ($topProducts as $i => $p):
                                $pct = round(($p['total_sales'] / $maxSales) * 100);
                                $c = $rankColors[$i] ?? $rankColors[4];
                            ?>
                                <div class="bg-white/40 border border-white/60 backdrop-blur-sm rounded-2xl p-4 hover:bg-white/60 transition duration-300 shadow-sm">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl <?php echo $c['bg'].' '.$c['text'].' border '.$c['border']; ?> flex items-center justify-center flex-shrink-0 font-bold font-['Outfit'] text-lg">
                                            <?php echo $i + 1; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <h3 class="font-bold text-gray-800 text-sm font-['Outfit'] truncate"><?php echo htmlspecialchars($p['product_name']); ?></h3>
                                                <span class="text-sm font-bold text-gray-800 font-['Outfit'] ml-3 flex-shrink-0">Rs. <?php echo number_format($p['total_sales']); ?></span>
                                            </div>
                                            <div class="w-full bg-gray-100 rounded-full h-2">
                                                <div class="bg-gradient-to-r <?php echo $c['bar']; ?> h-2 rounded-full transition-all duration-500" style="width: <?php echo $pct; ?>%"></div>
                                            </div>
                                            <p class="text-xs text-gray-400 mt-1.5"><?php echo number_format($p['total_qty']); ?> units sold</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-8">
                <!-- Delivery Efficiency -->
                <div class="glass-card rounded-3xl p-6 sm:p-8">
                    <div class="flex items-center space-x-2 mb-6">
                        <span class="material-symbols-rounded text-emerald-500 text-2xl">local_shipping</span>
                        <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Delivery Stats</h2>
                    </div>
                    <?php if (empty($deliveryStats['total_deliveries']) || $deliveryStats['total_deliveries'] == 0): ?>
                        <div class="flex flex-col items-center justify-center py-8">
                            <div class="w-14 h-14 rounded-full bg-gray-100/50 flex items-center justify-center text-gray-300 mb-3"><span class="material-symbols-rounded text-2xl">local_shipping</span></div>
                            <p class="text-sm text-gray-400">No delivery records</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white/40 border border-white/60 backdrop-blur-sm rounded-2xl p-4">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1 font-['Outfit']"><?php echo number_format($deliveryStats['total_deliveries']); ?></h3>
                            </div>
                            <div class="bg-white/40 border border-white/60 backdrop-blur-sm rounded-2xl p-4">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Completed</p>
                                <h3 class="text-2xl font-bold text-green-600 mt-1 font-['Outfit']"><?php echo number_format($deliveryStats['completed']); ?></h3>
                            </div>
                            <div class="bg-white/40 border border-white/60 backdrop-blur-sm rounded-2xl p-4">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Avg Time</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1 font-['Outfit']"><?php echo $deliveryStats['avg_hours'] ?? 0; ?>h</h3>
                            </div>
                            <div class="bg-white/40 border border-white/60 backdrop-blur-sm rounded-2xl p-4">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Success</p>
                                <h3 class="text-2xl font-bold text-teal-600 mt-1 font-['Outfit']"><?php echo $deliveryStats['completion_rate'] ?? 0; ?>%</h3>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="glass-card rounded-3xl p-6 sm:p-8">
                    <div class="flex items-center space-x-2 mb-6">
                        <span class="material-symbols-rounded text-yellow-500 text-2xl">bolt</span>
                        <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Quick Actions</h2>
                    </div>
                    <div class="space-y-3">
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=stock-reports" class="flex items-center p-4 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-200/50 transform hover:scale-[1.02] transition duration-300 group border border-white/20">
                            <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center mr-4 group-hover:bg-white/30 transition backdrop-blur-sm"><span class="material-symbols-rounded">bar_chart</span></div>
                            <div><h3 class="font-bold text-sm">Stock Reports</h3><p class="text-xs opacity-90">View inventory levels</p></div>
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=delivery-report" class="flex items-center p-4 rounded-xl bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-200/50 transform hover:scale-[1.02] transition duration-300 group border border-white/20">
                            <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center mr-4 group-hover:bg-white/30 transition backdrop-blur-sm"><span class="material-symbols-rounded">analytics</span></div>
                            <div><h3 class="font-bold text-sm">Delivery Efficiency</h3><p class="text-xs opacity-90">On-time delivery analytics</p></div>
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=stock-reports" class="flex items-center p-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-lg shadow-teal-200/50 transform hover:scale-[1.02] transition duration-300 group border border-white/20">
                            <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center mr-4 group-hover:bg-white/30 transition backdrop-blur-sm"><span class="material-symbols-rounded">swap_horiz</span></div>
                            <div><h3 class="font-bold text-sm">Transfer Approvals</h3><p class="text-xs opacity-90"><?php echo count($pendingTransfers); ?> pending requests</p></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <?php if (!empty($lowStockAlerts)): ?>
        <div class="glass-panel rounded-3xl p-6 sm:p-8 mb-10">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-rounded text-red-500 text-2xl">warning</span>
                    <h2 class="text-xl font-bold text-gray-800 font-['Outfit']">Low Stock Alerts</h2>
                    <span class="bg-red-100 text-red-600 text-xs font-bold px-3 py-1 rounded-full"><?php echo count($lowStockAlerts); ?> items</span>
                </div>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=stock-reports" class="text-sm font-semibold text-teal-600 hover:text-teal-700 flex items-center transition">
                    Full Report <span class="material-symbols-rounded text-sm ml-1">arrow_forward</span>
                </a>
            </div>
            <div class="space-y-3">
                <?php foreach ($lowStockAlerts as $a):
                    $ratio = ($a['minimum_stock_level'] > 0) ? ($a['available_quantity'] / $a['minimum_stock_level']) : 0;
                    $urgencyBg = $ratio < 0.25 ? 'bg-red-100/50 text-red-600 border-red-100' : ($ratio < 0.5 ? 'bg-yellow-100/50 text-yellow-600 border-yellow-100' : 'bg-orange-100/50 text-orange-600 border-orange-100');
                ?>
                    <div class="bg-white/40 border border-white/60 backdrop-blur-sm rounded-2xl p-4 hover:bg-white/60 transition duration-300 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 rounded-xl <?php echo $urgencyBg; ?> border flex items-center justify-center flex-shrink-0"><span class="material-symbols-rounded text-lg">warning</span></div>
                                <div>
                                    <h3 class="font-bold text-gray-800 text-sm font-['Outfit']"><?php echo htmlspecialchars($a['product_name']); ?></h3>
                                    <div class="flex items-center text-xs text-gray-500 mt-0.5 space-x-3">
                                        <span><?php echo htmlspecialchars($a['product_code']); ?></span>
                                        <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                                        <span><?php echo htmlspecialchars($a['rdc_name']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold <?php echo $ratio < 0.25 ? 'text-red-600' : 'text-yellow-600'; ?>"><?php echo number_format($a['available_quantity']); ?> left</p>
                                <p class="text-xs text-gray-400">min: <?php echo number_format($a['minimum_stock_level']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    Chart.defaults.font.family = "'Outfit', 'Segoe UI', system-ui, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#9ca3af';

    var rdcData     = <?php echo json_encode($rdcChartData); ?>;
    var monthlyData = <?php echo json_encode($monthlyChartData); ?>;
    var statusData  = <?php echo json_encode($statusChartData); ?>;
    var pieColors   = <?php echo json_encode($pieColors); ?>;
    var barPalette  = ['#14b8a6','#3b82f6','#8b5cf6','#f59e0b','#ef4444'];

    var el;
    el = document.getElementById('rdcBarChart');
    if (el && rdcData.labels.length > 0) {
        new Chart(el, {
            type: 'bar',
            data: { labels: rdcData.labels, datasets: [{ label: 'Revenue (Rs)', data: rdcData.revenues, backgroundColor: barPalette.slice(0, rdcData.labels.length), borderRadius: 8, barPercentage: 0.6 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: function(v){return 'Rs.'+(v/1000)+'K';} } }, x: { grid: { display: false } } } }
        });
    }

    el = document.getElementById('monthlyLineChart');
    if (el && monthlyData.labels.length > 0) {
        new Chart(el, {
            type: 'line',
            data: { labels: monthlyData.labels, datasets: [{ label: 'Revenue (Rs)', data: monthlyData.revenues, borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.08)', fill: true, tension: 0.4, pointBackgroundColor: '#8b5cf6', pointRadius: 4, pointHoverRadius: 6, borderWidth: 2.5 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: function(v){return 'Rs.'+(v/1000)+'K';} } }, x: { grid: { display: false } } } }
        });
    }

    el = document.getElementById('statusPieChart');
    if (el && statusData.labels.length > 0) {
        new Chart(el, {
            type: 'doughnut',
            data: { labels: statusData.labels, datasets: [{ data: statusData.counts, backgroundColor: pieColors, borderWidth: 3, borderColor: 'rgba(255,255,255,0.8)', hoverOffset: 8 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' } } } }
        });
    }
})();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
