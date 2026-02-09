<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 1. Authentication & Context Setup ---
$user_id = $_SESSION['user_id'] ?? 3; // Default to North Manager
$user_role = $_SESSION['role'] ?? 'rdc_manager';

try {
    // Fetch RDC Details
    $stmt = $pdo->prepare("SELECT u.username, u.rdc_id, r.rdc_name, r.rdc_code 
                           FROM users u 
                           LEFT JOIN rdcs r ON u.rdc_id = r.rdc_id 
                           WHERE u.id = ?");
    $stmt->execute([$user_id]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentUser) {
        die("User not found. Please run seed data.");
    }

    $rdc_id = $currentUser['rdc_id'];
    $rdc_name = $currentUser['rdc_name'] ?? 'Unknown RDC';
    $manager_name = $currentUser['username'];

    // --- 2. Data Fetching ---

    // A. Dashboard Stats
    $statsQuery = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN status IN ('processing', 'out_for_delivery') THEN 1 ELSE 0 END) as active_deliveries,
                    SUM(CASE WHEN status = 'delivered' THEN total_amount ELSE 0 END) as revenue
                   FROM orders 
                   WHERE rdc_id = ?";
    $stmt = $pdo->prepare($statsQuery);
    $stmt->execute([$rdc_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // B. Recent Orders
    $recentOrdersQuery = "SELECT o.*, u.username as customer_name, u.email 
                          FROM orders o 
                          LEFT JOIN users u ON o.customer_id = u.id 
                          WHERE o.rdc_id = ? 
                          ORDER BY o.created_at DESC 
                          LIMIT 5";
    $stmt = $pdo->prepare($recentOrdersQuery);
    $stmt->execute([$rdc_id]);
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // C. All Orders
    $allOrdersQuery = "SELECT o.*, u.username as customer_name, u.email 
                       FROM orders o 
                       LEFT JOIN users u ON o.customer_id = u.id 
                       WHERE o.rdc_id = ? 
                       ORDER BY o.created_at DESC";
    $stmt = $pdo->prepare($allOrdersQuery);
    $stmt->execute([$rdc_id]);
    $allOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // D. Inventory
    $inventoryQuery = "SELECT p.product_name, p.product_code, p.category, p.image_url, p.minimum_stock_level, ps.available_quantity 
                       FROM product_stocks ps 
                       JOIN products p ON ps.product_id = p.product_id 
                       WHERE ps.rdc_id = ?";
    $stmt = $pdo->prepare($inventoryQuery);
    $stmt->execute([$rdc_id]);
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // E. Drivers (For Route Planning)
    // Fetch drivers linked to this RDC via users table or rdc_drivers table logic
    // We assume rdc_drivers table exists and might need a join. 
    // Checking seed: rdc_drivers has user_id, name. users has rdc_id.
    $driversQuery = "SELECT d.id, d.name, u.email 
                     FROM rdc_drivers d 
                     JOIN users u ON d.user_id = u.id 
                     WHERE u.rdc_id = ?";
    $stmt = $pdo->prepare($driversQuery);
    $stmt->execute([$rdc_id]);
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // G. Customers (For New Order Dropdown)
    $stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'customer' ORDER BY username");
    $allCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- 3. Form Handling (Stock & Order) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // Handle Stock Request
        if (isset($_POST['request_stock'])) {
            $product_id = $_POST['product_id'];
            $quantity = $_POST['quantity'];
            $reason = $_POST['reason'] ?? '';
            
            if($product_id && $quantity > 0) {
                $pdo->beginTransaction();
                try {
                    $transferRef = 'REQ-' . strtoupper(uniqid());
                    $stmt = $pdo->prepare("INSERT INTO stock_transfers (transfer_number, source_rdc_id, destination_rdc_id, requested_by, request_reason, transfer_status) VALUES (?, ?, ?, ?, ?, 'PENDING_APPROVAL')");
                    $headOfficeId = 6; 
                    $stmt->execute([$transferRef, $headOfficeId, $rdc_id, $user_id, $reason]);
                    $transferId = $pdo->lastInsertId();

                    $stmt = $pdo->prepare("INSERT INTO stock_transfer_items (transfer_id, product_id, requested_quantity) VALUES (?, ?, ?)");
                    $stmt->execute([$transferId, $product_id, $quantity]);

                    $pdo->commit();
                    $success_msg = "Stock request submitted successfully! Ref: " . $transferRef;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error_msg = "Failed to submit request: " . $e->getMessage();
                }
            }
        }

        // Handle New Order
        if (isset($_POST['create_order'])) {
             $customer_id = $_POST['customer_id'];
             $product_id = $_POST['product_id'];
             $quantity = $_POST['quantity'];
             
             if($customer_id && $product_id && $quantity > 0) {
                 $pdo->beginTransaction();
                 try {
                     // Get Product Price
                     $stmt = $pdo->prepare("SELECT unit_price FROM products WHERE product_id = ?");
                     $stmt->execute([$product_id]);
                     $product = $stmt->fetch(PDO::FETCH_ASSOC);
                     $price = $product['unit_price'] ?? 0;
                     $total = $price * $quantity;

                     // Create Order
                     $orderRef = 'ORD-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                     $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_id, rdc_id, total_amount, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
                     $stmt->execute([$orderRef, $customer_id, $rdc_id, $total]);
                     $orderId = $pdo->lastInsertId();

                     // Create Order Item
                     $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, selling_price) VALUES (?, ?, ?, ?)");
                     $stmt->execute([$orderId, $product_id, $quantity, $price]);

                     $pdo->commit();
                     $success_msg = "Order created successfully! Ref: " . $orderRef;
                     
                     // Refresh Data
                     header("Location: " . $_SERVER['PHP_SELF']);
                     exit;
                     
                 } catch (Exception $e) {
                     $pdo->rollBack();
                     $error_msg = "Failed to create order: " . $e->getMessage();
                 }
             }
        }
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

function getStatusBadge($status) {
    switch(strtolower($status)) {
        case 'pending': return '<span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-lg text-xs font-bold border border-yellow-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-yellow-500 mr-1"></span>Pending</span>';
        case 'processing': return '<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-lg text-xs font-bold border border-blue-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-blue-500 mr-1"></span>Processing</span>';
        case 'delivered': return '<span class="bg-green-100 text-green-700 px-2 py-1 rounded-lg text-xs font-bold border border-green-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-green-500 mr-1"></span>Delivered</span>';
        case 'out_for_delivery': return '<span class="bg-teal-100 text-teal-700 px-2 py-1 rounded-lg text-xs font-bold border border-teal-200 flex w-fit items-center"><span class="w-2 h-2 rounded-full bg-teal-500 mr-1"></span>Out for Delivery</span>';
        default: return '<span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-lg text-xs font-bold border border-gray-200">' . ucfirst($status) . '</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RDC Manager Dashboard - <?php echo APP_NAME; ?></title>
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
                    <span class="material-symbols-rounded text-xl">local_shipping</span>
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-tight text-gray-800">ISDN</h1>
                    <p class="text-[9px] uppercase tracking-wider font-semibold text-teal-600">RDC Manager</p>
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
                    <p class="text-[10px] text-gray-500 font-medium">Branch ID: #<?php echo str_pad($rdc_id, 3, '0', STR_PAD_LEFT); ?></p>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto sidebar-scroll py-4 space-y-1">
            <div class="px-6 mb-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Main Menu</div>
            <button onclick="switchTab('dashboard')" class="w-full text-left sidebar-link active flex items-center px-6 py-3 text-sm font-medium text-gray-600 transition-colors group" id="nav-dashboard">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">dashboard</span> Dashboard
            </button>
            <button onclick="switchTab('orders')" class="w-full text-left sidebar-link flex items-center px-6 py-3 text-sm font-medium text-gray-600 transition-colors group" id="nav-orders">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">shopping_cart</span> Order Management
            </button>
            <button onclick="switchTab('routes')" class="w-full text-left sidebar-link flex items-center px-6 py-3 text-sm font-medium text-gray-600 transition-colors group" id="nav-routes">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">map</span> Route Planning
            </button>
            <div class="px-6 mt-6 mb-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Operations</div>
            <button onclick="switchTab('tracking')" class="w-full text-left sidebar-link flex items-center px-6 py-3 text-sm font-medium text-gray-600 transition-colors group" id="nav-tracking">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">location_on</span> Live Tracking
            </button>
            <button onclick="switchTab('inventory')" class="w-full text-left sidebar-link flex items-center px-6 py-3 text-sm font-medium text-gray-600 transition-colors group" id="nav-inventory">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">inventory_2</span> Inventory
            </button>
            <button onclick="switchTab('staff')" class="w-full text-left sidebar-link flex items-center px-6 py-3 text-sm font-medium text-gray-600 transition-colors group" id="nav-staff">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">group</span> Staff
            </button>
        </div>

        <div class="p-4 border-t border-gray-100">
            <a href="<?php echo BASE_PATH; ?>/index.php" class="flex items-center space-x-3 hover:bg-red-50 p-2 rounded-lg transition group">
                <span class="material-symbols-rounded text-gray-400 group-hover:text-red-500 transition">logout</span>
                <span class="text-sm font-medium text-gray-600 group-hover:text-red-600 transition">Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden relative bg-slate-50">
        <header class="h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-6 z-10 sticky top-0 md:static">
            <h2 class="text-lg font-bold text-gray-800" id="page-title">Dashboard Overview</h2>
            <div class="flex items-center space-x-4">
                <div class="hidden sm:block text-right mr-2">
                    <p class="text-sm font-bold text-gray-800"><?php echo htmlspecialchars($manager_name); ?></p>
                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Manager</p>
                </div>
                <div class="relative w-10 h-10 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center border border-teal-200 shadow-sm">
                    <span class="material-symbols-rounded">person</span>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth" id="content-area">
            <div id="tab-content-area">
                
                <!-- DASHBOARD TAB -->
                <div id="tab-dashboard" class="tab-content block space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="glass-card p-6 rounded-3xl border-l-4 border-blue-500">
                            <p class="text-xs font-bold text-gray-500 uppercase">Total Orders</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_orders'] ?? 0; ?></h3>
                        </div>
                        <div class="glass-card p-6 rounded-3xl border-l-4 border-yellow-500">
                            <p class="text-xs font-bold text-gray-500 uppercase">Pending Orders</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['pending_orders'] ?? 0; ?></h3>
                            <p class="text-xs text-gray-500 mt-1">Needs Approval</p>
                        </div>
                        <div class="glass-card p-6 rounded-3xl border-l-4 border-teal-500">
                            <p class="text-xs font-bold text-gray-500 uppercase">Active Deliveries</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['active_deliveries'] ?? 0; ?></h3>
                        </div>
                        <div class="glass-card p-6 rounded-3xl border-l-4 border-emerald-500">
                            <p class="text-xs font-bold text-gray-500 uppercase">Revenue</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-2">Rs. <?php echo number_format($stats['revenue'] ?? 0); ?></h3>
                        </div>
                    </div>

                    <div class="glass-card p-6 rounded-3xl">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-lg text-gray-800">Recent Orders</h3>
                            <button onclick="switchTab('orders')" class="text-sm text-teal-600 hover:underline">View All</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-gray-600">
                                <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b border-gray-100">
                                    <tr>
                                        <th class="px-4 py-3">Order ID</th>
                                        <th class="px-4 py-3">Customer</th>
                                        <th class="px-4 py-3">Date</th>
                                        <th class="px-4 py-3">Amount</th>
                                        <th class="px-4 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php if(empty($recentOrders)): ?>
                                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No recent orders found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($recentOrders as $order): ?>
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-4 py-3 font-bold text-gray-800"><?php echo $order['order_number']; ?></td>
                                            <td class="px-4 py-3"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td class="px-4 py-3"><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                            <td class="px-4 py-3">Rs. <?php echo number_format($order['total_amount']); ?></td>
                                            <td class="px-4 py-3"><?php echo getStatusBadge($order['status']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ORDERS TAB -->
                <div id="tab-orders" class="tab-content hidden space-y-6">
                    <div class="glass-card p-6 rounded-3xl">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-lg text-gray-800">All Confirmed Orders</h3>
                            <div class="flex space-x-2">
                                <button class="px-4 py-2 bg-gray-100 rounded-lg text-xs font-bold text-gray-600 hover:bg-gray-200">Export PDF</button>
                                <button onclick="openModal('orderModal')" class="px-4 py-2 bg-teal-600 rounded-lg text-xs font-bold text-white hover:bg-teal-700">New Order</button>
                            </div>
                        </div>
                         <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-gray-600">
                                <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b border-gray-100">
                                    <tr>
                                        <th class="px-4 py-3">Order ID</th>
                                        <th class="px-4 py-3">Customer</th>
                                        <th class="px-4 py-3">Date</th>
                                        <th class="px-4 py-3">Total</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach($allOrders as $order): ?>
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-4 py-3 font-bold text-gray-800"><?php echo $order['order_number']; ?></td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-800"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                            <div class="text-[10px] text-gray-400"><?php echo htmlspecialchars($order['email']); ?></div>
                                        </td>
                                        <td class="px-4 py-3"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td class="px-4 py-3 font-medium">Rs. <?php echo number_format($order['total_amount']); ?></td>
                                        <td class="px-4 py-3"><?php echo getStatusBadge($order['status']); ?></td>
                                        <td class="px-4 py-3 text-right">
                                            <button class="text-teal-600 hover:bg-teal-50 px-3 py-1 rounded-md text-xs font-bold border border-teal-200 transition">Details</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ROUTE PLANNING TAB -->
                <div id="tab-routes" class="tab-content hidden space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[600px]">
                        <!-- Left Panel: Create/Assign -->
                        <div class="glass-panel p-6 rounded-2xl border border-gray-100 flex flex-col h-full bg-white">
                            <h3 class="font-bold text-lg mb-4 text-gray-800">Route Planner</h3>
                            <div class="space-y-4 mb-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Route Name</label>
                                    <input type="text" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm outline-none focus:ring-2 focus:ring-teal-500" placeholder="e.g. Jaffna Town Morning">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Assign Driver</label>
                                    <select class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm outline-none focus:ring-2 focus:ring-teal-500">
                                        <option value="">Select a Driver...</option>
                                        <?php foreach($drivers as $driver): ?>
                                            <option value="<?= $driver['id'] ?>"><?= htmlspecialchars($driver['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button class="w-full bg-teal-600 text-white font-bold py-3 rounded-xl shadow-lg shadow-teal-500/30 hover:bg-teal-700 transition">
                                    Create Route
                                </button>
                            </div>
                            <div class="flex-1 overflow-y-auto border-t border-gray-100 pt-4">
                                <h4 class="text-xs font-bold text-gray-400 uppercase mb-3">Available Orders</h4>
                                <div class="space-y-2">
                                    <?php 
                                    $pendingOrders = array_filter($allOrders, fn($o) => $o['status'] === 'pending');
                                    if(empty($pendingOrders)): ?>
                                        <p class="text-xs text-center text-gray-400 py-4">No pending orders available.</p>
                                    <?php else: foreach($pendingOrders as $order): ?>
                                        <div class="p-3 border border-gray-100 rounded-xl hover:bg-gray-50 cursor-move draggable-order flex justify-between items-center group">
                                            <div>
                                                <div class="font-bold text-xs text-gray-700"><?php echo $order['order_number']; ?></div>
                                                <div class="text-[10px] text-gray-500"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                            </div>
                                            <div class="w-6 h-6 rounded-full border border-gray-200 flex items-center justify-center text-gray-300 group-hover:bg-teal-50 group-hover:text-teal-600 transition">
                                                <span class="material-symbols-rounded text-sm">add</span>
                                            </div>
                                        </div>
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>
                        </div>
                        <!-- Right Panel: Map -->
                        <div class="lg:col-span-2 glass-panel p-1 rounded-2xl border border-gray-100 bg-gray-100 h-full relative overflow-hidden">
                            <div id="map"></div>
                            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur px-4 py-2 rounded-lg shadow-sm z-[500] text-xs font-bold text-gray-600">
                                Interactive Delivery Map
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LIVE TRACKING TAB -->
                <div id="tab-tracking" class="tab-content hidden h-full">
                    <div class="h-[600px] rounded-3xl overflow-hidden relative border border-gray-200 shadow-inner">
                        <div id="liveMap"></div>
                        <div class="absolute bottom-6 left-6 right-6 z-[500] flex space-x-4 overflow-x-auto pb-2">
                            <?php foreach($drivers as $driver): ?>
                            <div class="bg-white/90 backdrop-blur-md p-3 rounded-xl shadow-lg border border-gray-100 min-w-[200px] flex items-center space-x-3 cursor-pointer hover:bg-white transition">
                                <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 font-bold text-xs">
                                    <?= substr($driver['name'],0,2) ?>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-800"><?= htmlspecialchars($driver['name']) ?></div>
                                    <div class="text-[10px] text-green-600 flex items-center">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse mr-1"></span> Active Now
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- INVENTORY TAB -->
                <div id="tab-inventory" class="tab-content hidden space-y-6">
                    <div class="glass-card p-6 rounded-3xl">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-lg text-gray-800">Current Inventory Levels</h3>
                            <button onclick="openModal('stockModal')" class="bg-teal-600 text-white px-4 py-2 rounded-lg text-xs font-bold shadow hover:bg-teal-700 transition">Request Stock</button>
                            <?php if(isset($success_msg)): ?>
                                <div class="absolute top-20 right-6 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-lg z-50 flex items-center animate-fade-in-down">
                                    <span class="material-symbols-rounded mr-2">check_circle</span>
                                    <?= htmlspecialchars($success_msg) ?>
                                    <button onclick="this.parentElement.remove()" class="ml-4 text-green-500 hover:text-green-800">&times;</button>
                                </div>
                            <?php endif; ?>
                            <?php if(isset($error_msg)): ?>
                                <div class="absolute top-20 right-6 bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-lg z-50 flex items-center animate-fade-in-down">
                                    <span class="material-symbols-rounded mr-2">error</span>
                                    <?= htmlspecialchars($error_msg) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach($inventory as $item): 
                                $isLow = $item['available_quantity'] <= $item['minimum_stock_level'];
                            ?>
                            <div class="bg-white border <?php echo $isLow ? 'border-red-200 bg-red-50/30' : 'border-gray-100'; ?> p-4 rounded-2xl flex items-center space-x-4 shadow-sm hover:shadow-md transition">
                                <img src="<?php echo $item['image_url']; ?>" alt="Product" class="w-16 h-16 rounded-xl object-cover bg-gray-200">
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($item['category']); ?></p>
                                    <div class="mt-2 flex items-center justify-between">
                                        <span class="text-sm font-bold <?php echo $isLow ? 'text-red-600' : 'text-teal-600'; ?>">
                                            <?php echo $item['available_quantity']; ?> Units
                                        </span>
                                        <?php if($isLow): ?>
                                            <span class="text-[10px] font-bold text-red-500 bg-red-100 px-2 py-0.5 rounded-full">Low Stock</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- STAFF TAB -->
                <div id="tab-staff" class="tab-content hidden space-y-6">
                    <div class="glass-card p-6 rounded-3xl">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-lg text-gray-800">Staff Management</h3>
                            <button class="text-teal-600 text-xs font-bold border border-teal-200 px-3 py-1 rounded hover:bg-teal-50">Add Staff</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="col-span-1 border-r border-gray-100 pr-6">
                                <h4 class="text-xs font-bold text-gray-400 uppercase mb-4">Drivers</h4>
                                <div class="space-y-3">
                                    <?php foreach($drivers as $driver): ?>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">DR</div>
                                            <div>
                                                <div class="text-sm font-bold text-gray-700"><?= htmlspecialchars($driver['name']) ?></div>
                                                <div class="text-[10px] text-green-600">Available</div>
                                            </div>
                                        </div>
                                        <button class="text-gray-400 hover:text-teal-600"><span class="material-symbols-rounded text-lg">more_vert</span></button>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="col-span-2 pl-6">
                                <h4 class="text-xs font-bold text-gray-400 uppercase mb-4">Performance Metrics</h4>
                                <div class="h-40 bg-gray-50 rounded-xl flex items-center justify-center text-gray-400 text-sm">
                                    Chart: Driver Efficiency & On-Time Delivery Rates
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ANALYTICS TAB -->
                <div id="tab-analytics" class="tab-content hidden text-center py-20 text-gray-500">Analytics Module Coming Soon</div>

            </div>
        </div>


        <!-- Stock Request Modal -->
        <div id="stockModal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all scale-100">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-lg text-gray-800">Request Stock from Head Office</h3>
                    <button onclick="closeModal('stockModal')" class="text-gray-400 hover:text-red-500 transition">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>
                <form method="POST" class="p-6 space-y-4">
                    <input type="hidden" name="request_stock" value="1">
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Select Product</label>
                        <div class="relative">
                            <select name="product_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 pl-10 text-sm outline-none focus:ring-2 focus:ring-teal-500 appearance-none">
                                <option value="">Choose a product...</option>
                                <?php foreach($allProducts as $p): ?>
                                    <option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="material-symbols-rounded absolute left-3 top-3 text-gray-400 text-lg">inventory_2</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Quantity Needed</label>
                        <div class="relative">
                            <input type="number" name="quantity" min="1" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 pl-10 text-sm outline-none focus:ring-2 focus:ring-teal-500" placeholder="e.g. 50">
                            <span class="material-symbols-rounded absolute left-3 top-3 text-gray-400 text-lg">numbers</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Reason / Notes</label>
                        <textarea name="reason" rows="3" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" placeholder="e.g. Low stock due to high demand..."></textarea>
                    </div>

                    <div class="pt-2 flex space-x-3">
                        <button type="button" onclick="closeModal('stockModal')" class="flex-1 py-3 rounded-xl font-bold text-gray-600 hover:bg-gray-100 transition">Cancel</button>
                        <button type="submit" class="flex-1 bg-teal-600 text-white py-3 rounded-xl font-bold shadow-lg shadow-teal-500/30 hover:bg-teal-700 transition">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- New Order Modal -->
        <div id="orderModal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all scale-100">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-lg text-gray-800">Create New Order</h3>
                    <button onclick="closeModal('orderModal')" class="text-gray-400 hover:text-red-500 transition">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>
                <form method="POST" class="p-6 space-y-4">
                    <input type="hidden" name="create_order" value="1">
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Customer</label>
                        <div class="relative">
                            <select name="customer_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 pl-10 text-sm outline-none focus:ring-2 focus:ring-teal-500 appearance-none">
                                <option value="">Select Customer...</option>
                                <?php foreach($allCustomers as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="material-symbols-rounded absolute left-3 top-3 text-gray-400 text-lg">person</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Product</label>
                        <div class="relative">
                            <select name="product_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 pl-10 text-sm outline-none focus:ring-2 focus:ring-teal-500 appearance-none">
                                <option value="">Choose Product...</option>
                                <?php foreach($allProducts as $p): ?>
                                    <option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="material-symbols-rounded absolute left-3 top-3 text-gray-400 text-lg">shopping_bag</span>
                        </div>
                    </div>

                     <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Quantity</label>
                        <div class="relative">
                            <input type="number" name="quantity" min="1" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 pl-10 text-sm outline-none focus:ring-2 focus:ring-teal-500" placeholder="e.g. 1">
                            <span class="material-symbols-rounded absolute left-3 top-3 text-gray-400 text-lg">numbers</span>
                        </div>
                    </div>

                    <div class="pt-2 flex space-x-3">
                        <button type="button" onclick="closeModal('orderModal')" class="flex-1 py-3 rounded-xl font-bold text-gray-600 hover:bg-gray-100 transition">Cancel</button>
                        <button type="submit" class="flex-1 bg-teal-600 text-white py-3 rounded-xl font-bold shadow-lg shadow-teal-500/30 hover:bg-teal-700 transition">Create Order</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        let mapInitialized = false;
        let map, liveMap;

        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.sidebar-link').forEach(el => {
                el.classList.remove('active');
                el.querySelector('span').classList.remove('text-teal-500');
                el.querySelector('span').classList.add('text-gray-400');
            });
            document.getElementById('tab-' + tabId).classList.remove('hidden');
            
            const activeLink = document.getElementById('nav-' + tabId);
            activeLink.classList.add('active');
            activeLink.querySelector('span').classList.remove('text-gray-400');
            activeLink.querySelector('span').classList.add('text-teal-500');
            
            const titles = {
                'dashboard': 'Dashboard Overview',
                'orders': 'Orders Management',
                'routes': 'Route Planning',
                'tracking': 'Live Tracking',
                'inventory': 'Inventory Control',
                'staff': 'Staff Management',
                'analytics': 'Performance Analytics'
            };
            document.getElementById('page-title').textContent = titles[tabId] || 'Dashboard';

            if (tabId === 'routes' && !mapInitialized) {
                setTimeout(() => {
                    initRouteMap();
                    mapInitialized = true;
                }, 100);
            }
            if (tabId === 'tracking') {
                 setTimeout(() => {
                    initLiveMap();
                }, 100);
            }
        }

        function initRouteMap() {
            if(map) return;
            map = L.map('map').setView([9.6615, 80.0255], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            L.marker([9.6615, 80.0255]).addTo(map).bindPopup('<b>Start Point</b><br>North RDC').openPopup();
        }

        function initLiveMap() {
             if(liveMap) {
                 liveMap.invalidateSize();
                 return; 
             }
            liveMap = L.map('liveMap').setView([9.6615, 80.0255], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(liveMap);
        }

        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>
</body>
</html>
