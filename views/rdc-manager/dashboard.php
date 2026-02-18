<?php
require_once __DIR__ . '/../../includes/header.php';

// Use controller-provided data when available; otherwise fall back to sensible defaults / dummy data
if (!isset($current_user)) {
    $current_user = [
        'user_id' => $_SESSION['user_id'] ?? 5,
        'name' => $_SESSION['username'] ?? 'Manager',
        'role' => $_SESSION['role'] ?? 'rdc_manager',
        'rdc_id' => $_SESSION['rdc_id'] ?? 2,
        'rdc_name' => $_SESSION['rdc_name'] ?? ($rdcName ?? 'South RDC'),
        'rdc_code' => $_SESSION['rdc_code'] ?? ($rdcCode ?? 'SOUTH')
    ];
}

// Expect `$current_stock` to be provided by the controller. If not present, use an empty array
if (!isset($current_stock) || !is_array($current_stock)) {
    $current_stock = [];
}

// Calculate KPIs
$total_products = count($current_stock);
$critical_stock = count(array_filter($current_stock, fn($item) => $item['status'] === 'CRITICAL'));
$low_stock = count(array_filter($current_stock, fn($item) => $item['status'] === 'LOW'));
$ok_stock = count(array_filter($current_stock, fn($item) => $item['status'] === 'OK'));
$total_stock_value = array_sum(array_map(fn($item) => $item['current_stock'] * $item['unit_price'], $current_stock));

// Expect `$pending_transfers` to be provided by the controller. If not present, use an empty array
if (!isset($pending_transfers) || !is_array($pending_transfers)) {
    $pending_transfers = [];
}

// Expect `$recent_orders` to be provided by the controller. If not present, use an empty array
if (!isset($recent_orders) || !is_array($recent_orders)) {
    $recent_orders = [];
}

// Expect `$recent_movements` to be provided by the controller. If not present, use an empty array
if (!isset($recent_movements) || !is_array($recent_movements)) {
    $recent_movements = [];
}

