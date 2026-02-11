<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 1. Authentication & Context Setup ---
try {
    // Attempt to find a Sales Rep user
    $stmt = $pdo->prepare("SELECT u.id, u.username, u.rdc_id, r.rdc_name, s.id as sales_ref_id
                           FROM users u 
                           JOIN rdcs r ON u.rdc_id = r.rdc_id 
                           LEFT JOIN rdc_sales_refs s ON u.id = s.user_id
                           WHERE u.role = 'rdc_sales_ref' LIMIT 1");
    $stmt->execute();
    $rep = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rep) {
        // Fallback for demo
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
            // Simplified: Insert into retail_customers directly or users table if they need login
            // For this demo, we'll insert into retail_customers and assume they are linked to this rep conceptually
            $stmt = $pdo->prepare("INSERT INTO retail_customers (name, email, contact_number, address, user_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$cName, $cEmail, $cPhone, $cAddr, $user_id]);
            $success_msg = "New customer added successfully.";
            // Refresh
            header("Location: " . $_SERVER['PHP_SELF'] . "?page=rep");
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
                $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_id, rdc_id, total_amount, status) VALUES (?, ?, ?, ?, 'pending')");
                $stmt->execute([$orderRef, $custId, $rdc_id, $total]);
                $orderId = $pdo->lastInsertId();

                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, selling_price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$orderId, $prodId, $qty, $price]);

                $pdo->commit();
                $success_msg = "Order placed for customer! Ref: " . $orderRef;
                // Refresh
                header("Location: " . $_SERVER['PHP_SELF'] . "?page=rep");
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error_msg = "Order failed: " . $e->getMessage();
            }
        }
    }
}

// --- 3. Data Fetching ---

