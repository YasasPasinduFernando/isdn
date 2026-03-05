<?php
// Start output buffering
ob_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Profile.php';


$role = current_user_role();
$userId = (int) $_SESSION['user_id'];
$profileModel = new Profile($pdo);
$profile = $profileModel->getProfile($userId, $role);

// Include the main header which handles session start and navigation
require_once __DIR__ . '/../../includes/header.php';
?>
<!-- Leaflet Configuration -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
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
            $stmt = $pdo->prepare("INSERT INTO retail_customers (name, email, contact_number, address, user_id) VALUES (?, ?, ?, ?, NULL)");
            $stmt->execute([$cName, $cEmail, $cPhone, $cAddr]);
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

        if ($custId && $prodId && $qty > 0) {
            $pdo->beginTransaction();
            try {
                // Get Price
                $stmt = $pdo->prepare("SELECT unit_price FROM products WHERE product_id = ?");
                $stmt->execute([$prodId]);
                $price = $stmt->fetchColumn();
                $total = $price * $qty;

                // Create Order
                $orderRef = 'ORD-' . date('Y') . '-' . mt_rand(1000, 9999);
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
if (isset($_SESSION['success_msg']))
    unset($_SESSION['success_msg']);

// --- 3. Data Fetching ---

// Fetch Rep's Customers from retail_customers table
$customers = $pdo->query("SELECT rc.id, rc.name, rc.email, rc.contact_number, rc.address 
                          FROM retail_customers rc 
                          ORDER BY rc.name")->fetchAll(PDO::FETCH_ASSOC);
// Fetch Today's Customer Visits (from today's orders)
$todayVisitsQuery = $pdo->prepare("
    SELECT DISTINCT rc.id, rc.name, rc.address, rc.contact_number, o.created_at
    FROM orders o
    JOIN retail_customers rc ON o.customer_id = rc.id
    LEFT JOIN users u ON o.placed_by = u.id
    WHERE (o.placed_by = ? OR u.rdc_id = ?)
    AND DATE(o.created_at) = CURDATE()
    ORDER BY o.created_at ASC
");
$todayVisitsQuery->execute([$user_id, $rdc_id]);
$todayVisits = $todayVisitsQuery->fetchAll(PDO::FETCH_ASSOC);

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
$visitsToday = count($todayVisits);
$salesThisMonth = 0;
foreach ($orders as $o) {
    // engaging logic: calculate from actual orders if available for this month
    if (date('Y-m', strtotime($o['created_at'])) === date('Y-m')) {
        $salesThisMonth += $o['total_amount'];
    }
}
// If 0, keep mock base for demo visuals if preferred, or show 0
if ($salesThisMonth == 0)
    $salesThisMonth = 150000;

$target = 500000; // Mock
$achievement = ($salesThisMonth / $target) * 100;

function getStatusBadge($status)
{
    switch (strtolower($status)) {
        case 'pending':
            return '<span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-lg text-xs font-bold border border-yellow-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-yellow-500 mr-1"></span>Pending</span>';
        case 'processing':
            return '<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-lg text-xs font-bold border border-blue-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-blue-500 mr-1"></span>Processing</span>'; // Fixed 'on_the_way' mapping if necessary
        case 'out_for_delivery':
            return '<span class="bg-orange-100 text-orange-700 px-2 py-1 rounded-lg text-xs font-bold border border-orange-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-orange-500 mr-1"></span>On Way</span>';
        case 'delivered':
            return '<span class="bg-green-100 text-green-700 px-2 py-1 rounded-lg text-xs font-bold border border-green-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-green-500 mr-1"></span>Delivered</span>';
        case 'failed':
            return '<span class="bg-red-100 text-red-700 px-2 py-1 rounded-lg text-xs font-bold border border-red-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-red-500 mr-1"></span>Failed</span>';
        default:
            return '<span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-lg text-xs font-bold border border-gray-200">' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
    }
}
?>

<style>
    .font-outfit {
        font-family: 'Outfit', sans-serif;
    }

    #map,
    #liveMap,
    #routeMap,
    #miniMap {
        height: 100%;
        width: 100%;
        border-radius: 1rem;
        z-index: 0;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .hover-lift {
        transition: transform 0.2s;
    }

    .hover-lift:hover {
        transform: translateY(-2px);
    }

    .custom-marker {
        background: none !important;
        border: none !important;
        box-shadow: none !important;
    }
</style>

<div class="flex flex-1 overflow-hidden h-full flex-col">

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative w-full">

        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth pb-20 md:pb-8">

            <!-- Page Title Area -->
            <div
                class="glass-panel rounded-3xl p-6 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 font-['Outfit']" id="page-title">Dashboard Overview</h1>
                    <p class="text-gray-600">Welcome back, <?= htmlspecialchars($profile['name']) ?></p>
                </div>
                <!-- Action Buttons could go here -->
                <div
                    class="flex items-center space-x-3 bg-white/30 px-4 py-2 rounded-xl backdrop-blur-sm border border-white/40">
                    <span class="material-symbols-rounded text-teal-700">badge</span>
                    <span class="font-bold text-teal-800 text-sm"><?= htmlspecialchars($rdc_name) ?></span>
                </div>
            </div>

            <?php if ($success_msg): ?>
                <div
                    class="glass-card bg-green-50/80 border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm relative z-20">
                    <span class="material-symbols-rounded mr-2 text-green-600">check_circle</span>
                    <?= htmlspecialchars($success_msg) ?>
                </div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div
                    class="glass-card bg-red-50/80 border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm relative z-20">
                    <span class="material-symbols-rounded mr-2 text-red-600">error</span>
                    <?= htmlspecialchars($error_msg) ?>
                </div>
            <?php endif; ?>

            <!-- OVERVIEW TAB -->
            <div id="tab-dashboard" class="tab-content active space-y-8">
                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-teal-500 hover-lift group">
                        <div class="flex items-center space-x-4 mb-2">
                            <div
                                class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center group-hover:scale-110 transition">
                                <span class="material-symbols-rounded">group</span>
                            </div>
                            <p class="text-xs font-bold text-gray-500 uppercase">My Customers</p>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $myCustomersCount ?></h3>
                    </div>
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-orange-500 hover-lift group">
                        <div class="flex items-center space-x-4 mb-2">
                            <div
                                class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center group-hover:scale-110 transition">
                                <span class="material-symbols-rounded">calendar_today</span>
                            </div>
                            <p class="text-xs font-bold text-gray-500 uppercase">Visits Today</p>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $visitsToday ?></h3>
                    </div>
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-green-500 hover-lift group">
                        <div class="flex items-center space-x-4 mb-2">
                            <div
                                class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center group-hover:scale-110 transition">
                                <span class="material-symbols-rounded">payments</span>
                            </div>
                            <p class="text-xs font-bold text-gray-500 uppercase">Sales (Month)</p>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-800">Rs. <?= number_format($salesThisMonth / 1000) ?>K
                        </h3>
                    </div>
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-blue-500 hover-lift group">
                        <div class="flex items-center space-x-4 mb-2">
                            <div
                                class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition">
                                <span class="material-symbols-rounded">track_changes</span>
                            </div>
                            <p class="text-xs font-bold text-gray-500 uppercase">Achievement</p>
                        </div>
                        <div class="mt-2">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold text-gray-400">Target</span>
                                <span
                                    class="text-xs font-bold text-blue-600"><?= number_format($achievement, 0) ?>%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full shadow-lg shadow-blue-500/30"
                                    style="width: <?= $achievement ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visits & Map Preview -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="glass-panel rounded-3xl p-6 sm:p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center space-x-3">
                                <span class="material-symbols-rounded text-gray-500 text-2xl">history</span>
                                <h2 class="text-xl font-bold text-gray-800 font-['Outfit']">Recent Orders</h2>
                            </div>
                            <a href="index.php?page=rdc-sales-ref-sales-orders"
                                class="text-sm font-semibold text-teal-600 hover:text-teal-700 flex items-center transition">
                                View All <span class="material-symbols-rounded text-sm ml-1">arrow_forward</span>
                            </a>
                        </div>

                        <div class="space-y-4">
                            <!-- Order Item 1 -->
                            <div
                                class="bg-white/40 border border-white/60 backdrop-blur-sm rounded-2xl p-5 hover:bg-white/60 transition duration-300 group shadow-sm">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center space-x-4">
                                        <div
                                            class="w-12 h-12 rounded-xl bg-blue-100/50 text-blue-600 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition duration-300 border border-blue-100">
                                            <span class="material-symbols-rounded">inventory_2</span>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-800 font-['Outfit']">Order #ORD-2025-001</h3>
                                            <div class="flex items-center text-xs text-gray-600 mt-1 space-x-3">
                                                <span>15 items</span>
                                                <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                                                <span>Rs. 45,250.00</span>
                                            </div>
                                            <div class="flex items-center text-xs text-gray-500 mt-1">
                                                <span
                                                    class="material-symbols-rounded text-sm mr-1">calendar_today</span>
                                                Jan 10, 2025
                                            </div>
                                        </div>
                                    </div>
                                    <span
                                        class="px-4 py-2 rounded-xl bg-green-100/60 border border-green-200 text-green-700 text-sm font-bold flex items-center justify-center self-start sm:self-center">
                                        <span class="material-symbols-rounded text-sm mr-2">check_circle</span>
                                        Delivered
                                    </span>
                                </div>
                            </div>

                            <!-- Order Item 2 -->
                            <div
                                class="bg-white/40 border border-white/60 backdrop-blur-sm rounded-2xl p-5 hover:bg-white/60 transition duration-300 group shadow-sm">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center space-x-4">
                                        <div
                                            class="w-12 h-12 rounded-xl bg-purple-100/50 text-purple-600 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition duration-300 border border-purple-100">
                                            <span class="material-symbols-rounded">checkroom</span>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-800 font-['Outfit']">Order #ORD-2025-002</h3>
                                            <div class="flex items-center text-xs text-gray-600 mt-1 space-x-3">
                                                <span>8 items</span>
                                                <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                                                <span>Rs. 28,900.00</span>
                                            </div>
                                            <div class="flex items-center text-xs text-gray-500 mt-1">
                                                <span
                                                    class="material-symbols-rounded text-sm mr-1">calendar_today</span>
                                                Jan 12, 2025
                                            </div>
                                        </div>
                                    </div>
                                    <span
                                        class="px-4 py-2 rounded-xl bg-purple-100/60 border border-purple-200 text-purple-700 text-sm font-bold flex items-center justify-center self-start sm:self-center">
                                        <span class="material-symbols-rounded text-sm mr-2">local_shipping</span> In
                                        Transit
                                    </span>
                                </div>
                            </div>

                            <!-- Order Item 3 -->
                            <div
                                class="bg-white/40 border border-white/60 backdrop-blur-sm rounded-2xl p-5 hover:bg-white/60 transition duration-300 group shadow-sm">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center space-x-4">
                                        <div
                                            class="w-12 h-12 rounded-xl bg-yellow-100/50 text-yellow-600 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition duration-300 border border-yellow-100">
                                            <span class="material-symbols-rounded">smartphone</span>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-800 font-['Outfit']">Order #ORD-2025-003</h3>
                                            <div class="flex items-center text-xs text-gray-600 mt-1 space-x-3">
                                                <span>22 items</span>
                                                <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                                                <span>Rs. 67,400.00</span>
                                            </div>
                                            <div class="flex items-center text-xs text-gray-500 mt-1">
                                                <span
                                                    class="material-symbols-rounded text-sm mr-1">calendar_today</span>
                                                Jan 13, 2025
                                            </div>
                                        </div>
                                    </div>
                                    <span
                                        class="px-4 py-2 rounded-xl bg-yellow-100/60 border border-yellow-200 text-yellow-700 text-sm font-bold flex items-center justify-center self-start sm:self-center">
                                        <span class="material-symbols-rounded text-sm mr-2">schedule</span> Processing
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="lg:col-span-1 glass-card rounded-3xl overflow-hidden flex flex-col h-[500px]">
                        <div
                            class="p-6 border-b border-gray-100/50 flex justify-between items-center bg-white/30 backdrop-blur-md">
                            <h3 class="font-bold text-gray-800 flex items-center">
                                <span class="material-symbols-rounded text-teal-500 mr-2">route</span> Today's Route
                            </h3>
                            <button onclick="window.location.href='index.php?page=rdc-sales-ref-dashboard&tab=visits'"
                                class="text-xs text-teal-600 font-bold hover:underline bg-teal-50/80 px-3 py-1 rounded-full">View
                                Full Map</button>
                        </div>
                        <div id="miniMap" class="flex-1 bg-gray-100 z-0"></div>
                    </div>

                </div>

                <!-- CUSTOMERS TAB -->
                <div id="tab-customers" class="tab-content hidden space-y-6">
                    <div class="glass-panel rounded-3xl p-6 mb-6 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center">
                            <span class="material-symbols-rounded text-teal-500 mr-2">groups</span> My Customers
                        </h3>
                        <button onclick="toggleModal('modal-customer')"
                            class="bg-teal-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg hover:bg-teal-700 hover:shadow-teal-200 transition flex items-center">
                            <span class="material-symbols-rounded text-lg mr-2">add</span> Add Customer
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php 
                        foreach ($customers as $c): ?>
                            <div class="glass-card p-6 rounded-3xl hover-lift">
                                <div class="flex items-start justify-between mb-4">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center font-bold text-gray-600 text-lg shadow-inner">
                                        <?= strtoupper(substr($c['name'], 0, 2)) ?>
                                    </div>
                                    <span
                                        class="bg-green-100 text-green-700 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wide">Active</span>
                                </div>
                                <h4 class="font-bold text-gray-800 text-lg mb-1 leading-tight">
                                    <?= htmlspecialchars($c['name']) ?>
                                </h4>
                                <p class="text-sm text-gray-500 mb-5 flex items-center">
                                    <span class="material-symbols-rounded text-sm mr-1">mail</span>
                                    <?= htmlspecialchars(string: $c['email']) ?>
                                </p>

                                <div class="border-t border-gray-100/50 pt-4 flex justify-between items-center">
                                    <div>
                                        <p class="text-[10px] uppercase text-gray-400 font-bold">LIFETIME VALUE</p>
                                        <p class="text-lg font-bold text-gray-800">Rs. 45k</p>
                                    </div>
                                    <button
                                        class="text-teal-600 text-xs font-bold hover:text-teal-800 transition bg-teal-50/80 px-3 py-2 rounded-lg">View
                                        History</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- VISITS TAB -->
                <div id="tab-visits" class="tab-content hidden h-full">
                    <div class="glass-panel p-6 rounded-3xl mb-6 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center">
                            <span class="material-symbols-rounded text-teal-500 mr-2">share_location</span> Visits &
                            Route
                        </h3>
                    </div>
                    <div
                        class="glass-card h-[500px] w-full rounded-3xl overflow-hidden border border-gray-200/50 relative p-1">
                        <div id="routeMap" class="w-full h-full rounded-2xl z-0"></div>
                    </div>

                    <!-- Scheduled Visits List -->
                    <div class="glass-card rounded-3xl p-6 mt-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-gray-800 flex items-center">
                                <span class="material-symbols-rounded text-orange-500 mr-2">list_alt</span> Scheduled
                                Visits Today
                            </h3>
                            <span class="text-sm text-gray-500"><?= count($todayVisits) ?>
                                Visit<?= count($todayVisits) !== 1 ? 's' : '' ?></span>
                        </div>

                        <div class="overflow-x-auto">
                            <?php if (!empty($todayVisits)): ?>
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="text-xs font-bold text-gray-500 border-b border-gray-100">
                                            <th class="py-3 px-2">Time</th>
                                            <th class="py-3 px-2">Customer</th>
                                            <th class="py-3 px-2">Location</th>
                                            <th class="py-3 px-2">Contact</th>
                                            <th class="py-3 px-2">Status</th>
                                            <th class="py-3 px-2">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm">
                                        <?php foreach ($todayVisits as $index => $visit): ?>
                                            <tr class="hover:bg-gray-50/50 transition border-b border-gray-100/50">
                                                <td class="py-4 px-2 font-bold text-gray-700">
                                                    <?= date('h:i A', strtotime($visit['created_at'])) ?>
                                                </td>
                                                <td class="py-4 px-2 font-medium text-gray-800">
                                                    <?= htmlspecialchars($visit['name']) ?>
                                                </td>
                                                <td class="py-4 px-2 text-gray-500">
                                                    <?= htmlspecialchars($visit['address'] ?? 'N/A') ?>
                                                </td>
                                                <td class="py-4 px-2 text-gray-500">
                                                    <?= htmlspecialchars($visit['contact_number'] ?? 'N/A') ?>
                                                </td>
                                                <td class="py-4 px-2">
                                                    <?php if ($index === 0): ?>
                                                        <span class="text-teal-500 font-bold text-xs flex items-center"><span
                                                                class="w-1.5 h-1.5 rounded-full bg-teal-500 mr-1"></span>
                                                            Active</span>
                                                    <?php else: ?>
                                                        <span class="text-gray-400 font-bold text-xs flex items-center"><span
                                                                class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1"></span>
                                                            Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-4 px-2">
                                                    <?php if ($index === 0): ?>
                                                        <button
                                                            class="bg-teal-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm hover:bg-teal-700">Check-In</button>
                                                    <?php else: ?>
                                                        <button
                                                            class="bg-gray-100 text-gray-400 px-3 py-1.5 rounded-lg text-xs font-bold cursor-not-allowed">Wait</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-center py-10">
                                    <div
                                        class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                                        <span class="material-symbols-rounded text-3xl">event_busy</span>
                                    </div>
                                    <h4 class="text-lg font-bold text-gray-700 mb-2">No Visits Scheduled</h4>
                                    <p class="text-gray-500 text-sm">No customer visits planned for today.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ORDERS TAB -->
                <div id="tab-orders" class="tab-content hidden space-y-6">
                    <div class="glass-panel p-6 rounded-3xl mb-6 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center">
                            <span class="material-symbols-rounded text-teal-500 mr-2">shopping_cart</span> Manage Orders
                        </h3>
                        <button onclick="toggleModal('modal-order')"
                            class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg hover:bg-emerald-700 hover:shadow-emerald-200 transition flex items-center">
                            <span class="material-symbols-rounded text-lg mr-2">add_shopping_cart</span> Place Order
                        </button>
                    </div>

                    <div class="glass-card rounded-3xl overflow-hidden shadow-sm">
                        <table class="w-full text-left">
                            <thead
                                class="bg-teal-50/40 text-gray-500 text-xs uppercase font-bold border-b border-gray-100/50">
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
                                    <?php foreach ($orders as $o): ?>
                                        <tr class="hover:bg-white/40 transition">
                                            <td class="p-5 font-bold text-gray-700"><?= $o['order_number'] ?></td>
                                            <td class="p-5 font-medium text-gray-800"><?= htmlspecialchars($o['username']) ?>
                                            </td>
                                            <td class="p-5 text-gray-500"><?= date('M d, Y', strtotime($o['created_at'])) ?>
                                            </td>
                                            <td class="p-5 font-bold text-gray-800">Rs. <?= number_format($o['total_amount']) ?>
                                            </td>
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
                        <div
                            class="w-24 h-24 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-6 text-teal-600">
                            <span class="material-symbols-rounded text-5xl">leaderboard</span>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Performance Analytics</h3>
                        <p class="text-gray-500 mb-6">Sales targets, efficient routes, and customer growth insights will
                            serve here.</p>

                        <div class="grid grid-cols-2 gap-4 text-left">
                            <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100/50">
                                <div class="text-xs text-gray-500 uppercase font-bold mb-1">Monthly Target</div>
                                <div class="text-xl font-bold text-gray-800">85%</div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                                    <div class="bg-teal-500 h-1.5 rounded-full shadow-lg shadow-teal-500/30"
                                        style="width: 85%"></div>
                                </div>
                            </div>
                            <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100/50">
                                <div class="text-xs text-gray-500 uppercase font-bold mb-1">Customer Retention</div>
                                <div class="text-xl font-bold text-gray-800">92%</div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                                    <div class="bg-blue-500 h-1.5 rounded-full shadow-lg shadow-blue-500/30"
                                        style="width: 92%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
    </main>

    <!-- Modal: Add Customer -->
    <div id="modal-customer"
        class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-md z-50 flex items-center justify-center p-4 transition-opacity animate-fade-in">
        <form method="POST" action="index.php?page=rdc-sales-ref-dashboard"
            class="glass-card bg-white/95 rounded-3xl w-full max-w-md p-8 shadow-2xl relative border-white/50">
            <button type="button" onclick="toggleModal('modal-customer')"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
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
                    <input type="text" name="name"
                        class="w-full border border-gray-200 bg-gray-50/50 p-3 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition"
                        required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Email Address</label>
                    <input type="email" name="email"
                        class="w-full border border-gray-200 bg-gray-50/50 p-3 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition"
                        required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Phone Number</label>
                    <input type="text" name="contact_number"
                        class="w-full border border-gray-200 bg-gray-50/50 p-3 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition"
                        required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Address</label>
                    <textarea name="address"
                        class="w-full border border-gray-200 bg-gray-50/50 p-3 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition"
                        rows="2"></textarea>
                </div>
                <input type="hidden" name="add_customer" value="1">
            </div>
            <div class="mt-8 flex space-x-3">
                <button type="button" onclick="toggleModal('modal-customer')"
                    class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold transition">Cancel</button>
                <button type="submit"
                    class="flex-1 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold shadow-lg shadow-teal-200 transition">Save
                    Customer</button>
            </div>
        </form>
    </div>

    <!-- Modal: Place Order -->
    <div id="modal-order"
        class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-md z-50 flex items-center justify-center p-4 transition-opacity animate-fade-in">
        <form method="POST" action="index.php?page=rdc-sales-ref-dashboard"
            class="glass-card bg-white/95 rounded-3xl w-full max-w-md p-8 shadow-2xl relative border-white/50">
            <button type="button" onclick="toggleModal('modal-order')"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
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
                    <select name="customer_id"
                        class="w-full border border-gray-200 bg-gray-50/50 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition"
                        required>
                        <option value="">Choose...</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Select Product</label>
                    <select name="product_id"
                        class="w-full border border-gray-200 bg-gray-50/50 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition"
                        required>
                        <option value="">Choose...</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Quantity</label>
                    <input type="number" name="quantity" min="1"
                        class="w-full border border-gray-200 bg-gray-50/50 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition"
                        required>
                </div>
                <input type="hidden" name="place_order" value="1">
            </div>
            <div class="mt-8 flex space-x-3">
                <button type="button" onclick="toggleModal('modal-order')"
                    class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold transition">Cancel</button>
                <button type="submit"
                    class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-lg shadow-emerald-200 transition">Place
                    Order</button>
            </div>
        </form>
    </div>

    <script>
        // Global map instances
        var mainRouteMap = null;
        var miniMapObj = null;

        // Shared Route Data - Dynamic from today's orders
        const routeLocations = [
            <?php
            if (!empty($todayVisits)) {
                $locationJs = [];
                foreach ($todayVisits as $visit) {
                    $safeName = addslashes($visit['name']);
                    $safeAddress = addslashes($visit['address'] ?? 'Address not available');
                    $locationJs[] = "{address: '$safeAddress', title: '$safeName'}";
                }
                echo implode(",\n            ", $locationJs);
            } else {
                // Fallback to sample data if no orders today
                echo "{address: 'Jaffna Town, Jaffna', title: 'Siva Stores'},";
                echo "\n            {address: 'Kokuvil, Jaffna', title: 'New City Mart'},";
                echo "\n            {address: 'Kopay, Jaffna', title: 'Raja Traders'}";
            }
            ?>
        ];

        // Final fallback coordinates for major Sri Lankan cities
        const locationKeywords = [
            { keywords: ['colombo'], coords: [6.9271, 79.8612] },
            { keywords: ['kandy'], coords: [7.2906, 80.6337] },
            { keywords: ['galle'], coords: [6.0535, 80.2210] },
            { keywords: ['jaffna'], coords: [9.6615, 80.0255] },
            { keywords: ['trincomalee'], coords: [8.5874, 81.2152] },
            { keywords: ['batticaloa'], coords: [7.7310, 81.6747] },
            { keywords: ['badulla'], coords: [6.9934, 81.0550] },
            { keywords: ['ratnapura'], coords: [6.7056, 80.3847] },
            { keywords: ['kurunegala'], coords: [7.4863, 80.3623] },
            { keywords: ['anuradhapura'], coords: [8.3114, 80.4037] },
            { keywords: ['negombo'], coords: [7.2083, 79.8358] },
            { keywords: ['matara'], coords: [5.9485, 80.5353] },
            { keywords: ['nuwara eliya'], coords: [6.9497, 80.7891] },
            { keywords: ['ampara'], coords: [7.2975, 81.6681] },
            { keywords: ['hambantota'], coords: [6.1429, 81.1212] },
            { keywords: ['kalutara'], coords: [6.5833, 79.9611] },
            { keywords: ['gampaha'], coords: [7.0917, 80.0142] },
            { keywords: ['kilinochchi'], coords: [9.3811, 80.4037] },
            { keywords: ['vavuniya'], coords: [8.7514, 80.4972] },
            { keywords: ['mannar'], coords: [8.9833, 79.9167] },
            { keywords: ['puttalam'], coords: [8.0408, 79.8356] },
            { keywords: ['polonnaruwa'], coords: [7.9403, 81.0188] },
            { keywords: ['monaragala'], coords: [6.8722, 81.3508] },
            { keywords: ['kegalle'], coords: [7.2528, 80.3464] },
            { keywords: ['matale'], coords: [7.4675, 80.6234] }
        ];

        // Track used coordinates to add offsets
        const usedCoords = {};

        // Geocoding cache
        const geocodeCache = JSON.parse(localStorage.getItem('geocodeCache') || '{}');
        let lastGeocodeTime = 0;

        // Get exact coordinates using OpenStreetMap Nominatim API
        async function geocodeAddress(address) {
            if (geocodeCache[address]) {
                return geocodeCache[address];
            }

            try {
                const now = Date.now();
                const timeSinceLastRequest = now - lastGeocodeTime;
                if (timeSinceLastRequest < 1000) {
                    await new Promise(resolve => setTimeout(resolve, 1000 - timeSinceLastRequest));
                }
                lastGeocodeTime = Date.now();

                let searchAddress = encodeURIComponent(address + ', Sri Lanka');
                let response = await fetch(`https://nominatim.openstreetmap.org/search?q=${searchAddress}&format=json&limit=1&countrycodes=lk`, {
                    headers: { 'User-Agent': 'ISDN-SalesApp/1.0' }
                });

                if (!response.ok) throw new Error('Geocoding API error');
                let data = await response.json();

                if (!data || data.length === 0) {
                    const cityMatch = address.match(/\b(Colombo|Galle|Kandy|Jaffna|Negombo|Matara|Trincomalee|Batticaloa|Anuradhapura|Kurunegala|Ratnapura|Badulla|Ampara|Kalutara|Gampaha|Kilinochchi|Vavuniya|Mannar|Puttalam|Polonnaruwa|Monaragala|Kegalle|Matale|Hambantota|Nuwara Eliya)\b/i);

                    if (cityMatch) {
                        const cityName = cityMatch[0];
                        await new Promise(resolve => setTimeout(resolve, 1000));

                        searchAddress = encodeURIComponent(cityName + ', Sri Lanka');
                        response = await fetch(`https://nominatim.openstreetmap.org/search?q=${searchAddress}&format=json&limit=1&countrycodes=lk`, {
                            headers: { 'User-Agent': 'ISDN-SalesApp/1.0' }
                        });
                        data = await response.json();
                    }
                }

                if (data && data.length > 0) {
                    const coords = [parseFloat(data[0].lat), parseFloat(data[0].lon)];
                    geocodeCache[address] = coords;
                    localStorage.setItem('geocodeCache', JSON.stringify(geocodeCache));
                    return coords;
                }
            } catch (error) {
                console.warn('Geocoding error for', address, ':', error);
            }

            return getCoordinatesFallback(address);
        }

        // Fallback coordinates from address keywords
        function getCoordinatesFallback(address) {
            if (!address) return [9.6615, 80.0255]; // Default Jaffna

            const addrLower = address.toLowerCase();

            for (const loc of locationKeywords) {
                for (const keyword of loc.keywords) {
                    if (addrLower.includes(keyword.toLowerCase())) {
                        return loc.coords;
                    }
                }
            }

            // Generate unique position based on address hash
            const hash = hashString(address);
            const baseLat = 9.6615;  // Jaffna center
            const baseLng = 80.0255;
            const latOffset = ((hash % 1000) / 1000) * 0.15 - 0.075;
            const lngOffset = (((hash >> 10) % 1000) / 1000) * 0.15 - 0.075;
            return [baseLat + latOffset, baseLng + lngOffset];
        }

        // Synchronous version for immediate use
        function getCoordinates(address) {
            if (geocodeCache[address]) {
                return addOffset(geocodeCache[address]);
            }
            return addOffset(getCoordinatesFallback(address));
        }

        // Add offset to prevent marker overlap
        function addOffset(coords) {
            const key = coords[0].toFixed(4) + ',' + coords[1].toFixed(4);
            if (!usedCoords[key]) {
                usedCoords[key] = 0;
            }
            usedCoords[key]++;
            const count = usedCoords[key];
            if (count === 1) return coords;

            const angle = (count - 1) * (2.4);
            const radius = 0.003 * Math.ceil(count / 6);
            return [
                coords[0] + radius * Math.cos(angle),
                coords[1] + radius * Math.sin(angle)
            ];
        }

        // Hash function
        function hashString(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash;
            }
            return Math.abs(hash);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab') || 'dashboard';

            // Initial Tab Selection
            const target = document.getElementById('tab-' + tab);
            if (target) {
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
            const target = document.getElementById('tab-' + id);
            if (target) {
                target.classList.remove('hidden');
                target.classList.add('active');
                updateTitle(id);

                // Handle Map Refresh if switching to visits tab
                if (id === 'visits') {
                    setTimeout(function () {
                        if (!mainRouteMap) {
                            initRouteMap();
                        } else {
                            mainRouteMap.invalidateSize();
                        }
                    }, 200);
                }
                // Handle Mini Map Refresh if switching to dashboard
                if (id === 'dashboard') {
                    setTimeout(function () {
                        if (!miniMapObj) {
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
            if (titleEl) titleEl.innerText = titles[id] || 'Dashboard';
        }

        function toggleModal(id) {
            const el = document.getElementById(id);
            el.classList.toggle('hidden');
        }

        function initMaps() {
            // Always try to init visible maps
            const dashboardTab = document.getElementById('tab-dashboard');
            const visitsTab = document.getElementById('tab-visits');

            if (document.getElementById('miniMap') && dashboardTab && !dashboardTab.classList.contains('hidden')) {
                setTimeout(initMiniMap, 100);
            }
            if (document.getElementById('routeMap') && visitsTab && !visitsTab.classList.contains('hidden')) {
                setTimeout(initRouteMap, 100);
            }
        }

        function initMiniMap() {
            const mapContainer = document.getElementById('miniMap');
            if (!miniMapObj && mapContainer) {
                miniMapObj = L.map('miniMap', { zoomControl: false, attributionControl: false }).setView([9.6615, 80.0255], 12);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap'
                }).addTo(miniMapObj);

                // Add markers from route locations using geocoding
                routeLocations.forEach(loc => {
                    const coords = getCoordinates(loc.address);
                    const marker = L.marker(coords).addTo(miniMapObj);
                    marker.bindPopup(`<b>${loc.title}</b><br><small>${loc.address}</small>`);
                });

                // Ensure map renders correctly
                setTimeout(() => miniMapObj.invalidateSize(), 100);
            }
        }

        async function initRouteMap() {
            const mapContainer = document.getElementById('routeMap');
            if (!mainRouteMap && mapContainer && routeLocations.length > 0) {
                // Show loading
                mapContainer.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;background:#f3f4f6;"><div style="text-align:center;"><div style="border:4px solid #e5e7eb;border-top:4px solid #14b8a6;border-radius:50%;width:40px;height:40px;animation:spin 1s linear infinite;margin:0 auto 10px;"></div><p style="color:#6b7280;font-size:14px;">Loading customer locations...</p></div></div><style>@keyframes spin{to{transform:rotate(360deg);}}</style>';

                // Geocode all addresses
                const geocodedLocations = await Promise.all(
                    routeLocations.map(async (loc) => {
                        const coords = await geocodeAddress(loc.address);
                        return { ...loc, coords };
                    })
                );

                // Clear loading
                mapContainer.innerHTML = '';

                // Create map
                mainRouteMap = L.map('routeMap').setView([9.6615, 80.0255], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap'
                }).addTo(mainRouteMap);

                const bounds = [];

                // Add markers with popups
                geocodedLocations.forEach((loc, index) => {
                    const coords = addOffset(loc.coords);
                    bounds.push(coords);

                    // Marker color (teal for sales visits)
                    const markerColor = '#14b8a6';

                    const customIcon = L.divIcon({
                        className: 'custom-marker',
                        html: `<div style="background-color: ${markerColor}; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">${index + 1}</div>`,
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    });

                    const marker = L.marker(coords, { icon: customIcon }).addTo(mainRouteMap);
                    marker.bindPopup(`<b>#${index + 1} - ${loc.title}</b><br><small>${loc.address}</small>`);
                });

                // Fit bounds if we have locations
                if (bounds.length > 0) {
                    mainRouteMap.fitBounds(bounds, { padding: [50, 50] });
                }

                setTimeout(() => mainRouteMap.invalidateSize(), 100);
            }
        }
    </script>
</div>
</body>

</html>