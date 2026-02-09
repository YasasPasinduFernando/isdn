<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 1. Authentication & Context Setup ---
try {
    // Attempt to find a Driver user
    // For demo/dev purposes, if no specific user is logged in, we fetch the first driver or a specific one
    $stmt = $pdo->prepare("SELECT u.id as user_id, u.username, u.email, u.rdc_id, r.rdc_name, d.id as driver_id, d.contact_number 
                           FROM users u 
                           JOIN rdcs r ON u.rdc_id = r.rdc_id 
                           LEFT JOIN rdc_drivers d ON u.id = d.user_id
                           WHERE u.role = 'rdc_driver' LIMIT 1");
    $stmt->execute();
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$driver) {
        // Fallback for demo
        $driver = ['user_id' => 0, 'username' => 'Demo Driver', 'email' => 'driver@example.com', 'rdc_id' => 1, 'rdc_name' => 'Northern RDC', 'driver_id' => 1, 'contact_number' => '0771234567'];
    }

    $user_id = $driver['user_id'];
    $rdc_id = $driver['rdc_id'];
    $driver_name = $driver['username'];
    $rdc_name = $driver['rdc_name'];
    $driver_id = $driver['driver_id'];

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$success_msg = '';
$error_msg = '';

// --- 2. Action Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A. Update Delivery Status
    if (isset($_POST['update_status'])) {
        $orderId = $_POST['order_id']; // This is the order ID, not delivery ID for simplicity in this demo context
        $newStatus = $_POST['status'];
        $notes = $_POST['notes'] ?? '';
        
        if ($orderId && $newStatus) {
            try {
                $pdo->beginTransaction();

                // Update Order Status
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $orderId]);

                // Update Delivery Record (if exists, or create one? Assuming it exists for assigned orders)
                // For 'delivered', set completed_date
                if ($newStatus === 'delivered') {
                    $stmt = $pdo->prepare("UPDATE order_deliveries SET completed_date = NOW() WHERE order_id = ? AND driver_id = ?");
                    $stmt->execute([$orderId, $driver_id]); // Matches if we have a record
                }

                $pdo->commit();
                $success_msg = "Delivery status updated to " . ucfirst($newStatus);
                // Refresh to reflect changes
                header("Location: " . $_SERVER['PHP_SELF'] . "?page=driver"); 
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $error_msg = "Failed to update status: " . $e->getMessage();
            }
        }
    }

    // B. Collect Payment (Mock)
    if (isset($_POST['collect_payment'])) {
        $orderId = $_POST['order_id'];
        $amount = $_POST['amount'];
        $method = $_POST['method']; // Cash/Cheque
        
        // In a real app, insertion into payments table
        // For now, we mock success
        $success_msg = "Payment of Rs. " . number_format($amount) . " collected via " . ucfirst($method);
    }
}

// --- 3. Data Fetching ---

// Fetch "My Deliveries Today"
// Query logic: Get orders assigned to this driver (via order_deliveries) or just RDC orders for demo if none assigned
// Assuming 'out_for_delivery', 'processing' are relevant statuses
// NOTE: For demo purposes, we might need to simulate some assigned orders if the table is empty.

// Check if any deliveries assigned
$check = $pdo->prepare("SELECT COUNT(*) FROM order_deliveries WHERE driver_id = ?");
$check->execute([$driver_id]);
$count = $check->fetchColumn();

// If no deliveries assigned, let's fetch some 'processing' orders from this RDC and pretend they are ours for the UI 
$deliveriesQuery = "
    SELECT o.id, o.order_number, o.total_amount, o.status, o.customer_id, 
           u.username as customer_name, u.email as customer_email,
           rc.address as delivery_address, rc.contact_number as customer_phone
    FROM orders o
    JOIN users u ON o.customer_id = u.id
    LEFT JOIN retail_customers rc ON u.id = rc.user_id -- Assuming address in retail_customers or mapped
    WHERE o.rdc_id = ? AND o.status IN ('processing', 'out_for_delivery', 'confirmed')
    ORDER BY CASE WHEN o.status = 'out_for_delivery' THEN 1 ELSE 2 END, o.created_at ASC
    LIMIT 10
