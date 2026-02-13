<?php
// Start output buffering
ob_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

// Include the main header which handles session start and navigation
require_once __DIR__ . '/../../includes/header.php';
?>
<!-- Leaflet Configuration -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<?php

// --- 1. Authentication & Context Setup ---
try {
    // Attempt to find a Sales Rep user if logged in, or use demo fallback logic if needed (though header handles auth check mostly)
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT u.id, u.username, u.rdc_id, r.rdc_name, s.id as sales_ref_id
                               FROM users u 
                               LEFT JOIN rdc_sales_refs s ON u.id = s.user_id
                               JOIN rdcs r ON u.rdc_id = r.rdc_id 
                               WHERE u.id = ?");
        $stmt->execute([$userId]);
        $rep = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$rep) {
        // Fallback for demo / development if direct access without login (though usually blocked)
        $rep = ['id' => 0, 'username' => 'Demo Rep', 'rdc_id' => 1, 'rdc_name' => 'Northern RDC', 'sales_ref_id' => 0];
    }

    $user_id = $rep['id'];
    $rdc_id = $rep['rdc_id'];
    $rep_name = $rep['username'];
    $rdc_name = $rep['rdc_name'];

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$success_msg = '';
$error_msg = '';

// --- 2. Action Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // A. Add New Customer
    if (isset($_POST['add_customer'])) {
        $cName = $_POST['name'];
        $cEmail = $_POST['email'];
        $cPhone = $_POST['contact_number'];
        $cAddr = $_POST['address'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO retail_customers (name, email, contact_number, address, user_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$cName, $cEmail, $cPhone, $cAddr, $user_id]);
            $_SESSION['success_msg'] = "New customer added successfully.";
            header("Location: index.php?page=rdc-sales-ref-dashboard&tab=customers");
            exit;
        } catch (Exception $e) {
            $error_msg = "Failed to add customer: " . $e->getMessage();
        }
    }

    // B. Place Order
    if (isset($_POST['place_order'])) {
        $custId = $_POST['customer_id'];
        $prodId = $_POST['product_id'];
        $qty = $_POST['quantity'];
        
        if($custId && $prodId && $qty > 0) {
            $pdo->beginTransaction();
            try {
                // Get Price
                $stmt = $pdo->prepare("SELECT unit_price FROM products WHERE product_id = ?");
                $stmt->execute([$prodId]);
                $price = $stmt->fetchColumn();
                $total = $price * $qty;

                // Create Order
                $orderRef = 'ORD-' . date('Y') . '-' . mt_rand(1000,9999);
                $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_id, rdc_id, total_amount, status, placed_by, created_at) VALUES (?, ?, ?, ?, 'pending', ?, NOW())");
                $stmt->execute([$orderRef, $custId, $rdc_id, $total, $user_id]);
                $orderId = $pdo->lastInsertId();

                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, selling_price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$orderId, $prodId, $qty, $price]);

                $pdo->commit();
                $_SESSION['success_msg'] = "Order placed successfully! Ref: " . $orderRef;
                header("Location: index.php?page=rdc-sales-ref-dashboard&tab=orders");
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error_msg = "Order failed: " . $e->getMessage();
            }
        }
    }
}

// Check for session messages
$success_msg = isset($_SESSION['success_msg']) ? $_SESSION['success_msg'] : $success_msg;
if (isset($_SESSION['success_msg'])) unset($_SESSION['success_msg']);

// --- 3. Data Fetching ---

