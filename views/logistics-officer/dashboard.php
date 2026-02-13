<?php
ob_start();
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

// --- 1. Context Setup ---
$user_id = $_SESSION['user_id'] ?? 0;
$rdc_id = 0;
$logistics_name = $_SESSION['username'] ?? 'Officer';

if ($user_id) {
    $stmt = $pdo->prepare("SELECT rdc_id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u) $rdc_id = $u['rdc_id'];
}
// Fallback for demo
if (!$rdc_id) $rdc_id = 1; // Default to 1 (or 19 if matching seed)

// --- 2. Action Handling ---
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_schedule'])) {
    try {
        $driver_id = $_POST['driver_id'];
        $vehicle_id = $_POST['vehicle_id'];
        $schedule_date = $_POST['schedule_date'];
        $selected_orders = $_POST['order_ids'] ?? [];

        if (empty($selected_orders)) {
            throw new Exception("Please select at least one order.");
        }
        if (empty($driver_id) || empty($vehicle_id) || empty($schedule_date)) {
            throw new Exception("Please select driver, vehicle, and date.");
        }

        $pdo->beginTransaction();

        // Create Schedule
        $stmt = $pdo->prepare("INSERT INTO delivery_schedules (rdc_id, driver_id, vehicle_id, scheduled_date, status) VALUES (?, ?, ?, ?, 'scheduled')");
        $stmt->execute([$rdc_id, $driver_id, $vehicle_id, $schedule_date]);
        $schedule_id = $pdo->lastInsertId();

        // Update Orders
        $upd = $pdo->prepare("UPDATE orders SET schedule_id = ?, status = 'dispatched', updated_at = NOW() WHERE id = ?");
        foreach ($selected_orders as $oid) {
            $upd->execute([$schedule_id, $oid]);
        }

        // Update Vehicle Status (Optional)
        $pdo->prepare("UPDATE vehicles SET status = 'in_use' WHERE vehicle_id = ?")->execute([$vehicle_id]);

        $pdo->commit();
        $success_msg = "Delivery schedule created successfully with " . count($selected_orders) . " orders.";

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// --- 3. Data Fetching ---
// Drivers
$drivers = $pdo->prepare("SELECT id, username FROM users WHERE role = 'rdc_driver' AND rdc_id = ?");
$drivers->execute([$rdc_id]);
$driversList = $drivers->fetchAll(PDO::FETCH_ASSOC);

// Vehicles
$vehicles = $pdo->prepare("SELECT vehicle_id, registration_number, vehicle_type, capacity FROM vehicles WHERE rdc_id = ? AND status = 'available'");
$vehicles->execute([$rdc_id]);
$vehiclesList = $vehicles->fetchAll(PDO::FETCH_ASSOC);

// Pending Orders (Processed but not Scheduled)
$ordersSql = "SELECT o.id, o.order_number, o.total_amount, rc.name as customer_name, rc.address 
              FROM orders o
              JOIN retail_customers rc ON o.customer_id = rc.id
              LEFT JOIN users u ON o.placed_by = u.id 
              WHERE (u.rdc_id = ? OR rc.user_id IN (SELECT id FROM users WHERE rdc_id = ?))
              AND o.status = 'processing' 
              AND o.schedule_id IS NULL";
$params = [$rdc_id, $rdc_id];

// Ensure we catch orders linked to this RDC properly. 
// Similar logic to clerk dashboard: linked by placed_by OR customer user.
// Simplified: Just use placed_by based on seed logic?
// Seed logic used placed_by = clerk (rdc_id 19).
// So u.rdc_id = ? should work.

$stmt = $pdo->prepare($ordersSql);
$stmt->execute($params);
$pendingOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent Schedules
$histSql = "SELECT ds.*, u.username as driver_name, v.registration_number 
            FROM delivery_schedules ds
            JOIN users u ON ds.driver_id = u.id
            JOIN vehicles v ON ds.vehicle_id = v.vehicle_id
            WHERE ds.rdc_id = ? 
            ORDER BY ds.created_at DESC LIMIT 5";
$stmt = $pdo->prepare($histSql);
$stmt->execute([$rdc_id]);
$recentSchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

$activeTab = $_GET['tab'] ?? 'overview';

// Helper for total weight? Not tracking weight yet.
?>

<div class="min-h-screen py-6 font-sans relative">
    <!-- Background handled by custom.css body -->
    <div class="container mx-auto px-4 max-w-7xl">
        
        <!-- Header -->
        <div class="glass-panel rounded-3xl shadow-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 font-['Outfit']">
                        Logistics Dashboard <span class="text-teal-600 block text-lg font-normal mt-1">Hello, <?= htmlspecialchars($logistics_name) ?></span>
                    </h1>
                </div>
                <!-- Tabs -->
                <div class="flex bg-white/40 backdrop-blur-md p-1.5 rounded-2xl border border-white/50 shadow-sm">
                    <a href="?page=logistics-officer-dashboard&tab=overview" class="px-6 py-2.5 rounded-xl text-sm font-bold transition <?= $activeTab==='overview' ? 'bg-white/80 text-teal-700 shadow-sm' : 'text-gray-600 hover:text-teal-600' ?>">Overview</a>
                    <a href="?page=logistics-officer-dashboard&tab=create" class="px-6 py-2.5 rounded-xl text-sm font-bold transition <?= $activeTab==='create' ? 'bg-teal-500 text-white shadow-md shadow-teal-200' : 'text-gray-600 hover:text-teal-600' ?>">Create Schedule</a>
                    <a href="?page=logistics-officer-dashboard&tab=history" class="px-6 py-2.5 rounded-xl text-sm font-bold transition <?= $activeTab==='history' ? 'bg-white/80 text-teal-700 shadow-sm' : 'text-gray-600 hover:text-teal-600' ?>">History</a>
                </div>
            </div>
        </div>

        <?php if($success_msg): ?>
            <div class="glass-panel border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-xl flex items-center bg-green-50/80">
                <span class="material-symbols-rounded mr-2">check_circle</span> <?= $success_msg ?>
            </div>
        <?php endif; ?>
        <?php if($error_msg): ?>
            <div class="glass-panel border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-xl flex items-center bg-red-50/80">
                <span class="material-symbols-rounded mr-2">error</span> <?= $error_msg ?>
            </div>
        <?php endif; ?>

        <?php if($activeTab === 'create'): ?>
            <!-- Create Schedule View -->
            <form method="POST" action="?page=logistics-officer-dashboard&tab=create">
                <input type="hidden" name="create_schedule" value="1">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Left: Select Orders -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="glass-card rounded-3xl p-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center font-['Outfit']">
                                <span class="material-symbols-rounded mr-2 text-teal-500">checklist</span> Select Orders for Delivery
                            </h2>
                            
                            <?php if(empty($pendingOrders)): ?>
                                <div class="text-center py-12 text-gray-400 bg-white/30 rounded-2xl border-2 border-dashed border-gray-300">
                                    <span class="material-symbols-rounded text-4xl mb-2">inbox</span>
                                    <p>No processed orders waiting for delivery.</p>
                                </div>
                            <?php else: ?>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left">
                                        <thead class="bg-teal-50/50 text-gray-500 text-xs uppercase font-bold">
                                            <tr>
                                                <th class="p-4 rounded-l-xl w-10">
                                                    <input type="checkbox" onclick="toggleAll(this)" class="rounded text-teal-500 focus:ring-teal-500 border-gray-300 bg-white/50">
                                                </th>
                                                <th class="p-4">Order Details</th>
                                                <th class="p-4">Customer</th>
                                                <th class="p-4 text-right rounded-r-xl">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100/50">
                                            <?php foreach($pendingOrders as $o): ?>
                                            <tr class="hover:bg-teal-50/30 transition cursor-pointer group" onclick="document.getElementById('chk-<?= $o['id'] ?>').click()">
                                                <td class="p-4">
                                                    <input type="checkbox" name="order_ids[]" value="<?= $o['id'] ?>" id="chk-<?= $o['id'] ?>" class="order-chk rounded text-teal-500 focus:ring-teal-500 border-gray-300 bg-white/50" onclick="event.stopPropagation()">
                                                </td>
                                                <td class="p-4">
                                                    <span class="font-mono font-bold text-gray-800 block group-hover:text-teal-700 transition"><?= $o['order_number'] ?></span>
                                                    <span class="text-xs text-gray-500">ID: <?= $o['id'] ?></span>
                                                </td>
                                                <td class="p-4">
                                                    <div class="font-medium text-gray-800"><?= htmlspecialchars($o['customer_name']) ?></div>
                                                    <div class="text-xs text-gray-500 truncate max-w-[200px]" title="<?= htmlspecialchars($o['address']) ?>"><?= htmlspecialchars($o['address']) ?></div>
                                                </td>
                                                <td class="p-4 text-right font-bold text-gray-700">
                                                    Rs. <?= number_format($o['total_amount']) ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right: Schedule Details -->
                    <div class="space-y-6">
                        <div class="glass-card rounded-3xl p-6 sticky top-6 border-t-4 border-teal-500">
                            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center font-['Outfit']">
                                <span class="material-symbols-rounded mr-2 text-teal-600">local_shipping</span> 
                                Trip Details
                            </h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Select Driver</label>
                                    <div class="relative">
                                        <select name="driver_id" required class="w-full appearance-none bg-white/50 border border-white/60 backdrop-blur-sm text-gray-700 py-3 px-4 pr-8 rounded-xl focus:outline-none focus:bg-white/80 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 cursor-pointer shadow-sm transition">
                                            <option value="">-- Choose Driver --</option>
                                            <?php foreach($driversList as $d): ?>
                                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['username']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                            <span class="material-symbols-rounded">expand_more</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Select Vehicle</label>
                                    <div class="relative">
                                        <select name="vehicle_id" required class="w-full appearance-none bg-white/50 border border-white/60 backdrop-blur-sm text-gray-700 py-3 px-4 pr-8 rounded-xl focus:outline-none focus:bg-white/80 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 cursor-pointer shadow-sm transition">
                                            <option value="">-- Choose Vehicle --</option>
                                            <?php foreach($vehiclesList as $v): ?>
                                                <option value="<?= $v['vehicle_id'] ?>">
                                                    <?= htmlspecialchars($v['registration_number']) ?> (<?= $v['vehicle_type'] ?> - <?= $v['capacity'] ?>kg)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                            <span class="material-symbols-rounded">expand_more</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Schedule Date</label>
                                    <input type="date" name="schedule_date" required min="<?= date('Y-m-d') ?>" class="w-full bg-white/50 border border-white/60 backdrop-blur-sm text-gray-700 py-3 px-4 rounded-xl focus:outline-none focus:bg-white/80 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 shadow-sm transition">
                                </div>

                                <div class="pt-4 border-t border-gray-100/50">
                                    <button type="submit" class="w-full bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-teal-500/30 transition transform hover:-translate-y-0.5 flex justify-center items-center font-['Outfit']">
                                        <span class="material-symbols-rounded mr-2">add_task</span> Create Schedule
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>

        <?php elseif($activeTab === 'history'): ?>
             <!-- History View -->
             <div class="glass-card rounded-3xl p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 font-['Outfit']">Recent Schedules</h2>
                <!-- Table similar to overview -->
                <?php if(empty($recentSchedules)): ?>
                    <p class="text-gray-400 text-center py-8">No delivery history found.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-teal-50/50 text-gray-500 text-xs uppercase font-bold">
                                <tr>
                                    <th class="p-4 rounded-l-xl">ID</th>
                                    <th class="p-4">Driver</th>
                                    <th class="p-4">Vehicle</th>
                                    <th class="p-4">Date</th>
                                    <th class="p-4 text-center">Status</th>
                                    <th class="p-4 rounded-r-xl text-right">Created</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100/50">
                                <?php foreach($recentSchedules as $rs): ?>
                                <tr class="hover:bg-white/40 transition">
                                    <td class="p-4 font-mono font-bold text-gray-700">SCH-<?= $rs['schedule_id'] ?></td>
                                    <td class="p-4 font-medium text-gray-800"><?= htmlspecialchars($rs['driver_name']) ?></td>
                                    <td class="p-4 text-gray-600"><?= htmlspecialchars($rs['registration_number']) ?></td>
                                    <td class="p-4"><?= date('M d, Y', strtotime($rs['scheduled_date'])) ?></td>
                                    <td class="p-4 text-center">
                                        <span class="px-3 py-1 bg-white/60 backdrop-blur-sm border border-blue-200 text-blue-700 rounded-full text-xs font-bold uppercase shadow-sm"><?= $rs['status'] ?></span>
                                    </td>
                                    <td class="p-4 text-right text-gray-500 text-sm"><?= date('M d, H:i', strtotime($rs['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Overview Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Stats Cards -->
                 <div class="glass-card p-6 rounded-3xl hover-lift border-l-4 border-amber-400">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pending Delivery</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']"><?= count($pendingOrders) ?></h3>
                        </div>
                        <div class="p-3 bg-amber-100/50 backdrop-blur-sm rounded-2xl text-amber-600 shadow-inner">
                            <span class="material-symbols-rounded">pending_actions</span>
                        </div>
                    </div>
                </div>
                 <div class="glass-card p-6 rounded-3xl hover-lift border-l-4 border-teal-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Active Vehicles</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']"><?= count($vehiclesList) ?></h3>
                        </div>
                        <div class="p-3 bg-teal-100/50 backdrop-blur-sm rounded-2xl text-teal-600 shadow-inner">
                            <span class="material-symbols-rounded">local_shipping</span>
                        </div>
                    </div>
                </div>
                 <div class="glass-card p-6 rounded-3xl hover-lift border-l-4 border-blue-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Available Drivers</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']"><?= count($driversList) ?></h3>
                        </div>
                        <div class="p-3 bg-blue-100/50 backdrop-blur-sm rounded-2xl text-blue-600 shadow-inner">
                            <span class="material-symbols-rounded">person_pin</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Schedules List -->
            <div class="glass-card rounded-3xl p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 font-['Outfit']">Recent Schedules</h2>
                <?php if(empty($recentSchedules)): ?>
                    <p class="text-gray-400 text-center py-8">No schedules created yet.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-teal-50/50 text-gray-500 text-xs uppercase font-bold">
                                <tr>
                                    <th class="p-4 rounded-l-xl">ID</th>
                                    <th class="p-4">Driver</th>
                                    <th class="p-4">Vehicle</th>
                                    <th class="p-4">Date</th>
                                    <th class="p-4 text-center">Status</th>
                                    <th class="p-4 rounded-r-xl text-right">Created</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100/50">
                                <?php foreach($recentSchedules as $rs): ?>
                                <tr class="hover:bg-white/40 transition">
                                    <td class="p-4 font-mono font-bold text-gray-700">SCH-<?= $rs['schedule_id'] ?></td>
                                    <td class="p-4 font-medium text-gray-800"><?= htmlspecialchars($rs['driver_name']) ?></td>
                                    <td class="p-4 text-gray-600"><?= htmlspecialchars($rs['registration_number']) ?></td>
                                    <td class="p-4"><?= date('M d, Y', strtotime($rs['scheduled_date'])) ?></td>
                                    <td class="p-4 text-center">
                                        <span class="px-3 py-1 bg-white/60 backdrop-blur-sm border border-blue-200 text-blue-700 rounded-full text-xs font-bold uppercase shadow-sm"><?= $rs['status'] ?></span>
                                    </td>
                                    <td class="p-4 text-right text-gray-500 text-sm"><?= date('M d, H:i', strtotime($rs['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        <?php endif; ?>

    </div>
</div>

<script>
function toggleAll(source) {
    checkboxes = document.querySelectorAll('.order-chk');
    for(var i=0, n=checkboxes.length;i<n;i++) {
        checkboxes[i].checked = source.checked;
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
