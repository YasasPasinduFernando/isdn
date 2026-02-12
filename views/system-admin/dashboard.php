<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/SystemAdmin.php';

// ── Role guard ───────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== USER_ROLE_SYSTEM_ADMIN) {
    redirect('/index.php?page=login');
}

// ── Data fetching ────────────────────────────────────────────
$error = null;
try {
    $admin = new SystemAdmin($pdo);
    $stats         = $admin->getDashboardStats();
    $usersByRole   = $admin->getUsersByRole();
    $ordersByMonth = $admin->getOrdersByMonth(6);
    $recentOrders  = $admin->getRecentOrders(5);
    $recentLogs    = $admin->getRecentAuditLogs(8);
} catch (Exception $e) {
    $error = $e->getMessage();
    $stats = ['total_users' => 0, 'active_users' => 0, 'total_products' => 0, 'total_orders' => 0, 'total_revenue' => 0, 'pending_orders' => 0];
    $usersByRole = $ordersByMonth = $recentOrders = $recentLogs = [];
}

// Chart data
$roleChartData = [
    'labels' => array_map(fn($r) => ucwords(str_replace('_', ' ', $r['role'])), $usersByRole),
    'counts' => array_map(fn($r) => (int) $r['count'], $usersByRole),
];
$roleColors = ['#14b8a6','#3b82f6','#8b5cf6','#f59e0b','#ef4444','#ec4899','#6366f1','#10b981'];

$orderChartData = [
    'labels'   => array_column($ordersByMonth, 'month_label'),
    'orders'   => array_map('intval', array_column($ordersByMonth, 'order_count')),
    'revenues' => array_map('floatval', array_column($ordersByMonth, 'revenue')),
];