// Count pending approvals and urgent transfers
$pending_approvals = count(array_filter($pending_transfers, fn($t) => $t['status'] === 'PENDING'));
$urgent_transfers = count(array_filter($pending_transfers, fn($t) => $t['is_urgent']));
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RDC Manager Dashboard - ISDN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        * {
            font-family: 'Outfit', sans-serif;
        }
        
        .mono {
            font-family: 'Space Mono', monospace;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-up {
            animation: slideUp 0.4s ease-out;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .pulse-slow {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<div class="bg-gradient-to-br from-purple-50 to-blue-50 min-h-screen py-6 sm:py-8">
    <div class="container mx-auto px-4 max-w-7xl">

        <!-- Welcome Header -->
        <div class="bg-white rounded-2xl shadow-xl p-5 sm:p-8 mb-6 slide-up">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-4xl font-bold text-gray-800">
                        Welcome, <span class="text-purple-600"><?php echo $current_user['name']; ?>!</span>
                    </h1>
                    <p class="text-gray-600 mt-2 flex items-center">
                        <i class="fas fa-warehouse mr-2 text-purple-600"></i>
                        <?php echo $current_user['rdc_name']; ?> - <?php echo date('l, F d, Y'); ?>
                    </p>
                </div>
                <div class="hidden md:block">
                    <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-4 rounded-full">
                        <i class="fas fa-chart-line text-white text-3xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Performance Indicators -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
            <!-- Total Products -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500 slide-up" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wide font-medium mb-1">Total Products</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $total_products; ?></p>
                        <p class="text-xs text-gray-500 mt-1">In inventory</p>
                    </div>
                    <div class="h-14 w-14 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-boxes text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Critical Stock -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500 slide-up" style="animation-delay: 0.2s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wide font-medium mb-1">Critical Stock</p>
                        <p class="text-3xl font-bold text-red-600"><?php echo $critical_stock; ?></p>
                        <p class="text-xs text-red-500 mt-1 pulse-slow">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Needs attention
                        </p>
                    </div>
                    <div class="h-14 w-14 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-red-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500 slide-up" style="animation-delay: 0.3s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wide font-medium mb-1">Pending Approvals</p>
                        <p class="text-3xl font-bold text-yellow-600"><?php echo $pending_approvals; ?></p>
                        <p class="text-xs text-gray-500 mt-1">Transfer requests</p>
                    </div>
                    <div class="h-14 w-14 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Stock Value -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500 slide-up" style="animation-delay: 0.4s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wide font-medium mb-1">Stock Value</p>
                        <p class="text-3xl font-bold text-green-600">LKR <?php echo number_format($total_stock_value, 0); ?></p>
                        <p class="text-xs text-gray-500 mt-1">Current inventory</p>
                    </div>
                    <div class="h-14 w-14 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6 slide-up" style="animation-delay: 0.5s">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-bolt mr-2 text-purple-600"></i>
                Quick Actions
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <a href="<?php echo BASE_PATH; ?>/index.php?page=request-product-units" 
                   class="flex items-center justify-center px-4 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg shadow-md hover:from-purple-700 hover:to-blue-700 transition font-semibold">
                    <i class="fas fa-inbox mr-2"></i>Request Stock
                </a>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=send-product-units" 
                   class="flex items-center justify-center px-4 py-3 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg shadow-md hover:from-green-700 hover:to-teal-700 transition font-semibold">
                    <i class="fas fa-paper-plane mr-2"></i>Approve Transfers
                </a>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=stock-reports" 
                   class="flex items-center justify-center px-4 py-3 bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-lg shadow-md hover:from-orange-700 hover:to-red-700 transition font-semibold">
                    <i class="fas fa-chart-bar mr-2"></i>View Reports
                </a>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=stock-movement-management" 
                   class="flex items-center justify-center px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg shadow-md hover:from-indigo-700 hover:to-purple-700 transition font-semibold">
                    <i class="fas fa-exchange-alt mr-2"></i>Stock Movements
                </a>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Low Stock Alerts -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden slide-up" style="animation-delay: 0.6s">
                <div class="bg-gradient-to-r from-red-600 to-orange-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Low Stock Alerts
                    </h3>
                    <p class="text-red-100 text-sm mt-1">Products requiring immediate attention</p>
                </div>
                <div class="p-6">
                    <?php 
                    $low_stock_items = array_filter($current_stock, fn($item) => in_array($item['status'], ['CRITICAL', 'LOW']));
                    if (empty($low_stock_items)): 
                    ?>
                        <div class="text-center py-8">
                            <div class="h-16 w-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                            </div>
                            <p class="text-gray-600">All stock levels are healthy!</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            <?php foreach ($low_stock_items as $item): 
                                $shortage = $item['minimum_level'] - $item['current_stock'];
                                $urgency = $item['status'];
                            ?>
                            <div class="border-l-4 <?php echo $urgency === 'CRITICAL' ? 'border-red-500 bg-red-50' : 'border-yellow-500 bg-yellow-50'; ?> rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <span class="mono text-xs text-gray-500"><?php echo $item['product_code']; ?></span>
                                            <span class="px-2 py-0.5 <?php echo $urgency === 'CRITICAL' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'; ?> text-xs font-semibold rounded-full">
                                                <?php echo $urgency; ?>
                                            </span>
                                        </div>
                                        <h4 class="font-semibold text-gray-900"><?php echo $item['product_name']; ?></h4>
                                        <p class="text-xs text-gray-500"><?php echo $item['category']; ?></p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-sm mb-2">
                                    <div>
                                        <div class="text-xs text-gray-500">Current</div>
                                        <div class="font-bold text-red-600"><?php echo $item['current_stock']; ?></div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Required</div>
                                        <div class="font-bold text-gray-900"><?php echo $item['minimum_level']; ?></div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Shortage</div>
                                        <div class="font-bold text-orange-600"><?php echo $shortage; ?></div>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                    <div class="<?php echo $urgency === 'CRITICAL' ? 'bg-red-500' : 'bg-yellow-500'; ?> h-2 rounded-full" 
                                         style="width: <?php echo min(($item['current_stock'] / $item['minimum_level']) * 100, 100); ?>%"></div>
                                </div>
                                <a href="<?php echo BASE_PATH; ?>/index.php?page=request-product-units" 
                                   class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center">
                                    <i class="fas fa-plus-circle mr-1"></i>Request Stock
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pending Transfer Requests -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden slide-up" style="animation-delay: 0.7s">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <i class="fas fa-exchange-alt mr-2"></i>
                        Pending Transfer Requests
                    </h3>
                    <p class="text-blue-100 text-sm mt-1">Transfers awaiting your action</p>
                </div>
                <div class="p-6">
                    <?php if (empty($pending_transfers)): ?>
                        <div class="text-center py-8">
                            <div class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-check text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-600">No pending transfer requests</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            <?php foreach ($pending_transfers as $transfer): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-md transition">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <span class="mono text-sm font-bold text-gray-900"><?php echo $transfer['transfer_number']; ?></span>
                                            <?php if ($transfer['is_urgent']): ?>
                                            <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full flex items-center pulse-slow">
                                                <i class="fas fa-bolt text-xs mr-1"></i>URGENT
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-sm text-gray-600">From: <span class="font-semibold text-gray-900"><?php echo $transfer['source_rdc']; ?></span></p>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?php 
                                        echo $transfer['status'] === 'PENDING' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'; 
                                    ?>">
                                        <?php echo $transfer['status']; ?>
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                                    <div>
                                        <div class="text-xs text-gray-500">Items</div>
                                        <div class="font-semibold text-gray-900"><?php echo $transfer['items']; ?> products</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Date</div>
                                        <div class="font-semibold text-gray-900"><?php echo date('M d, h:i A', strtotime($transfer['date'])); ?></div>
                                    </div>
                                </div>
                                <a href="<?php echo BASE_PATH; ?>/index.php?page=send-product-units" 
                                   class="text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center">
                                    <i class="fas fa-arrow-right mr-1"></i>Review & Approve
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Recent Activity Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            
            <!-- Recent Orders -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden slide-up" style="animation-delay: 0.8s">
                <div class="bg-gradient-to-r from-green-600 to-teal-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Recent Orders
                    </h3>
                    <p class="text-green-100 text-sm mt-1">Latest customer orders</p>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($recent_orders as $order): ?>
                    <div class="p-4 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between mb-2">
                            <span class="mono text-sm font-bold text-gray-900"><?php echo $order['order_number']; ?></span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                    'confirmed' => 'bg-blue-100 text-blue-700',
                                    'processing' => 'bg-purple-100 text-purple-700',
                                    'delivered' => 'bg-green-100 text-green-700',
                                    'cancelled' => 'bg-red-100 text-red-700'
                                ];
                                echo $statusColors[$order['status']];
                            ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-1"><?php echo $order['customer']; ?></p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-green-600">LKR <?php echo number_format($order['total'], 2); ?></span>
                            <span class="text-xs text-gray-500"><?php echo date('M d, h:i A', strtotime($order['date'])); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Stock Movements -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden slide-up" style="animation-delay: 0.9s">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <i class="fas fa-history mr-2"></i>
                        Recent Stock Movements
                    </h3>
                    <p class="text-indigo-100 text-sm mt-1">Latest inventory changes</p>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($recent_movements as $movement): 
                        $isPositive = $movement['quantity'] > 0;
                        $typeConfig = [
                            'STOCK_IN' => ['icon' => 'fa-arrow-down', 'color' => 'green'],
                            'STOCK_OUT' => ['icon' => 'fa-arrow-up', 'color' => 'blue'],
                            'DAMAGED' => ['icon' => 'fa-exclamation-triangle', 'color' => 'red'],
                            'EXPIRED' => ['icon' => 'fa-calendar-times', 'color' => 'orange']
                        ];
                        $config = $typeConfig[$movement['type']] ?? ['icon' => 'fa-exchange-alt', 'color' => 'gray'];
                    ?>
                    <div class="p-4 hover:bg-gray-50 transition">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <div class="h-8 w-8 bg-<?php echo $config['color']; ?>-100 rounded-full flex items-center justify-center">
                                        <i class="fas <?php echo $config['icon']; ?> text-<?php echo $config['color']; ?>-600 text-xs"></i>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900"><?php echo $movement['product']; ?></span>
                                </div>
                                <p class="text-xs text-gray-500 ml-10"><?php echo str_replace('_', ' ', $movement['type']); ?></p>
                            </div>
                            <span class="text-sm font-bold <?php echo $isPositive ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $isPositive ? '+' : ''; ?><?php echo $movement['quantity']; ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between ml-10">
                            <span class="text-xs text-gray-500">By: <?php echo $movement['user']; ?></span>
                            <span class="text-xs text-gray-500"><?php echo date('M d, h:i A', strtotime($movement['date'])); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

    </div>
</div>