// Fetch Rep's Customers (Users with customer role or linked)
// For demo, fetching all customers
$customers = $pdo->query("SELECT id, username, email FROM users WHERE role = 'customer'")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Products
$products = $pdo->query("SELECT product_id, product_name FROM products")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Recent Orders (Placed by this rep's customers? For now, all RDC orders to show activity)
$recentOrders = $pdo->prepare("SELECT o.*, u.username FROM orders o JOIN users u ON o.customer_id = u.id WHERE o.rdc_id = ? ORDER BY o.created_at DESC LIMIT 5");
$recentOrders->execute([$rdc_id]);
$orders = $recentOrders->fetchAll(PDO::FETCH_ASSOC);

// Dashboard Stats
$myCustomersCount = count($customers); 
$visitsToday = 4; // Mock
$salesThisMonth = 150000; // Mock
$target = 500000; // Mock
$achievement = ($salesThisMonth / $target) * 100;

function getStatusBadge($status) {
    switch(strtolower($status)) {
        case 'pending': return '<span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-lg text-xs font-bold border border-yellow-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-yellow-500 mr-1"></span>Pending</span>';
        case 'processing': return '<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-lg text-xs font-bold border border-blue-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-blue-500 mr-1"></span>Processing</span>';
        case 'delivered': return '<span class="bg-green-100 text-green-700 px-2 py-1 rounded-lg text-xs font-bold border border-green-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-green-500 mr-1"></span>Delivered</span>';
        default: return '<span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-lg text-xs font-bold border border-gray-200">' . ucfirst($status) . '</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Rep Dashboard - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/custom.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <style>
        .font-outfit { font-family: 'Outfit', sans-serif; }
        .sidebar-link.active {
            background: linear-gradient(90deg, rgba(20, 184, 166, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
            border-left: 4px solid #0d9488;
            color: #0f766e;
        }
        .sidebar-link:hover:not(.active) {
            background-color: #f0fdfa;
            color: #0d9488;
        }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
        .hover-lift { transition: transform 0.2s; }
        .hover-lift:hover { transform: translateY(-2px); }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.5); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        #map, #liveMap { height: 100%; width: 100%; border-radius: 1rem; z-index: 0; }
    </style>
</head>
<body class="bg-slate-50 font-outfit flex h-screen overflow-hidden text-gray-800">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-100 flex flex-col z-20 shadow-sm hidden md:flex">
        <div class="h-16 flex items-center px-6 border-b border-gray-100">
            <div class="flex items-center space-x-2">
                <div class="bg-gradient-to-br from-teal-500 to-emerald-600 p-1.5 rounded-lg shadow-md text-white flex items-center justify-center">
                    <span class="material-symbols-rounded text-xl">dataset</span>
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-tight text-gray-800">ISDN</h1>
                    <p class="text-[9px] uppercase tracking-wider font-semibold text-teal-600">Sales Rep</p>
                </div>
            </div>
        </div>
        
        <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full bg-white border border-gray-200 text-teal-600 flex items-center justify-center shadow-sm">
                    <span class="material-symbols-rounded text-lg">domain</span>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-gray-800"><?php echo htmlspecialchars($rdc_name); ?></h3>
                    <p class="text-[10px] text-gray-500 font-medium">Station ID: #<?php echo str_pad($rdc_id, 3, '0', STR_PAD_LEFT); ?></p>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto sidebar-scroll py-4 space-y-1">
            <div class="px-6 mb-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Main Menu</div>
             <button onclick="switchTab('dashboard')" id="nav-dashboard" class="w-full text-left sidebar-link active flex items-center px-6 py-3 text-sm font-medium text-gray-600 transition-colors group">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">dashboard</span> Overview
            </button>
            <button onclick="switchTab('customers')" id="nav-customers" class="w-full text-left sidebar-link flex items-center px-6 py-3 text-sm font-medium text-gray-600 transition-colors group">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">groups</span> My Customers
            </button>
            <button onclick="switchTab('visits')" id="nav-visits" class="w-full text-left sidebar-link flex items-center px-6 py-3 text-sm font-medium text-gray-600 transition-colors group">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">share_location</span> Visits & Route
            </button>
            <button onclick="switchTab('orders')" id="nav-orders" class="w-full text-left sidebar-link flex items-center px-6 py-3 text-sm font-medium text-gray-600 transition-colors group">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">shopping_cart</span> Orders
            </button>
            <button onclick="switchTab('performance')" id="nav-performance" class="w-full text-left sidebar-link flex items-center px-6 py-3 text-sm font-medium text-gray-600 transition-colors group">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">leaderboard</span> Performance
            </button>
        </div>

        <div class="p-4 border-t border-gray-100">
            <a href="index.php?page=home" class="flex items-center space-x-3 hover:bg-red-50 p-2 rounded-lg transition group">
                <span class="material-symbols-rounded text-gray-400 group-hover:text-red-500 transition">logout</span>
                <span class="text-sm font-medium text-gray-600 group-hover:text-red-600 transition">Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-slate-50">
        <header class="h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-6 z-10 sticky top-0 md:static">
            <h2 class="text-lg font-bold text-gray-800" id="page-title">Dashboard Overview</h2>
            <div class="flex items-center space-x-4">
                <div class="hidden sm:block text-right mr-2">
                    <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($rep_name) ?></p>
                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Sales Rep</p>
                </div>
                <div class="relative w-10 h-10 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center border border-teal-200 shadow-sm">
                    <span class="material-symbols-rounded">person</span>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth relative">
            <?php if($success_msg): ?>
                <div class="glass-card bg-green-50 border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm relative z-20">
                    <span class="material-symbols-rounded mr-2 text-green-600">check_circle</span> <?= $success_msg ?>
                </div>
            <?php endif; ?>

            <!-- OVERVIEW TAB -->
            <div id="tab-dashboard" class="tab-content space-y-8">
                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-teal-500 hover-lift">
                        <div class="flex items-center space-x-4 mb-2">
                            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                                <span class="material-symbols-rounded">group</span>
                            </div>
                            <p class="text-xs font-bold text-gray-500 uppercase">My Customers</p>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $myCustomersCount ?></h3>
                    </div>
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-orange-500 hover-lift">
                        <div class="flex items-center space-x-4 mb-2">
                            <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center">
                                <span class="material-symbols-rounded">calendar_today</span>
                            </div>
                            <p class="text-xs font-bold text-gray-500 uppercase">Visits Today</p>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $visitsToday ?></h3>
                    </div>
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-green-500 hover-lift">
                        <div class="flex items-center space-x-4 mb-2">
                            <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center">
                                <span class="material-symbols-rounded">payments</span>
                            </div>
                            <p class="text-xs font-bold text-gray-500 uppercase">Sales (Month)</p>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-800">Rs. <?= number_format($salesThisMonth/1000) ?>K</h3>
                    </div>
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-blue-500 hover-lift">
                         <div class="flex items-center space-x-4 mb-2">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
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
                                <div class="bg-blue-500 h-2 rounded-full" style="width: <?= $achievement ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visits & Map Preview -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 glass-card rounded-3xl overflow-hidden flex flex-col h-[400px]">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-white/50">
                            <h3 class="font-bold text-gray-800 flex items-center">
                                <span class="material-symbols-rounded text-teal-500 mr-2">route</span> Today's Route
                            </h3>
                            <button class="text-xs text-teal-600 font-bold hover:underline bg-teal-50 px-3 py-1 rounded-full">View Full Map</button>
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
                 <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center">
                         <span class="material-symbols-rounded text-teal-500 mr-2">groups</span> My Customers
                    </h3>
                    <button onclick="toggleModal('modal-customer')" class="bg-teal-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg hover:bg-teal-700 hover:shadow-teal-200 transition flex items-center">
                        <span class="material-symbols-rounded text-lg mr-2">add</span> Add Customer
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach($customers as $c): ?>
                    <div class="glass-card p-6 rounded-3xl hover-lift bg-white">
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
                        
                        <div class="border-t border-gray-100 pt-4 flex justify-between items-center">
                            <div>
                                <p class="text-[10px] uppercase text-gray-400 font-bold">LIFETIME VALUE</p>
                                <p class="text-lg font-bold text-gray-800">Rs. 45k</p>
                            </div>
                            <button class="text-teal-600 text-xs font-bold hover:text-teal-800 transition bg-teal-50 px-3 py-2 rounded-lg">View History</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ORDERS TAB -->
            <div id="tab-orders" class="tab-content hidden space-y-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center">
                        <span class="material-symbols-rounded text-teal-500 mr-2">shopping_cart</span> Manage Orders
                    </h3>
                    <button onclick="toggleModal('modal-order')" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg hover:bg-emerald-700 hover:shadow-emerald-200 transition flex items-center">
                         <span class="material-symbols-rounded text-lg mr-2">add_shopping_cart</span> Place Order
                    </button>
                </div>

                <div class="glass-card rounded-3xl overflow-hidden shadow-sm">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 text-gray-500 text-xs uppercase font-medium border-b border-gray-100">
                            <tr>
                                <th class="p-5">Order ID</th>
                                <th class="p-5">Customer</th>
                                <th class="p-5">Date</th>
                                <th class="p-5">Amount</th>
                                <th class="p-5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm bg-white/50">
                            <?php foreach($orders as $o): ?>
                            <tr class="hover:bg-teal-50/30 transition">
                                <td class="p-5 font-bold text-gray-700"><?= $o['order_number'] ?></td>
                                <td class="p-5 font-medium text-gray-800"><?= htmlspecialchars($o['username']) ?></td>
                                <td class="p-5 text-gray-500"><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                <td class="p-5 font-bold text-gray-800">Rs. <?= number_format($o['total_amount']) ?></td>
                                <td class="p-5">
                                     <?= getStatusBadge($o['status']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VISITS TAB (Placeholder) -->
            <div id="tab-visits" class="tab-content hidden h-full">
                <div class="glass-card h-[600px] w-full rounded-3xl overflow-hidden border border-gray-200 relative p-1 bg-white">
                    <div id="routeMap" class="w-full h-full rounded-2xl z-0"></div>
                </div>
            </div>
            
            <!-- PERFORMANCE TAB (Placeholder) -->
            <div id="tab-performance" class="tab-content hidden h-full text-center py-20">
                <div class="max-w-md mx-auto">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-400">
                        <span class="material-symbols-rounded text-5xl">bar_chart</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Analytics Dashboard</h3>
                    <p class="text-gray-500">Detailed performance analytics and reporting features will appear here soon.</p>
                </div>
            </div>

        </div>
    </main>

    <!-- Modal: Add Customer -->
    <div id="modal-customer" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity">
        <form method="POST" class="bg-white rounded-3xl w-full max-w-md p-8 shadow-2xl relative">
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
                     <input type="text" name="name" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Email Address</label>
                    <input type="email" name="email" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Phone Number</label>
                    <input type="text" name="contact_number" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Address</label>
                    <textarea name="address" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition" rows="2"></textarea>
                </div>
            </div>
            <div class="mt-8 flex space-x-3">
                <button type="button" onclick="toggleModal('modal-customer')" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold transition">Cancel</button>
                <button type="submit" name="add_customer" class="flex-1 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold shadow-lg shadow-teal-200 transition">Save Customer</button>
            </div>
        </form>
    </div>

    <!-- Modal: Place Order -->
    <div id="modal-order" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity">
        <form method="POST" class="bg-white rounded-3xl w-full max-w-md p-8 shadow-2xl relative">
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
                     <select name="customer_id" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition" required>
                         <option value="">Choose...</option>
                         <?php foreach($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['username']) ?></option>
                         <?php endforeach; ?>
                     </select>
                </div>
                <div>
                     <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Select Product</label>
                     <select name="product_id" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition" required>
                         <option value="">Choose...</option>
                         <?php foreach($products as $p): ?>
                            <option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name']) ?></option>
                         <?php endforeach; ?>
                     </select>
                </div>
                <div>
                     <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Quantity</label>
                     <input type="number" name="quantity" min="1" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition" required>
                </div>
            </div>
            <div class="mt-8 flex space-x-3">
                <button type="button" onclick="toggleModal('modal-order')" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold transition">Cancel</button>
                <button type="submit" name="place_order" class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-lg shadow-emerald-200 transition">Place Order</button>
            </div>
        </form>
    </div>

    <script>
        function switchTab(id) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.getElementById('tab-'+id).classList.remove('hidden');
            document.getElementById('page-title').innerText = id.charAt(0).toUpperCase() + id.slice(1);
            
            document.querySelectorAll('.sidebar-link').forEach(el => el.classList.remove('active'));
            // document.getElementById('nav-'+id).classList.add('active'); // Optional: Add active state logic
            
            // Map Init
            if(id === 'visits' && !window.routeMap) {
                setTimeout(initRouteMap, 100);
            }
        }

        function toggleModal(id) {
            document.getElementById(id).classList.toggle('hidden');
        }

        function initRouteMap() {
            window.routeMap = L.map('routeMap').setView([9.6615, 80.0255], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(window.routeMap);
            // Mock Route
            L.marker([9.6615, 80.0255]).addTo(window.routeMap).bindPopup("Start Point");
            L.marker([9.6650, 80.0300]).addTo(window.routeMap).bindPopup("Customer A");
            L.marker([9.6700, 80.0200]).addTo(window.routeMap).bindPopup("Customer B");
        }
        
        // Mini Map
        const miniMap = L.map('miniMap', {zoomControl: false}).setView([9.6615, 80.0255], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(miniMap);
        L.marker([9.6615, 80.0255]).addTo(miniMap);
    </script>
</body>
</html>
