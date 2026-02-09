<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 1. Authentication & Context Setup ---
// In a real app, use $_SESSION['user_id']. For demo, we simulate a Clerk.
// We'll try to find an RDC Clerk user.
try {
    $stmt = $pdo->prepare("SELECT u.id, u.username, u.rdc_id, r.rdc_name 
                           FROM users u 
                           JOIN rdcs r ON u.rdc_id = r.rdc_id 
                           WHERE u.role = 'rdc_clerk' LIMIT 1");
    $stmt->execute();
    $clerk = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$clerk) {
        // Fallback if no clerk found, use Manager for demo purposes or die
        $clerk = ['id' => 0, 'username' => 'Demo Clerk', 'rdc_id' => 1, 'rdc_name' => 'Northern RDC'];
    }

    $user_id = $clerk['id'];
    $rdc_id = $clerk['rdc_id'];
    $clerk_name = $clerk['username'];
    $rdc_name = $clerk['rdc_name'];

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// --- 2. Action Handling (POST) ---
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_order'])) {
        $orderId = $_POST['order_id'];
        // Update Status
        $stmt = $pdo->prepare("UPDATE orders SET status = 'confirmed' WHERE id = ?");
        if ($stmt->execute([$orderId])) {
            $success_msg = "Order #$orderId has been confirmed and marked ready for delivery.";
        } else {
            $error_msg = "Failed to confirm order.";
        }
    }

    if (isset($_POST['update_stock'])) {
        $prodId = $_POST['product_id'];
        $qtyChange = (int)$_POST['quantity'];
        $reason = $_POST['reason'];

        // Simple Stock Update
        $stmt = $pdo->prepare("UPDATE product_stocks SET available_quantity = available_quantity + ? WHERE product_id = ? AND rdc_id = ?");
        if ($stmt->execute([$qtyChange, $prodId, $rdc_id])) {
            $success_msg = "Stock updated successfully.";
        } else {
            $error_msg = "Failed to update stock.";
        }
    }
    
    // Request Stock Logic (Same as Manager)
    if (isset($_POST['request_stock'])) {
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        $reason = $_POST['reason'] ?? '';
        
        $transferRef = 'REQ-' . strtoupper(uniqid());
        $headOfficeId = 6; // Default
        
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO stock_transfers (transfer_number, source_rdc_id, destination_rdc_id, requested_by, request_reason, transfer_status) VALUES (?, ?, ?, ?, ?, 'PENDING_APPROVAL')");
            $stmt->execute([$transferRef, $headOfficeId, $rdc_id, $user_id, $reason]);
            $transferId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO stock_transfer_items (transfer_id, product_id, requested_quantity) VALUES (?, ?, ?)");
            $stmt->execute([$transferId, $product_id, $quantity]);

            $pdo->commit();
            $success_msg = "Stock request submitted. Ref: " . $transferRef;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}

// --- 3. Data Fetching ---

