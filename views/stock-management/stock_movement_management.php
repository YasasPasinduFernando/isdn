<?php
require_once __DIR__ . '/../../includes/header.php';

// Simulated logged-in user data
$current_user = [
    'user_id' => 5,
    'name' => 'Kasun Silva',
    'role' => 'rdc_manager', // Options: rdc_manager, head_office_manager
    'rdc_id' => 2,
    'rdc_name' => 'South RDC',
    'rdc_code' => 'SOUTH'
];

// Check permission - Only RDC_MANAGER and HEAD_OFFICE_MANAGER allowed
$allowed_roles = ['rdc_manager', 'head_office_manager'];
if (!in_array($current_user['role'], $allowed_roles)) {
    die('Access Denied: You do not have permission to manage stock movements.');
}

// All RDCs (for head office manager)
$all_rdcs = [
    ['rdc_id' => 1, 'rdc_name' => 'North RDC', 'rdc_code' => 'NORTH'],
    ['rdc_id' => 2, 'rdc_name' => 'South RDC', 'rdc_code' => 'SOUTH'],
    ['rdc_id' => 3, 'rdc_name' => 'East RDC', 'rdc_code' => 'EAST'],
    ['rdc_id' => 4, 'rdc_name' => 'West RDC', 'rdc_code' => 'WEST'],
    ['rdc_id' => 5, 'rdc_name' => 'Central RDC', 'rdc_code' => 'CENTRAL']
];

// Dummy Products with current stock
$products = [
    ['product_id' => 1, 'product_code' => 'BEV001', 'product_name' => 'Coca Cola 1L', 'category' => 'Beverages', 'current_stock' => 20, 'unit' => 'Bottles'],
    ['product_id' => 2, 'product_code' => 'BEV002', 'product_name' => 'Sprite 1L', 'category' => 'Beverages', 'current_stock' => 15, 'unit' => 'Bottles'],
    ['product_id' => 3, 'product_code' => 'FOOD001', 'product_name' => 'Rice 5kg', 'category' => 'Packaged Foods', 'current_stock' => 5, 'unit' => 'Bags'],
    ['product_id' => 4, 'product_code' => 'FOOD002', 'product_name' => 'Bread Loaf', 'category' => 'Packaged Foods', 'current_stock' => 30, 'unit' => 'Pieces'],
    ['product_id' => 5, 'product_code' => 'CLEAN001', 'product_name' => 'Detergent 500g', 'category' => 'Home Cleaning', 'current_stock' => 150, 'unit' => 'Packets'],
    ['product_id' => 6, 'product_code' => 'CARE001', 'product_name' => 'Toothpaste 100ml', 'category' => 'Personal Care', 'current_stock' => 80, 'unit' => 'Tubes']
];

