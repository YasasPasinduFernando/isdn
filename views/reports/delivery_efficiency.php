<?php
/**
 * RDC-wise Delivery Efficiency Report
 *
 * Access: head_office_manager, system_admin only
 * Features: KPI summary, RDC comparison table, bar chart (Chart.js), CSV export
 *
 * Architecture:
 *   View (this file) → DeliveryReport model → MySQL aggregation
 *   Filters applied via GET parameters with prepared statements
 */
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/DeliveryReport.php';

// ── Role-based access control ────────────────────────────────
$allowedRoles = [USER_ROLE_HEAD_OFFICE_MANAGER, USER_ROLE_SYSTEM_ADMIN];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', $allowedRoles, true)) {
    flash_message('Access denied. This report requires Head Office Manager or System Admin privileges.', 'error');
    redirect('/index.php?page=login');
}

// ── Initialize model and ensure indexes ──────────────────────
$report = new DeliveryReport($pdo);
$report->ensureIndexes(); // Idempotent index creation for performance

// ── Sanitize filters ─────────────────────────────────────────
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
if (!empty($_GET['status']) && in_array($_GET['status'], ['completed', 'pending', 'on_time', 'delayed'])) {
    $filters['status'] = $_GET['status'];
}

// ── Fetch data ───────────────────────────────────────────────
$error = null;
try {
    $rdcData    = $report->getRdcEfficiency($filters);
    $summary    = $report->getOverallSummary($filters);
    $details    = $report->getDeliveryDetails($filters, 50);
    $allRdcs    = $report->getAllRdcs();
} catch (Exception $e) {
    $error   = $e->getMessage();
    $rdcData = $details = $allRdcs = [];
    $summary = ['total_deliveries' => 0, 'completed' => 0, 'on_time' => 0, 'delayed' => 0, 'pending' => 0, 'overall_efficiency' => 0, 'avg_hours' => 0];
}

// ── CSV Export ───────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="delivery_efficiency_report_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');

    // Summary section
    fputcsv($out, ['=== ISDN Delivery Efficiency Report ===']);
    fputcsv($out, ['Generated', date('Y-m-d H:i:s')]);
    fputcsv($out, ['Filters', json_encode($filters)]);
    fputcsv($out, []);

    // Overall summary
    fputcsv($out, ['--- Overall Summary ---']);
    fputcsv($out, ['Total Deliveries', $summary['total_deliveries']]);
    fputcsv($out, ['Completed', $summary['completed']]);
    fputcsv($out, ['On-time', $summary['on_time']]);
    fputcsv($out, ['Delayed', $summary['delayed']]);
    fputcsv($out, ['Pending', $summary['pending']]);
    fputcsv($out, ['Overall Efficiency %', $summary['overall_efficiency']]);
    fputcsv($out, ['Avg Delivery Hours', $summary['avg_hours']]);
    fputcsv($out, []);

    // RDC breakdown
    fputcsv($out, ['--- RDC Breakdown ---']);
    fputcsv($out, ['RDC Name', 'Code', 'Total', 'Completed', 'On-time', 'Delayed', 'Pending', 'Efficiency %', 'Avg Hours']);
    foreach ($rdcData as $row) {
        fputcsv($out, [
            $row['rdc_name'], $row['rdc_code'], $row['total_deliveries'],
            $row['completed'], $row['on_time'], $row['delayed'], $row['pending'],
            $row['efficiency_pct'] ?? 0, $row['avg_delivery_hours'] ?? 0,
        ]);
    }

    fputcsv($out, []);
    fputcsv($out, ['--- Delivery Details ---']);
    fputcsv($out, ['Order #', 'RDC', 'Driver', 'Scheduled', 'Completed', 'Status', 'Duration (hrs)']);
    foreach ($details as $d) {
        fputcsv($out, [
            $d['order_number'], $d['rdc_name'], $d['driver_name'] ?? '-',
            $d['scheduled_date'], $d['completed_date'] ?? '-',
            $d['delivery_status'], $d['duration_hours'] ?? '-',
        ]);
    }

    fclose($out);
    exit;
}

// ── Chart data preparation ───────────────────────────────────
$chartData = [
    'labels'     => array_column($rdcData, 'rdc_name'),
    'on_time'    => array_map('intval', array_column($rdcData, 'on_time')),
    'delayed'    => array_map('intval', array_column($rdcData, 'delayed')),
    'pending'    => array_map('intval', array_column($rdcData, 'pending')),
    'efficiency' => array_map('floatval', array_column($rdcData, 'efficiency_pct')),
    'avg_hours'  => array_map('floatval', array_column($rdcData, 'avg_delivery_hours')),
];