";
// Note: If retail_customers doesn't link perfectly, we fallback to user data mock
$stmt = $pdo->prepare($deliveriesQuery);
$stmt->execute([$rdc_id]);
$deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mock Address if missing (common in these dev setups)
foreach ($deliveries as &$d) {
    if (empty($d['delivery_address'])) $d['delivery_address'] = "123 Main Street, Jaffna City Center";
    if (empty($d['customer_phone'])) $d['customer_phone'] = "077" . mt_rand(1000000, 9999999);
}
unset($d);

// Separate into Active/Next/Completed/Pending for UI
$activeDelivery = null;
$pendingDeliveries = [];
$completedDeliveries = []; // Fetch delivered separately if needed, or filter form above list if we included 'delivered'

foreach($deliveries as $d) {
    if ($d['status'] === 'out_for_delivery' && !$activeDelivery) {
        $activeDelivery = $d;
    } else {
        $pendingDeliveries[] = $d;
    }
}
// If no specific 'out_for_delivery', take the first one as 'Next Up'
if (!$activeDelivery && count($pendingDeliveries) > 0) {
    $activeDelivery = array_shift($pendingDeliveries);
}


// Stats
$totalAssigned = count($deliveries); // + completed count if we had it
$completedCount = 0; // Mock or fetch real count of today's delivered
$remainingCount = count($pendingDeliveries) + ($activeDelivery ? 1 : 0);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/custom.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0" />
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
        #map { height: 100%; width: 100%; border-radius: 1rem; z-index: 0; }
        /* Mobile Optimizations */
        @media (max-width: 640px) {
            .mobile-nav-bottom {
                position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-around; padding: 10px; z-index: 50;
            }
        }
    </style>