// Dummy Recent Stock Movements (for history display)
$recent_movements = [
    [
        'movement_id' => 1,
        'date' => '2026-02-10 10:30:00',
        'product_code' => 'BEV001',
        'product_name' => 'Coca Cola 1L',
        'movement_type' => 'STOCK_IN',
        'quantity' => 100,
        'previous_stock' => 20,
        'new_stock' => 120,
        'created_by_name' => 'Kasun Silva',
        'created_by_role' => 'RDC_MANAGER',
        'note' => 'New delivery'
    ],
    [
        'movement_id' => 2,
        'date' => '2026-02-09 14:15:00',
        'product_code' => 'FOOD001',
        'product_name' => 'Rice 5kg',
        'movement_type' => 'DAMAGED',
        'quantity' => -10,
        'previous_stock' => 15,
        'new_stock' => 5,
        'created_by_name' => 'Kasun Silva',
        'created_by_role' => 'RDC_MANAGER',
        'note' => 'Water damage during storage'
    ],
    [
        'movement_id' => 3,
        'date' => '2026-02-09 09:00:00',
        'product_code' => 'BEV002',
        'product_name' => 'Sprite 1L',
        'movement_type' => 'EXPIRED',
        'quantity' => -5,
        'previous_stock' => 20,
        'new_stock' => 15,
        'created_by_name' => 'Kasun Silva',
        'created_by_role' => 'RDC_MANAGER',
        'note' => 'Expired batch removed'
    ],
    [
        'movement_id' => 4,
        'date' => '2026-02-08 16:45:00',
        'product_code' => 'CARE001',
        'product_name' => 'Toothpaste 100ml',
        'movement_type' => 'RETURNED',
        'quantity' => 5,
        'previous_stock' => 75,
        'new_stock' => 80,
        'created_by_name' => 'Priya Fernando',
        'created_by_role' => 'RDC_CLERK',
        'note' => 'Customer return - unused items'
    ],
    [
        'movement_id' => 5,
        'date' => '2026-02-08 11:20:00',
        'product_code' => 'CLEAN001',
        'product_name' => 'Detergent 500g',
        'movement_type' => 'ADJUSTMENT',
        'quantity' => -10,
        'previous_stock' => 160,
        'new_stock' => 150,
        'created_by_name' => 'Kasun Silva',
        'created_by_role' => 'RDC_MANAGER',
        'note' => 'Inventory count correction'
    ],
    [
        'movement_id' => 6,
        'date' => '2026-02-07 13:30:00',
        'product_code' => 'FOOD002',
        'product_name' => 'Bread Loaf',
        'movement_type' => 'STOCK_OUT',
        'quantity' => -50,
        'previous_stock' => 80,
        'new_stock' => 30,
        'created_by_name' => 'System',
        'created_by_role' => 'SYSTEM',
        'note' => 'Order #ORD-2026-001 fulfilled'
    ]
];

