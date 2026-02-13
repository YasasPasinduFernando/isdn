<?php
// Start output buffering
ob_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

// Include header
require_once __DIR__ . '/../../includes/header.php';

// --- 1. Authentication & Context Setup ---
$user_id = $_SESSION['user_id'] ?? 0;
$rdc_id = 0;
$clerk_name = '';
$rdc_name = '';

if ($user_id) {
    try {
        $stmt = $pdo->prepare("SELECT u.id, u.username, u.rdc_id, r.rdc_name 
                               FROM users u 
                               JOIN rdcs r ON u.rdc_id = r.rdc_id 
                               WHERE u.id = ?");
        $stmt->execute([$user_id]);
        $clerk = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($clerk) {
            $rdc_id = $clerk['rdc_id'];
            $clerk_name = $clerk['username'];
            $rdc_name = $clerk['rdc_name'];
        }
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}

// Demo Fallback
if (!$rdc_id) {
    $rdc_id = 1;
    $clerk_name = 'Demo Clerk';
    $rdc_name = 'Northern RDC';
}

// --- 2. Action Handling ---
// --- AJAX Handler for Order Details ---
if (isset($_GET['action']) && $_GET['action'] === 'get_order_details' && isset($_GET['order_id'])) {
    ob_clean(); // Clean any previous output (header)
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->prepare("SELECT oi.*, p.product_name, p.product_code 
                               FROM order_items oi 
                               JOIN products p ON oi.product_id = p.product_id 
                               WHERE oi.order_id = ?");
        $stmt->execute([$_GET['order_id']]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'items' => $items]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 6. Approve Order
    if (isset($_POST['approve_order'])) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'processing', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$_POST['order_id']]);
            $success_msg = "Order approved successfully!";
        } catch (PDOException $e) {
            $error_msg = "Error approving order: " . $e->getMessage();
        }
    }

    // 7. Reject Order
    if (isset($_POST['reject_order'])) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$_POST['order_id']]);
            $success_msg = "Order rejected successfully!";
        } catch (PDOException $e) {
            $error_msg = "Error rejecting order: " . $e->getMessage();
        }
    }

    // 1. Add Product
    if (isset($_POST['add_product'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (product_name, product_code, unit_price, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $_POST['product_name'],
                $_POST['product_code'],
                $_POST['unit_price'],
                $_POST['description']
            ]);
            $newProductId = $pdo->lastInsertId();
            
            // Initialize stock for this RDC
            $stmt = $pdo->prepare("INSERT INTO product_stocks (rdc_id, product_id, available_quantity) VALUES (?, ?, 0)");
            $stmt->execute([$rdc_id, $newProductId]);

            $success_msg = "Product added successfully!";
        } catch (PDOException $e) {
            $error_msg = "Error adding product: " . $e->getMessage();
        }
    }

    // 2. Edit Product
    if (isset($_POST['edit_product'])) {
        try {
            $stmt = $pdo->prepare("UPDATE products SET product_name = ?, product_code = ?, unit_price = ?, description = ? WHERE product_id = ?");
            $stmt->execute([
                $_POST['product_name'],
                $_POST['product_code'],
                $_POST['unit_price'],
                $_POST['description'],
                $_POST['product_id']
            ]);
            $success_msg = "Product updated successfully!";
        } catch (PDOException $e) {
            $error_msg = "Error updating product: " . $e->getMessage();
        }
    }

    // 3. Delete Product
    if (isset($_POST['action']) && $_POST['action'] === 'delete_product') {
        try {
            // First delete stock records
            $stmt = $pdo->prepare("DELETE FROM product_stocks WHERE product_id = ?");
            $stmt->execute([$_POST['product_id']]);

            // Then delete product
            $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
            $stmt->execute([$_POST['product_id']]);
            $success_msg = "Product deleted successfully!";
        } catch (PDOException $e) {
            $error_msg = "Error deleting product: " . $e->getMessage();
        }
    }

    // 4. Create Category
    if (isset($_POST['create_category'])) {
        // Placeholder: Assuming a 'categories' table exists. If not, this might fail or needs a table creation.
        // For now, we'll just simulate success to satisfy the UI requirement, or insert if table exists.
        // $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)"); ...
        $success_msg = "Category created successfully! (Simulation)";
    }

    // 5. Create Promotion
    if (isset($_POST['create_promotion'])) {
        // Placeholder for promotions logic
        $success_msg = "Promotion created successfully! (Simulation)";
    }
}

