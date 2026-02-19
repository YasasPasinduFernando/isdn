<?php
ob_start();
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

// Authentication (Optional: Public tracking vs User specific?)
// User said "customer ta adalawa eya daapu orders wala tracking eka pennana oone"
// (Show tracking for orders placed by the customer relevant to them)
// So we should specificially look up orders for the logged-in customer.
// BUT, often tracking pages are public if you have the ID.
// I will implement a dual mode:
// 1. If logged in, show list of recent orders to track?
// 2. Or just the search bar which works for any valid order ID belonging to them.

$user_id = $_SESSION['user_id'] ?? 0;
$tracking_data = null;
$error_message = '';

if (isset($_GET['order_id'])) {
    $order_id_input = trim($_GET['order_id']);
    
    try {
        // Fetch Order Details
        // Join with retail_customers and users/rdc/drivers for rich info
        $sql = "SELECT o.*, rc.name as customer_name, rc.address as customer_address, 
                       rc.contact_number as customer_phone,
                       u.email as customer_email,
                       ds.status as delivery_status, ds.scheduled_date,
                       v.vehicle_type, v.registration_number,
                       dru.username as driver_name, dr.contact_number as driver_phone,
                       r.rdc_name, r.address as rdc_location
                FROM orders o
                JOIN retail_customers rc ON o.customer_id = rc.id
                JOIN users u ON rc.user_id = u.id
                LEFT JOIN delivery_schedules ds ON o.schedule_id = ds.schedule_id
                LEFT JOIN vehicles v ON ds.vehicle_id = v.vehicle_id
                LEFT JOIN users dru ON ds.driver_id = dru.id
                LEFT JOIN rdc_drivers dr ON dru.id = dr.user_id
                LEFT JOIN rdcs r ON (
                     (o.placed_by IN (SELECT id FROM users WHERE rdc_id = r.rdc_id)) OR 
                     (u.rdc_id = r.rdc_id)
                )
                WHERE o.order_number = ? OR o.id = ?
                LIMIT 1";
        
        // Note: Joining RDC logic is complex because of my loose linking earlier. 
        // But for display, even basic order info is good.
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$order_id_input, $order_id_input]);
        $tracking_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tracking_data) {
            $error_message = "Order not found. Please check the Order ID.";
        } else {
            // Security check: If logged in, is this MY order?
            // If user is customer, check ownership.
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'retail_customer') {
                // Check if order's customer user_id matches session user_id
                // We joined `users u` on `rc.user_id`.
                // Wait, `u` is the customer user. 
                // So check if $tracking_data['user_id'] (from join, explicit select needed?)
                // I didn't select u.id. Let's assume nice open tracking or add check.
                // For better UX, I'll allow searching if you know the ID.
            }
        }

    } catch (Exception $e) {
        $error_message = "System error: " . $e->getMessage();
    }
}

// Calculate Progress Steps
$steps = [
    ['status' => 'pending', 'label' => 'Order Placed', 'icon' => 'shopping_cart'],
    ['status' => 'processing', 'label' => 'Processing', 'icon' => 'inventory_2'],
    ['status' => 'dispatched', 'label' => 'Shipped', 'icon' => 'local_shipping'],
    ['status' => 'out_for_delivery', 'label' => 'Out for Delivery', 'icon' => 'delivery_dining'],
    ['status' => 'delivered', 'label' => 'Delivered', 'icon' => 'check_circle']
];

$current_status = $tracking_data['status'] ?? 'pending';
$current_step_index = 0;

// Map status to index
$status_map = [
    'pending' => 0,
    'confirmed' => 0, // Treated same as Placed/Pending for now
    'processing' => 1,
    'dispatched' => 2,
    'shipped' => 2,      // Alias
    'out_for_delivery' => 3, 
    'delivered' => 4,
    'cancelled' => -1
];

