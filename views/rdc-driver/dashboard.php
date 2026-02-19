<?php
// Start output buffering to handle any output before redirects
ob_start();

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

// Check for session messages
$success_msg = isset($_SESSION['success_msg']) ? $_SESSION['success_msg'] : '';
$error_msg = isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : '';

// Clear session messages after reading
if ($success_msg) unset($_SESSION['success_msg']);
if ($error_msg) unset($_SESSION['error_msg']);

// Handle AJAX requests for order updates (polling)
if (isset($_GET['action']) && $_GET['action'] === 'get_orders' && 
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    
    // Fetch current orders
    $ordersQuery = "
        SELECT o.id, o.order_number, o.total_amount, o.status, o.customer_id, 
               rc.name as customer_name, u.email as customer_email,
               rc.address as delivery_address, rc.contact_number as customer_phone
        FROM orders o
        JOIN retail_customers rc ON o.customer_id = rc.id
        JOIN users u ON rc.user_id = u.id
        JOIN users placed_by_user ON o.placed_by = placed_by_user.id
        WHERE placed_by_user.rdc_id = ? 
        AND DATE(o.created_at) = CURDATE()
        ORDER BY CASE 
            WHEN o.status = 'out_for_delivery' THEN 1 
            WHEN o.status = 'delivered' THEN 3
            WHEN o.status = 'failed' THEN 4
            ELSE 2 
        END, o.created_at ASC
    ";
    $stmt = $pdo->prepare($ordersQuery);
    $stmt->execute([$rdc_id]);
    $currentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'orders' => $currentOrders,
        'timestamp' => time()
    ]);
    exit;
}