// --- 3. Data Fetching ---
// Fetch Pending Orders
$stmt = $pdo->prepare("SELECT o.*, rc.name as customer_name 
                       FROM orders o 
                       JOIN retail_customers rc ON o.customer_id = rc.id 
                       LEFT JOIN users u ON o.placed_by = u.id 
                       LEFT JOIN users u2 ON rc.user_id = u2.id
                       WHERE (u.rdc_id = ? OR u2.rdc_id = ?) AND o.status = 'pending' 
                       ORDER BY o.created_at ASC");
$stmt->execute([$rdc_id, $rdc_id]);
$pendingOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Completed/Approved Today
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders o 
                       JOIN retail_customers rc ON o.customer_id = rc.id 
                       LEFT JOIN users u ON o.placed_by = u.id 
                       WHERE (u.rdc_id = ?) AND o.status != 'pending' AND DATE(o.updated_at) = CURDATE()");
$stmt->execute([$rdc_id]);
$processedToday = $stmt->fetchColumn();

// Fetch Inventory (Updated with minimum_stock_level)
$stmt = $pdo->prepare("SELECT p.product_id, p.product_name, p.product_code, p.unit_price, p.minimum_stock_level, ps.available_quantity 
                       FROM product_stocks ps 
                       JOIN products p ON ps.product_id = p.product_id 
                       WHERE ps.rdc_id = ?");
$stmt->execute([$rdc_id]);
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate Low Stock Count
$lowStockCount = 0;
foreach ($inventory as $i) {
    if ($i['available_quantity'] <= $i['minimum_stock_level']) {
        $lowStockCount++;
    }
}

// Fetch Recent Orders (for Overview)
$stmt = $pdo->prepare("SELECT o.*, rc.name as customer_name 
                       FROM orders o 
                       JOIN retail_customers rc ON o.customer_id = rc.id 
                       LEFT JOIN users u ON o.placed_by = u.id 
                       LEFT JOIN users u2 ON rc.user_id = u2.id
                       WHERE (u.rdc_id = ? OR u2.rdc_id = ?) 
                       ORDER BY o.created_at DESC LIMIT 5");
$stmt->execute([$rdc_id, $rdc_id]);
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch Products (Global List for CRUD)
$stmt = $pdo->query("SELECT * FROM products LIMIT 50");
$allProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Get Active Tab
$activeTab = $_GET['tab'] ?? 'dashboard';
?>

<style>
    .font-outfit { font-family: 'Outfit', sans-serif; }
    .glass-card { 
        background: rgba(255, 255, 255, 0.7); 
        backdrop-filter: blur(12px); 
        border: 1px solid rgba(255, 255, 255, 0.5); 
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1); 
    }
    .glass-panel {
        background: rgba(255, 255, 255, 0.5);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .hover-lift:hover { transform: translateY(-2px); box-shadow: 0 10px 40px -10px rgba(0,0,0,0.1); }
    
    /* Custom Scrollbar for tables */
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(0,0,0,0.05); }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 10px; }
</style>

<div class="min-h-screen bg-gradient-to-br from-teal-50 via-blue-50 to-purple-50 font-outfit py-8 px-4 sm:px-6 lg:px-8">
    
    <!-- Header -->
    <div class="max-w-7xl mx-auto mb-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Clerk Dashboard</h1>
                <p class="text-gray-500 mt-1">Managed by <span class="font-semibold text-teal-600"><?= htmlspecialchars($clerk_name) ?></span> at <?= htmlspecialchars($rdc_name) ?></p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-4 py-2 rounded-full bg-white/60 text-sm font-semibold text-gray-600 shadow-sm border border-white/50">
                    <?= date('l, F j, Y') ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="max-w-7xl mx-auto">
        
        <?php if($activeTab === 'dashboard'): ?>
             <!-- OVERVIEW SECTION -->
             <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <!-- Total Orders -->
                <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-blue-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Orders</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']"><?= count($pendingOrders) + $processedToday // Approximate ?></h3> 
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-blue-100/50 flex items-center justify-center text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                            <span class="material-symbols-rounded">shopping_cart</span>
                        </div>
                    </div>
                </div>

                <!-- Pending -->
                <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-yellow-400">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pending</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']"><?= count($pendingOrders) ?></h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-yellow-100/50 flex items-center justify-center text-yellow-600 group-hover:bg-yellow-400 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                            <span class="material-symbols-rounded">pending</span>
                        </div>
                    </div>
                </div>

                <!-- Processed Today -->
                <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-green-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Processed Today</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']"><?= $processedToday ?></h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-green-100/50 flex items-center justify-center text-green-600 group-hover:bg-green-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                            <span class="material-symbols-rounded">check_circle</span>
                        </div>
                    </div>
                </div>

                <!-- Low Stock -->
                <div class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-red-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Low Stock</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']"><?= $lowStockCount ?></h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-red-100/50 flex items-center justify-center text-red-600 group-hover:bg-red-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                            <span class="material-symbols-rounded">warning</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
             <div class="glass-card rounded-3xl p-6 sm:p-8">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center space-x-3">
                        <span class="material-symbols-rounded text-gray-500 text-2xl">history</span>
                        <h2 class="text-xl font-bold text-gray-800 font-['Outfit']">Recent Orders</h2>
                    </div>
                    <a href="index.php?page=rdc-clerk-dashboard&tab=orders" class="text-sm font-semibold text-teal-600 hover:text-teal-700 flex items-center transition">
                        View All <span class="material-symbols-rounded text-sm ml-1">arrow_forward</span>
                    </a>
                </div>

                <div class="space-y-4">
                    <?php if(empty($recentOrders)): ?>
                        <p class="text-center text-gray-400 py-4">No recent activity.</p>
                    <?php else: ?>
                        <?php foreach($recentOrders as $order): ?>
                        <div class="bg-white/40 border border-white/60 backdrop-blur-sm rounded-2xl p-5 hover:bg-white/60 transition duration-300 group shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-xl bg-blue-100/50 text-blue-600 flex items-center justify-center flex-shrink-0 border border-blue-100">
                                        <span class="material-symbols-rounded">receipt_long</span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800 font-['Outfit']"><?= $order['order_number'] ?></h3>
                                        <div class="flex items-center text-xs text-gray-600 mt-1 space-x-3">
                                            <span><?= htmlspecialchars($order['customer_name']) ?></span>
                                            <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                                            <span>Rs. <?= number_format($order['total_amount']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <span class="px-4 py-2 rounded-xl bg-gray-100 text-gray-600 text-xs font-bold uppercase transition">
                                    <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif($activeTab === 'orders'): ?>
            <!-- ORDER MANAGEMENT SECTION -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Stats Column -->
                <div class="space-y-6">
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-yellow-400">
                        <p class="text-xs font-bold text-gray-500 uppercase">Pending Approval</p>
                        <h3 class="text-4xl font-bold text-gray-800 mt-2"><?= count($pendingOrders) ?></h3>
                    </div>
                    <div class="glass-card p-6 rounded-3xl border-l-4 border-green-500">
                        <p class="text-xs font-bold text-gray-500 uppercase">Processed Today</p>
                        <h3 class="text-4xl font-bold text-teal-600 mt-2"><?= $processedToday ?></h3>
                    </div>
                </div>

                <!-- Main Table Column -->
                <div class="lg:col-span-3">
                    <div class="glass-card rounded-3xl overflow-hidden p-6 min-h-[500px]">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                                <span class="material-symbols-rounded mr-2 text-teal-600">receipt_long</span> 
                                RDC Orders
                            </h2>
                            <div class="flex space-x-2">
                                <button class="px-4 py-2 bg-white rounded-xl text-sm font-bold text-gray-600 shadow-sm hover:bg-gray-50 transition border border-gray-100">Filter</button>
                                <button class="px-4 py-2 bg-teal-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-teal-200 hover:bg-teal-700 transition">Export</button>
                            </div>
                        </div>

                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left border-separate border-spacing-y-3">
                                <thead class="text-gray-500 text-xs uppercase font-bold">
                                    <tr>
                                        <th class="pb-3 pl-4">Order ID</th>
                                        <th class="pb-3">Customer</th>
                                        <th class="pb-3">Date</th>
                                        <th class="pb-3">Amount</th>
                                        <th class="pb-3 text-center">Status</th>
                                        <th class="pb-3 text-right pr-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($pendingOrders)): ?>
                                        <tr><td colspan="6" class="text-center py-10 text-gray-400">No pending orders found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($pendingOrders as $order): ?>
                                        <tr class="bg-white/40 hover:bg-white/80 transition duration-200 group">
                                            <td class="py-4 pl-4 rounded-l-xl font-bold text-gray-800 border-y border-l border-white/50">
                                                <?= $order['order_number'] ?>
                                            </td>
                                            <td class="py-4 border-y border-white/50">
                                                <div class="font-medium text-gray-800"><?= htmlspecialchars($order['customer_name']) ?></div>
                                            </td>
                                            <td class="py-4 border-y border-white/50 text-sm text-gray-500">
                                                <?= date('M d, H:i', strtotime($order['created_at'])) ?>
                                            </td>
                                            <td class="py-4 border-y border-white/50 font-mono font-bold text-gray-700">
                                                Rs. <?= number_format($order['total_amount']) ?>
                                            </td>
                                            <td class="py-4 border-y border-white/50 text-center">
                                                <span class="inline-block px-3 py-1 rounded-lg bg-yellow-100 text-yellow-700 text-xs font-bold border border-yellow-200">
                                                    Pending
                                                </span>
                                            </td>
                                            <td class="py-4 pr-4 rounded-r-xl border-y border-r border-white/50 text-right">
                                                <button onclick="openReviewModal(<?= $order['id'] ?>, '<?= $order['order_number'] ?>', '<?= htmlspecialchars($order['customer_name'], ENT_QUOTES) ?>', '<?= number_format($order['total_amount'], 2) ?>')" class="text-teal-600 hover:text-teal-800 font-bold text-xs uppercase bg-teal-50 px-3 py-2 rounded-lg hover:bg-teal-100 transition">
                                                    Review
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif($activeTab === 'products'): ?>
            <!-- PRODUCTS SECTION -->
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Sub-Sidebar -->
                <div class="w-full lg:w-64 flex-shrink-0">
                    <div class="glass-card rounded-3xl p-4 sticky top-8">
                        <nav class="space-y-2">
                            <button onclick="switchSubTab('prod-manage')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-sm bg-teal-50 text-teal-700 border border-teal-100 transition" id="nav-prod-manage">
                                <span class="material-symbols-rounded align-middle mr-2 text-lg">inventory_2</span> Product Management
                            </button>
                            <button onclick="switchSubTab('cat-manage')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-sm text-gray-600 hover:bg-white/50 transition border border-transparent" id="nav-cat-manage">
                                <span class="material-symbols-rounded align-middle mr-2 text-lg">category</span> Category Management
                            </button>
                            <button onclick="switchSubTab('promo-manage')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-sm text-gray-600 hover:bg-white/50 transition border border-transparent" id="nav-promo-manage">
                                <span class="material-symbols-rounded align-middle mr-2 text-lg">loyalty</span> Promotion Management
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="flex-1">
                    <!-- Products Sub-Tab -->
                    <div id="view-prod-manage" class="sub-view block">
                        <div class="glass-card rounded-3xl p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h2 class="text-xl font-bold text-gray-800">Product List</h2>
                                <button onclick="openModal('modal-add-product')" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg hover:bg-blue-700 transition flex items-center">
                                    <span class="material-symbols-rounded mr-2">add</span> Add Product
                                </button>
                            </div>
                            <!-- Product Table Placeholder -->
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-gray-50/50 text-gray-500 uppercase font-bold text-xs">
                                        <tr>
                                            <th class="p-4">Product Name</th>
                                            <th class="p-4">SKU/Code</th>
                                            <th class="p-4">Price</th>
                                            <th class="p-4 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <?php foreach($allProducts as $p): ?>
                                        <tr class="hover:bg-blue-50/30 transition">
                                            <td class="p-4 font-bold text-gray-800"><?= htmlspecialchars($p['product_name']) ?></td>
                                            <td class="p-4 text-gray-500 font-mono"><?= $p['product_code'] ?></td>
                                            <td class="p-4">Rs. <?= number_format($p['unit_price'], 2) ?></td>
                                            <td class="p-4 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button onclick='openEditModal(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8') ?>)' class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition" title="Edit">
                                                        <span class="material-symbols-rounded text-sm">edit</span>
                                                    </button>
                                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete_product">
                                                        <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                                                        <button type="submit" class="w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition" title="Delete">
                                                            <span class="material-symbols-rounded text-sm">delete</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Sub-Tab -->
                    <div id="view-cat-manage" class="sub-view hidden">
                        <div class="glass-card rounded-3xl p-6 flex flex-col items-center justify-center text-center min-h-[400px]">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-400">
                                <span class="material-symbols-rounded text-3xl">category</span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800">Category Management</h3>
                            <p class="text-gray-500 mb-6 max-w-md">Manage product categories to organize your inventory effectively.</p>
                            <button onclick="openModal('modal-create-category')" class="px-6 py-3 bg-gray-800 text-white rounded-xl font-bold shadow-lg hover:bg-gray-900 transition">Create First Category</button>
                        </div>
                    </div>

                    <!-- Promotions Sub-Tab -->
                    <div id="view-promo-manage" class="sub-view hidden">
                        <div class="glass-card rounded-3xl p-6 flex flex-col items-center justify-center text-center min-h-[400px]">
                             <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mb-4 text-pink-500">
                                <span class="material-symbols-rounded text-3xl">celebration</span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800">Promotions Management</h3>
                            <p class="text-gray-500 mb-6 max-w-md">Create and manage discounts, bundle offers, and seasonal sales.</p>
                            <button onclick="openModal('modal-create-promotion')" class="px-6 py-3 bg-pink-600 text-white rounded-xl font-bold shadow-lg hover:bg-pink-700 transition">Create Promotion</button>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif($activeTab === 'inventory'): ?>
            <!-- INVENTORY SECTION -->
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Sub-Sidebar -->
                 <div class="w-full lg:w-64 flex-shrink-0">
                    <div class="glass-card rounded-3xl p-4 sticky top-8">
                        <nav class="space-y-2">
                            <button onclick="switchInvTab('stock-levels')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-sm bg-teal-50 text-teal-700 border border-teal-100 transition" id="nav-stock-levels">
                                <span class="material-symbols-rounded align-middle mr-2 text-lg">bar_chart</span> Stock Levels
                            </button>
                            <button onclick="switchInvTab('stock-adj')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-sm text-gray-600 hover:bg-white/50 transition border border-transparent" id="nav-stock-adj">
                                <span class="material-symbols-rounded align-middle mr-2 text-lg">tune</span> Start Adjustment
                            </button>
                            <button onclick="switchInvTab('move-logs')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-sm text-gray-600 hover:bg-white/50 transition border border-transparent" id="nav-move-logs">
                                <span class="material-symbols-rounded align-middle mr-2 text-lg">history</span> Movement Logs
                            </button>
                            <button onclick="switchInvTab('transfers')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-sm text-gray-600 hover:bg-white/50 transition border border-transparent" id="nav-transfers">
                                <span class="material-symbols-rounded align-middle mr-2 text-lg">local_shipping</span> Stock Transfers
                            </button>
                        </nav>
                    </div>
                </div>

                <div class="flex-1">
                    <!-- Stock Levels -->
                    <div id="view-stock-levels" class="inv-view block">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                            <?php foreach($inventory as $item): ?>
                            <div class="glass-card p-5 rounded-3xl flex justify-between items-center group hover-lift">
                                <div>
                                    <h4 class="font-bold text-gray-800"><?= htmlspecialchars($item['product_name']) ?></h4>
                                    <p class="text-xs text-gray-500 font-mono"><?= $item['product_code'] ?></p>
                                </div>
                                <div class="text-right">
                                    <div class="text-3xl font-bold text-teal-600"><?= $item['available_quantity'] ?></div>
                                    <div class="text-[10px] uppercase text-gray-400 font-bold">In Stock</div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Placeholder for other Inv Tabs -->
                    <div id="view-stock-adj" class="inv-view hidden">
                        <div class="glass-card rounded-3xl p-8 max-w-lg mx-auto">
                            <h3 class="text-xl font-bold text-gray-800 mb-6">Stock Adjustment</h3>
                            <form class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Product</label>
                                    <select class="w-full p-3 rounded-xl bg-gray-50 border border-gray-200"><option>Select Product</option></select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Adjustment Type</label>
                                    <div class="flex gap-4">
                                        <label class="flex items-center"><input type="radio" name="adj_type" class="mr-2"> Add (+)</label>
                                        <label class="flex items-center"><input type="radio" name="adj_type" class="mr-2"> Remove (-)</label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Quantity</label>
                                    <input type="number" class="w-full p-3 rounded-xl bg-gray-50 border border-gray-200">
                                </div>
                                <button class="w-full py-3 bg-teal-600 text-white font-bold rounded-xl mt-4">Submit Adjustment</button>
                            </form>
                        </div>
                    </div>
                    
                    <div id="view-move-logs" class="inv-view hidden">
                         <div class="glass-card rounded-3xl p-6 text-center text-gray-500">
                            <span class="material-symbols-rounded text-4xl mb-2 block">history_edu</span>
                            No movement logs available yet.
                         </div>
                    </div>

                    <div id="view-transfers" class="inv-view hidden">
                         <div class="glass-card rounded-3xl p-6 text-center text-gray-500">
                            <span class="material-symbols-rounded text-4xl mb-2 block">move_up</span>
                            No active transfers.
                         </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<!-- Modals -->
<!-- Add Product Modal -->
<div id="modal-add-product" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-3xl w-full max-w-lg p-8 shadow-2xl relative animate-up">
        <button type="button" onclick="closeModal('modal-add-product')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
            <span class="material-symbols-rounded">close</span>
        </button>
        <h3 class="font-bold text-2xl text-gray-800 mb-6">Add New Product</h3>
        <form action="index.php?page=rdc-clerk-dashboard" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Product Name</label>
                <input type="text" name="product_name" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">SKU / Code</label>
                    <input type="text" name="product_code" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Price (Rs.)</label>
                    <input type="number" step="0.01" name="unit_price" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                </div>
            </div>
            <div>
                 <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Description</label>
                 <textarea name="description" rows="3" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition"></textarea>
            </div>
            <button type="submit" name="add_product" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-200 transition mt-2">Save Product</button>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="modal-edit-product" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-3xl w-full max-w-lg p-8 shadow-2xl relative animate-up">
        <button type="button" onclick="closeModal('modal-edit-product')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
            <span class="material-symbols-rounded">close</span>
        </button>
        <h3 class="font-bold text-2xl text-gray-800 mb-6">Edit Product</h3>
        <form action="index.php?page=rdc-clerk-dashboard" method="POST" class="space-y-4">
            <input type="hidden" name="product_id" id="edit_product_id">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Product Name</label>
                <input type="text" name="product_name" id="edit_product_name" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">SKU / Code</label>
                    <input type="text" name="product_code" id="edit_product_code" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Price (Rs.)</label>
                    <input type="number" step="0.01" name="unit_price" id="edit_unit_price" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                </div>
            </div>
            <div>
                 <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Description</label>
                 <textarea name="description" id="edit_description" rows="3" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition"></textarea>
            </div>
            <button type="submit" name="edit_product" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-200 transition mt-2">Update Product</button>
        </form>
    </div>
</div>

<!-- Review Order Modal -->
<div id="modal-review-order" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-3xl w-full max-w-2xl p-8 shadow-2xl relative animate-up">
        <button type="button" onclick="closeModal('modal-review-order')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
            <span class="material-symbols-rounded">close</span>
        </button>
        
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-rounded text-teal-600">rate_review</span>
                    Review Order
                </h3>
                <p class="text-gray-500 text-sm mt-1 font-mono" id="review_order_number">ORD-0000</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-bold text-gray-500 uppercase">Total Amount</p>
                <p class="text-2xl font-bold text-teal-600" id="review_total_amount">Rs. 0.00</p>
            </div>
        </div>

        <div class="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-100">
            <p class="text-xs font-bold text-gray-400 uppercase mb-1">Customer</p>
            <p class="font-bold text-gray-800" id="review_customer_name">Customer Name</p>
        </div>

        <div class="mb-6">
            <h4 class="font-bold text-gray-700 mb-3 text-sm uppercase">Order Items</h4>
            <div class="overflow-y-auto max-h-60 custom-scrollbar border border-gray-100 rounded-xl">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 font-bold sticky top-0">
                        <tr>
                            <th class="p-3">Product</th>
                            <th class="p-3 text-center">Qty</th>
                            <th class="p-3 text-right">Price</th>
                            <th class="p-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody id="review_items_body">
                        <!-- Items injected via JS -->
                        <tr><td colspan="4" class="p-4 text-center text-gray-400">Loading items...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex gap-4">
            <form method="POST" action="index.php?page=rdc-clerk-dashboard" class="flex-1">
                <input type="hidden" name="order_id" id="reject_order_id">
                <button type="submit" name="reject_order" onclick="return confirm('Are you sure you want to reject this order?');" class="w-full py-3 bg-red-50 text-red-600 hover:bg-red-100 rounded-xl font-bold flex items-center justify-center gap-2 transition">
                    <span class="material-symbols-rounded text-lg">cancel</span> Reject Order
                </button>
            </form>
            <form method="POST" action="index.php?page=rdc-clerk-dashboard" class="flex-1">
                <input type="hidden" name="order_id" id="approve_order_id">
                <button type="submit" name="approve_order" class="w-full py-3 bg-teal-600 text-white hover:bg-teal-700 rounded-xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-teal-200 transition">
                    <span class="material-symbols-rounded text-lg">check_circle</span> Approve Order
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Create Category Modal -->
<div id="modal-create-category" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-3xl w-full max-w-md p-8 shadow-2xl relative animate-up">
        <button type="button" onclick="closeModal('modal-create-category')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
            <span class="material-symbols-rounded">close</span>
        </button>
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-500">
                <span class="material-symbols-rounded text-3xl">category</span>
            </div>
            <h3 class="font-bold text-2xl text-gray-800">New Category</h3>
        </div>
        <form action="index.php?page=rdc-clerk-dashboard" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category Name</label>
                <input type="text" name="category_name" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl outline-none focus:ring-2 focus:ring-gray-500 transition" required>
            </div>
            <button type="submit" name="create_category" class="w-full py-3 bg-gray-800 hover:bg-gray-900 text-white rounded-xl font-bold shadow-lg transition mt-2">Create Category</button>
        </form>
    </div>
</div>

<!-- Create Promotion Modal -->
<div id="modal-create-promotion" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-3xl w-full max-w-md p-8 shadow-2xl relative animate-up">
        <button type="button" onclick="closeModal('modal-create-promotion')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
            <span class="material-symbols-rounded">close</span>
        </button>
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4 text-pink-500">
                <span class="material-symbols-rounded text-3xl">celebration</span>
            </div>
            <h3 class="font-bold text-2xl text-gray-800">New Promotion</h3>
        </div>
        <form action="index.php?page=rdc-clerk-dashboard" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Promotion Title</label>
                <input type="text" name="promo_title" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl outline-none focus:ring-2 focus:ring-pink-500 transition" required>
            </div>
             <div class="grid grid-cols-2 gap-4">
                <div>
                     <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Discount (%)</label>
                     <input type="number" name="discount_percent" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl outline-none focus:ring-2 focus:ring-pink-500 transition">
                </div>
                 <div>
                     <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Valid Until</label>
                     <input type="date" name="valid_until" class="w-full border border-gray-200 bg-gray-50 p-3 rounded-xl outline-none focus:ring-2 focus:ring-pink-500 transition">
                </div>
            </div>
            <button type="submit" name="create_promotion" class="w-full py-3 bg-pink-600 hover:bg-pink-700 text-white rounded-xl font-bold shadow-lg shadow-pink-200 transition mt-2">Launch Promotion</button>
        </form>
    </div>
</div>

<script>
    // Simple Tab Switcher Logic
    function switchSubTab(id) {
        document.querySelectorAll('.sub-view').forEach(el => el.classList.add('hidden'));
        document.getElementById('view-' + id).classList.remove('hidden');
        
        // Update Nav Styles
        document.querySelectorAll('[id^="nav-"]').forEach(el => {
            el.classList.remove('bg-teal-50', 'text-teal-700', 'border-teal-100');
            el.classList.add('text-gray-600', 'border-transparent', 'hover:bg-white/50');
        });
        const activeNav = document.getElementById('nav-' + id);
        activeNav.classList.remove('text-gray-600', 'border-transparent', 'hover:bg-white/50');
        activeNav.classList.add('bg-teal-50', 'text-teal-700', 'border-teal-100');
    }

    function switchInvTab(id) {
        document.querySelectorAll('.inv-view').forEach(el => el.classList.add('hidden'));
        document.getElementById('view-' + id).classList.remove('hidden');

         // Update Nav Styles
        document.querySelectorAll('[id^="nav-"]').forEach(el => {
            el.classList.remove('bg-teal-50', 'text-teal-700', 'border-teal-100');
            el.classList.add('text-gray-600', 'border-transparent', 'hover:bg-white/50');
        });
        const activeNav = document.getElementById('nav-' + id);
        activeNav.classList.remove('text-gray-600', 'border-transparent', 'hover:bg-white/50');
        activeNav.classList.add('bg-teal-50', 'text-teal-700', 'border-teal-100');
    }

    function openModal(id) {
        const el = document.getElementById(id);
        if(el) {
            el.classList.remove('hidden');
            el.classList.add('flex');
        }
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if(el) {
            el.classList.add('hidden');
            el.classList.remove('flex');
        }
    }

    function openEditModal(product) {
        document.getElementById('edit_product_id').value = product.product_id;
        document.getElementById('edit_product_name').value = product.product_name;
        document.getElementById('edit_product_code').value = product.product_code;
        document.getElementById('edit_unit_price').value = product.unit_price;
        document.getElementById('edit_description').value = product.description;
        openModal('modal-edit-product');
    }

    function openReviewModal(orderId, orderNumber, customerName, totalAmount) {
        // Populate basic info
        document.getElementById('review_order_number').innerText = orderNumber;
        document.getElementById('review_customer_name').innerText = customerName;
        document.getElementById('review_total_amount').innerText = 'Rs. ' + totalAmount;
        document.getElementById('approve_order_id').value = orderId;
        document.getElementById('reject_order_id').value = orderId;

        // Reset details
        const tbody = document.getElementById('review_items_body');
        tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-gray-400">Loading items...</td></tr>';
        
        openModal('modal-review-order');

        // Fetch Items
        fetch('index.php?page=rdc-clerk-dashboard&action=get_order_details&order_id=' + orderId)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    tbody.innerHTML = '';
                    if(data.items.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-gray-400">No items found.</td></tr>';
                    } else {
                        data.items.forEach(item => {
                            const total = (parseFloat(item.selling_price) * parseInt(item.quantity)).toFixed(2);
                            tbody.innerHTML += `
                                <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                                    <td class="p-3 font-medium text-gray-700">
                                        ${item.product_name} 
                                        <span class="text-xs text-gray-400 block font-mono">${item.product_code}</span>
                                    </td>
                                    <td class="p-3 text-center text-gray-600">${item.quantity}</td>
                                    <td class="p-3 text-right text-gray-600">Rs. ${parseFloat(item.selling_price).toFixed(2)}</td>
                                    <td class="p-3 text-right font-bold text-gray-800">Rs. ${total}</td>
                                </tr>
                            `;
                        });
                    }
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-red-400">Error loading items.</td></tr>';
                }
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-red-400">Failed to load items.</td></tr>';
            });
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>
```