// Movement type configurations
$movement_types = [
    'STOCK_IN' => [
        'label' => 'Stock In',
        'icon' => 'fa-arrow-down',
        'color' => 'green',
        'direction' => 'positive',
        'description' => 'Add new stock received from suppliers'
    ],
    'STOCK_OUT' => [
        'label' => 'Stock Out',
        'icon' => 'fa-arrow-up',
        'color' => 'blue',
        'direction' => 'negative',
        'description' => 'Remove stock for fulfilled orders/sales'
    ],
    'ADJUSTMENT' => [
        'label' => 'Adjustment',
        'icon' => 'fa-sliders-h',
        'color' => 'purple',
        'direction' => 'both',
        'description' => 'Manual correction based on physical count'
    ],
    'DAMAGED' => [
        'label' => 'Damaged',
        'icon' => 'fa-exclamation-triangle',
        'color' => 'red',
        'direction' => 'negative',
        'description' => 'Remove damaged or broken items'
    ],
    'EXPIRED' => [
        'label' => 'Expired',
        'icon' => 'fa-calendar-times',
        'color' => 'orange',
        'direction' => 'negative',
        'description' => 'Remove expired products'
    ],
    'RETURNED' => [
        'label' => 'Returned',
        'icon' => 'fa-undo',
        'color' => 'teal',
        'direction' => 'positive',
        'description' => 'Add stock from customer returns'
    ]
];
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stock Movements - ISDN</title>
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
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
        
        /* Animations */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .slide-down {
            animation: slideDown 0.3s ease-out;
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        .pulse-slow {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Movement type cards */
        .movement-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .movement-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .movement-card.selected {
            border-width: 3px;
            transform: scale(1.02);
        }

        /* Tab styles */
        .tab-btn {
            transition: all 0.2s ease;
        }

        .tab-btn.active {
            border-bottom: 3px solid #3b82f6;
            color: #3b82f6;
        }
    </style>
</head>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Manage Stock Movements</h1>
                <p class="text-gray-600 mt-1">Record manual stock adjustments and track inventory changes</p>
            </div>
        </div>

        <!-- RDC Selector (for HEAD_OFFICE_MANAGER only) -->
        <?php if ($current_user['role'] === 'head_office_manager'): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <label class="block text-sm font-semibold text-blue-900 mb-2">Select RDC to Manage</label>
            <select id="rdc-selector" class="w-full md:w-1/2 px-4 py-3 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                <?php foreach ($all_rdcs as $rdc): ?>
                <option value="<?php echo $rdc['rdc_id']; ?>" <?php echo ($rdc['rdc_id'] == $current_user['rdc_id']) ? 'selected' : ''; ?>>
                    <?php echo $rdc['rdc_name']; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
    </div>

    <!-- Tab Navigation -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button onclick="showTab('new_movement')" id="tab-new_movement" class="tab-btn active whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-plus-circle mr-2"></i>New Movement
                </button>
                <button onclick="showTab('history')" id="tab-history" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-history mr-2"></i>Movement History
                </button>
            </nav>
        </div>
    </div>

    <!-- New Movement Section -->
    <div id="section-new_movement" class="tab-section">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left: Movement Type Selection -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-4">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-clipboard-list mr-2 text-blue-600"></i>
                        Movement Type
                    </h3>
                    
                    <div class="space-y-3">
                        <?php foreach ($movement_types as $type => $config): ?>
                        <div class="movement-card border-2 border-gray-200 rounded-lg p-4 hover:border-<?php echo $config['color']; ?>-400" 
                             data-type="<?php echo $type; ?>"
                             onclick="selectMovementType('<?php echo $type; ?>')">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 bg-<?php echo $config['color']; ?>-100 rounded-lg flex items-center justify-center">
                                        <i class="fas <?php echo $config['icon']; ?> text-<?php echo $config['color']; ?>-600"></i>
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <div class="font-semibold text-gray-900"><?php echo $config['label']; ?></div>
                                    <div class="text-xs text-gray-500 mt-1"><?php echo $config['description']; ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right: Movement Form -->
            <div class="lg:col-span-2">
                <form id="movement-form" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-edit mr-2 text-purple-600"></i>
                        Movement Details
                    </h3>

                    <!-- Selected Movement Type Display -->
                    <div id="selected-type-display" class="mb-6 p-4 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg text-center">
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-hand-point-left mr-2"></i>
                            Please select a movement type from the left
                        </div>
                    </div>

                    <!-- Form Fields (initially hidden) -->
                    <div id="form-fields" class="hidden space-y-6">
                        <input type="hidden" id="movement-type" name="movement_type">

                        <!-- Product Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Product <span class="text-red-500">*</span>
                            </label>
                            <select id="product-select" name="product_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">-- Select Product --</option>
                                <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['product_id']; ?>" 
                                        data-current-stock="<?php echo $product['current_stock']; ?>"
                                        data-unit="<?php echo $product['unit']; ?>">
                                    <?php echo $product['product_code']; ?> - <?php echo $product['product_name']; ?> 
                                    (Current: <?php echo $product['current_stock']; ?> <?php echo $product['unit']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Current Stock Display -->
                        <div id="current-stock-display" class="hidden p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs text-blue-600 font-medium mb-1">Current Stock</div>
                                    <div class="text-2xl font-bold text-blue-900">
                                        <span id="current-stock-value">0</span> <span id="current-stock-unit">units</span>
                                    </div>
                                </div>
                                <div class="h-16 w-16 bg-blue-200 rounded-full flex items-center justify-center">
                                    <i class="fas fa-boxes text-blue-700 text-2xl"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Quantity <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center space-x-4">
                                <input type="number" 
                                       id="quantity-input" 
                                       name="quantity" 
                                       min="1" 
                                       required 
                                       placeholder="Enter quantity"
                                       class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <span id="quantity-direction" class="px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-gray-700 min-w-[100px] text-center">
                                    +/-
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Enter the number of units to add or remove</p>
                        </div>

                        <!-- New Stock Preview -->
                        <div id="new-stock-preview" class="hidden p-4 bg-gradient-to-r from-green-50 to-teal-50 border border-green-200 rounded-lg">
                            <div class="text-xs text-green-700 font-medium mb-2">
                                <i class="fas fa-calculator mr-1"></i>New Stock After Movement
                            </div>
                            <div class="text-3xl font-bold text-green-900">
                                <span id="new-stock-value">0</span> <span id="new-stock-unit">units</span>
                            </div>
                            <div id="stock-warning" class="hidden mt-2 text-sm text-red-700 bg-red-100 px-3 py-2 rounded">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <span id="warning-message"></span>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Notes/Reason <span class="text-red-500">*</span>
                            </label>
                            <textarea id="notes-input" 
                                      name="note" 
                                      rows="4" 
                                      required
                                      placeholder="Explain the reason for this stock movement..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                        </div>

                        <!-- Summary Box -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Movement Summary</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Movement Type:</span>
                                    <span id="summary-type" class="font-semibold text-gray-900">-</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Product:</span>
                                    <span id="summary-product" class="font-semibold text-gray-900">-</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Quantity Change:</span>
                                    <span id="summary-quantity" class="font-semibold text-gray-900">-</span>
                                </div>
                                <div class="flex justify-between border-t border-gray-300 pt-2 mt-2">
                                    <span class="text-gray-600">Current Stock:</span>
                                    <span id="summary-current" class="font-semibold text-gray-900">-</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">New Stock:</span>
                                    <span id="summary-new" class="font-bold text-lg text-blue-900">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex space-x-4">
                            <button type="submit" class="flex-1 px-6 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
                                <i class="fas fa-save"></i>
                                <span>Record Movement</span>
                            </button>
                            <button type="button" onclick="resetForm()" class="px-6 py-4 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                                <i class="fas fa-redo mr-2"></i>Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Movement History Section -->
    <div id="section-history" class="tab-section hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- History Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white">Stock Movement History</h2>
                <p class="text-indigo-100 text-sm mt-1">Recent inventory adjustments and changes</p>
            </div>

            <!-- Filters -->
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Movement Type</label>
                        <select id="filter-movement-type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="all">All Types</option>
                            <?php foreach ($movement_types as $type => $config): ?>
                            <option value="<?php echo $type; ?>"><?php echo $config['label']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                        <select id="filter-date-range" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="7">Last 7 Days</option>
                            <option value="30" selected>Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                        <select id="filter-product" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="all">All Products</option>
                            <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['product_code']; ?>"><?php echo $product['product_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button onclick="applyHistoryFilters()" class="w-full px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-filter mr-2"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- History Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Movement Type</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Previous</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">New Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        </tr>
                    </thead>
                    <tbody id="history-tbody" class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recent_movements as $movement): 
                            $config = $movement_types[$movement['movement_type']];
                            $is_positive = $movement['quantity'] > 0;
                        ?>
                        <tr class="hover:bg-gray-50 transition" data-movement-type="<?php echo $movement['movement_type']; ?>" data-product="<?php echo $movement['product_code']; ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo date('M d, Y', strtotime($movement['date'])); ?>
                                <div class="text-xs text-gray-500"><?php echo date('h:i A', strtotime($movement['date'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $movement['product_name']; ?></div>
                                <div class="text-xs mono text-gray-500"><?php echo $movement['product_code']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 bg-<?php echo $config['color']; ?>-100 text-<?php echo $config['color']; ?>-700 text-xs font-semibold rounded-full">
                                    <i class="fas <?php echo $config['icon']; ?> mr-1"></i>
                                    <?php echo $config['label']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-bold <?php echo $is_positive ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $is_positive ? '+' : ''; ?><?php echo $movement['quantity']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500"><?php echo $movement['previous_stock']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900"><?php echo $movement['new_stock']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $movement['created_by_name']; ?></div>
                                <div class="text-xs text-gray-500"><?php echo $movement['created_by_role']; ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="<?php echo $movement['note']; ?>">
                                <?php echo $movement['note']; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (placeholder) -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span class="font-semibold">1-<?php echo count($recent_movements); ?></span> of <span class="font-semibold"><?php echo count($recent_movements); ?></span> movements
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-100 disabled:opacity-50" disabled>
                        Previous
                    </button>
                    <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-100 disabled:opacity-50" disabled>
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Success Modal -->
<div id="success-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center" style="background: rgba(0, 0, 0, 0.5);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-8 text-center">
        <div class="h-16 w-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-check text-green-600 text-3xl"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Movement Recorded!</h3>
        <p class="text-gray-600 mb-6">Stock movement has been successfully saved and inventory updated.</p>
        <button onclick="closeSuccessModal()" class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
            Close
        </button>
    </div>
</div>

<!-- JavaScript -->
<script>
const movementTypes = <?php echo json_encode($movement_types); ?>;
const products = <?php echo json_encode($products); ?>;
let selectedType = null;
let currentStock = 0;

// Tab switching
function showTab(tab) {
    document.querySelectorAll('.tab-section').forEach(section => {
        section.classList.add('hidden');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active', 'border-blue-600', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    
    document.getElementById('section-' + tab).classList.remove('hidden');
    const activeTab = document.getElementById('tab-' + tab);
    activeTab.classList.add('active', 'border-blue-600', 'text-blue-600');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
}

// Select movement type
function selectMovementType(type) {
    selectedType = type;
    const config = movementTypes[type];
    
    // Update all movement cards
    document.querySelectorAll('.movement-card').forEach(card => {
        card.classList.remove('selected', 'border-green-500', 'border-blue-500', 'border-purple-500', 'border-red-500', 'border-orange-500', 'border-teal-500');
        if (card.dataset.type === type) {
            card.classList.add('selected', 'border-' + config.color + '-500');
        }
    });
    
    // Update selected type display
    document.getElementById('selected-type-display').innerHTML = `
        <div class="flex items-center justify-center space-x-3">
            <div class="h-12 w-12 bg-${config.color}-100 rounded-lg flex items-center justify-center">
                <i class="fas ${config.icon} text-${config.color}-600 text-xl"></i>
            </div>
            <div class="text-left">
                <div class="font-bold text-lg text-gray-900">${config.label}</div>
                <div class="text-xs text-gray-500">${config.description}</div>
            </div>
        </div>
    `;
    
    // Show form fields
    document.getElementById('form-fields').classList.remove('hidden');
    document.getElementById('movement-type').value = type;
    
    // Update quantity direction
    let directionText = '+/-';
    let directionClass = 'bg-gray-100 text-gray-700';
    
    if (config.direction === 'positive') {
        directionText = '+ Add';
        directionClass = 'bg-green-100 text-green-700';
    } else if (config.direction === 'negative') {
        directionText = '- Remove';
        directionClass = 'bg-red-100 text-red-700';
    }
    
    const directionEl = document.getElementById('quantity-direction');
    directionEl.textContent = directionText;
    directionEl.className = 'px-4 py-3 border border-gray-300 rounded-lg font-semibold min-w-[100px] text-center ' + directionClass;
    
    // Update summary
    document.getElementById('summary-type').textContent = config.label;
    
    // Trigger calculation if product and quantity already selected
    calculateNewStock();
}

// Product selection change
document.getElementById('product-select')?.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    if (!selected.value) {
        document.getElementById('current-stock-display').classList.add('hidden');
        return;
    }
    
    currentStock = parseInt(selected.dataset.currentStock);
    const unit = selected.dataset.unit;
    
    document.getElementById('current-stock-value').textContent = currentStock;
    document.getElementById('current-stock-unit').textContent = unit;
    document.getElementById('current-stock-display').classList.remove('hidden');
    
    document.getElementById('summary-product').textContent = selected.text.split('(')[0].trim();
    document.getElementById('summary-current').textContent = currentStock + ' ' + unit;
    
    calculateNewStock();
});

// Quantity input change
document.getElementById('quantity-input')?.addEventListener('input', calculateNewStock);

// Calculate new stock
function calculateNewStock() {
    if (!selectedType || !currentStock || !document.getElementById('quantity-input').value) {
        document.getElementById('new-stock-preview').classList.add('hidden');
        return;
    }
    
    const config = movementTypes[selectedType];
    let quantity = parseInt(document.getElementById('quantity-input').value) || 0;
    
    // Determine actual quantity change based on movement type
    let actualChange = quantity;
    if (config.direction === 'negative' || ['STOCK_OUT', 'DAMAGED', 'EXPIRED'].includes(selectedType)) {
        actualChange = -quantity;
    }
    
    const newStock = currentStock + actualChange;
    const unit = document.getElementById('current-stock-unit').textContent;
    
    document.getElementById('new-stock-value').textContent = newStock;
    document.getElementById('new-stock-unit').textContent = unit;
    document.getElementById('new-stock-preview').classList.remove('hidden');
    
    // Update summary
    const changeText = (actualChange >= 0 ? '+' : '') + actualChange;
    document.getElementById('summary-quantity').textContent = changeText;
    document.getElementById('summary-new').textContent = newStock + ' ' + unit;
    
    // Show warning if stock goes negative
    const warningDiv = document.getElementById('stock-warning');
    if (newStock < 0) {
        warningDiv.classList.remove('hidden');
        document.getElementById('warning-message').textContent = `Warning: Stock cannot be negative! Current stock is only ${currentStock} ${unit}.`;
    } else {
        warningDiv.classList.add('hidden');
    }
}

// Form submission
document.getElementById('movement-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Validate
    if (!selectedType) {
        alert('Please select a movement type!');
        return;
    }
    
    const newStock = parseInt(document.getElementById('new-stock-value').textContent);
    if (newStock < 0) {
        alert('Cannot proceed: New stock would be negative!');
        return;
    }
    
    // In real app, this would be an AJAX call
    console.log('Recording stock movement:', {
        ...data,
        rdc_id: <?php echo $current_user['rdc_id']; ?>,
        created_by: <?php echo $current_user['user_id']; ?>,
        created_by_name: '<?php echo $current_user['name']; ?>',
        created_by_role: '<?php echo $current_user['role']; ?>',
        previous_quantity: currentStock,
        new_quantity: newStock
    });
    
    // Show success modal
    document.getElementById('success-modal').classList.remove('hidden');
    
    // Reset form
    setTimeout(() => {
        resetForm();
    }, 2000);
});

// Reset form
function resetForm() {
    document.getElementById('movement-form').reset();
    document.getElementById('form-fields').classList.add('hidden');
    document.getElementById('current-stock-display').classList.add('hidden');
    document.getElementById('new-stock-preview').classList.add('hidden');
    
    document.querySelectorAll('.movement-card').forEach(card => {
        card.classList.remove('selected', 'border-green-500', 'border-blue-500', 'border-purple-500', 'border-red-500', 'border-orange-500', 'border-teal-500');
    });
    
    selectedType = null;
    currentStock = 0;
    
    document.getElementById('selected-type-display').innerHTML = `
        <div class="text-sm text-gray-500">
            <i class="fas fa-hand-point-left mr-2"></i>
            Please select a movement type from the left
        </div>
    `;
    
    // Reset summary
    ['summary-type', 'summary-product', 'summary-quantity', 'summary-current', 'summary-new'].forEach(id => {
        document.getElementById(id).textContent = '-';
    });
}

// Close success modal
function closeSuccessModal() {
    document.getElementById('success-modal').classList.add('hidden');
}

// History filters
function applyHistoryFilters() {
    const typeFilter = document.getElementById('filter-movement-type').value;
    const productFilter = document.getElementById('filter-product').value;
    
    const rows = document.querySelectorAll('#history-tbody tr');
    rows.forEach(row => {
        let show = true;
        
        if (typeFilter !== 'all' && row.dataset.movementType !== typeFilter) {
            show = false;
        }
        
        if (productFilter !== 'all' && row.dataset.product !== productFilter) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>