// --- 2. Action Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A. Update Delivery Status
    if (isset($_POST['update_status'])) {
        $orderId = $_POST['order_id'] ?? null;
        $newStatus = $_POST['status'] ?? null;
        $notes = $_POST['notes'] ?? '';
        
        if ($orderId && $newStatus) {
            try {
                // Debug Log
                file_put_contents('debug_post.log', date('Y-m-d H:i:s') . " - Updating Order $orderId to $newStatus\n", FILE_APPEND);

                $pdo->beginTransaction();

                // First, get current status for debugging
                $currentStmt = $pdo->prepare("SELECT status, order_number FROM orders WHERE id = ?");
                $currentStmt->execute([$orderId]);
                $currentOrder = $currentStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$currentOrder) {
                    throw new Exception("Order not found with ID: $orderId");
                }

                // Update Order Status
                $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
                $updateResult = $stmt->execute([$newStatus, $orderId]);
                
                if (!$updateResult) {
                    throw new Exception("Failed to update order status");
                }

                // Update or Create Delivery Record
                if (in_array($newStatus, ['delivered', 'failed', 'out_for_delivery', 'cancelled', 'arrived'])) {
                    // Check if delivery record exists
                    $checkStmt = $pdo->prepare("SELECT id FROM order_deliveries WHERE order_id = ?");
                    $checkStmt->execute([$orderId]);
                    
                    if ($checkStmt->rowCount() > 0) {
                        // Update existing delivery record
                        if ($newStatus === 'delivered' || $newStatus === 'failed' || $newStatus === 'cancelled') {
                            $stmt = $pdo->prepare("UPDATE order_deliveries SET completed_date = NOW() WHERE order_id = ?");
                            $stmt->execute([$orderId]);
                        }
                    } else {
                        // Create new delivery record
                        if (!$driver_id) {
                            // Try to recover driver_id if missing from session/context
                            $dStmt = $pdo->prepare("SELECT id FROM rdc_drivers WHERE user_id = ?");
                            $dStmt->execute([$user_id]);
                            $dRow = $dStmt->fetch();
                            if ($dRow) {
                                $driver_id = $dRow['id'];
                            } else {
                                throw new Exception("Driver ID not found. Cannot create delivery record.");
                            }
                        }

                        $stmt = $pdo->prepare("INSERT INTO order_deliveries (order_id, driver_id, delivery_date, completed_date) VALUES (?, ?, NOW(), ?)");
                        $completedDate = ($newStatus === 'delivered' || $newStatus === 'failed' || $newStatus === 'cancelled') ? date('Y-m-d H:i:s') : null;
                        $stmt->execute([$orderId, $driver_id, $completedDate]);
                    }
                }

                $pdo->commit();
                
                $_SESSION['success_msg'] = "Status updated to " . ucfirst(str_replace('_', ' ', $newStatus));
                if (ob_get_level()) ob_end_clean();
                header("Location: index.php?page=rdc-driver-dashboard&v=" . time()); 
                exit;

            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                file_put_contents('debug_error.log', date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
                $_SESSION['error_msg'] = "Failed to update status: " . $e->getMessage();
                if (ob_get_level()) ob_end_clean();
                header("Location: index.php?page=rdc-driver-dashboard");
                exit;
            }
        } else {
            $_SESSION['error_msg'] = "Missing required data for status update";
            if (ob_get_level()) ob_end_clean();
            header("Location: index.php?page=rdc-driver-dashboard");
            exit;
        }
    }

    // B. Collect Payment (Mock)
    if (isset($_POST['collect_payment'])) {
        $orderId = $_POST['order_id'];
        $amount = $_POST['amount'];
        $method = $_POST['method']; // Cash/Cheque
        
        // In a real app, insertion into payments table
        // For now, we mock success
        $_SESSION['success_msg'] = "Payment of Rs. " . number_format($amount) . " collected via " . ucfirst($method);
        ob_end_clean();
        header("Location: index.php?page=rdc-driver-dashboard");
        exit;
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

// Today's Deliveries (all statuses for today)
$deliveriesQuery = "
    SELECT o.id, o.order_number, o.total_amount, o.status, o.customer_id, 
           rc.name as customer_name, u.email as customer_email,
           rc.address as delivery_address, rc.contact_number as customer_phone
    FROM orders o
    JOIN retail_customers rc ON o.customer_id = rc.id
    JOIN users u ON rc.user_id = u.id
    JOIN users placed_by_user ON o.placed_by = placed_by_user.id
    WHERE placed_by_user.rdc_id = ? 
    AND DATE(o.created_at) = CURDATE()
    ORDER BY CASE 
        WHEN o.status = 'out_for_delivery' THEN 1 
        WHEN o.status = 'delivered' THEN 3
        WHEN o.status = 'failed' THEN 4
        ELSE 2 
    END, o.created_at ASC
";
$stmt = $pdo->prepare($deliveriesQuery);
$stmt->execute([$rdc_id]);
$deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Delivery History (completed today)
$historyQuery = "
    SELECT o.id, o.order_number, o.total_amount, o.status, o.updated_at,
           rc.name as customer_name, rc.address as delivery_address, 
           rc.contact_number as customer_phone,
           od.completed_date
    FROM orders o
    JOIN retail_customers rc ON o.customer_id = rc.id
    JOIN users placed_by_user ON o.placed_by = placed_by_user.id
    LEFT JOIN order_deliveries od ON o.id = od.order_id
    WHERE placed_by_user.rdc_id = ? 
    AND o.status IN ('delivered', 'cancelled', 'failed')
    AND DATE(o.updated_at) = CURDATE()
    ORDER BY o.updated_at DESC
";
$stmt = $pdo->prepare($historyQuery);
$stmt->execute([$rdc_id]);
$completedDeliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate Total Cash Collected Today
$totalCashCollected = 0;
foreach ($completedDeliveries as $d) {
    if ($d['status'] === 'delivered') {
        $totalCashCollected += $d['total_amount'];
    }
}

// All deliveries for map (include delivered for color coding)
$allDeliveriesQuery = "
    SELECT o.id, o.order_number, o.total_amount, o.status, o.customer_id, 
           rc.name as customer_name, rc.address as delivery_address, 
           rc.contact_number as customer_phone
    FROM orders o
    JOIN retail_customers rc ON o.customer_id = rc.id
    JOIN users placed_by_user ON o.placed_by = placed_by_user.id
    WHERE placed_by_user.rdc_id = ? 
    AND DATE(o.created_at) = CURDATE()
    ORDER BY o.created_at ASC
";
$stmt = $pdo->prepare($allDeliveriesQuery);
$stmt->execute([$rdc_id]);
$allDeliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate into Active/Next for UI
$activeDelivery = null;
$pendingDeliveries = [];

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
$totalAssigned = count($deliveries) + count($completedDeliveries);
$completedCount = count($completedDeliveries);
$remainingCount = count($deliveries);

?>
<?php
// Debug: Log the deliveries data to verify status
file_put_contents('debug_view.log', date('Y-m-d H:i:s') . " - View Data: " . print_r($deliveries, true) . "\n", FILE_APPEND);

// Include header
require_once __DIR__ . '/../../includes/header.php';
?>
<!-- Leaflet Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<style>
    .glass-card { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.5); box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07); }
    .glass-panel { background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.4); }
    #map { height: 100%; width: 100%; border-radius: 1rem; z-index: 0; }
    /* Override Leaflet default div icon styles */
    .custom-marker {
        background: none !important;
        border: none !important;
        box-shadow: none !important;
    }
    /* Enforce checked state styling */
    input:checked + div {
        background-color: #eff6ff !important;
        border-color: #3b82f6 !important;
        color: #1d4ed8 !important;
    }
</style>