// Fetch Rep's Customers
$customers = $pdo->query("SELECT id, username, email FROM users WHERE role = 'customer'")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Products
$products = $pdo->query("SELECT product_id, product_name FROM products")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Recent Orders (Placed by this rep/user or related to RDC)
$recentOrders = $pdo->prepare("SELECT o.*, rc.name as username 
                               FROM orders o 
                               JOIN retail_customers rc ON o.customer_id = rc.id 
                               LEFT JOIN users u ON o.placed_by = u.id 
                               WHERE o.placed_by = ? OR u.rdc_id = ?
                               ORDER BY o.created_at DESC");
$recentOrders->execute([$user_id, $rdc_id]);
$orders = $recentOrders->fetchAll(PDO::FETCH_ASSOC);

// Dashboard Stats
$myCustomersCount = count($customers); 
$visitsToday = 4; // Mock
$salesThisMonth = 0;
foreach($orders as $o) {
    // engaging logic: calculate from actual orders if available for this month
    if(date('Y-m', strtotime($o['created_at'])) === date('Y-m')) {
        $salesThisMonth += $o['total_amount'];
    }
}
// If 0, keep mock base for demo visuals if preferred, or show 0
if ($salesThisMonth == 0) $salesThisMonth = 150000; 

$target = 500000; // Mock
$achievement = ($salesThisMonth / $target) * 100;

function getStatusBadge($status) {
    switch(strtolower($status)) {
        case 'pending': return '<span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-lg text-xs font-bold border border-yellow-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-yellow-500 mr-1"></span>Pending</span>';
        case 'processing': return '<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-lg text-xs font-bold border border-blue-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-blue-500 mr-1"></span>Processing</span>'; // Fixed 'on_the_way' mapping if necessary
        case 'out_for_delivery': return '<span class="bg-orange-100 text-orange-700 px-2 py-1 rounded-lg text-xs font-bold border border-orange-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-orange-500 mr-1"></span>On Way</span>';
        case 'delivered': return '<span class="bg-green-100 text-green-700 px-2 py-1 rounded-lg text-xs font-bold border border-green-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-green-500 mr-1"></span>Delivered</span>';
        case 'failed': return '<span class="bg-red-100 text-red-700 px-2 py-1 rounded-lg text-xs font-bold border border-red-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-red-500 mr-1"></span>Failed</span>';
        default: return '<span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-lg text-xs font-bold border border-gray-200">' . ucfirst(str_replace('_',' ',$status)) . '</span>';
    }
}
?>

<style>
    .font-outfit { font-family: 'Outfit', sans-serif; }
    #map, #liveMap, #routeMap { height: 100%; width: 100%; border-radius: 1rem; z-index: 0; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .hover-lift { transition: transform 0.2s; }
    .hover-lift:hover { transform: translateY(-2px); }
</style>

<div class="flex flex-1 overflow-hidden h-full flex-col">

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative w-full">

        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth pb-20 md:pb-8">
            
            <!-- Page Title Area -->
            <div class="glass-panel rounded-3xl p-6 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 font-['Outfit']" id="page-title">Dashboard Overview</h1>
                    <p class="text-gray-600">Welcome back, <?= htmlspecialchars($rep_name) ?></p>
                </div>
                <!-- Action Buttons could go here -->
                <div class="flex items-center space-x-3 bg-white/30 px-4 py-2 rounded-xl backdrop-blur-sm border border-white/40">
                    <span class="material-symbols-rounded text-teal-700">badge</span>
                    <span class="font-bold text-teal-800 text-sm"><?= htmlspecialchars($rdc_name) ?></span>
                </div>
            </div>

            <?php if($success_msg): ?>
                <div class="glass-card bg-green-50/80 border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm relative z-20">
                    <span class="material-symbols-rounded mr-2 text-green-600">check_circle</span> <?= htmlspecialchars($success_msg) ?>
                </div>
            <?php endif; ?>
            <?php if($error_msg): ?>
                <div class="glass-card bg-red-50/80 border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm relative z-20">
                    <span class="material-symbols-rounded mr-2 text-red-600">error</span> <?= htmlspecialchars($error_msg) ?>
                </div>
            <?php endif; ?>

            <!-- OVERVIEW TAB -->
            <div id="tab-dashboard" class="tab-content active space-y-8">
                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-teal-500 hover-lift group">
                        <div class="flex items-center space-x-4 mb-2">
                            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center group-hover:scale-110 transition">
                                <span class="material-symbols-rounded">group</span>
                            </div>
                            <p class="text-xs font-bold text-gray-500 uppercase">My Customers</p>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $myCustomersCount ?></h3>
                    </div>
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-orange-500 hover-lift group">
                        <div class="flex items-center space-x-4 mb-2">
                            <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center group-hover:scale-110 transition">
                                <span class="material-symbols-rounded">calendar_today</span>
                            </div>
                            <p class="text-xs font-bold text-gray-500 uppercase">Visits Today</p>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $visitsToday ?></h3>
                    </div>
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-green-500 hover-lift group">
                        <div class="flex items-center space-x-4 mb-2">
                            <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center group-hover:scale-110 transition">
                                <span class="material-symbols-rounded">payments</span>
                            </div>
                            <p class="text-xs font-bold text-gray-500 uppercase">Sales (Month)</p>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-800">Rs. <?= number_format($salesThisMonth/1000) ?>K</h3>
                    </div>
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-blue-500 hover-lift group">
                         <div class="flex items-center space-x-4 mb-2">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition">
                                <span class="material-symbols-rounded">track_changes</span>
                            </div>
                             <p class="text-xs font-bold text-gray-500 uppercase">Achievement</p>
                        </div>
                        <div class="mt-2">
                             <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold text-gray-400">Target</span>
                                <span class="text-xs font-bold text-blue-600"><?= number_format($achievement,0) ?>%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full shadow-lg shadow-blue-500/30" style="width: <?= $achievement ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visits & Map Preview -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 glass-card rounded-3xl overflow-hidden flex flex-col h-[400px]">
                        <div class="p-6 border-b border-gray-100/50 flex justify-between items-center bg-white/30 backdrop-blur-md">
                            <h3 class="font-bold text-gray-800 flex items-center">
                                <span class="material-symbols-rounded text-teal-500 mr-2">route</span> Today's Route
                            </h3>
                            <button onclick="window.location.href='index.php?page=rdc-sales-ref-dashboard&tab=visits'" class="text-xs text-teal-600 font-bold hover:underline bg-teal-50/80 px-3 py-1 rounded-full">View Full Map</button>
                        </div>
                        <div id="miniMap" class="flex-1 bg-gray-100 z-0"></div>
                    </div>
                    <div class="glass-card rounded-3xl p-6 h-[400px] overflow-y-auto sidebar-scroll">
                         <h3 class="font-bold text-gray-800 mb-6 flex items-center">
                            <span class="material-symbols-rounded text-orange-500 mr-2">schedule</span> Upcoming Visits
                         </h3>
                         <div class="space-y-4">
                             <!-- Timeline Item -->
                             <div class="flex gap-4 relative">
                                 <div class="flex flex-col items-center">
                                     <div class="w-3 h-3 rounded-full bg-green-500 ring-4 ring-green-100"></div>
                                     <div class="w-0.5 h-full bg-gray-200 my-1"></div>
                                 </div>
                                 <div class="pb-6">
                                     <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">10:00 AM</span>
                                     <h4 class="text-sm font-bold text-gray-800">Siva Stores</h4>
                                     <p class="text-xs text-gray-500 mb-2">Jaffna Town • Routine Check</p>
                                     <button class="bg-teal-50 text-teal-600 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-teal-100 transition">Check-in</button>
                                 </div>
                             </div>
                             <!-- Timeline Item -->
                             <div class="flex gap-4 relative">
                                 <div class="flex flex-col items-center">
                                     <div class="w-3 h-3 rounded-full bg-gray-300"></div>
                                     <div class="w-0.5 h-full bg-gray-200 my-1"></div>
                                 </div>
                                 <div class="pb-6">
                                     <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">02:00 PM</span>
                                     <h4 class="text-sm font-bold text-gray-800">New City Mart</h4>
                                     <p class="text-xs text-gray-500">Kokuvil • Order Collection</p>
                                 </div>
                             </div>
                         </div>
                    </div>
                </div>
            </div>

            <!-- CUSTOMERS TAB -->
            <div id="tab-customers" class="tab-content hidden space-y-6">
                 <div class="glass-panel rounded-3xl p-6 mb-6 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center">
                         <span class="material-symbols-rounded text-teal-500 mr-2">groups</span> My Customers
                    </h3>
                    <button onclick="toggleModal('modal-customer')" class="bg-teal-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg hover:bg-teal-700 hover:shadow-teal-200 transition flex items-center">
                        <span class="material-symbols-rounded text-lg mr-2">add</span> Add Customer
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach($customers as $c): ?>
                    <div class="glass-card p-6 rounded-3xl hover-lift">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center font-bold text-gray-600 text-lg shadow-inner">
                                <?= strtoupper(substr($c['username'],0,2)) ?>
                            </div>
                            <span class="bg-green-100 text-green-700 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wide">Active</span>
                        </div>
                        <h4 class="font-bold text-gray-800 text-lg mb-1 leading-tight"><?= htmlspecialchars($c['username']) ?></h4>
                        <p class="text-sm text-gray-500 mb-5 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">mail</span> <?= htmlspecialchars($c['email']) ?>
                        </p>
                        
                        <div class="border-t border-gray-100/50 pt-4 flex justify-between items-center">
                            <div>
                                <p class="text-[10px] uppercase text-gray-400 font-bold">LIFETIME VALUE</p>
                                <p class="text-lg font-bold text-gray-800">Rs. 45k</p>
                            </div>
                            <button class="text-teal-600 text-xs font-bold hover:text-teal-800 transition bg-teal-50/80 px-3 py-2 rounded-lg">View History</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- VISITS TAB -->
            <div id="tab-visits" class="tab-content hidden h-full">
                <div class="glass-panel p-6 rounded-3xl mb-6 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center">
                        <span class="material-symbols-rounded text-teal-500 mr-2">share_location</span> Visits & Route
                    </h3>
                </div>
                <div class="glass-card h-[500px] w-full rounded-3xl overflow-hidden border border-gray-200/50 relative p-1">
                    <div id="routeMap" class="w-full h-full rounded-2xl z-0"></div>
                </div>

                <!-- Scheduled Visits List -->
                <div class="glass-card rounded-3xl p-6 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-gray-800 flex items-center">
                            <span class="material-symbols-rounded text-orange-500 mr-2">list_alt</span> Scheduled Visits Today
                        </h3>
                        <span class="text-sm text-gray-500">3 Visits Remaining</span>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-xs font-bold text-gray-500 border-b border-gray-100">
                                    <th class="py-3 px-2">Time</th>
                                    <th class="py-3 px-2">Customer</th>
                                    <th class="py-3 px-2">Location</th>
                                    <th class="py-3 px-2">Purpose</th>
                                    <th class="py-3 px-2">Status</th>
                                    <th class="py-3 px-2">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr class="hover:bg-gray-50/50 transition border-b border-gray-100/50">
                                    <td class="py-4 px-2 font-bold text-gray-700">10:00 AM</td>
                                    <td class="py-4 px-2 font-medium text-gray-800">Siva Stores</td>
                                    <td class="py-4 px-2 text-gray-500">Jaffna Town</td>
                                    <td class="py-4 px-2"><span class="bg-blue-50 text-blue-600 px-2 py-1 rounded-md text-xs font-bold">Routine Check</span></td>
                                    <td class="py-4 px-2"><span class="text-orange-500 font-bold text-xs flex items-center"><span class="w-1.5 h-1.5 rounded-full bg-orange-500 mr-1"></span> Pending</span></td>
                                    <td class="py-4 px-2">
                                        <button class="bg-teal-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm hover:bg-teal-700">Check-In</button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50/50 transition border-b border-gray-100/50">
                                    <td class="py-4 px-2 font-bold text-gray-700">02:00 PM</td>
                                    <td class="py-4 px-2 font-medium text-gray-800">New City Mart</td>
                                    <td class="py-4 px-2 text-gray-500">Kokuvil</td>
                                    <td class="py-4 px-2"><span class="bg-purple-50 text-purple-600 px-2 py-1 rounded-md text-xs font-bold">Order Collection</span></td>
                                    <td class="py-4 px-2"><span class="text-orange-500 font-bold text-xs flex items-center"><span class="w-1.5 h-1.5 rounded-full bg-orange-500 mr-1"></span> Pending</span></td>
                                    <td class="py-4 px-2">
                                        <button class="bg-gray-100 text-gray-400 px-3 py-1.5 rounded-lg text-xs font-bold cursor-not-allowed">Wait</button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="py-4 px-2 font-bold text-gray-400">04:30 PM</td>
                                    <td class="py-4 px-2 font-medium text-gray-400">Raja Traders</td>
                                    <td class="py-4 px-2 text-gray-400">Kopay</td>
                                    <td class="py-4 px-2"><span class="bg-gray-50 text-gray-500 px-2 py-1 rounded-md text-xs font-bold">Delivery</span></td>
                                    <td class="py-4 px-2"><span class="text-green-500 font-bold text-xs flex items-center"><span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1"></span> Completed</span></td>
                                    <td class="py-4 px-2">
                                        <button class="text-teal-600 text-xs font-bold hover:underline">View Report</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ORDERS TAB -->
            <div id="tab-orders" class="tab-content hidden space-y-6">
                <div class="glass-panel p-6 rounded-3xl mb-6 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center">
                        <span class="material-symbols-rounded text-teal-500 mr-2">shopping_cart</span> Manage Orders
                    </h3>
                    <button onclick="toggleModal('modal-order')" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg hover:bg-emerald-700 hover:shadow-emerald-200 transition flex items-center">
                         <span class="material-symbols-rounded text-lg mr-2">add_shopping_cart</span> Place Order
                    </button>
                </div>

                <div class="glass-card rounded-3xl overflow-hidden shadow-sm">
                    <table class="w-full text-left">
                        <thead class="bg-teal-50/40 text-gray-500 text-xs uppercase font-bold border-b border-gray-100/50">
                            <tr>
                                <th class="p-5">Order ID</th>
                                <th class="p-5">Customer</th>
                                <th class="p-5">Date</th>
                                <th class="p-5">Amount</th>
                                <th class="p-5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/50 text-sm">
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="5" class="p-5 text-center text-gray-500">No orders found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($orders as $o): ?>
                                <tr class="hover:bg-white/40 transition">
                                    <td class="p-5 font-bold text-gray-700"><?= $o['order_number'] ?></td>
                                    <td class="p-5 font-medium text-gray-800"><?= htmlspecialchars($o['username']) ?></td>
                                    <td class="p-5 text-gray-500"><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                    <td class="p-5 font-bold text-gray-800">Rs. <?= number_format($o['total_amount']) ?></td>
                                    <td class="p-5">
                                         <?= getStatusBadge($o['status']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- PERFORMANCE TAB -->
            <div id="tab-performance" class="tab-content hidden h-full text-center py-20">
                <div class="glass-card rounded-3xl p-10 max-w-2xl mx-auto">
                    <div class="w-24 h-24 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-6 text-teal-600">
                        <span class="material-symbols-rounded text-5xl">leaderboard</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Performance Analytics</h3>
                    <p class="text-gray-500 mb-6">Sales targets, efficient routes, and customer growth insights will serve here.</p>
                    
                    <div class="grid grid-cols-2 gap-4 text-left">
                        <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100/50">
                            <div class="text-xs text-gray-500 uppercase font-bold mb-1">Monthly Target</div>
                            <div class="text-xl font-bold text-gray-800">85%</div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                                <div class="bg-teal-500 h-1.5 rounded-full shadow-lg shadow-teal-500/30" style="width: 85%"></div>
                            </div>
                        </div>
                         <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100/50">
                            <div class="text-xs text-gray-500 uppercase font-bold mb-1">Customer Retention</div>
                            <div class="text-xl font-bold text-gray-800">92%</div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                                <div class="bg-blue-500 h-1.5 rounded-full shadow-lg shadow-blue-500/30" style="width: 92%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Modal: Add Customer -->
    <div id="modal-customer" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-md z-50 flex items-center justify-center p-4 transition-opacity animate-fade-in">
        <form method="POST" action="index.php?page=rdc-sales-ref-dashboard" class="glass-card bg-white/95 rounded-3xl w-full max-w-md p-8 shadow-2xl relative border-white/50">
            <button type="button" onclick="toggleModal('modal-customer')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <span class="material-symbols-rounded">close</span>
            </button>
            <div class="flex items-center space-x-3 mb-6">
                <div class="bg-teal-100 text-teal-600 p-2 rounded-xl">
                    <span class="material-symbols-rounded">person_add</span>
                </div>
                <h3 class="font-bold text-xl text-gray-800">Add New Customer</h3>
            </div>
            
            <div class="space-y-4">
                <div>
                     <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Full Name</label>
                     <input type="text" name="name" class="w-full border border-gray-200 bg-gray-50/50 p-3 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Email Address</label>
                    <input type="email" name="email" class="w-full border border-gray-200 bg-gray-50/50 p-3 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Phone Number</label>
                    <input type="text" name="contact_number" class="w-full border border-gray-200 bg-gray-50/50 p-3 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Address</label>
                    <textarea name="address" class="w-full border border-gray-200 bg-gray-50/50 p-3 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition" rows="2"></textarea>
                </div>
                 <input type="hidden" name="add_customer" value="1">
            </div>
            <div class="mt-8 flex space-x-3">
                <button type="button" onclick="toggleModal('modal-customer')" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold transition">Cancel</button>
                <button type="submit" class="flex-1 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold shadow-lg shadow-teal-200 transition">Save Customer</button>
            </div>
        </form>
    </div>

    <!-- Modal: Place Order -->
    <div id="modal-order" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-md z-50 flex items-center justify-center p-4 transition-opacity animate-fade-in">
        <form method="POST" action="index.php?page=rdc-sales-ref-dashboard" class="glass-card bg-white/95 rounded-3xl w-full max-w-md p-8 shadow-2xl relative border-white/50">
            <button type="button" onclick="toggleModal('modal-order')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <span class="material-symbols-rounded">close</span>
            </button>
            <div class="flex items-center space-x-3 mb-6">
                <div class="bg-emerald-100 text-emerald-600 p-2 rounded-xl">
                    <span class="material-symbols-rounded">shopping_cart</span>
                </div>
                <h3 class="font-bold text-xl text-gray-800">Place New Order</h3>
            </div>
            
            <div class="space-y-4">
                <div>
                     <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Select Customer</label>
                     <select name="customer_id" class="w-full border border-gray-200 bg-gray-50/50 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition" required>
                         <option value="">Choose...</option>
                         <?php foreach($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['username']) ?></option>
                         <?php endforeach; ?>
                     </select>
                </div>
                <div>
                     <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Select Product</label>
                     <select name="product_id" class="w-full border border-gray-200 bg-gray-50/50 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition" required>
                         <option value="">Choose...</option>
                         <?php foreach($products as $p): ?>
                            <option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name']) ?></option>
                         <?php endforeach; ?>
                     </select>
                </div>
                <div>
                     <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Quantity</label>
                     <input type="number" name="quantity" min="1" class="w-full border border-gray-200 bg-gray-50/50 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition" required>
                </div>
                <input type="hidden" name="place_order" value="1">
            </div>
            <div class="mt-8 flex space-x-3">
                <button type="button" onclick="toggleModal('modal-order')" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold transition">Cancel</button>
                <button type="submit" class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-lg shadow-emerald-200 transition">Place Order</button>
            </div>
        </form>
    </div>

    <script>
        // Global map instances
        var mainRouteMap = null;
        var miniMapObj = null;

        // Shared Route Data
        const routeLocations = [
            {lat: 9.6615, lng: 80.0255, title: "Start Point (Jaffna)"},
            {lat: 9.6680, lng: 80.0150, title: "Siva Stores"},
            {lat: 9.6750, lng: 80.0300, title: "New City Mart"},
            {lat: 9.6650, lng: 80.0400, title: "Raja Traders"}
        ];

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab') || 'dashboard';
            
            // Initial Tab Selection
            const target = document.getElementById('tab-' + tab);
            if(target) {
                // Hide all first
                document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
                
                // Show target
                target.classList.remove('hidden');
                target.classList.add('active');
                updateTitle(tab);
            }

            // Init Maps based on visibility
            initMaps();
        });


        function switchTab(id) {
            // Hide all
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            
            // Show selected
            const target = document.getElementById('tab-'+id);
            if(target) {
                target.classList.remove('hidden');
                target.classList.add('active');
                updateTitle(id);
                
                // Handle Map Refresh if switching to visits tab
                if(id === 'visits') {
                    setTimeout(function() {
                        if(!mainRouteMap) {
                            initRouteMap();
                        } else {
                            mainRouteMap.invalidateSize();
                        }
                    }, 200);
                }
                 // Handle Mini Map Refresh if switching to dashboard
                 if(id === 'dashboard') {
                    setTimeout(function() {
                        if(!miniMapObj) {
                            initMiniMap();
                        } else {
                            miniMapObj.invalidateSize();
                        }
                    }, 200);
                }
            }
        }

        function updateTitle(id) {
            const titles = {
                'dashboard': 'Dashboard Overview',
                'customers': 'My Customers',
                'visits': 'Visits & Route',
                'orders': 'Manage Orders',
                'performance': 'Performance Analytics'
            };
            const titleEl = document.getElementById('page-title');
            if(titleEl) titleEl.innerText = titles[id] || 'Dashboard';
        }

        function toggleModal(id) {
            const el = document.getElementById(id);
            el.classList.toggle('hidden');
        }

        function initMaps() {
            // Always try to init visible maps
            if(document.getElementById('miniMap') && document.getElementById('tab-dashboard').classList.contains('active')) {
                initMiniMap();
            }
            if(document.getElementById('routeMap') && document.getElementById('tab-visits').classList.contains('active')) {
                initRouteMap();
            }
        }

        function initMiniMap() {
            if(!miniMapObj && document.getElementById('miniMap')) {
                miniMapObj = L.map('miniMap', {zoomControl: false, attributionControl: false}).setView([9.6615, 80.0255], 12);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(miniMapObj);
                
                // Add Same Markers (no popups for mini map to keep it clean, or can add if desired)
                routeLocations.forEach(loc => {
                    L.marker([loc.lat, loc.lng]).addTo(miniMapObj);
                });
            }
        }

        function initRouteMap() {
            if(!mainRouteMap && document.getElementById('routeMap')) {
                mainRouteMap = L.map('routeMap').setView([9.6615, 80.0255], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mainRouteMap);
                
                // Add Markers with Popups
                routeLocations.forEach(loc => {
                    L.marker([loc.lat, loc.lng]).addTo(mainRouteMap).bindPopup(loc.title);
                });
            }
        }
    </script>
</div>
</body>
</html>
