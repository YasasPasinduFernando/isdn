<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 1. Authentication & Context Setup ---
try {
    // Attempt to find a Head Office Manager user
    $stmt = $pdo->prepare("SELECT u.id as user_id, u.username, u.email, ho.id as manager_id 
                           FROM users u 
                           LEFT JOIN head_office_managers ho ON u.id = ho.user_id
                           WHERE u.role = 'head_office_manager' LIMIT 1");
    $stmt->execute();
    $hoUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hoUser) {
        $hoUser = ['user_id' => 0, 'username' => 'Demo Admin', 'email' => 'admin@ho.com', 'manager_id' => 1];
    }
    $user_id = $hoUser['user_id'];
    $ho_name = $hoUser['username'];

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$success_msg = '';
$error_msg = '';

// --- 2. Action Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // A. Approve/Reject Stock Transfers
    if (isset($_POST['update_transfer_status'])) {
        $transferId = $_POST['transfer_id'];
        $status = $_POST['status']; // APPROVED / REJECTED
        $remarks = $_POST['remarks'] ?? '';

        if ($transferId && in_array($status, ['APPROVED', 'REJECTED'])) {
            try {
                $pdo->beginTransaction();

                // Update Status
                $stmt = $pdo->prepare("UPDATE stock_transfers SET approval_status = ?, approved_by = ?, approval_date = NOW(), approval_remarks = ? WHERE transfer_id = ?");
                $stmt->execute([$status, $user_id, $remarks, $transferId]);

                // Log change
                // (Assuming transfer_status_logs exists as per migration 007)
                $stmt = $pdo->prepare("INSERT INTO transfer_status_logs (transfer_id, previous_status, new_status, changed_by, remarks) VALUES (?, 'PENDING', ?, ?, ?)");
                $stmt->execute([$transferId, $status, $user_id, $remarks]);

                $pdo->commit();
                $success_msg = "Transfer #$transferId has been $status.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error_msg = "Operation failed: " . $e->getMessage();
            }
        }
    }
}

// --- 3. Data Fetching ---

// A. Island-wide Summary
$summary = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()) as orders_today,
    (SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) = CURDATE()) as revenue_today,
    (SELECT COUNT(*) FROM orders WHERE status = 'delivered' AND DATE(updated_at) = CURDATE()) as deliveries_today,
    (SELECT SUM(available_quantity) FROM product_stocks) as total_stock_units
")->fetch(PDO::FETCH_ASSOC);

// B. RDC Performance Comparison
$rdcPerfQuery = "
    SELECT r.rdc_name, 
           COUNT(o.id) as order_count, 
           COALESCE(SUM(o.total_amount), 0) as total_revenue,
           (SELECT COUNT(*) FROM orders o2 WHERE o2.rdc_id = r.rdc_id AND o2.status = 'delivered') as completed_orders
    FROM rdcs r
    LEFT JOIN orders o ON r.rdc_id = o.rdc_id AND DATE(o.created_at) = CURDATE()
    GROUP BY r.rdc_id
    ORDER BY total_revenue DESC
";
$rdcPerformance = $pdo->query($rdcPerfQuery)->fetchAll(PDO::FETCH_ASSOC);

// C. Stock Transfer Requests (Pending)
$transfersQuery = "
    SELECT st.*, r1.rdc_name as source_rdc, r2.rdc_name as dest_rdc, u.username as requester
    FROM stock_transfers st
    JOIN rdcs r1 ON st.source_rdc_id = r1.rdc_id
    JOIN rdcs r2 ON st.destination_rdc_id = r2.rdc_id
    JOIN users u ON st.requested_by = u.id
    WHERE st.approval_status = 'PENDING'
    ORDER BY st.requested_date DESC
";
$pendingTransfers = $pdo->query($transfersQuery)->fetchAll(PDO::FETCH_ASSOC);