<div class="flex flex-1 overflow-hidden h-full flex-col bg-gradient-to-br from-indigo-50 via-white to-cyan-50 font-outfit text-gray-800">

    <!-- Mobile Header Removed -->


    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative w-full">


        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth pb-20 md:pb-8">
            <?php if($success_msg): ?>
                <div class="glass-card bg-green-50 border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm relative z-20">
                    <span class="material-symbols-rounded mr-2 text-green-600">check_circle</span> <?= htmlspecialchars($success_msg) ?>
                </div>
            <?php endif; ?>
            <?php if($error_msg): ?>
                <div class="glass-card bg-red-50 border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm relative z-20">
                    <span class="material-symbols-rounded mr-2 text-red-600">error</span> <?= htmlspecialchars($error_msg) ?>
                </div>
            <?php endif; ?>

            <!-- OVERVIEW TAB -->
            <div id="tab-overview" class="tab-content space-y-6">
                <!-- Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Assigned -->
                    <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift !border-l-4 !border-blue-500">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Assigned</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']"><?= $totalAssigned ?></h3>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-blue-100/50 flex items-center justify-center text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                                <span class="material-symbols-rounded">local_shipping</span>
                            </div>
                        </div>
                    </div>

                    <!-- Remaining -->
                    <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift !border-l-4 !border-orange-500">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Remaining</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']"><?= $remainingCount ?></h3>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-orange-100/50 flex items-center justify-center text-orange-600 group-hover:bg-orange-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                                <span class="material-symbols-rounded">pending_actions</span>
                            </div>
                        </div>
                    </div>

                    <!-- Completed -->
                    <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift !border-l-4 !border-green-500">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Completed</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']"><?= $completedCount ?></h3>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-green-100/50 flex items-center justify-center text-green-600 group-hover:bg-green-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                                <span class="material-symbols-rounded">task_alt</span>
                            </div>
                        </div>
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

                <!-- Today's Route -->
                <div class="glass-card rounded-3xl p-6">
                    <h3 class="font-bold text-gray-800 mb-6 flex items-center">
                        <span class="material-symbols-rounded text-orange-500 mr-2">alt_route</span> Today's Route (<?= count($deliveries) ?> orders)
                    </h3>
                    <div class="space-y-3">
                        <?php if (empty($deliveries)): ?>
                            <p class="text-gray-400 text-sm italic">No deliveries for today.</p>
                        <?php else: ?>
                            <?php foreach($deliveries as $index => $d): ?>
                            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-md transition overflow-hidden">
                                <div class="p-4 cursor-pointer" onclick="toggleActions('actions-<?= $d['id'] ?>')">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-bold text-gray-600">
                                                    <?= $index + 1 ?>
                                                </span>
                                                <h4 class="font-bold text-gray-800"><?= htmlspecialchars($d['customer_name']) ?></h4>
                                            </div>
                                            <p class="text-sm text-gray-500 ml-8"><?= htmlspecialchars($d['delivery_address']) ?></p>
                                        </div>
                                        <div class="flex flex-col items-end gap-2">
                                            <span class="text-xs font-bold px-3 py-1 rounded-full 
                                                <?php
                                                switch($d['status']) {
                                                    case 'out_for_delivery':
                                                        echo 'bg-orange-100 text-orange-700';
                                                        break;
                                                    case 'arrived':
                                                        echo 'bg-purple-100 text-purple-700';
                                                        break;
                                                    case 'delivered':
                                                        echo 'bg-green-100 text-green-700';
                                                        break;
                                                    case 'failed':
                                                        echo 'bg-red-100 text-red-700';
                                                        break;
                                                    case 'cancelled':
                                                        echo 'bg-gray-100 text-gray-700';
                                                        break;
                                                    default:
                                                        echo 'bg-blue-100 text-blue-700';
                                                }
                                                ?>">
                                                <?= ucfirst(str_replace('_', ' ', $d['status'])) ?>
                                            </span>
                                            <span class="text-xs text-gray-400">#<?= $d['order_number'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Action Panel (toggled on click) -->
                                <div id="actions-<?= $d['id'] ?>" class="hidden border-t border-gray-100 bg-gray-50 px-4 py-3">
                                    <div class="flex gap-2">
                                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?= urlencode($d['delivery_address']) ?>" 
                                           target="_blank" 
                                           class="flex-1 flex items-center justify-center gap-1.5 bg-sky-500 hover:bg-sky-600 text-white py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                                            <span class="material-symbols-rounded text-base">navigation</span>
                                            Navigate
                                        </a>
                                        <a href="tel:<?= htmlspecialchars($d['customer_phone']) ?>" 
                                           class="flex-1 flex items-center justify-center gap-1.5 bg-emerald-500 hover:bg-emerald-600 text-white py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                                            <span class="material-symbols-rounded text-base">call</span>
                                            Call
                                        </a>
                                        <?php if ($d['status'] !== 'delivered' && $d['status'] !== 'failed' && $d['status'] !== 'cancelled'): ?>
                                        <button onclick="openModal('modal-status-<?= $d['id'] ?>')" 
                                                class="flex-1 flex items-center justify-center gap-1.5 bg-teal-600 hover:bg-teal-700 text-white py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                                            <span class="material-symbols-rounded text-base">sync</span>
                                            Update
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ROUTE MAP TAB -->
            <div id="tab-map" class="tab-content hidden space-y-6">
                <div class="glass-card rounded-3xl overflow-hidden" style="height: calc(100vh - 250px); min-height: 500px;">
                    <div class="p-4 bg-gradient-to-r from-teal-600 to-emerald-600 text-white flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-lg flex items-center">
                                <span class="material-symbols-rounded mr-2">alt_route</span> Delivery Route Map
                            </h3>
                            <p class="text-sm text-teal-100 mt-1"><?= count($deliveries) ?> delivery locations</p>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-teal-100">Total Distance</div>
                            <div class="font-bold text-xl">~25 km</div>
                        </div>
                    </div>
                    <div id="routeMap" style="height: calc(100% - 80px); width: 100%;"></div>
                </div>
                
                <!-- Legend -->
                <div class="glass-card p-4 rounded-2xl">
                    <h4 class="font-bold text-gray-800 mb-3 text-sm">Legend</h4>
                    <div class="flex flex-wrap gap-4 text-xs">
                        <div class="flex items-center">
                            <div class="w-6 h-6 rounded-full bg-orange-500 mr-2 border-2 border-white shadow-md"></div>
                            <span class="text-gray-600">On the Way</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-6 h-6 rounded-full bg-purple-500 mr-2 border-2 border-white shadow-md"></div>
                            <span class="text-gray-600">Arrived</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-6 h-6 rounded-full bg-blue-500 mr-2 border-2 border-white shadow-md"></div>
                            <span class="text-gray-600">Pending</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-6 h-6 rounded-full bg-green-500 mr-2 border-2 border-white shadow-md"></div>
                            <span class="text-gray-600">Delivered</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-6 h-6 rounded-full bg-red-500 mr-2 border-2 border-white shadow-md"></div>
                            <span class="text-gray-600">Failed</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HISTORY TAB -->
            <div id="tab-history" class="tab-content hidden space-y-6">
                <?php if (empty($completedDeliveries)): ?>
                    <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-300">
                        <span class="material-symbols-rounded text-4xl text-gray-300 mb-2">history</span>
                        <p class="text-gray-500 font-medium">No history available for today.</p>
                    </div>
                <?php else: ?>
                    <div class="glass-card rounded-3xl p-6">
                        <h3 class="font-bold text-gray-800 mb-6 flex items-center">
                            <span class="material-symbols-rounded text-green-500 mr-2">task_alt</span> 
                            Today's Completed Deliveries (<?= count($completedDeliveries) ?>)
                        </h3>
                        <div class="space-y-3">
                            <?php foreach($completedDeliveries as $delivery): ?>
                            <div class="bg-white border border-gray-100 p-4 rounded-2xl hover:shadow-md transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-bold text-gray-800"><?= htmlspecialchars($delivery['customer_name']) ?></h4>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($delivery['delivery_address']) ?></p>
                                    </div>
                                    <span class="text-xs font-bold px-2 py-1 rounded 
                                        <?= $delivery['status'] === 'delivered' ? 'bg-green-100 text-green-700' : 
                                            ($delivery['status'] === 'failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') ?>">
                                        <?= ucfirst($delivery['status']) ?>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-xs text-gray-400 mt-2 pt-2 border-t border-gray-50">
                                    <span>#<?= $delivery['order_number'] ?></span>
                                    <span>Rs. <?= number_format($delivery['total_amount']) ?></span>
                                    <span><?= date('h:i A', strtotime($delivery['completed_date'] ?? $delivery['updated_at'])) ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
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

    </main>

    <!-- Status Update Modals for All Deliveries -->
    <?php foreach($deliveries as $delivery): ?>
    
    <!-- Status Update Modal -->
    <div id="modal-status-<?= $delivery['id'] ?>" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-end md:items-center justify-center p-4">
        <form method="POST" action="index.php?page=rdc-driver-dashboard" class="bg-white rounded-t-3xl md:rounded-3xl w-full max-w-md p-6 shadow-2xl relative animate-up" onsubmit="return validateStatusForm(this)">
            <button type="button" onclick="closeModal('modal-status-<?= $delivery['id'] ?>')" class="absolute top-4 right-4 text-gray-400">
                <span class="material-symbols-rounded">close</span>
            </button>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Update Delivery Status</h3>
            <p class="text-sm text-gray-500 mb-6"><?= htmlspecialchars($delivery['customer_name']) ?> - #<?= $delivery['order_number'] ?></p>
            <input type="hidden" name="order_id" value="<?= $delivery['id'] ?>">
            <input type="hidden" name="update_status" value="1">
            
            <div class="grid grid-cols-2 gap-3 mb-6">
                 <label class="cursor-pointer">
                    <input type="radio" name="status" value="out_for_delivery" class="peer hidden" <?= $delivery['status'] === 'out_for_delivery' ? 'checked' : '' ?>>
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-center peer-checked:bg-blue-50 peer-checked:border-blue-500 peer-checked:text-blue-700 transition">
                        <span class="material-symbols-rounded block mb-1">local_shipping</span>
                        <span class="text-xs font-bold">On the Way</span>
                    </div>
                </label>
                 <label class="cursor-pointer">
                    <input type="radio" name="status" value="arrived" class="peer hidden" <?= $delivery['status'] === 'arrived' ? 'checked' : '' ?>>
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-center peer-checked:bg-orange-50 peer-checked:border-orange-500 peer-checked:text-orange-700 transition">
                        <span class="material-symbols-rounded block mb-1">location_on</span>
                        <span class="text-xs font-bold">Arrived</span>
                    </div>
                </label>
                 <label class="cursor-pointer">
                    <input type="radio" name="status" value="delivered" class="peer hidden" <?= $delivery['status'] === 'delivered' ? 'checked' : '' ?>>
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-center peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:text-green-700 transition">
                        <span class="material-symbols-rounded block mb-1">check_circle</span>
                        <span class="text-xs font-bold">Delivered</span>
                    </div>
                </label>
                 <label class="cursor-pointer">
                    <input type="radio" name="status" value="failed" class="peer hidden" <?= $delivery['status'] === 'failed' ? 'checked' : '' ?>>
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

            <button type="submit" class="w-full bg-teal-600 text-white py-3.5 rounded-xl font-bold hover:bg-teal-700 transition shadow-lg shadow-teal-100">Confirm Update</button>
        </form>
    </div>

    <?php endforeach; ?>
    
    <script>
    // Simple form validation - no AJAX, just validate before normal form submit
    function validateStatusForm(form) {
        const selectedStatus = form.querySelector('input[name="status"]:checked');
        if (!selectedStatus) {
            alert('Please select a status before updating!');
            return false;
        }
        // Show loading state on submit button (but do NOT disable - disabled buttons are excluded from POST data)
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.innerHTML = 'Updating...';
        }
        return true; // Allow normal form submission
    }

    // Toggle action panel for a delivery item in Today's Route
    function toggleActions(id) {
        const panel = document.getElementById(id);
        if (!panel) return;

        // Close all other open panels first
        document.querySelectorAll('[id^="actions-"]').forEach(el => {
            if (el.id !== id) {
                el.classList.add('hidden');
            }
        });

        // Toggle this panel
        panel.classList.toggle('hidden');
    }
    
    function updateStatusBadgeInRoute(orderId, newStatus) {
        // Find the order in Today's Route list and update its status badge
        const routeItems = document.querySelectorAll('[onclick*="modal-status-' + orderId + '"]');
        routeItems.forEach(item => {
            const statusBadge = item.closest('.bg-white').querySelector('.font-bold.px-3.py-1');
            if (statusBadge) {
                // Update badge color and text
                statusBadge.className = 'text-xs font-bold px-3 py-1 rounded-full ' + getStatusBadgeClass(newStatus);
                statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1).replace('_', ' ');
            }
        });
    }
    
    function getStatusBadgeClass(status) {
        switch(status) {
            case 'out_for_delivery': return 'bg-orange-100 text-orange-700';
            case 'arrived': return 'bg-purple-100 text-purple-700';
            case 'delivered': return 'bg-green-100 text-green-700';
            case 'failed': return 'bg-red-100 text-red-700';
            case 'cancelled': return 'bg-gray-100 text-gray-700';
            default: return 'bg-blue-100 text-blue-700';
        }
    }
    
    function updateMapMarkerColor(orderId, newStatus) {
        // Find the delivery in allDeliveries array and update the map marker
        if (window.routeMapObj && allDeliveries) {
            allDeliveries.forEach((delivery, index) => {
                if (delivery.id == orderId) {
                    // Update the data
                    delivery.status = newStatus;
                    
                    // Reinitialize the map to show new colors
                    window.routeMapObj.remove();
                    window.routeMapObj = null;
                    setTimeout(() => {
                        initRouteMap();
                    }, 100);
                }
            });
        }
    }
    
    function showMessage(message, type) {
        // Create or update the message bar at the top
        let messageBar = document.querySelector('.message-bar');
        if (!messageBar) {
            messageBar = document.createElement('div');
            messageBar.className = 'message-bar fixed top-4 left-1/2 transform -translate-x-1/2 z-50 px-4 py-2 rounded-lg font-medium';
            document.body.appendChild(messageBar);
        }
        
        messageBar.textContent = message;
        messageBar.className = 'message-bar fixed top-4 left-1/2 transform -translate-x-1/2 z-50 px-4 py-2 rounded-lg font-medium ' + 
                              (type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white');
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            if (messageBar) {
                messageBar.remove();
            }
        }, 3000);
    }
    
    // Auto-refresh orders every 30 seconds
    let pollingInterval;
    
    function startPolling() {
        pollingInterval = setInterval(() => {
            checkForUpdates();
        }, 30000); // Poll every 30 seconds
    }
    
    function checkForUpdates() {
        fetch('views/rdc/driver_dashboard.php?action=get_orders', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.orders) {
                // Check if there are new orders or status changes
                let hasChanges = false;
                
                if (data.orders.length !== allDeliveries.length) {
                    hasChanges = true;
                    showMessage('New orders received!', 'success');
                } else {
                    // Check for status changes
                    data.orders.forEach(newOrder => {
                        const existingOrder = allDeliveries.find(order => order.id == newOrder.id);
                        if (existingOrder && existingOrder.status !== newOrder.status) {
                            hasChanges = true;
                        }
                    });
                }
                
                if (hasChanges) {
                    // Update the data
                    allDeliveries.length = 0; // Clear array
                    allDeliveries.push(...data.orders); // Add new data
                    
                    // Update the map
                    if (window.routeMapObj) {
                        window.routeMapObj.remove();
                        window.routeMapObj = null;
                        setTimeout(() => {
                            initRouteMap();
                        }, 100);
                    }
                    
                    // Update the counts in the dashboard
                    updateDeliveryCounts(data.orders);
                }
            }
        })
        .catch(error => {
            console.log('Polling error:', error);
        });
    }
    
    function updateDeliveryCounts(orders) {
        const assignedCount = orders.length;
        const completedCount = orders.filter(o => ['delivered', 'failed', 'cancelled'].includes(o.status)).length;
        const remainingCount = assignedCount - completedCount;
        
        // Update the dashboard numbers
        document.querySelector('.text-4xl:contains("' + document.querySelector('.text-4xl').textContent + '")').textContent = assignedCount;
        // Update remaining and completed counts if elements exist
    }
    
    // Start polling when page loads
    document.addEventListener('DOMContentLoaded', function() {
        startPolling();
    });
    
    // Stop polling when page unloads
    window.addEventListener('beforeunload', function() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
    });
    </script>
    
    <!-- Order Items Modals -->
    <?php if($activeDelivery): ?>
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
        // Comprehensive keyword-based geocoding for Northern Province locations
        // Each keyword maps to real coordinates of that area
        const locationKeywords = [
            // Jaffna District - Towns & Areas
            { keywords: ['jaffna fort', 'fort area', 'stanley'], coords: [9.6630, 80.0100] },
            { keywords: ['nallur', 'point pedro road'], coords: [9.6856, 80.0331] },
            { keywords: ['hospital road'], coords: [9.6615, 80.0255] },
            { keywords: ['kopay', 'kks road'], coords: [9.7183, 80.0561] },
            { keywords: ['chavakachcheri', 'chavakacheri'], coords: [9.6678, 80.1657] },
            { keywords: ['tellippalai', 'palaly'], coords: [9.7689, 80.0789] },
            { keywords: ['chunnakam', 'station road'], coords: [9.7167, 80.0333] },
            { keywords: ['nelliady', 'market street'], coords: [9.7500, 80.1500] },
            { keywords: ['point pedro', 'valvettithurai road'], coords: [9.8167, 80.2333] },
            { keywords: ['valvettithurai', 'harbor road'], coords: [9.8167, 80.1667] },
            { keywords: ['karainagar', 'beach road'], coords: [9.7897, 79.9622] },
            { keywords: ['nainativu', 'temple road'], coords: [9.5692, 79.8333] },
            { keywords: ['kayts', 'velanai'], coords: [9.6500, 79.9833] },
            { keywords: ['sandilipay'], coords: [9.7100, 80.0200] },
            { keywords: ['manipay'], coords: [9.7300, 80.0400] },
            { keywords: ['uduvil'], coords: [9.7400, 80.0600] },
            { keywords: ['chankanai'], coords: [9.7550, 80.0900] },
            { keywords: ['araly', 'alaveddy'], coords: [9.7450, 80.0150] },
            { keywords: ['moolai'], coords: [9.7250, 80.0750] },
            { keywords: ['kokuvil'], coords: [9.6950, 80.0450] },
            { keywords: ['thirunelvely', 'thirunelveli'], coords: [9.6880, 80.0350] },
            { keywords: ['kondavil'], coords: [9.7050, 80.0200] },
            { keywords: ['columbuthurai'], coords: [9.6550, 80.0050] },
            { keywords: ['gurunagar'], coords: [9.6580, 80.0120] },
            { keywords: ['navanthurai'], coords: [9.6630, 80.0180] },
            { keywords: ['passaiyoor'], coords: [9.6480, 80.0150] },
            // Kilinochchi District
            { keywords: ['kilinochchi', 'kandy road'], coords: [9.3811, 80.4037] },
            { keywords: ['elephant pass', 'mannar road'], coords: [9.5400, 80.4097] },
            { keywords: ['poonakary', 'pooneryn'], coords: [9.5200, 80.2200] },
            { keywords: ['paranthan'], coords: [9.4500, 80.3900] },
            // Mullaitivu District
            { keywords: ['mullaitivu'], coords: [9.2671, 80.8142] },
            { keywords: ['oddusuddan'], coords: [9.3200, 80.6100] },
            { keywords: ['puthukkudiyiruppu'], coords: [9.2800, 80.5800] },
            // Mannar District
            { keywords: ['mannar'], coords: [8.9833, 79.9167] },
            { keywords: ['madhu'], coords: [8.8500, 80.2000] },
            // Vavuniya District
            { keywords: ['vavuniya'], coords: [8.7514, 80.4972] },
            // Catch-all Jaffna areas
            { keywords: ['jaffna'], coords: [9.6615, 80.0255] },
            { keywords: ['main street'], coords: [9.6620, 80.0150] }
        ];

        // Track used coordinates to add offsets for overlapping markers
        const usedCoords = {};

        // Get approximate coordinates from address with smart keyword matching
        function getCoordinates(address) {
            if (!address) return addOffset([9.6615, 80.0255]); // Default Jaffna

            const addrLower = address.toLowerCase();

            // Try to find the best match by checking each keyword group
            for (const loc of locationKeywords) {
                for (const keyword of loc.keywords) {
                    if (addrLower.includes(keyword.toLowerCase())) {
                        return addOffset(loc.coords);
                    }
                }
            }

            // If no match found, generate a unique position based on the address string hash
            // This ensures each unique address gets a different location scattered around Jaffna
            const hash = hashString(address);
            const baseLat = 9.6615;
            const baseLng = 80.0255;
            const latOffset = ((hash % 1000) / 1000) * 0.15 - 0.075; // spread ±0.075 degrees
            const lngOffset = (((hash >> 10) % 1000) / 1000) * 0.15 - 0.075;
            return [baseLat + latOffset, baseLng + lngOffset];
        }

        // Add a small offset to prevent markers from stacking on top of each other
        function addOffset(coords) {
            const key = coords[0].toFixed(4) + ',' + coords[1].toFixed(4);
            if (!usedCoords[key]) {
                usedCoords[key] = 0;
            }
            usedCoords[key]++;
            const count = usedCoords[key];
            if (count === 1) return coords; // First marker at this location, no offset

            // Spread subsequent markers in a circle around the base point
            const angle = (count - 1) * (2.4); // golden angle in radians for even distribution
            const radius = 0.003 * Math.ceil(count / 6); // ~300m radius, expand for more markers
            return [
                coords[0] + radius * Math.cos(angle),
                coords[1] + radius * Math.sin(angle)
            ];
        }

        // Simple string hash function for generating consistent positions
        function hashString(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // Convert to 32bit integer
            }
            return Math.abs(hash);
        }

        // Delivery data from PHP - use same data as Today's Route (includes all statuses)
        const allDeliveries = <?php echo json_encode($deliveries); ?>;
        console.log('Map data:', allDeliveries.map(d => d.order_number + ' => [' + d.status + ']'));
        