// A. Pending Orders (New Orders)
$stmt = $pdo->prepare("SELECT o.*, u.username as customer_name 
                       FROM orders o 
                       JOIN users u ON o.customer_id = u.id 
                       WHERE o.status = 'pending' 
                       ORDER BY o.created_at ASC");
$stmt->execute();
$pendingOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// B. Processed Today (Confirmed)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status = 'confirmed' AND DATE(updated_at) = CURDATE()");
$stmt->execute();
$processedToday = $stmt->fetchColumn();

// C. Stock Inventory
$stmt = $pdo->prepare("SELECT p.product_id, p.product_name, p.product_code, ps.available_quantity 
                       FROM product_stocks ps 
                       JOIN products p ON ps.product_id = p.product_id 
                       WHERE ps.rdc_id = ?");
$stmt->execute([$rdc_id]);
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// D. Customers (Simple List)
$stmt = $pdo->prepare("SELECT * FROM retail_customers LIMIT 10"); // Mock or linked to users
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// E. Payments (Billing) - Mocking join with orders pending payments if table not fully populated
// We check orders that are delivered but maybe payment logic is separate.
// For now listing Delivered orders as "Invoicing" context
$stmt = $pdo->prepare("SELECT o.*, u.username as customer_name, p.amount as paid_amount
                       FROM orders o
                       JOIN users u ON o.customer_id = u.id
                       LEFT JOIN payments p ON o.order_number = p.order_id
                       WHERE o.status = 'delivered'
                       LIMIT 10");
$stmt->execute();
$billingOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStockForOrder($pdo, $orderId, $rdcId) {
    $stmt = $pdo->prepare("SELECT oi.product_id, oi.quantity as req_qty, ps.available_quantity as stock_qty, p.product_name
                           FROM order_items oi
                           JOIN product_stocks ps ON oi.product_id = ps.product_id AND ps.rdc_id = ?
                           JOIN products p ON oi.product_id = p.product_id
                           WHERE oi.order_id = ?");
    $stmt->execute([$rdcId, $orderId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clerk Dashboard - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0" />
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .sidebar-link.active { background-color: #f0fdfa; color: #0d9488; border-right: 3px solid #0d9488; }
    </style>
</head>
<body class="bg-gray-100 h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col z-10">
        <div class="h-16 flex items-center px-6 border-b border-gray-100">
            <h1 class="text-xl font-bold text-teal-700">ISDN <span class="text-xs text-gray-500 font-normal uppercase ml-1">Clerk</span></h1>
        </div>
        
        <div class="p-4">
            <div class="bg-teal-50 p-3 rounded-xl border border-teal-100 mb-6">
                <p class="text-xs text-teal-600 font-bold uppercase mb-1">Station</p>
                <div class="font-bold text-gray-800"><?= htmlspecialchars($rdc_name) ?></div>
                <div class="text-xs text-gray-500 mt-1">user: <?= htmlspecialchars($clerk_name) ?></div>
            </div>

            <nav class="space-y-1">
                <button onclick="showTab('dashboard')" id="nav-dashboard" class="sidebar-link w-full flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg transition active">
                    <span class="material-symbols-rounded mr-3">dashboard</span> Overview
                </button>
                <button onclick="showTab('orders')" id="nav-orders" class="sidebar-link w-full flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg transition">
                    <span class="material-symbols-rounded mr-3">checklist</span> Order Processing
                    <?php if(count($pendingOrders) > 0): ?>
                    <span class="ml-auto bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full"><?= count($pendingOrders) ?></span>
                    <?php endif; ?>
                </button>
                <button onclick="showTab('inventory')" id="nav-inventory" class="sidebar-link w-full flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg transition">
                    <span class="material-symbols-rounded mr-3">inventory</span> Inventory
                </button>
                <button onclick="showTab('billing')" id="nav-billing" class="sidebar-link w-full flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg transition">
                    <span class="material-symbols-rounded mr-3">receipt_long</span> Billing & Returns
                </button>
                <button onclick="showTab('customers')" id="nav-customers" class="sidebar-link w-full flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg transition">
                    <span class="material-symbols-rounded mr-3">group</span> Customers
                </button>
            </nav>
        </div>

        <div class="mt-auto p-4 border-t border-gray-100">
            <a href="index.php?page=home" class="flex items-center text-red-500 hover:bg-red-50 p-3 rounded-lg transition text-sm font-medium">
                <span class="material-symbols-rounded mr-3">logout</span> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full relative overflow-y-auto">
        <!-- Messages -->
        <?php if($success_msg): ?>
        <div class="absolute top-4 right-4 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-lg z-50 flex items-center">
            <span class="material-symbols-rounded mr-2">check_circle</span> <?= $success_msg ?>
            <button onclick="this.parentElement.remove()" class="ml-4">&times;</button>
        </div>
        <?php endif; ?>

        <!-- Dashboard Tab -->
        <div id="tab-dashboard" class="tab-content p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Good Morning, <?= htmlspecialchars($clerk_name) ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="text-gray-500 text-xs font-bold uppercase">Pending Orders</div>
                    <div class="text-3xl font-bold text-gray-800 mt-2"><?= count($pendingOrders) ?></div>
                    <div class="text-xs text-red-500 mt-1 font-medium">Needs Attention</div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="text-gray-500 text-xs font-bold uppercase">Processed Today</div>
                    <div class="text-3xl font-bold text-teal-600 mt-2"><?= $processedToday ?></div>
                </div>
                <!-- More mock stats -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="text-gray-500 text-xs font-bold uppercase">Low Stock Items</div>
                    <div class="text-3xl font-bold text-yellow-600 mt-2">3</div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-lg mb-4">My Tasks for Today</h3>
                <ul class="space-y-3">
                    <li class="flex items-center text-sm text-gray-600">
                        <span class="material-symbols-rounded text-yellow-500 mr-2">circle</span> Process <?= count($pendingOrders) ?> new orders
                    </li>
                    <li class="flex items-center text-sm text-gray-600">
                        <span class="material-symbols-rounded text-green-500 mr-2">check_circle</span> Update inventory for arrived shipment
                    </li>
                    <li class="flex items-center text-sm text-gray-600">
                        <span class="material-symbols-rounded text-gray-300 mr-2">circle</span> Verify returns from yesterday
                    </li>
                </ul>
            </div>
        </div>

        <!-- Orders Tab -->
        <div id="tab-orders" class="tab-content hidden p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Order Processing</h2>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-medium">
                        <tr>
                            <th class="p-4">Order ID</th>
                            <th class="p-4">Customer</th>
                            <th class="p-4">Date</th>
                            <th class="p-4">Total</th>
                            <th class="p-4 text-center">Stock Check</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php foreach($pendingOrders as $order): 
                            $items = getStockForOrder($pdo, $order['id'], $rdc_id);
                            $allAvailable = true;
                            foreach($items as $i) {
                                if($i['stock_qty'] < $i['req_qty']) $allAvailable = false;
                            }
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="p-4 font-bold text-gray-800"><?= $order['order_number'] ?></td>
                            <td class="p-4"><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td class="p-4"><?= date('M d, H:i', strtotime($order['created_at'])) ?></td>
                            <td class="p-4 font-medium">Rs. <?= number_format($order['total_amount']) ?></td>
                            <td class="p-4 text-center">
                                <?php if($allAvailable): ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-bold inline-flex items-center">
                                        <span class="material-symbols-rounded text-sm mr-1">check</span> Available
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-bold inline-flex items-center">
                                        <span class="material-symbols-rounded text-sm mr-1">close</span> LOW STOCK
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <?php 
                                    $itemsJson = json_encode(array_map(function($i){
                                        return ['name'=>$i['product_name'], 'qty'=>$i['req_qty']];
                                    }, $items));
                                ?>
                                <button onclick='printPickList("<?= $order['order_number'] ?>", <?= $itemsJson ?>)' class="text-gray-500 hover:text-blue-600 text-xs font-bold uppercase tracking-wide">
                                    <span class="material-symbols-rounded align-middle text-lg">print</span> Print
                                </button>
                                <?php if($allAvailable): ?>
                                <button onclick="openConfirmModal(<?= $order['id'] ?>, '<?= $order['order_number'] ?>')" class="bg-teal-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-teal-700 transition shadow-sm">
                                    Confirm
                                </button>
                                <?php else: ?>
                                <button class="bg-gray-200 text-gray-400 px-3 py-1.5 rounded-lg text-xs font-bold cursor-not-allowed">
                                    Confirm
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($pendingOrders)): ?>
                            <tr><td colspan="6" class="p-8 text-center text-gray-400">No pending orders.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Inventory Tab -->
        <div id="tab-inventory" class="tab-content hidden p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Inventory Management</h2>
                <div class="flex space-x-2">
                    <button onclick="openStockModal('update')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-blue-700">Update Stock</button>
                    <button onclick="openStockModal('request')" class="bg-teal-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-teal-700">Request Transfer</button>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                 <?php foreach($inventory as $item): ?>
                 <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex justify-between items-center">
                     <div>
                         <h4 class="font-bold text-gray-800"><?= htmlspecialchars($item['product_name']) ?></h4>
                         <p class="text-xs text-gray-500"><?= $item['product_code'] ?></p>
                     </div>
                     <div class="text-right">
                         <div class="text-2xl font-bold text-teal-600"><?= $item['available_quantity'] ?></div>
                         <div class="text-[10px] uppercase text-gray-400 font-bold">In Stock</div>
                     </div>
                 </div>
                 <?php endforeach; ?>
            </div>
        </div>

        <!-- Billing Tab -->
        <div id="tab-billing" class="tab-content hidden p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Billing & Invoices</h2>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <tr>
                            <th class="p-4">Invoice #</th>
                            <th class="p-4">Customer</th>
                            <th class="p-4">Amount</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php foreach($billingOrders as $bo): ?>
                        <tr>
                            <td class="p-4 font-bold">INV-<?= $bo['order_number'] ?></td>
                            <td class="p-4"><?= htmlspecialchars($bo['customer_name']) ?></td>
                            <td class="p-4">Rs. <?= number_format($bo['total_amount']) ?></td>
                            <td class="p-4">
                                <?php if($bo['paid_amount'] >= $bo['total_amount']): ?>
                                    <span class="text-green-600 font-bold text-xs uppercase">Paid</span>
                                <?php else: ?>
                                    <span class="text-red-500 font-bold text-xs uppercase">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right">
                                <button class="text-blue-600 hover:underline text-xs font-bold">View Invoice</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

         <!-- Customers Tab -->
         <div id="tab-customers" class="tab-content hidden p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Customer Directory</h2>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="p-4 border-b border-gray-100">
                    <input type="text" placeholder="Search customers..." class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div class="p-4">
                     <p class="text-sm text-gray-500 mb-4">Displaying recent customers</p>
                     <!-- List would go here -->
                </div>
            </div>
        </div>

    </main>

    <!-- Modals -->
    <div id="modal-confirm" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <form method="POST" class="bg-white rounded-2xl w-full max-w-sm p-6 shadow-2xl">
            <h3 class="font-bold text-lg mb-2">Confirm Order</h3>
            <p class="text-sm text-gray-600 mb-4">Mark <span id="conf-order-ref" class="font-bold"></span> as ready for delivery?</p>
            <input type="hidden" name="order_id" id="conf-order-id">
            <div class="flex space-x-2">
                <button type="button" onclick="closeModal('modal-confirm')" class="flex-1 py-2 bg-gray-100 rounded-lg text-sm font-bold">Cancel</button>
                <button type="submit" name="confirm_order" class="flex-1 py-2 bg-teal-600 text-white rounded-lg text-sm font-bold shadow">Confirm</button>
            </div>
        </form>
    </div>

    <!-- Stock Modal -->
    <div id="modal-stock" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <form method="POST" class="bg-white rounded-2xl w-full max-w-sm p-6 shadow-2xl">
            <h3 class="font-bold text-lg mb-4" id="stock-modal-title">Inventory Update</h3>
            
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Product</label>
                <select name="product_id" class="w-full border border-gray-200 rounded-lg p-2 text-sm">
                    <?php foreach($inventory as $prod): ?>
                    <option value="<?= $prod['product_id'] ?>"><?= htmlspecialchars($prod['product_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Quantity</label>
                <input type="number" name="quantity" class="w-full border border-gray-200 rounded-lg p-2 text-sm" placeholder="Amount">
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Reason / Note</label>
                <textarea name="reason" class="w-full border border-gray-200 rounded-lg p-2 text-sm"></textarea>
            </div>

            <input type="hidden" name="update_stock" id="action_update_stock" disabled>
            <input type="hidden" name="request_stock" id="action_request_stock" disabled>

            <div class="flex space-x-2">
                <button type="button" onclick="closeModal('modal-stock')" class="flex-1 py-2 bg-gray-100 rounded-lg text-sm font-bold">Cancel</button>
                <button type="submit" class="flex-1 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold shadow">Submit</button>
            </div>
        </form>
    </div>

    <script>
        function showTab(tab) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.getElementById('tab-' + tab).classList.remove('hidden');
            
            document.querySelectorAll('.sidebar-link').forEach(el => el.classList.remove('active'));
            document.getElementById('nav-' + tab).classList.add('active');
        }

        function openConfirmModal(id, ref) {
            document.getElementById('conf-order-id').value = id;
            document.getElementById('conf-order-ref').innerText = '#' + ref;
            document.getElementById('modal-confirm').classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function openStockModal(type) {
            document.getElementById('modal-stock').classList.remove('hidden');
            document.getElementById('action_update_stock').disabled = true;
            document.getElementById('action_request_stock').disabled = true;

            if(type === 'update') {
                document.getElementById('stock-modal-title').innerText = 'Update Stock Level (Goods Received)';
                document.getElementById('action_update_stock').disabled = false;
            } else {
                document.getElementById('stock-modal-title').innerText = 'Request Stock Transfer';
                document.getElementById('action_request_stock').disabled = false;
            }
        }

        function printPickList(orderRef, items) {
            const win = window.open('', '', 'width=600,height=600');
            win.document.write('<html><head><title>Picking List</title>');
            win.document.write('<style>body{font-family:sans-serif; padding:20px;} table{width:100%; border-collapse:collapse; margin-top:20px;} th, td{border:1px solid #ddd; padding:8px; text-align:left;} th{background-color:#f2f2f2;}</style>');
            win.document.write('</head><body>');
            win.document.write('<h2>Picking List - #' + orderRef + '</h2>');
            win.document.write('<p><strong>Date:</strong> ' + new Date().toLocaleString() + '</p>');
            win.document.write('<p>Please pick the following items for this order:</p>');
            win.document.write('<table><thead><tr><th>Product Name</th><th>Quantity</th><th>Checked</th></tr></thead><tbody>');
            
            items.forEach(item => {
                win.document.write(`<tr><td>${item.name}</td><td>${item.qty}</td><td>[ ]</td></tr>`);
            });
            
            win.document.write('</tbody></table>');
            win.document.write('<br><p>_________________________<br>Picker Signature</p>');
            win.document.write('</body></html>');
            win.document.close();
            win.print();
        }
    </script>
</body>
</html>