</head>
<body class="bg-slate-50 font-outfit h-screen flex overflow-hidden text-gray-800">

    <!-- Desktop Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-100 flex flex-col z-20 shadow-sm hidden md:flex">
        <div class="h-20 flex items-center px-6 border-b border-gray-100">
            <div class="flex items-center space-x-3">
                <div class="bg-gradient-to-br from-teal-500 to-emerald-600 p-2 rounded-xl shadow-md text-white flex items-center justify-center">
                    <span class="material-symbols-rounded text-2xl">local_shipping</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-gray-800">ISDN</h1>
                    <p class="text-[10px] uppercase tracking-wider font-semibold text-teal-600">Driver Portal</p>
                </div>
            </div>
        </div>

        <div class="px-6 py-6 border-b border-gray-50 bg-gray-50/50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-white border border-gray-200 text-teal-600 flex items-center justify-center shadow-sm">
                    <span class="material-symbols-rounded text-xl">person</span>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-gray-800"><?php echo htmlspecialchars($driver_name); ?></h3>
                    <p class="text-[10px] text-gray-500 font-medium"><?php echo htmlspecialchars($rdc_name); ?></p>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between bg-white p-2 rounded-lg border border-gray-100">
                <span class="text-xs font-bold text-gray-500 uppercase ml-1">Status</span>
                <span class="flex items-center text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded-md">
                    <span class="w-2 h-2 rounded-full bg-green-500 mr-2 animate-pulse"></span> Active
                </span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto py-6 space-y-1">
             <button onclick="switchTab('overview')" class="w-full text-left sidebar-link active flex items-center px-6 py-3.5 text-sm font-medium text-gray-600 transition-colors group">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">dashboard</span> Today's Route
            </button>
            <button onclick="switchTab('history')" class="w-full text-left sidebar-link flex items-center px-6 py-3.5 text-sm font-medium text-gray-600 transition-colors group">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">history</span> Delivery History
            </button>
            <button onclick="switchTab('wallet')" class="w-full text-left sidebar-link flex items-center px-6 py-3.5 text-sm font-medium text-gray-600 transition-colors group">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">account_balance_wallet</span> Cash Collection
            </button>
             <button onclick="switchTab('profile')" class="w-full text-left sidebar-link flex items-center px-6 py-3.5 text-sm font-medium text-gray-600 transition-colors group">
                <span class="material-symbols-rounded mr-3 text-gray-400 group-hover:text-teal-500 transition">settings</span> My Profile
            </button>
        </div>

        <div class="p-4 border-t border-gray-100">
            <a href="index.php?page=home" class="flex items-center justify-center space-x-2 text-red-500 hover:bg-red-50 p-3 rounded-xl transition w-full font-bold text-sm">
                <span class="material-symbols-rounded">logout</span>
                <span>End Shift</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-slate-50 w-full">
        <!-- Mobile Header -->
        <header class="h-16 bg-white border-b border-gray-100 flex md:hidden items-center justify-between px-4 z-10 sticky top-0">
             <div class="flex items-center space-x-2">
                <div class="bg-teal-600 p-1.5 rounded-lg text-white">
                    <span class="material-symbols-rounded text-lg">local_shipping</span>
                </div>
                <span class="font-bold text-gray-800">ISDN Driver</span>
            </div>
             <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                <span class="material-symbols-rounded text-gray-600">person</span>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth pb-20 md:pb-8">
            <?php if($success_msg): ?>
                <div class="glass-card bg-green-50 border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm relative z-20">
                    <span class="material-symbols-rounded mr-2 text-green-600">check_circle</span> <?= $success_msg ?>
                </div>
            <?php endif; ?>

            <!-- OVERVIEW TAB -->
            <div id="tab-overview" class="tab-content space-y-6">
                <!-- Stats Row -->
                <div class="grid grid-cols-3 gap-3 md:gap-6">
                    <div class="glass-card p-3 md:p-5 rounded-2xl border-b-4 border-teal-500 text-center">
                        <div class="text-xs font-bold text-gray-500 uppercase">Assigned</div>
                        <div class="text-2xl md:text-3xl font-bold text-gray-800"><?= $totalAssigned ?></div>
                    </div>
                    <div class="glass-card p-3 md:p-5 rounded-2xl border-b-4 border-orange-500 text-center">
                         <div class="text-xs font-bold text-gray-500 uppercase">Remaining</div>
                        <div class="text-2xl md:text-3xl font-bold text-gray-800"><?= $remainingCount ?></div>
                    </div>
                    <div class="glass-card p-3 md:p-5 rounded-2xl border-b-4 border-green-500 text-center">
                         <div class="text-xs font-bold text-gray-500 uppercase">Completed</div>
                        <div class="text-2xl md:text-3xl font-bold text-gray-800"><?= $completedCount ?></div>
                    </div>
                </div>

                <!-- Current Active Delivery -->
                <?php if($activeDelivery): ?>
                <div class="glass-card rounded-3xl overflow-hidden border border-teal-100 shadow-lg relative">
                    <div class="absolute top-0 right-0 p-4">
                        <span class="bg-teal-100 text-teal-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide animate-pulse">Current Stop</span>
                    </div>
                    <div class="p-6 md:p-8">
                        <div class="flex items-start mb-6">
                            <div class="mr-4 mt-1">
                                <div class="w-12 h-12 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center ring-4 ring-teal-50/50">
                                    <span class="material-symbols-rounded text-2xl">location_on</span>
                                </div>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800 leading-tight mb-1"><?= htmlspecialchars($activeDelivery['customer_name']) ?></h2>
                                <p class="text-lg text-gray-600 font-medium"><?= htmlspecialchars($activeDelivery['delivery_address']) ?></p>
                                <p class="text-sm text-gray-400 mt-1">Order #<?= $activeDelivery['order_number'] ?> • <span class="text-teal-600 font-bold"><?= count(explode(',',$activeDelivery['customer_phone'])) ?> items</span></p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <a href="https://www.google.com/maps/dir/?api=1&destination=<?= urlencode($activeDelivery['delivery_address']) ?>" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-xl font-bold text-center flex items-center justify-center transition shadow-md shadow-blue-200">
                                <span class="material-symbols-rounded mr-2">navigation</span> Navigate
                            </a>
                            <a href="tel:<?= $activeDelivery['customer_phone'] ?>" class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 p-3 rounded-xl font-bold text-center flex items-center justify-center transition">
                                <span class="material-symbols-rounded mr-2">call</span> Call
                            </a>
                            <button onclick="openModal('modal-items-<?= $activeDelivery['id'] ?>')" class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 p-3 rounded-xl font-bold text-center flex items-center justify-center transition">
                                <span class="material-symbols-rounded mr-2">receipt_long</span> Details
                            </button>
                            <button onclick="openModal('modal-status-<?= $activeDelivery['id'] ?>')" class="bg-teal-600 hover:bg-teal-700 text-white p-3 rounded-xl font-bold text-center flex items-center justify-center transition shadow-md shadow-teal-200">
                                <span class="material-symbols-rounded mr-2">check_circle</span> Update
                            </button>
                        </div>
                    </div>
                    
                    <!-- Integrated Map Preview -->
                    <div class="h-48 bg-gray-100 border-t border-gray-100" id="currentMap"></div>
                </div>
                <?php else: ?>
                <div class="glass-card rounded-3xl p-10 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                        <span class="material-symbols-rounded text-3xl">celebration</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">All caught up!</h3>
                    <p class="text-gray-500">No active deliveries at the moment.</p>
                </div>
                <?php endif; ?>

                <!-- Upcoming Route -->
                <div class="glass-card rounded-3xl p-6">
                    <h3 class="font-bold text-gray-800 mb-6 flex items-center">
                        <span class="material-symbols-rounded text-orange-500 mr-2">alt_route</span> Next Up
                    </h3>
                    <div class="space-y-0 relative border-l-2 border-gray-100 ml-3">
                        <?php if (empty($pendingDeliveries)): ?>
                            <p class="text-gray-400 text-sm ml-6 italic">No pending deliveries.</p>
                        <?php else: ?>
                            <?php foreach($pendingDeliveries as $index => $pd): ?>
                            <div class="mb-8 ml-6 relative group">
                                <span class="absolute -left-[31px] top-0 bg-white border-2 border-gray-200 w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-gray-500 group-hover:border-teal-500 group-hover:text-teal-600 transition">
                                    <?= $index + 2 ?>
                                </span>
                                <div class="bg-white border border-gray-100 p-4 rounded-2xl shadow-sm hover:shadow-md transition">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-bold text-gray-800"><?= htmlspecialchars($pd['customer_name']) ?></h4>
                                            <p class="text-sm text-gray-500 line-clamp-1"><?= htmlspecialchars($pd['delivery_address']) ?></p>
                                        </div>
                                        <span class="text-xs font-bold text-gray-400 bg-gray-50 px-2 py-1 rounded">#<?= $pd['order_number'] ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- HISTORY TAB -->
            <div id="tab-history" class="tab-content hidden space-y-6">
                <!-- Similar List View for History -->
                 <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-300">
                    <span class="material-symbols-rounded text-4xl text-gray-300 mb-2">history</span>
                    <p class="text-gray-500 font-medium">No history available for today.</p>
                </div>
            </div>

            <!-- WALLET TAB -->
            <div id="tab-wallet" class="tab-content hidden space-y-6">
                <div class="glass-card p-6 rounded-3xl bg-gradient-to-br from-gray-900 to-gray-800 text-white">
                    <p class="text-sm text-gray-400 font-bold uppercase mb-1">Total Cash Collected</p>
                    <h2 class="text-4xl font-bold">Rs. 45,250</h2>
                    <div class="mt-6 flex space-x-3">
                        <button class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-lg text-sm font-bold backdrop-blur-sm transition">View Breakdown</button>
                        <button class="bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-lg shadow-emerald-900/20 transition">Deposit Cash</button>
                    </div>
                </div>
            </div>

             <!-- PROFILE TAB -->
             <div id="tab-profile" class="tab-content hidden space-y-6">
                 <div class="glass-card p-8 rounded-3xl text-center">
                    <div class="w-24 h-24 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4 text-teal-600">
                        <span class="material-symbols-rounded text-5xl">person</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800"><?= $driver_name ?></h2>
                    <p class="text-gray-500"><?= $rdc_name ?></p>
                    <div class="mt-6 border-t border-gray-100 pt-6 text-left">
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <span class="text-gray-500 text-sm">Vehicle No</span>
                            <span class="font-bold text-gray-800">WP CA-4521</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <span class="text-gray-500 text-sm">License No</span>
                            <span class="font-bold text-gray-800">B12345678</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <span class="text-gray-500 text-sm">Phone</span>
                            <span class="font-bold text-gray-800"><?= $driver['contact_number'] ?></span>
                        </div>
                    </div>
                 </div>
            </div>

        </div>

        <!-- Mobile Bottom Nav -->
        <div class="mobile-nav-bottom md:hidden shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
            <button onclick="switchTab('overview')" class="flex flex-col items-center p-2 text-teal-600">
                <span class="material-symbols-rounded">dashboard</span>
                <span class="text-[10px] font-bold mt-1">Route</span>
            </button>
            <button onclick="switchTab('history')" class="flex flex-col items-center p-2 text-gray-400 hover:text-teal-500">
                <span class="material-symbols-rounded">history</span>
                <span class="text-[10px] font-bold mt-1">History</span>
            </button>
            <button onclick="switchTab('wallet')" class="flex flex-col items-center p-2 text-gray-400 hover:text-teal-500">
                <span class="material-symbols-rounded">account_balance_wallet</span>
                <span class="text-[10px] font-bold mt-1">Wallet</span>
            </button>
             <button onclick="switchTab('profile')" class="flex flex-col items-center p-2 text-gray-400 hover:text-teal-500">
                <span class="material-symbols-rounded">person</span>
                <span class="text-[10px] font-bold mt-1">Profile</span>
            </button>
        </div>
    </main>

    <!-- Modals for Active Order -->
    <?php if($activeDelivery): ?>
    
    <!-- Status Update Modal -->
    <div id="modal-status-<?= $activeDelivery['id'] ?>" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-end md:items-center justify-center p-4">
        <form method="POST" class="bg-white rounded-t-3xl md:rounded-3xl w-full max-w-md p-6 shadow-2xl relative animate-up">
            <button type="button" onclick="closeModal('modal-status-<?= $activeDelivery['id'] ?>')" class="absolute top-4 right-4 text-gray-400">
                <span class="material-symbols-rounded">close</span>
            </button>
            <h3 class="text-lg font-bold text-gray-800 mb-6">Update Delivery Status</h3>
            <input type="hidden" name="order_id" value="<?= $activeDelivery['id'] ?>">
            
            <div class="grid grid-cols-2 gap-3 mb-6">
                 <label class="cursor-pointer">
                    <input type="radio" name="status" value="out_for_delivery" class="peer hidden">
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-center peer-checked:bg-blue-50 peer-checked:border-blue-500 peer-checked:text-blue-700 transition">
                        <span class="material-symbols-rounded block mb-1">local_shipping</span>
                        <span class="text-xs font-bold">On the Way</span>
                    </div>
                </label>
                 <label class="cursor-pointer">
                    <input type="radio" name="status" value="arrived" class="peer hidden">
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-center peer-checked:bg-orange-50 peer-checked:border-orange-500 peer-checked:text-orange-700 transition">
                        <span class="material-symbols-rounded block mb-1">location_on</span>
                        <span class="text-xs font-bold">Arrived</span>
                    </div>
                </label>
                 <label class="cursor-pointer">
                    <input type="radio" name="status" value="delivered" class="peer hidden" checked>
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-center peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:text-green-700 transition">
                        <span class="material-symbols-rounded block mb-1">check_circle</span>
                        <span class="text-xs font-bold">Delivered</span>
                    </div>
                </label>
                 <label class="cursor-pointer">
                    <input type="radio" name="status" value="failed" class="peer hidden">
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-center peer-checked:bg-red-50 peer-checked:border-red-500 peer-checked:text-red-700 transition">
                         <span class="material-symbols-rounded block mb-1">cancel</span>
                        <span class="text-xs font-bold">Failed</span>
                    </div>
                </label>
            </div>
            
            <div class="mb-6">
                <label class="text-xs font-bold text-gray-500 uppercase mb-2 block">Proof / Notes</label>
                <textarea name="notes" class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-teal-500 outline-none" rows="3" placeholder="e.g. Received by Mr. Perera"></textarea>
                <div class="flex gap-2 mt-2">
                    <button type="button" class="flex-1 bg-gray-100 hover:bg-gray-200 py-2 rounded-lg text-xs font-bold text-gray-600 flex items-center justify-center">
                        <span class="material-symbols-rounded text-sm mr-1">signature</span> Signature
                    </button>
                    <button type="button" class="flex-1 bg-gray-100 hover:bg-gray-200 py-2 rounded-lg text-xs font-bold text-gray-600 flex items-center justify-center">
                        <span class="material-symbols-rounded text-sm mr-1">photo_camera</span> Photo
                    </button>
                </div>
            </div>

            <button type="submit" name="update_status" class="w-full bg-teal-600 text-white py-3.5 rounded-xl font-bold hover:bg-teal-700 transition shadow-lg shadow-teal-100">Confirm Update</button>
        </form>
    </div>

    <!-- Order Items Modal -->
    <div id="modal-items-<?= $activeDelivery['id'] ?>" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl relative">
            <button type="button" onclick="closeModal('modal-items-<?= $activeDelivery['id'] ?>')" class="absolute top-4 right-4 text-gray-400">
                <span class="material-symbols-rounded">close</span>
            </button>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Order Details</h3>
            <p class="text-sm text-gray-500 mb-6">Order #<?= $activeDelivery['order_number'] ?></p>

            <div class="bg-gray-50 rounded-xl p-4 mb-4 space-y-3">
                <!-- Mock Items for UI -->
                <div class="flex justify-between items-center text-sm">
                    <div>
                        <span class="font-bold text-gray-800">Coca Cola 1.5L</span>
                        <div class="text-xs text-gray-500">10 Bottles x Rs. 450</div>
                    </div>
                    <span class="font-bold text-gray-800">Rs. 4,500</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <div>
                        <span class="font-bold text-gray-800">Munchee Biscuits</span>
                        <div class="text-xs text-gray-500">5 Boxes x Rs. 1200</div>
                    </div>
                    <span class="font-bold text-gray-800">Rs. 6,000</span>
                </div>
                <div class="border-t border-gray-200 pt-3 flex justify-between items-center">
                    <span class="font-bold text-gray-600 uppercase text-xs">Total Amount</span>
                    <span class="font-bold text-teal-600 text-lg">Rs. <?= number_format($activeDelivery['total_amount']) ?></span>
                </div>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-100 p-3 rounded-xl text-xs text-yellow-800 font-medium flex items-start mb-6">
                <span class="material-symbols-rounded text-sm mr-2 mt-0.5">info</span>
                Special Instruction: Deliver to the back gate. Call before arrival.
            </div>

            <button onclick="openModal('modal-payment-<?= $activeDelivery['id'] ?>')" class="w-full bg-emerald-600 text-white py-3.5 rounded-xl font-bold hover:bg-emerald-700 transition shadow-lg shadow-emerald-100 flex items-center justify-center">
                <span class="material-symbols-rounded mr-2">payments</span> Collect Payment
            </button>
        </div>
    </div>
    
    <!-- Payment Modal -->
    <div id="modal-payment-<?= $activeDelivery['id'] ?>" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-end md:items-center justify-center p-4">
        <form method="POST" class="bg-white rounded-t-3xl md:rounded-3xl w-full max-w-md p-6 shadow-2xl relative animate-up">
            <button type="button" onclick="closeModal('modal-payment-<?= $activeDelivery['id'] ?>')" class="absolute top-4 right-4 text-gray-400">
                <span class="material-symbols-rounded">close</span>
            </button>
            <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                 <span class="material-symbols-rounded text-emerald-600 mr-2">payments</span> Collect Payment
            </h3>
            <input type="hidden" name="order_id" value="<?= $activeDelivery['id'] ?>">
            
            <div class="bg-gray-900 text-white p-6 rounded-2xl text-center mb-6">
                <p class="text-sm text-gray-400 uppercase font-bold">Amount Due</p>
                <h2 class="text-4xl font-bold mt-1">Rs. <?= number_format($activeDelivery['total_amount']) ?></h2>
            </div>

            <div class="space-y-4">
                 <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Payment Method</label>
                    <select name="method" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="cash">Cash</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div>
                     <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Received Amount</label>
                     <input type="number" name="amount" value="<?= $activeDelivery['total_amount'] ?>" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none font-bold text-gray-800">
                </div>
            </div>

            <button type="submit" name="collect_payment" class="w-full bg-emerald-600 text-white py-3.5 rounded-xl font-bold hover:bg-emerald-700 transition shadow-lg shadow-emerald-100 mt-6">Confirm Collection</button>
        </form>
    </div>

    <?php endif; ?>

    <script>
        function switchTab(id) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.getElementById('tab-'+id).classList.remove('hidden');
            
            // Map Init if viewing map tab or refreshing
            if(id === 'overview' && document.getElementById('currentMap') && !window.currentMapObj) {
                setTimeout(initMap, 100);
            }
        }

        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function initMap() {
             var mapContainer = document.getElementById('currentMap');
             if(mapContainer) {
                 window.currentMapObj = L.map('currentMap', {zoomControl: false}).setView([9.6615, 80.0255], 13);
                 L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(window.currentMapObj);
                 L.marker([9.6615, 80.0255]).addTo(window.currentMapObj);
             }
        }
        
        // Auto init map on load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initMap, 500);
        });
    </script>
</body>
</html>