function switchTab(id) {
            // Show selected tab
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            const target = document.getElementById('tab-'+id);
            if(target) {
                target.classList.remove('hidden');
            } else {
                // Fallback
                document.getElementById('tab-overview').classList.remove('hidden');
                id = 'overview';
            }
            
            // Initialize maps if needed
            if(id === 'overview' && document.getElementById('currentMap') && !window.currentMapObj) {
                setTimeout(initMap, 100);
            }
            if(id === 'map' && document.getElementById('routeMap') && !window.routeMapObj) {
                setTimeout(initRouteMap, 100);
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
             if(mapContainer && !window.currentMapObj) {
                 <?php if($activeDelivery): ?>
                 const coords = getCoordinates('<?php echo addslashes($activeDelivery['delivery_address']); ?>');
                 window.currentMapObj = L.map('currentMap', {zoomControl: false}).setView(coords, 14);
                 L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                     attribution: '© OpenStreetMap'
                 }).addTo(window.currentMapObj);
                 
                 const marker = L.marker(coords).addTo(window.currentMapObj);
                 marker.bindPopup('<b><?php echo htmlspecialchars($activeDelivery['customer_name']); ?></b><br><?php echo htmlspecialchars($activeDelivery['delivery_address']); ?>');
                 <?php endif; ?>
             }
        }

        function initRouteMap() {
            var mapContainer = document.getElementById('routeMap');
            if(mapContainer && allDeliveries.length > 0 && !window.routeMapObj) {
                // Center map on Jaffna area
                window.routeMapObj = L.map('routeMap').setView([9.6615, 80.0255], 11);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(window.routeMapObj);

                const bounds = [];
                
                // Add markers for all deliveries (including completed)
                allDeliveries.forEach((delivery, index) => {
                    const coords = getCoordinates(delivery.delivery_address);
                    bounds.push(coords);
                    
                    // Determine marker color based on status (using hex codes matching the legend)
                    let markerColor = '#3b82f6'; // blue - pending/processing/confirmed
                    if (delivery.status === 'out_for_delivery') {
                        markerColor = '#f97316'; // orange - on the way
                    } else if (delivery.status === 'arrived') {
                        markerColor = '#a855f7'; // purple - arrived at location
                    } else if (delivery.status === 'delivered') {
                        markerColor = '#22c55e'; // green - completed
                    } else if (delivery.status === 'failed' || delivery.status === 'cancelled') {
                        markerColor = '#ef4444'; // red - failed
                    }
                    
                    // Create custom icon
                    const customIcon = L.divIcon({
                        className: 'custom-marker',
                        html: `<div style="background-color: ${markerColor}; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">${index + 1}</div>`,
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    });
                    
                    const marker = L.marker(coords, {icon: customIcon}).addTo(window.routeMapObj);
                    
                    // Status badge
                    let statusBadge = '';
                    if (delivery.status === 'delivered') {
                        statusBadge = '<span style="display: inline-block; background: #10b981; color: white; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; margin-top: 4px;">✓ DELIVERED</span>';
                    } else if (delivery.status === 'out_for_delivery') {
                        statusBadge = '<span style="display: inline-block; background: #f97316; color: white; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; margin-top: 4px;">ON THE WAY</span>';
                    } else if (delivery.status === 'arrived') {
                        statusBadge = '<span style="display: inline-block; background: #a855f7; color: white; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; margin-top: 4px;">⬤ ARRIVED</span>';
                    } else if (delivery.status === 'failed') {
                        statusBadge = '<span style="display: inline-block; background: #ef4444; color: white; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; margin-top: 4px;">✗ FAILED</span>';
                    } else {
                        statusBadge = '<span style="display: inline-block; background: #3b82f6; color: white; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; margin-top: 4px;">' + delivery.status.toUpperCase() + '</span>';
                    }
                    
                    // Popup content
                    const popupContent = `
                        <div style="min-width: 200px;">
                            <h4 style="margin: 0 0 8px 0; font-weight: bold; color: #1f2937;">${delivery.customer_name}</h4>
                            <p style="margin: 0 0 4px 0; font-size: 13px; color: #6b7280;">${delivery.delivery_address}</p>
                            <p style="margin: 0 0 4px 0; font-size: 12px; color: #6b7280;">Order: #${delivery.order_number}</p>
                            <p style="margin: 0 0 8px 0; font-size: 12px; color: #6b7280;">Phone: ${delivery.customer_phone}</p>
                            ${statusBadge}
                            <div style="display: flex; gap: 8px; margin-top: 8px;">
                                <a href="https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(delivery.delivery_address)}" 
                                   target="_blank" 
                                   style="flex: 1; background: #0ea5e9; color: white; padding: 6px 12px; text-align: center; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: bold;">
                                    Navigate
                                </a>
                                <a href="tel:${delivery.customer_phone}" 
                                   style="flex: 1; background: #10b981; color: white; padding: 6px 12px; text-align: center; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: bold;">
                                    Call
                                </a>
                            </div>
                        </div>
                    `;
                    
                    marker.bindPopup(popupContent);
                });
                
                // Fit map to show all markers
                if (bounds.length > 0) {
                    window.routeMapObj.fitBounds(bounds, {padding: [50, 50]});
                }
            }
        }
        
        // Auto init on load
        document.addEventListener('DOMContentLoaded', function() {
            // Get tab from URL
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab') || 'overview';
            switchTab(tab);
        });
    </script>
</div>
<?php 
require_once __DIR__ . '/../../includes/footer.php';
ob_end_flush(); 
?>