// D. Recent Activities (Mock/Real)
// Using recent orders across all RDCs
$recentActivities = $pdo->query("
    SELECT o.order_number, o.total_amount, r.rdc_name, o.created_at, o.status
    FROM orders o
    JOIN rdcs r ON o.rdc_id = r.rdc_id
    ORDER BY o.created_at DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Head Office Dashboard - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/custom.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- For Analysis Charts -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <style>
        .font-outfit { font-family: 'Outfit', sans-serif; }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(229, 231, 235, 0.5); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .sidebar-link.active {
            background: linear-gradient(90deg, rgba(20, 184, 166, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
            border-left: 4px solid #0d9488;
            color: #0f766e;
        }
        .sidebar-link:hover:not(.active) {
            background-color: #f0fdfa;
            color: #0d9488;
        }
        #islandMap { height: 100%; width: 100%; border-radius: 1rem; z-index: 0; }
    </style>
</head>
<body class="bg-slate-50 font-outfit h-screen flex overflow-hidden text-gray-800">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-100 flex flex-col z-20 shadow-sm hidden md:flex">
        <div class="h-20 flex items-center px-6 border-b border-gray-100">
            <div class="flex items-center space-x-3">
                <div class="bg-gradient-to-br from-teal-500 to-emerald-600 p-2 rounded-xl shadow-md text-white flex items-center justify-center">
                    <span class="material-symbols-rounded text-2xl">apartment</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-gray-800">ISDN</h1>
                    <p class="text-[10px] uppercase tracking-wider font-semibold text-teal-600">Head Office</p>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto py-6 space-y-1">
             <button onclick="switchTab('overview')" class="w-full text-left sidebar-link active flex items-center px-6 py-3.5 text-sm font-medium text-gray-600 transition-colors group">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">grid_view</span> Island-Wide Overview
            </button>
            <button onclick="switchTab('performance')" class="w-full text-left sidebar-link flex items-center px-6 py-3.5 text-sm font-medium text-gray-600 transition-colors group">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">insights</span> RDC Performance
            </button>
            <button onclick="switchTab('map')" class="w-full text-left sidebar-link flex items-center px-6 py-3.5 text-sm font-medium text-gray-600 transition-colors group">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">map</span> Live Operations Map
            </button>
             <button onclick="switchTab('approvals')" class="w-full text-left sidebar-link flex items-center px-6 py-3.5 text-sm font-medium text-gray-600 transition-colors group">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">approval_delegation</span> Stock Approvals
                <?php if(count($pendingTransfers) > 0): ?>
                <span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full"><?= count($pendingTransfers) ?></span>
                <?php endif; ?>
            </button>
             <button onclick="switchTab('reports')" class="w-full text-left sidebar-link flex items-center px-6 py-3.5 text-sm font-medium text-gray-600 transition-colors group">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">description</span> Consolidated Reports
            </button>
        </div>

        <div class="p-4 border-t border-gray-100">
            <div class="flex items-center space-x-3 mb-4 px-2">
                <div class="w-8 h-8 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 font-bold text-xs">
                    HO
                </div>
                <div>
                    <h3 class="font-bold text-sm text-gray-800"><?= htmlspecialchars($ho_name) ?></h3>
                    <p class="text-[10px] text-gray-500">Senior Manager</p>
                </div>
            </div>
            <a href="index.php?page=home" class="flex items-center justify-center space-x-2 text-red-500 hover:bg-red-50 p-3 rounded-xl transition w-full font-bold text-sm">
                <span class="material-symbols-rounded">logout</span>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-slate-50 w-full">
        <header class="h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-6 z-10 sticky top-0 md:static">
            <h2 class="text-lg font-bold text-gray-800" id="page-title">Island-Wide Overview</h2>
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-gray-800"><?= date('l, F j, Y') ?></p>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth">
            <?php if($success_msg): ?>
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center">
                    <span class="material-symbols-rounded mr-2">check_circle</span> <?= $success_msg ?>
                </div>
            <?php endif; ?>

            <!-- OVERVIEW TAB -->
            <div id="tab-overview" class="tab-content space-y-8">
                <!-- Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-teal-600">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase">Today's Revenue</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-1">Rs. <?= number_format($summary['revenue_today']/1000, 1) ?>K</h3>
                            </div>
                            <div class="p-3 bg-teal-50 text-teal-600 rounded-xl">
                                <span class="material-symbols-rounded">payments</span>
                            </div>
                        </div>
                        <p class="text-xs text-green-600 font-bold flex items-center"><span class="material-symbols-rounded text-sm mr-1">trending_up</span> +12% from yesterday</p>
                    </div>
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-amber-500">
                         <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase">Total Orders</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-1"><?= number_format($summary['orders_today']) ?></h3>
                            </div>
                            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                                <span class="material-symbols-rounded">shopping_cart</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400">Since 12:00 AM</p>
                    </div>
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-emerald-600">
                         <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase">Successful Deliveries</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-1"><?= number_format($summary['deliveries_today']) ?></h3>
                            </div>
                            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                                <span class="material-symbols-rounded">local_shipping</span>
                            </div>
                        </div>
                        <p class="text-xs text-emerald-600 font-bold">98% Success Rate</p>
                    </div>
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-orange-600">
                         <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase">Stock Health</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-1"><?= number_format($summary['total_stock_units']) ?></h3>
                            </div>
                            <div class="p-3 bg-orange-50 text-orange-600 rounded-xl">
                                <span class="material-symbols-rounded">inventory_2</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400">Global Units Available</p>
                    </div>
                </div>

                <!-- Strategic Planning & Comparison -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- RDC Leaderboard -->
                    <div class="lg:col-span-2 glass-card rounded-3xl p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-gray-800 text-lg">Daily RDC Performance</h3>
                            <button class="text-xs font-bold text-teal-600 hover:text-teal-800">View Full Report</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="text-xs text-gray-400 uppercase font-bold border-b border-gray-100">
                                    <tr>
                                        <th class="py-3 px-2">RDC Name</th>
                                        <th class="py-3 px-2 text-right">Orders</th>
                                        <th class="py-3 px-2 text-right">Revenue</th>
                                        <th class="py-3 px-2 text-center">Status</th>
                                        <th class="py-3 px-2 text-right">Performance</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    <?php foreach($rdcPerformance as $rdc): 
                                        $perf = ($rdc['order_count'] > 0) ? round(($rdc['completed_orders'] / $rdc['order_count']) * 100) : 0;
                                        $color = ($perf >= 80) ? 'bg-green-500' : (($perf >= 50) ? 'bg-yellow-500' : 'bg-red-500');
                                    ?>
                                    <tr class="group hover:bg-gray-50 transition border-b border-gray-50 last:border-0">
                                        <td class="py-4 px-2 font-bold text-gray-800"><?= htmlspecialchars($rdc['rdc_name']) ?></td>
                                        <td class="py-4 px-2 text-right font-medium"><?= number_format($rdc['order_count']) ?></td>
                                        <td class="py-4 px-2 text-right font-bold text-gray-800">Rs. <?= number_format($rdc['total_revenue']) ?></td>
                                        <td class="py-4 px-2 text-center">
                                            <span class="inline-block w-2.5 h-2.5 rounded-full <?= $color ?>"></span>
                                        </td>
                                        <td class="py-4 px-2">
                                            <div class="flex items-center justify-end">
                                                <div class="w-24 bg-gray-100 rounded-full h-1.5 mr-2">
                                                    <div class="<?= $color ?> h-1.5 rounded-full" style="width: <?= $perf ?>%"></div>
                                                </div>
                                                <span class="text-xs font-bold text-gray-500"><?= $perf ?>%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Strategic Insights Area -->
                    <div class="glass-card rounded-3xl p-6 bg-gradient-to-br from-teal-900 to-emerald-900 text-white">
                        <h3 class="font-bold text-lg mb-4 text-white">Strategic Insights</h3>
                        <div class="space-y-6">
                            <div>
                                <p class="text-xs text-teal-200 uppercase font-bold mb-1">Running Low</p>
                                <div class="flex items-center justify-between bg-white/10 p-3 rounded-xl backdrop-blur-sm">
                                    <span class="font-medium">Munchee Biscuits</span>
                                    <span class="bg-red-500/80 text-white text-xs font-bold px-2 py-1 rounded">Critically Low</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-teal-200 uppercase font-bold mb-1">Top Selling Region</p>
                                <div class="flex items-center justify-between bg-white/10 p-3 rounded-xl backdrop-blur-sm">
                                    <span class="font-medium">Western Province</span>
                                    <span class="text-emerald-400 font-bold text-xs"><span class="material-symbols-rounded align-middle text-sm">trending_up</span> +15%</span>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-white/10">
                                <button class="w-full bg-white text-teal-900 py-3 rounded-xl font-bold shadow-lg hover:bg-gray-100 transition">View Full Analytics</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MAP TAB -->
            <div id="tab-map" class="tab-content hidden h-full pb-10">
                <div class="glass-card h-[600px] w-full rounded-3xl overflow-hidden border border-gray-200 relative p-1 bg-white">
                    <div id="islandMap" class="w-full h-full rounded-2xl z-0"></div>
                     <div class="absolute bottom-4 left-4 bg-white/90 backdrop-blur p-4 rounded-xl shadow-lg z-[400]">
                        <h4 class="font-bold text-gray-800 text-sm mb-2">Live Operations</h4>
                        <div class="space-y-2">
                            <div class="flex items-center text-xs"><span class="w-3 h-3 rounded-full bg-teal-500 mr-2"></span> Active RDC</div>
                            <div class="flex items-center text-xs"><span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span> Active Delivery</div>
                            <div class="flex items-center text-xs"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span> Route Alert</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- APPROVALS TAB -->
            <div id="tab-approvals" class="tab-content hidden space-y-6">
                <div class="glass-card rounded-3xl overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-gray-800 text-lg">Stock Transfer Requests</h3>
                            <p class="text-sm text-gray-500">Require approval for inter-RDC movements</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="bg-teal-100 text-teal-700 text-xs font-bold px-3 py-1 rounded-full"><?= count($pendingTransfers) ?> Pending</span>
                        </div>
                    </div>
                    
                    <?php if(empty($pendingTransfers)): ?>
                        <div class="p-12 text-center text-gray-400">
                            <span class="material-symbols-rounded text-6xl mb-4 text-gray-200">check_circle</span>
                            <p>All requests processed. No pending approvals.</p>
                        </div>
                    <?php else: ?>
                        <div class="divide-y divide-gray-100">
                        <?php foreach($pendingTransfers as $st): ?>
                            <div class="p-6 hover:bg-gray-50 transition">
                                <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-1">
                                            <span class="text-xs font-bold bg-gray-200 text-gray-600 px-2 py-0.5 rounded mr-3"><?= $st['transfer_number'] ?></span>
                                            <span class="text-xs text-gray-400"><?= date('M d, Y • h:i A', strtotime($st['requested_date'])) ?></span>
                                        </div>
                                        <h4 class="font-bold text-gray-800 text-lg flex items-center">
                                            <?= htmlspecialchars($st['source_rdc']) ?> 
                                            <span class="material-symbols-rounded mx-2 text-gray-400 text-sm">arrow_forward</span> 
                                            <?= htmlspecialchars($st['dest_rdc']) ?>
                                        </h4>
                                        <p class="text-sm text-gray-600 mt-1">Requested by <span class="font-medium"><?= htmlspecialchars($st['requester']) ?></span>: "<?= htmlspecialchars($st['request_reason']) ?>"</p>
                                    </div>
                                    <div class="flex space-x-3">
                                        <form method="POST" class="flex gap-3">
                                            <input type="hidden" name="transfer_id" value="<?= $st['transfer_id'] ?>">
                                            <button type="submit" name="update_transfer_status" value="REJECTED" class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-bold hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">Reject</button>
                                            <button type="submit" name="update_transfer_status" value="APPROVED" class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm font-bold hover:bg-teal-700 shadow-lg shadow-teal-200 transition">Approve</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- REPORTS TAB (Placeholder) -->
             <div id="tab-reports" class="tab-content hidden h-full text-center py-20">
                <div class="max-w-md mx-auto">
                    <div class="w-24 h-24 bg-teal-50 rounded-full flex items-center justify-center mx-auto mb-6 text-teal-500">
                        <span class="material-symbols-rounded text-5xl">summarize</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Consolidated Reports</h3>
                    <p class="text-gray-500 mb-8">Generate detailed PDF/Excel reports for board meetings and perform yearly trend analysis.</p>
                    <button class="bg-teal-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:bg-teal-700 transition">View Monthly Report</button>
                </div>
            </div>

        </div>
    </main>

    <script>
        function switchTab(id) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.getElementById('tab-'+id).classList.remove('hidden');
            document.getElementById('page-title').innerText = id === 'overview' ? 'Island-Wide Overview' : id.charAt(0).toUpperCase() + id.slice(1);
            
            document.querySelectorAll('.sidebar-link').forEach(el => el.classList.remove('active'));
            // Optional: visual active state logic if using IDs

            // Map Init
            if(id === 'map' && !window.islandMapObj) {
                setTimeout(initMap, 100);
            }
        }

        function initMap() {
            window.islandMapObj = L.map('islandMap').setView([7.8731, 80.7718], 7); // Center of Sri Lanka
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(window.islandMapObj);
            
            // Mock RDC Markers
            var rdcs = [
                {name: "North RDC (Jaffna)", pos: [9.6615, 80.0255]},
                {name: "Central RDC (Kandy)", pos: [7.2906, 80.6337]},
                {name: "South RDC (Galle)", pos: [6.0535, 80.2210]},
                {name: "West RDC (Colombo)", pos: [6.9271, 79.8612]}
            ];

            rdcs.forEach(function(rdc) {
                L.marker(rdc.pos).addTo(window.islandMapObj).bindPopup("<b>"+rdc.name+"</b><br>Active Status: Normal");
            });
        }
    </script>
</body>
</html>