$statusColors = [
    'pending'    => 'bg-yellow-100 text-yellow-700',
    'confirmed'  => 'bg-blue-100 text-blue-700',
    'processing' => 'bg-purple-100 text-purple-700',
    'delivered'  => 'bg-green-100 text-green-700',
    'cancelled'  => 'bg-red-100 text-red-700',
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
                        <p class="font-bold text-gray-800">Dashboard Error</p>
                        <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Welcome Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-10">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 font-['Outfit']">System Administration</h1>
                <p class="text-gray-500 mt-1">Welcome back, <span class="text-teal-600 font-semibold"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span> &mdash; <?php echo date('l, F j, Y'); ?></p>
            </div>
            <div class="flex items-center space-x-3 mt-4 md:mt-0">
                <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-users" class="px-5 py-2.5 rounded-full bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-bold text-sm shadow-lg shadow-teal-200/50 hover:scale-[1.02] transition flex items-center gap-2">
                    <span class="material-symbols-rounded text-lg">group_add</span> Manage Users
                </a>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-products" class="px-5 py-2.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold text-sm shadow-lg shadow-blue-200/50 hover:scale-[1.02] transition flex items-center gap-2">
                    <span class="material-symbols-rounded text-lg">inventory_2</span> Manage Products
                </a>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-5 mb-10">
            <!-- Total Users -->
            <div class="glass-card p-5 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-teal-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Total Users</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1.5 font-['Outfit']"><?php echo number_format($stats['total_users']); ?></h3>
                        <p class="text-teal-600 text-[10px] font-semibold mt-1.5 flex items-center"><span class="material-symbols-rounded text-xs mr-0.5">people</span> All accounts</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-teal-100/50 flex items-center justify-center text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition-colors duration-300"><span class="material-symbols-rounded text-xl">group</span></div>
                </div>
            </div>
            <!-- Active Users -->
            <div class="glass-card p-5 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-green-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Active Users</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1.5 font-['Outfit']"><?php echo number_format($stats['active_users']); ?></h3>
                        <p class="text-green-600 text-[10px] font-semibold mt-1.5 flex items-center"><span class="material-symbols-rounded text-xs mr-0.5">check_circle</span> Enabled</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-green-100/50 flex items-center justify-center text-green-600 group-hover:bg-green-500 group-hover:text-white transition-colors duration-300"><span class="material-symbols-rounded text-xl">verified_user</span></div>
                </div>
            </div>
            <!-- Total Products -->
            <div class="glass-card p-5 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-blue-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Products</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1.5 font-['Outfit']"><?php echo number_format($stats['total_products']); ?></h3>
                        <p class="text-blue-600 text-[10px] font-semibold mt-1.5 flex items-center"><span class="material-symbols-rounded text-xs mr-0.5">inventory_2</span> In catalogue</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-100/50 flex items-center justify-center text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition-colors duration-300"><span class="material-symbols-rounded text-xl">category</span></div>
                </div>
            </div>
            <!-- Total Orders -->
            <div class="glass-card p-5 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-purple-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Total Orders</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1.5 font-['Outfit']"><?php echo number_format($stats['total_orders']); ?></h3>
                        <p class="text-purple-600 text-[10px] font-semibold mt-1.5 flex items-center"><span class="material-symbols-rounded text-xs mr-0.5">receipt_long</span> All time</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-purple-100/50 flex items-center justify-center text-purple-600 group-hover:bg-purple-500 group-hover:text-white transition-colors duration-300"><span class="material-symbols-rounded text-xl">shopping_cart</span></div>
                </div>
            </div>
            <!-- Revenue -->
            <div class="glass-card p-5 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-yellow-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Revenue</p>
                        <h3 class="text-xl font-bold text-gray-800 mt-1.5 font-['Outfit']">Rs.<?php echo number_format($stats['total_revenue']); ?></h3>
                        <p class="text-yellow-600 text-[10px] font-semibold mt-1.5 flex items-center"><span class="material-symbols-rounded text-xs mr-0.5">payments</span> All sales</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-yellow-100/50 flex items-center justify-center text-yellow-600 group-hover:bg-yellow-400 group-hover:text-white transition-colors duration-300"><span class="material-symbols-rounded text-xl">account_balance</span></div>
                </div>
            </div>
            <!-- Pending Orders -->
            <div class="glass-card p-5 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-red-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Pending</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1.5 font-['Outfit']"><?php echo number_format($stats['pending_orders']); ?></h3>
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5 flex items-center"><span class="material-symbols-rounded text-xs mr-0.5">hourglass_top</span> Awaiting</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-red-100/50 flex items-center justify-center text-red-500 group-hover:bg-red-500 group-hover:text-white transition-colors duration-300"><span class="material-symbols-rounded text-xl">pending</span></div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
            <!-- Users by Role -->
            <div class="glass-panel rounded-3xl p-6 sm:p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <span class="material-symbols-rounded text-teal-500 text-2xl">pie_chart</span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Users by Role</h2>
                </div>
                <div class="h-72 flex items-center justify-center">
                    <?php if (empty($roleChartData['labels'])): ?>
                        <p class="text-sm text-gray-400">No user data</p>
                    <?php else: ?>
                        <canvas id="roleChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Orders by Month -->
            <div class="glass-panel rounded-3xl p-6 sm:p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <span class="material-symbols-rounded text-blue-500 text-2xl">bar_chart</span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Monthly Orders &amp; Revenue</h2>
                </div>
                <div class="h-72">
                    <?php if (empty($orderChartData['labels'])): ?>
                        <div class="flex items-center justify-center h-full"><p class="text-sm text-gray-400">No order data</p></div>
                    <?php else: ?>
                        <canvas id="ordersChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Bottom: Recent Orders + Activity + Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
            <!-- Recent Orders -->
            <div class="lg:col-span-2 glass-panel rounded-3xl p-6 sm:p-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <span class="material-symbols-rounded text-purple-500 text-2xl">receipt_long</span>
                        <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Recent Orders</h2>
                    </div>
                </div>
                <?php if (empty($recentOrders)): ?>
                    <div class="text-center py-10"><p class="text-sm text-gray-400">No orders yet</p></div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead><tr class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200/50">
                                <th class="pb-3 pr-4">Order #</th><th class="pb-3 pr-4">Customer</th><th class="pb-3 pr-4">RDC</th><th class="pb-3 pr-4">Amount</th><th class="pb-3 pr-4">Status</th><th class="pb-3">Date</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-100/50">
                                <?php foreach ($recentOrders as $o): ?>
                                <tr class="hover:bg-white/30 transition">
                                    <td class="py-3 pr-4 font-semibold text-gray-800"><?php echo htmlspecialchars($o['order_number']); ?></td>
                                    <td class="py-3 pr-4 text-gray-600"><?php echo htmlspecialchars($o['customer_name'] ?? 'N/A'); ?></td>
                                    <td class="py-3 pr-4 text-gray-600"><?php echo htmlspecialchars($o['rdc_name'] ?? '-'); ?></td>
                                    <td class="py-3 pr-4 font-semibold text-gray-800">Rs.<?php echo number_format($o['total_amount']); ?></td>
                                    <td class="py-3 pr-4"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo $statusColors[$o['status']] ?? 'bg-gray-100 text-gray-600'; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                                    <td class="py-3 text-gray-500 text-xs"><?php echo date('M j, H:i', strtotime($o['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="space-y-6">
                <div class="glass-card rounded-3xl p-6">
                    <div class="flex items-center space-x-2 mb-5">
                        <span class="material-symbols-rounded text-yellow-500 text-2xl">bolt</span>
                        <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Quick Actions</h2>
                    </div>
                    <div class="space-y-3">
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-users&action=add" class="flex items-center p-3.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 text-white shadow-lg shadow-teal-200/50 hover:scale-[1.02] transition group">
                            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center mr-3"><span class="material-symbols-rounded text-lg">person_add</span></div>
                            <div><h3 class="font-bold text-sm">Add User</h3><p class="text-[10px] opacity-90">Create new account</p></div>
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-products&action=add" class="flex items-center p-3.5 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-200/50 hover:scale-[1.02] transition group">
                            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center mr-3"><span class="material-symbols-rounded text-lg">add_box</span></div>
                            <div><h3 class="font-bold text-sm">Add Product</h3><p class="text-[10px] opacity-90">New catalogue item</p></div>
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-audit" class="flex items-center p-3.5 rounded-xl bg-gradient-to-r from-purple-500 to-violet-600 text-white shadow-lg shadow-purple-200/50 hover:scale-[1.02] transition group">
                            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center mr-3"><span class="material-symbols-rounded text-lg">history</span></div>
                            <div><h3 class="font-bold text-sm">Audit Logs</h3><p class="text-[10px] opacity-90">View all activity</p></div>
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-profile" class="flex items-center p-3.5 rounded-xl bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-200/50 hover:scale-[1.02] transition group">
                            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center mr-3"><span class="material-symbols-rounded text-lg">manage_accounts</span></div>
                            <div><h3 class="font-bold text-sm">My Profile</h3><p class="text-[10px] opacity-90">Settings & password</p></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Log -->
        <?php if (!empty($recentLogs)): ?>
        <div class="glass-panel rounded-3xl p-6 sm:p-8 mb-10">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-rounded text-indigo-500 text-2xl">history</span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Recent Activity</h2>
                </div>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-audit" class="text-sm font-semibold text-teal-600 hover:text-teal-700 flex items-center">View All <span class="material-symbols-rounded text-sm ml-1">arrow_forward</span></a>
            </div>
            <div class="space-y-3">
                <?php
                $actionIcons = [
                    'CREATE' => ['icon' => 'add_circle', 'bg' => 'bg-green-100/50 text-green-600'],
                    'UPDATE' => ['icon' => 'edit', 'bg' => 'bg-blue-100/50 text-blue-600'],
                    'DELETE' => ['icon' => 'delete', 'bg' => 'bg-red-100/50 text-red-600'],
                    'TOGGLE' => ['icon' => 'toggle_on', 'bg' => 'bg-yellow-100/50 text-yellow-600'],
                    'LOGIN'  => ['icon' => 'login', 'bg' => 'bg-teal-100/50 text-teal-600'],
                    'LOGOUT' => ['icon' => 'logout', 'bg' => 'bg-gray-100/50 text-gray-600'],
                ];
                foreach ($recentLogs as $log):
                    $ai = $actionIcons[$log['action']] ?? ['icon' => 'info', 'bg' => 'bg-gray-100/50 text-gray-500'];
                ?>
                <div class="flex items-center gap-4 bg-white/40 border border-white/60 rounded-2xl p-4 hover:bg-white/60 transition">
                    <div class="w-9 h-9 rounded-xl <?php echo $ai['bg']; ?> flex items-center justify-center flex-shrink-0"><span class="material-symbols-rounded text-lg"><?php echo $ai['icon']; ?></span></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800 font-medium truncate">
                            <span class="font-semibold"><?php echo htmlspecialchars($log['username']); ?></span>
                            <span class="text-gray-500 mx-1">&middot;</span>
                            <span class="font-bold text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-600"><?php echo $log['action']; ?></span>
                            <span class="text-gray-500 mx-1">&middot;</span>
                            <?php echo htmlspecialchars($log['entity_type']); ?>
                            <?php if ($log['entity_id']): ?> #<?php echo $log['entity_id']; ?><?php endif; ?>
                        </p>
                        <?php if ($log['details']): ?>
                            <p class="text-xs text-gray-400 truncate mt-0.5"><?php echo htmlspecialchars($log['details']); ?></p>
                        <?php endif; ?>
                    </div>
                    <span class="text-[10px] text-gray-400 flex-shrink-0"><?php echo date('M j, H:i', strtotime($log['created_at'])); ?></span>
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

    var roleData  = <?php echo json_encode($roleChartData); ?>;
    var roleColors = <?php echo json_encode(array_slice($roleColors, 0, count($roleChartData['labels']))); ?>;
    var orderData = <?php echo json_encode($orderChartData); ?>;

    var el = document.getElementById('roleChart');
    if (el && roleData.labels.length > 0) {
        new Chart(el, {
            type: 'doughnut',
            data: { labels: roleData.labels, datasets: [{ data: roleData.counts, backgroundColor: roleColors, borderWidth: 3, borderColor: 'rgba(255,255,255,0.8)', hoverOffset: 8 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '55%', plugins: { legend: { position: 'right', labels: { padding: 12, usePointStyle: true, pointStyle: 'circle', font: { size: 10 } } } } }
        });
    }

    el = document.getElementById('ordersChart');
    if (el && orderData.labels.length > 0) {
        new Chart(el, {
            type: 'bar',
            data: {
                labels: orderData.labels,
                datasets: [
                    { label: 'Orders', data: orderData.orders, backgroundColor: '#14b8a6', borderRadius: 6, barPercentage: 0.5, yAxisID: 'y' },
                    { label: 'Revenue (Rs)', data: orderData.revenues, type: 'line', borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.08)', fill: true, tension: 0.4, pointRadius: 4, borderWidth: 2.5, yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', padding: 16 } } },
                scales: {
                    y:  { beginAtZero: true, position: 'left',  grid: { color: 'rgba(0,0,0,0.04)' }, title: { display: true, text: 'Orders', font: { size: 10 } } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: function(v){ return 'Rs.'+(v/1000)+'K'; } }, title: { display: true, text: 'Revenue', font: { size: 10 } } },
                    x:  { grid: { display: false } }
                }
            }
        });
    }
})();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