$statusBadge = [
    'On-time' => 'bg-green-100 text-green-700',
    'Delayed' => 'bg-red-100 text-red-700',
    'Pending' => 'bg-yellow-100 text-yellow-700',
];
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        <?php display_flash(); ?>

        <?php if ($error): ?>
            <div class="glass-card rounded-2xl p-5 mb-6 border-l-4 border-red-500">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-rounded text-red-500 text-2xl">error</span>
                    <div>
                        <p class="font-bold text-gray-800">Report Error</p>
                        <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <a href="<?php echo BASE_PATH; ?>/index.php?page=<?php echo $_SESSION['role'] === 'system_admin' ? 'system-admin-dashboard' : 'head-office-manager-dashboard'; ?>" class="w-10 h-10 rounded-xl bg-white/50 border border-white/60 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-white/70 transition"><span class="material-symbols-rounded">arrow_back</span></a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 font-['Outfit']">Delivery Efficiency Report</h1>
                    <p class="text-sm text-gray-500">RDC-wise delivery performance analysis</p>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-4 md:mt-0">
                <?php
                    $exportQs = http_build_query(array_merge($_GET, ['export' => 'csv']));
                ?>
                <a href="<?php echo BASE_PATH; ?>/index.php?<?php echo $exportQs; ?>" class="px-5 py-2.5 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold text-sm shadow-lg shadow-green-200/50 hover:scale-[1.02] transition flex items-center gap-2">
                    <span class="material-symbols-rounded text-lg">download</span> Export CSV
                </a>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" action="<?php echo BASE_PATH; ?>/index.php" class="glass-card rounded-2xl p-5 mb-8 flex flex-wrap gap-4 items-end">
            <input type="hidden" name="page" value="delivery-report">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">From</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($filters['start_date'] ?? ''); ?>"
                       class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">To</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($filters['end_date'] ?? ''); ?>"
                       class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">RDC</label>
                <select name="rdc_id" class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 text-sm min-w-[150px] focus:ring-2 focus:ring-teal-500 transition shadow-sm">
                    <option value="">All RDCs</option>
                    <?php foreach ($allRdcs as $r): ?>
                        <option value="<?php echo $r['rdc_id']; ?>" <?php echo (($filters['rdc_id'] ?? '') == $r['rdc_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($r['rdc_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                <select name="status" class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 text-sm min-w-[130px] focus:ring-2 focus:ring-teal-500 transition shadow-sm">
                    <option value="">All</option>
                    <option value="completed" <?php echo (($filters['status'] ?? '') === 'completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="pending"   <?php echo (($filters['status'] ?? '') === 'pending')   ? 'selected' : ''; ?>>Pending</option>
                    <option value="on_time"   <?php echo (($filters['status'] ?? '') === 'on_time')   ? 'selected' : ''; ?>>On-time</option>
                    <option value="delayed"   <?php echo (($filters['status'] ?? '') === 'delayed')   ? 'selected' : ''; ?>>Delayed</option>
                </select>
            </div>
            <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white font-bold text-sm shadow-lg hover:scale-[1.02] transition flex items-center gap-1.5">
                <span class="material-symbols-rounded text-base">filter_list</span> Apply
            </button>
            <a href="<?php echo BASE_PATH; ?>/index.php?page=delivery-report" class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-white/40 transition text-sm flex items-center gap-1">
                <span class="material-symbols-rounded text-base">refresh</span> Reset
            </a>
        </form>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-10">
            <div class="glass-card p-5 rounded-3xl border-l-4 border-blue-500 group hover-lift">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Total</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1 font-['Outfit']"><?php echo number_format($summary['total_deliveries']); ?></h3>
                <p class="text-blue-600 text-[10px] font-semibold mt-1 flex items-center"><span class="material-symbols-rounded text-xs mr-0.5">local_shipping</span> Deliveries</p>
            </div>
            <div class="glass-card p-5 rounded-3xl border-l-4 border-green-500 group hover-lift">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">On-time</p>
                <h3 class="text-2xl font-bold text-green-600 mt-1 font-['Outfit']"><?php echo number_format($summary['on_time']); ?></h3>
                <p class="text-green-600 text-[10px] font-semibold mt-1 flex items-center"><span class="material-symbols-rounded text-xs mr-0.5">check_circle</span> Before deadline</p>
            </div>
            <div class="glass-card p-5 rounded-3xl border-l-4 border-red-500 group hover-lift">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Delayed</p>
                <h3 class="text-2xl font-bold text-red-600 mt-1 font-['Outfit']"><?php echo number_format($summary['delayed']); ?></h3>
                <p class="text-red-500 text-[10px] font-semibold mt-1 flex items-center"><span class="material-symbols-rounded text-xs mr-0.5">schedule</span> Past deadline</p>
            </div>
            <div class="glass-card p-5 rounded-3xl border-l-4 border-yellow-400 group hover-lift">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Pending</p>
                <h3 class="text-2xl font-bold text-yellow-600 mt-1 font-['Outfit']"><?php echo number_format($summary['pending']); ?></h3>
                <p class="text-yellow-600 text-[10px] font-semibold mt-1 flex items-center"><span class="material-symbols-rounded text-xs mr-0.5">hourglass_top</span> In progress</p>
            </div>
            <div class="glass-card p-5 rounded-3xl border-l-4 border-teal-500 group hover-lift">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Efficiency</p>
                <h3 class="text-2xl font-bold <?php echo ($summary['overall_efficiency'] ?? 0) >= 70 ? 'text-green-600' : 'text-red-600'; ?> mt-1 font-['Outfit']"><?php echo $summary['overall_efficiency'] ?? 0; ?>%</h3>
                <p class="text-teal-600 text-[10px] font-semibold mt-1 flex items-center"><span class="material-symbols-rounded text-xs mr-0.5">speed</span> On-time rate</p>
            </div>
            <div class="glass-card p-5 rounded-3xl border-l-4 border-purple-500 group hover-lift">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Avg Time</p>
                <h3 class="text-2xl font-bold text-purple-600 mt-1 font-['Outfit']"><?php echo $summary['avg_hours'] ?? 0; ?>h</h3>
                <p class="text-purple-600 text-[10px] font-semibold mt-1 flex items-center"><span class="material-symbols-rounded text-xs mr-0.5">timer</span> Order→Delivery</p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
            <!-- Stacked Bar Chart: On-time vs Delayed per RDC -->
            <div class="glass-panel rounded-3xl p-6 sm:p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <span class="material-symbols-rounded text-blue-500 text-2xl">bar_chart</span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">RDC Delivery Breakdown</h2>
                </div>
                <div class="h-72">
                    <?php if (empty($chartData['labels'])): ?>
                        <div class="flex flex-col items-center justify-center h-full"><p class="text-sm text-gray-400">No delivery data</p></div>
                    <?php else: ?>
                        <canvas id="rdcBreakdownChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Efficiency % + Avg Hours -->
            <div class="glass-panel rounded-3xl p-6 sm:p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <span class="material-symbols-rounded text-teal-500 text-2xl">speed</span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Efficiency &amp; Avg Duration</h2>
                </div>
                <div class="h-72">
                    <?php if (empty($chartData['labels'])): ?>
                        <div class="flex flex-col items-center justify-center h-full"><p class="text-sm text-gray-400">No data</p></div>
                    <?php else: ?>
                        <canvas id="efficiencyChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RDC Comparison Table -->
        <div class="glass-panel rounded-3xl p-6 sm:p-8 mb-10">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-rounded text-indigo-500 text-2xl">table_chart</span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">RDC Comparison</h2>
                </div>
            </div>
            <?php if (empty($rdcData)): ?>
                <div class="text-center py-12"><p class="text-sm text-gray-400">No delivery data available for the selected filters.</p></div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200/50">
                                <th class="pb-3 pr-4">RDC</th>
                                <th class="pb-3 pr-4 text-center">Total</th>
                                <th class="pb-3 pr-4 text-center">On-time</th>
                                <th class="pb-3 pr-4 text-center">Delayed</th>
                                <th class="pb-3 pr-4 text-center">Pending</th>
                                <th class="pb-3 pr-4 text-center">Efficiency</th>
                                <th class="pb-3 pr-4 text-center">Avg Hours</th>
                                <th class="pb-3">Performance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/50">
                            <?php foreach ($rdcData as $r):
                                $eff = $r['efficiency_pct'] ?? 0;
                                $effColor = $eff >= 80 ? 'text-green-600' : ($eff >= 50 ? 'text-yellow-600' : 'text-red-600');
                                $barColor = $eff >= 80 ? 'from-green-400 to-green-500' : ($eff >= 50 ? 'from-yellow-400 to-yellow-500' : 'from-red-400 to-red-500');
                            ?>
                            <tr class="hover:bg-white/30 transition">
                                <td class="py-4 pr-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-100/50 text-indigo-600 flex items-center justify-center flex-shrink-0"><span class="material-symbols-rounded text-base">warehouse</span></div>
                                        <div>
                                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($r['rdc_name']); ?></p>
                                            <p class="text-[10px] text-gray-400"><?php echo htmlspecialchars($r['rdc_code']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 pr-4 text-center font-bold text-gray-800"><?php echo $r['total_deliveries']; ?></td>
                                <td class="py-4 pr-4 text-center font-semibold text-green-600"><?php echo $r['on_time']; ?></td>
                                <td class="py-4 pr-4 text-center font-semibold text-red-600"><?php echo $r['delayed']; ?></td>
                                <td class="py-4 pr-4 text-center text-yellow-600"><?php echo $r['pending']; ?></td>
                                <td class="py-4 pr-4 text-center font-bold <?php echo $effColor; ?>"><?php echo $eff; ?>%</td>
                                <td class="py-4 pr-4 text-center text-gray-600"><?php echo $r['avg_delivery_hours'] ?? '-'; ?>h</td>
                                <td class="py-4 min-w-[120px]">
                                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                                        <div class="bg-gradient-to-r <?php echo $barColor; ?> h-2.5 rounded-full transition-all duration-500" style="width: <?php echo min(100, $eff); ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Detailed Delivery Records -->
        <?php if (!empty($details)): ?>
        <div class="glass-panel rounded-3xl p-6 sm:p-8 mb-10">
            <div class="flex items-center space-x-3 mb-6">
                <span class="material-symbols-rounded text-orange-500 text-2xl">list_alt</span>
                <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Delivery Records</h2>
                <span class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full"><?php echo count($details); ?> records</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200/50">
                            <th class="pb-3 pr-3">Order #</th>
                            <th class="pb-3 pr-3">RDC</th>
                            <th class="pb-3 pr-3">Driver</th>
                            <th class="pb-3 pr-3">Scheduled</th>
                            <th class="pb-3 pr-3">Completed</th>
                            <th class="pb-3 pr-3">Status</th>
                            <th class="pb-3">Duration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100/50">
                        <?php foreach ($details as $d): ?>
                        <tr class="hover:bg-white/30 transition">
                            <td class="py-3 pr-3 font-semibold text-gray-800"><?php echo htmlspecialchars($d['order_number']); ?></td>
                            <td class="py-3 pr-3 text-gray-600"><?php echo htmlspecialchars($d['rdc_name']); ?></td>
                            <td class="py-3 pr-3 text-gray-600"><?php echo htmlspecialchars($d['driver_name'] ?? '-'); ?></td>
                            <td class="py-3 pr-3 text-gray-500 text-xs"><?php echo $d['scheduled_date'] ? date('M j, Y H:i', strtotime($d['scheduled_date'])) : '-'; ?></td>
                            <td class="py-3 pr-3 text-gray-500 text-xs"><?php echo $d['completed_date'] ? date('M j, Y H:i', strtotime($d['completed_date'])) : '-'; ?></td>
                            <td class="py-3 pr-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo $statusBadge[$d['delivery_status']] ?? 'bg-gray-100 text-gray-600'; ?>"><?php echo $d['delivery_status']; ?></span></td>
                            <td class="py-3 text-gray-600"><?php echo $d['duration_hours'] !== null ? $d['duration_hours'] . 'h' : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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

    var data = <?php echo json_encode($chartData); ?>;
    var el;

    // Stacked bar: On-time vs Delayed vs Pending per RDC
    el = document.getElementById('rdcBreakdownChart');
    if (el && data.labels.length > 0) {
        new Chart(el, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    { label: 'On-time',  data: data.on_time, backgroundColor: '#10b981', borderRadius: 4, barPercentage: 0.6 },
                    { label: 'Delayed',  data: data.delayed, backgroundColor: '#ef4444', borderRadius: 4, barPercentage: 0.6 },
                    { label: 'Pending',  data: data.pending, backgroundColor: '#f59e0b', borderRadius: 4, barPercentage: 0.6 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', padding: 16 } } },
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, title: { display: true, text: 'Deliveries', font: { size: 10 } } }
                }
            }
        });
    }

    // Combo chart: Efficiency % (bars) + Avg Hours (line)
    el = document.getElementById('efficiencyChart');
    if (el && data.labels.length > 0) {
        new Chart(el, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Efficiency %',
                        data: data.efficiency,
                        backgroundColor: data.efficiency.map(function(v) { return v >= 80 ? '#10b981' : (v >= 50 ? '#f59e0b' : '#ef4444'); }),
                        borderRadius: 6,
                        barPercentage: 0.5,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Avg Hours',
                        data: data.avg_hours,
                        type: 'line',
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139,92,246,0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#8b5cf6',
                        borderWidth: 2.5,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', padding: 16 } } },
                scales: {
                    y:  { beginAtZero: true, max: 100, position: 'left', grid: { color: 'rgba(0,0,0,0.04)' }, title: { display: true, text: 'Efficiency %', font: { size: 10 } } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Avg Hours', font: { size: 10 } } },
                    x:  { grid: { display: false } }
                }
            }
        });
    }
})();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