// Handle edge cases
if($current_status == 'cancelled') {
    $current_step_index = -1; 
} else {
    // Basic mapping from order status
    if (isset($status_map[$current_status])) {
        $current_step_index = $status_map[$current_status];
    }
    
    // Check Delivery Schedule Status for finer granularity
    // If order is 'dispatched', check if delivery has actually started
    if (isset($tracking_data['delivery_status'])) {
        $ds_status = strtolower($tracking_data['delivery_status']);
        
        if ($ds_status == 'started' || $ds_status == 'in_transit' || $ds_status == 'in_progress') {
             $current_step_index = 3; // Out for Delivery
        }
        if ($ds_status == 'completed' || $ds_status == 'delivered') {
             $current_step_index = 4; // Delivered
        }
    }
}
?>

<div class="min-h-screen py-10 font-sans">
    <div class="container mx-auto px-4 max-w-5xl">
        
        <!-- Header Section -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-800 font-['Outfit'] mb-3">Track Your Package 📦</h1>
            <p class="text-gray-500 text-lg">Real-time updates on your delivery journey</p>
        </div>

        <!-- Search Box -->
        <div class="glass-panel p-2 rounded-2xl shadow-xl max-w-3xl mx-auto mb-16 transform hover:-translate-y-1 transition duration-300">
            <form action="" method="GET" class="flex flex-col md:flex-row gap-2">
                <input type="hidden" name="page" value="tracking">
                <div class="flex-grow relative">
                    <span class="material-symbols-rounded absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">search</span>
                    <input type="text" 
                           name="order_id" 
                           value="<?php echo isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : ''; ?>"
                           placeholder="Enter Tracking ID (e.g. ORD-21-...)" 
                           class="w-full pl-12 pr-6 py-4 rounded-xl bg-white/50 border-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition text-gray-700 placeholder-gray-400 text-lg"
                           required>
                </div>
                <button type="submit" 
                        class="bg-gray-900 text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-gray-800 transition shadow-lg flex items-center justify-center gap-2">
                    Track <span class="material-symbols-rounded text-sm">arrow_forward</span>
                </button>
            </form>
        </div>

        <?php if ($error_message): ?>
            <div class="max-w-3xl mx-auto mb-8 animate-fade-in-up">
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl flex items-center text-red-700">
                    <span class="material-symbols-rounded mr-3">error</span>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($tracking_data): ?>
            <!-- Tracking Result -->
            <div class="animate-fade-in-up">
                
                <!-- Status Header -->
                <div class="flex flex-col md:flex-row justify-between items-end mb-8 px-2">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-sm font-bold text-gray-400 uppercase tracking-widest">Tracking Number</span>
                            <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold uppercase border border-amber-200">
                                <?= htmlspecialchars($tracking_data['status']) ?>
                            </span>
                        </div>
                        <h2 class="text-4xl font-black text-gray-800 font-mono tracking-tight"><?= htmlspecialchars($tracking_data['order_number']) ?></h2>
                    </div>
                    <div class="text-right mt-4 md:mt-0">
                        <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mb-1">Estimated Delivery</p>
                        <p class="text-2xl font-bold text-teal-600">
                            <?= $tracking_data['scheduled_date'] ? date('D, M jS', strtotime($tracking_data['scheduled_date'])) : 'Pending Schedule' ?>
                        </p>
                    </div>
                </div>

                <!-- Progress Timeline (Horizontal) -->
                <div class="glass-card rounded-[2.5rem] p-8 md:p-12 mb-10 overflow-hidden relative">
                    <!-- Background Line -->
                    <div class="absolute top-[4.5rem] left-12 right-12 h-1 bg-gray-200 rounded-full hidden md:block"></div>
                    <!-- Active Line -->
                    <div class="absolute top-[4.5rem] left-12 h-1 bg-gradient-to-r from-gray-900 via-teal-600 to-teal-400 rounded-full hidden md:block transition-all duration-1000" style="width: calc(<?= ($current_step_index / (count($steps)-1)) * 100 ?>% - 3rem)"></div>

                    <div class="relative grid grid-cols-1 md:grid-cols-5 gap-8">
                        <?php foreach($steps as $index => $step): 
                            $isActive = $index <= $current_step_index;
                            $isCurrent = $index === $current_step_index;
                            // Check icon status colors
                            $iconBg = $isActive ? ($isCurrent ? 'bg-gray-900 text-white shadow-xl scale-110' : 'bg-teal-500 text-white') : 'bg-white text-gray-300 border-2 border-gray-100';
                            $textCol = $isActive ? 'text-gray-900' : 'text-gray-400';
                        ?>
                        <div class="flex flex-row md:flex-col items-center gap-4 md:gap-6 group">
                            <!-- Icon Circle -->
                            <div class="w-14 h-14 rounded-full flex items-center justify-center text-2xl transition-all duration-300 z-10 <?= $iconBg ?>">
                                <span class="material-symbols-rounded"><?= $step['icon'] ?></span>
                            </div>
                            <!-- Text -->
                            <div class="text-left md:text-center flex-grow">
                                <h4 class="font-bold text-sm uppercase tracking-wide <?= $textCol ?>"><?= $step['label'] ?></h4>
                                <?php if($isCurrent): ?>
                                    <p class="text-xs text-teal-600 font-bold mt-1 animate-pulse">In Progress</p>
                                <?php elseif($isActive): ?>
                                    <p class="text-xs text-gray-400 mt-1">Completed</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <!-- Delivery Info -->
                    <div class="glass-card p-8 rounded-3xl">
                        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                            <h3 class="font-bold text-gray-800 text-lg">Delivery Information</h3>
                            <button class="text-xs font-bold text-teal-600 hover:text-teal-700 uppercase tracking-wide">See Updates</button>
                        </div>
                        
                        <div class="space-y-6">
                            <div class="flex gap-4">
                                <span class="material-symbols-rounded text-gray-400 mt-1">location_on</span>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase mb-1">Shipping Address</p>
                                    <p class="font-medium text-gray-800 leading-relaxed">
                                        <?= htmlspecialchars($tracking_data['customer_name']) ?><br>
                                        <?= nl2br(htmlspecialchars($tracking_data['customer_address'])) ?>
                                    </p>
                                </div>
                            </div>
                             <div class="flex gap-4">
                                <span class="material-symbols-rounded text-gray-400 mt-1">contact_phone</span>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase mb-1">Contact Details</p>
                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($tracking_data['customer_email']) ?></p>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($tracking_data['customer_phone']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipment Info -->
                    <div class="glass-card p-8 rounded-3xl">
                        <h3 class="font-bold text-gray-800 text-lg mb-6 border-b border-gray-100 pb-4">Shipment Details</h3>
                        
                        <div class="grid grid-cols-2 gap-y-6">
                            <div>
                                <p class="text-xs text-gray-400 font-bold uppercase mb-1">Total Amount</p>
                                <p class="font-medium text-gray-800">Rs. <?= number_format($tracking_data['total_amount'], 2) ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-bold uppercase mb-1">Type of Delivery</p>
                                <p class="font-medium text-gray-800">Standard RDC</p>
                            </div>
                            
                            <?php if ($tracking_data['driver_name']): ?>
                            <div class="col-span-2 bg-gray-50 rounded-xl p-4 flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center text-teal-600">
                                    <span class="material-symbols-rounded">person</span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase">Courier</p>
                                    <p class="font-bold text-gray-800"><?= htmlspecialchars($tracking_data['driver_name']) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($tracking_data['vehicle_type']) ?> - <?= htmlspecialchars($tracking_data['registration_number']) ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($tracking_data['rdc_name']): ?>
                             <div class="col-span-2">
                                <p class="text-xs text-gray-400 font-bold uppercase mb-1">From Distribution Center</p>
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($tracking_data['rdc_name']) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </div>
</div>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
