<?php
require_once __DIR__ . '/../../includes/header.php';
?>
<?php
// ============================================
// REQUEST PRODUCT UNITS PAGE
// For: RDC_CLERK and RDC_MANAGER
// ============================================

// Logged-in user data (from session)
$role = $_SESSION['role'] ?? 'rdc_clerk';
$role_upper = strtoupper($role);
$current_user = [
    'user_id' => $_SESSION['user_id'] ?? null,
    'name' => $_SESSION['username'] ?? 'User',
    'role' => $role_upper,
    'rdc_id' => $_SESSION['rdc_id'] ?? null,
    'rdc_name' => $_SESSION['rdc_name'] ?? 'RDC',
    'rdc_code' => $_SESSION['rdc_code'] ?? ''
];

// Dummy data: Other RDCs
$other_rdcs = [
    ['rdc_id' => 1, 'rdc_name' => 'North RDC', 'rdc_code' => 'NORTH'],
    ['rdc_id' => 3, 'rdc_name' => 'East RDC', 'rdc_code' => 'EAST'],
    ['rdc_id' => 4, 'rdc_name' => 'West RDC', 'rdc_code' => 'WEST'],
    ['rdc_id' => 5, 'rdc_name' => 'Central RDC', 'rdc_code' => 'CENTRAL']
];

// Dummy data: Low stock products at current RDC (South)
$low_stock_products = [
    [
        'product_id' => 1,
        'product_code' => 'BEV001',
        'product_name' => 'Coca Cola 1L',
        'category' => 'Beverages',
        'current_stock' => 20,
        'minimum_level' => 100,
        'unit' => 'Bottles',
        'status' => 'low'
    ],
    [
        'product_id' => 2,
        'product_code' => 'BEV002',
        'product_name' => 'Sprite 1L',
        'category' => 'Beverages',
        'current_stock' => 15,
        'minimum_level' => 100,
        'unit' => 'Bottles',
        'status' => 'low'
    ],
    [
        'product_id' => 3,
        'product_code' => 'FOOD001',
        'product_name' => 'Rice 5kg',
        'category' => 'Packaged Foods',
        'current_stock' => 5,
        'minimum_level' => 50,
        'unit' => 'Bags',
        'status' => 'critical'
    ],
    [
        'product_id' => 4,
        'product_code' => 'FOOD002',
        'product_name' => 'Bread Loaf',
        'category' => 'Packaged Foods',
        'current_stock' => 30,
        'minimum_level' => 200,
        'unit' => 'Pieces',
        'status' => 'low'
    ],
    [
        'product_id' => 6,
        'product_code' => 'CARE001',
        'product_name' => 'Toothpaste 100ml',
        'category' => 'Personal Care',
        'current_stock' => 80,
        'minimum_level' => 150,
        'unit' => 'Tubes',
        'status' => 'low'
    ]
];

// Dummy data: Pending transfer requests with FULL DETAILS
$pending_transfers = [
    [
        'transfer_id' => 1,
        'transfer_number' => 'TRF-NORTH-SOUTH-001',
        'source_rdc_id' => 1,
        'source_rdc' => 'North RDC',
        'destination_rdc_id' => 2,
        'destination_rdc' => 'South RDC',
        'requested_by_name' => 'Kasun Silva',
        'requested_by_role' => 'RDC_CLERK',
        'product_count' => 3,
        'total_items' => 350,
        'requested_date' => '2026-02-02 10:30 AM',
        'request_reason' => 'High demand in Galle area for weekend sales. Need urgent stock replenishment.',
        'status' => 'CLERK_REQUESTED',
        'is_urgent' => true,
        'items' => [
            [
                'product_id' => 1,
                'product_code' => 'BEV001',
                'product_name' => 'Coca Cola 1L',
                'category' => 'Beverages',
                'requested_quantity' => 100,
                'source_stock' => 500, // Stock at North RDC
                'destination_stock' => 20 // Current stock at South RDC
            ],
            [
                'product_id' => 2,
                'product_code' => 'BEV002',
                'product_name' => 'Sprite 1L',
                'category' => 'Beverages',
                'requested_quantity' => 150,
                'source_stock' => 400,
                'destination_stock' => 15
            ],
            [
                'product_id' => 3,
                'product_code' => 'FOOD001',
                'product_name' => 'Rice 5kg',
                'category' => 'Packaged Foods',
                'requested_quantity' => 100,
                'source_stock' => 200,
                'destination_stock' => 5
            ]
        ]
    ],
    [
        'transfer_id' => 2,
        'transfer_number' => 'TRF-EAST-SOUTH-002',
        'source_rdc_id' => 3,
        'source_rdc' => 'East RDC',
        'destination_rdc_id' => 2,
        'destination_rdc' => 'South RDC',
        'requested_by_name' => 'Priya Fernando',
        'requested_by_role' => 'RDC_MANAGER',
        'product_count' => 2,
        'total_items' => 150,
        'requested_date' => '2026-02-01 02:15 PM',
        'request_reason' => 'Stock replenishment for regular operations.',
        'status' => 'PENDING',
        'is_urgent' => false,
        'items' => [
            [
                'product_id' => 4,
                'product_code' => 'FOOD002',
                'product_name' => 'Bread Loaf',
                'category' => 'Packaged Foods',
                'requested_quantity' => 100,
                'source_stock' => 300,
                'destination_stock' => 30
            ],
            [
                'product_id' => 6,
                'product_code' => 'CARE001',
                'product_name' => 'Toothpaste 100ml',
                'category' => 'Personal Care',
                'requested_quantity' => 50,
                'source_stock' => 350,
                'destination_stock' => 80
            ]
        ]
    ],
    [
        'transfer_id' => 3,
        'transfer_number' => 'TRF-WEST-SOUTH-003',
        'source_rdc_id' => 4,
        'source_rdc' => 'West RDC',
        'destination_rdc_id' => 2,
        'destination_rdc' => 'South RDC',
        'requested_by_name' => 'Saman Kumar',
        'requested_by_role' => 'RDC_CLERK',
        'product_count' => 1,
        'total_items' => 80,
        'requested_date' => '2026-02-03 09:00 AM',
        'request_reason' => 'Customer orders pending. Immediate transfer required.',
        'status' => 'APPROVED',
        'is_urgent' => true,
        'approval_remarks' => 'Approved by North RDC Manager. Ready for dispatch.',
        'items' => [
            [
                'product_id' => 2,
                'product_code' => 'BEV002',
                'product_name' => 'Sprite 1L',
                'category' => 'Beverages',
                'requested_quantity' => 80,
                'source_stock' => 320,
                'destination_stock' => 15
            ]
        ]
    ]
];
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Product Units - ISDN</title>
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
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .slide-down {
            animation: slideDown 0.3s ease-out;
        }
        
        .pulse-slow {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .fade-in {
            animation: fadeIn 0.2s ease-out;
        }

        .scale-in {
            animation: scaleIn 0.2s ease-out;
        }
        
        /* Stock level indicator */
        .stock-critical {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .stock-low {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }
        
        .stock-warning {
            background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%);
        }
        
        /* Product card hover effect */
        .product-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Checkbox styling */
        .custom-checkbox:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
        
        /* Badge animations */
        .badge-urgent {
            animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        /* Gradient backgrounds */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .bg-gradient-success {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        }
        
        .bg-gradient-danger {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        /* Modal styling */
        .modal-content {
            max-height: 90vh;
            overflow-y: auto;
        }

        /* Stock bar */
        .stock-bar {
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .stock-bar-fill {
            height: 100%;
            transition: width 0.3s ease;
        }
    </style>
</head>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Pending Transfers Alert Banner -->
    <?php if (count($pending_transfers) > 0): ?>
    <div class="mb-6 slide-down">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 rounded-lg shadow-sm p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">
                        Active Transfer Requests
                    </h3>
                    <div class="space-y-2">
                        <?php $transfer_index = 0; foreach ($pending_transfers as $transfer): ?>
                        <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-gray-200">
                            <div class="flex items-center space-x-3">
                                <span class="mono text-xs font-semibold text-gray-700"><?php echo $transfer['transfer_number']; ?></span>
                                <span class="text-xs text-gray-500">From: <?php echo $transfer['source_rdc']; ?></span>
                                <span class="text-xs text-gray-500">•</span>
                                <span class="text-xs text-gray-500"><?php echo $transfer['product_count']; ?> products (<?php echo $transfer['total_items']; ?> units)</span>
                                <?php if ($transfer['is_urgent']): ?>
                                <span class="badge-urgent px-2 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                                    <i class="fas fa-bolt text-xs"></i> URGENT
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="transfer-status px-3 py-1 text-xs font-semibold rounded-full <?php 
                                    $statusColors = [
                                        'CLERK_REQUESTED' => 'bg-yellow-100 text-yellow-700',
                                        'PENDING' => 'bg-blue-100 text-blue-700',
                                        'APPROVED' => 'bg-green-100 text-green-700',
                                        'REJECTED' => 'bg-red-100 text-red-700',
                                        'CANCELLED' => 'bg-gray-100 text-gray-700',
                                        'RECEIVED' => 'bg-purple-100 text-purple-700'
                                    ];
                                    echo $statusColors[$transfer['status']] ?? 'bg-gray-100 text-gray-700';
                                ?>">
                                    <?php echo str_replace('_', ' ', $transfer['status']); ?>
                                </span>
                                <button class="view-transfer-btn text-blue-600 hover:text-blue-700 text-sm font-medium" data-index="<?php echo $transfer_index++; ?>">
                                    View Details <i class="fas fa-arrow-right text-xs ml-1"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Page Title & Stats -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Request Product Units</h1>
        <p class="text-gray-600 mb-6">Select products with low stock and request units from other RDCs</p>
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Low Stock Items</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo count($low_stock_products); ?></p>
                    </div>
                    <div class="h-12 w-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Critical Stock</p>
                        <p class="text-2xl font-bold text-red-600">1</p>
                    </div>
                    <div class="h-12 w-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Pending Requests</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo count($pending_transfers); ?></p>
                    </div>
                    <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Your RDC</p>
                        <p class="text-lg font-bold text-gray-900"><?php echo $current_user['rdc_name']; ?></p>
                    </div>
                    <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-warehouse text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Check Other RDC Stock Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900 mb-1">Check Stock at Other RDCs</h2>
                <p class="text-sm text-gray-600">Select an RDC to view their available stock for comparison</p>
            </div>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select RDC to Check</label>
                <select id="check-rdc-dropdown" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <option value="">-- Select RDC --</option>
                    <?php foreach ($other_rdcs as $rdc): ?>
                    <option value="<?php echo $rdc['rdc_id']; ?>"><?php echo $rdc['rdc_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex items-end">
                <button id="check-stock-btn" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center space-x-2">
                    <i class="fas fa-search"></i>
                    <span>Check Stock</span>
                </button>
            </div>
        </div>
        
        <!-- Checked RDC Badge (shown after checking) -->
        <div id="checked-rdc-badge" class="hidden mt-4 p-3 bg-gradient-success rounded-lg border border-green-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2 text-green-800">
                    <i class="fas fa-check-circle"></i>
                    <span class="font-semibold">Currently viewing stock from: <span id="checked-rdc-name" class="font-bold">North RDC</span></span>
                </div>
                <button id="clear-check-btn" class="text-sm text-green-700 hover:text-green-900 font-medium">
                    Clear <i class="fas fa-times ml-1"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <form id="transfer-request-form" method="POST" action="process_transfer_request.php">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <?php foreach ($low_stock_products as $product): ?>
            <div class="product-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Product Header -->
                <div class="p-4 border-b border-gray-100">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-1">
                                <input type="checkbox" 
                                       name="selected_products[]" 
                                       value="<?php echo $product['product_id']; ?>"
                                       class="custom-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="mono text-xs text-gray-500"><?php echo $product['product_code']; ?></span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1"><?php echo $product['product_name']; ?></h3>
                            <span class="inline-block px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded">
                                <?php echo $product['category']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Stock Level Indicator -->
                    <div class="mt-3">
                        <?php
                        $stock_class = '';
                        $stock_text = '';
                        $stock_icon = '';
                        
                        if ($product['status'] === 'critical') {
                            $stock_class = 'stock-critical';
                            $stock_text = 'CRITICAL';
                            $stock_icon = 'fa-times-circle';
                        } elseif ($product['status'] === 'low') {
                            $stock_class = 'stock-low';
                            $stock_text = 'LOW STOCK';
                            $stock_icon = 'fa-exclamation-triangle';
                        }
                        ?>
                        <div class="<?php echo $stock_class; ?> rounded-lg p-3 text-white">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-semibold uppercase tracking-wider flex items-center">
                                    <i class="fas <?php echo $stock_icon; ?> mr-2"></i>
                                    <?php echo $stock_text; ?>
                                </span>
                            </div>
                            <div class="flex items-end justify-between">
                                <div>
                                    <div class="text-2xl font-bold"><?php echo $product['current_stock']; ?></div>
                                    <div class="text-xs opacity-90"><?php echo $product['unit']; ?> available</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs opacity-90">Minimum</div>
                                    <div class="text-sm font-semibold"><?php echo $product['minimum_level']; ?> <?php echo $product['unit']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Request Input -->
                <div class="p-4 bg-gray-50">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Request Quantity</label>
                    <input type="number" 
                           name="request_qty_<?php echo $product['product_id']; ?>"
                           min="1"
                           placeholder="Enter quantity needed"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    
                    <!-- Other RDC Stock Info (Hidden by default, shown after checking) -->
                    <div class="other-rdc-stock hidden mt-3 p-3 bg-white rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-gray-600">Stock at checked RDC:</span>
                            <span class="stock-value text-sm font-bold text-green-600">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Request Form Section -->
        <div id="request-form-section" class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-paper-plane mr-2 text-blue-600"></i>
                Submit Transfer Request
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Source RDC Selection -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Request From RDC <span class="text-red-500">*</span>
                    </label>
                    <select name="source_rdc_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Select Source RDC --</option>
                        <?php foreach ($other_rdcs as $rdc): ?>
                        <option value="<?php echo $rdc['rdc_id']; ?>"><?php echo $rdc['rdc_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Priority -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Priority <span class="text-red-500">*</span>
                    </label>
                    <div class="flex space-x-4">
                        <label class="flex-1 flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                            <input type="radio" name="is_urgent" value="0" checked class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Normal</span>
                        </label>
                        <label class="flex-1 flex items-center justify-center px-4 py-3 border border-red-300 rounded-lg cursor-pointer hover:bg-red-50 transition">
                            <input type="radio" name="is_urgent" value="1" class="mr-2">
                            <span class="text-sm font-medium text-red-700 flex items-center">
                                <i class="fas fa-bolt mr-1"></i> Urgent
                            </span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Reason -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Reason for Request <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" 
                          rows="4" 
                          required
                          placeholder="Explain why this transfer is needed..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
            </div>
            
            <!-- Estimated Delivery -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 bg-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-truck text-white"></i>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-700">Estimated Delivery Time</div>
                            <div class="text-xl font-bold text-blue-600">2-3 Business Days</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-500">Expected Arrival</div>
                        <div class="text-sm font-semibold text-gray-900" id="estimated-date">Feb 05-06, 2026</div>
                    </div>
                </div>
            </div>
            
            <!-- Selected Products Summary -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-gray-700">Selected Products</span>
                    <span id="selected-count" class="px-2 py-1 bg-blue-600 text-white text-xs font-bold rounded-full">0</span>
                </div>
                <div id="selected-summary" class="text-sm text-gray-500">
                    No products selected yet. Check the boxes on product cards above.
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="flex space-x-4">
                <button type="submit" class="flex-1 px-6 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2 text-lg">
                    <i class="fas fa-paper-plane"></i>
                    <span>Submit Transfer Request</span>
                </button>
                
                <button type="button" class="px-6 py-4 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </button>
            </div>
        </div>
    </form>

</div>

<!-- Enhanced Transfer Details Modal -->
<div id="transfer-modal" class="hidden fixed inset-0 z-50 fade-in" style="background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px);">
    <div class="flex items-center justify-center min-h-screen mx-auto p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl modal-content scale-in">
            <!-- Modal Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-2xl z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Transfer Request Details</h3>
                        <p id="modal-transfer-number" class="mono text-sm text-gray-600 mt-1"></p>
                    </div>
                    <button id="modal-close" class="h-10 w-10 rounded-full hover:bg-gray-100 flex items-center justify-center transition">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-6">
                
                <!-- Transfer Information -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4">
                        <div class="text-xs text-blue-600 font-medium mb-1">Source RDC</div>
                        <div id="modal-source-rdc" class="text-sm font-bold text-blue-900"></div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4">
                        <div class="text-xs text-purple-600 font-medium mb-1">Destination RDC</div>
                        <div id="modal-destination-rdc" class="text-sm font-bold text-purple-900"></div>
                    </div>
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4">
                        <div class="text-xs text-orange-600 font-medium mb-1">Requested Date</div>
                        <div id="modal-requested-date" class="text-xs font-bold text-orange-900"></div>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4">
                        <div class="text-xs text-green-600 font-medium mb-1">Total Items</div>
                        <div id="modal-total-items" class="text-sm font-bold text-green-900"></div>
                    </div>
                </div>

                <!-- Request Details -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-user text-yellow-600 mt-1"></i>
                        <div class="ml-3 flex-1">
                            <div class="text-xs text-yellow-700 font-medium mb-1">Requested By</div>
                            <div id="modal-requested-by" class="text-sm font-semibold text-gray-900"></div>
                        </div>
                        <span id="modal-urgent-badge" class="hidden badge-urgent px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                            <i class="fas fa-bolt mr-1"></i> URGENT
                        </span>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="text-xs text-blue-700 font-medium mb-2 flex items-center">
                        <i class="fas fa-comment-alt mr-2"></i> Request Reason
                    </div>
                    <div id="modal-request-reason" class="text-sm text-gray-700"></div>
                </div>

                <!-- Products List with Stock Check -->
                <div>
                    <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-boxes mr-2 text-blue-600"></i>
                        Requested Products & Stock Availability
                    </h4>
                    <div id="modal-products-list" class="space-y-3">
                        <!-- Products will be inserted here -->
                    </div>
                </div>

                <!-- Status Management Section (Only for RDC_MANAGER) -->
                <div id="status-management-section" class="hidden bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-tasks mr-2 text-purple-600"></i>
                        Status Management
                    </h4>

                    <!-- Current Status Display -->
                    <div class="mb-4 p-4 bg-white rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs text-gray-500 mb-1">Current Status</div>
                                <div id="current-status-display" class="text-lg font-bold"></div>
                            </div>
                            <div id="stock-check-result" class="hidden">
                                <!-- Stock check result will be shown here -->
                            </div>
                        </div>
                    </div>

                    <!-- Status Change Options (context-specific) -->
                    <div id="status-change-options" class="space-y-4">
                        <!-- Dynamic content based on current status -->
                    </div>

                    <!-- Approval/Delivery Remarks -->
                    <div id="remarks-section" class="hidden mt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Remarks <span class="text-red-500">*</span>
                        </label>
                        <textarea id="status-remarks" 
                                  rows="3" 
                                  placeholder="Add your comments..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div id="submit-status-section" class="hidden mt-4">
                        <button id="submit-status-btn" class="w-full px-6 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all shadow-lg flex items-center justify-center space-x-2">
                            <i class="fas fa-check-circle"></i>
                            <span>Update Status</span>
                        </button>
                    </div>
                </div>

                <!-- Approval Remarks Display (for PENDING/APPROVED status) -->
                <div id="approval-remarks-display" class="hidden bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="text-xs text-green-700 font-medium mb-2 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i> Approval Remarks
                    </div>
                    <div id="approval-remarks-text" class="text-sm text-gray-700"></div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 rounded-b-2xl flex justify-end space-x-3">
                <button id="modal-close-btn" class="px-6 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// PHP data passed to JavaScript
const transfers = <?php echo json_encode($pending_transfers); ?>;
const currentUser = <?php echo json_encode($current_user); ?>;
let currentTransferIndex = null;
let selectedNewStatus = null;

// Open Transfer Modal
function openTransferModal(index) {
    currentTransferIndex = index;
    selectedNewStatus = null;
    const transfer = transfers[index];
    if (!transfer) return;

    // Fill basic info
    document.getElementById('modal-transfer-number').textContent = transfer.transfer_number;
    document.getElementById('modal-source-rdc').textContent = transfer.source_rdc;
    document.getElementById('modal-destination-rdc').textContent = transfer.destination_rdc;
    document.getElementById('modal-requested-date').textContent = transfer.requested_date;
    document.getElementById('modal-total-items').textContent = transfer.product_count + ' products (' + transfer.total_items + ' units)';
    document.getElementById('modal-requested-by').textContent = transfer.requested_by_name + ' (' + transfer.requested_by_role + ')';
    document.getElementById('modal-request-reason').textContent = transfer.request_reason;

    // Urgent badge
    if (transfer.is_urgent) {
        document.getElementById('modal-urgent-badge').classList.remove('hidden');
    } else {
        document.getElementById('modal-urgent-badge').classList.add('hidden');
    }

    // Build products list with stock check
    const productsList = document.getElementById('modal-products-list');
    productsList.innerHTML = '';
    
    transfer.items.forEach(item => {
        const canFulfill = item.source_stock >= item.requested_quantity;
        const stockPercentage = Math.min((item.source_stock / item.requested_quantity) * 100, 100);
        
        const productCard = `
            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="mono text-xs text-gray-500">${item.product_code}</span>
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-medium rounded">${item.category}</span>
                        </div>
                        <h5 class="font-bold text-gray-900">${item.product_name}</h5>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-3 mb-3">
                    <div class="bg-blue-50 rounded p-2">
                        <div class="text-xs text-blue-600 font-medium">Requested</div>
                        <div class="text-lg font-bold text-blue-900">${item.requested_quantity}</div>
                    </div>
                    <div class="bg-${canFulfill ? 'green' : 'red'}-50 rounded p-2">
                        <div class="text-xs text-${canFulfill ? 'green' : 'red'}-600 font-medium">Source Stock</div>
                        <div class="text-lg font-bold text-${canFulfill ? 'green' : 'red'}-900">${item.source_stock}</div>
                    </div>
                    <div class="bg-orange-50 rounded p-2">
                        <div class="text-xs text-orange-600 font-medium">Current Stock</div>
                        <div class="text-lg font-bold text-orange-900">${item.destination_stock}</div>
                    </div>
                </div>
                
                <div class="mb-2">
                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                        <span>Availability</span>
                        <span class="font-semibold">${canFulfill ? '✓ Can Fulfill' : '✗ Insufficient'}</span>
                    </div>
                    <div class="stock-bar">
                        <div class="stock-bar-fill bg-${canFulfill ? 'green' : 'red'}-500" style="width: ${stockPercentage}%"></div>
                    </div>
                </div>
                
                ${canFulfill 
                    ? '<div class="flex items-center text-xs text-green-700 bg-green-50 rounded px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Sufficient stock available</div>'
                    : '<div class="flex items-center text-xs text-red-700 bg-red-50 rounded px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i> Insufficient stock at source</div>'
                }
            </div>
        `;
        productsList.innerHTML += productCard;
    });

    // Show approval remarks if exists
    if (transfer.approval_remarks) {
        document.getElementById('approval-remarks-text').textContent = transfer.approval_remarks;
        document.getElementById('approval-remarks-display').classList.remove('hidden');
    } else {
        document.getElementById('approval-remarks-display').classList.add('hidden');
    }

    // Status management (only for RDC_MANAGER)
    if (currentUser.role === 'RDC_MANAGER') {
        setupStatusManagement(transfer);
    } else {
        document.getElementById('status-management-section').classList.add('hidden');
    }

    // Show modal
    document.getElementById('transfer-modal').classList.remove('hidden');
    document.getElementById('transfer-modal').classList.add('flex');
}

// Setup Status Management based on current status
function setupStatusManagement(transfer) {
    const section = document.getElementById('status-management-section');
    const currentStatusDisplay = document.getElementById('current-status-display');
    const statusChangeOptions = document.getElementById('status-change-options');
    const remarksSection = document.getElementById('remarks-section');
    const submitSection = document.getElementById('submit-status-section');

    section.classList.remove('hidden');
    
    // Display current status
    const statusColors = {
        'CLERK_REQUESTED': 'text-yellow-700',
        'PENDING': 'text-blue-700',
        'APPROVED': 'text-green-700',
        'REJECTED': 'text-red-700',
        'CANCELLED': 'text-gray-700',
        'RECEIVED': 'text-purple-700'
    };
    currentStatusDisplay.textContent = transfer.status.replace(/_/g, ' ');
    currentStatusDisplay.className = 'text-lg font-bold ' + statusColors[transfer.status];

    // Check if all products can be fulfilled
    const allCanFulfill = transfer.items.every(item => item.source_stock >= item.requested_quantity);
    
    // Status-specific options
    statusChangeOptions.innerHTML = '';
    
    if (transfer.status === 'CLERK_REQUESTED') {
        // Can change to PENDING or CANCELLED
        statusChangeOptions.innerHTML = `
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <p class="text-sm text-gray-700 mb-4">
                    ${allCanFulfill 
                        ? '<i class="fas fa-check-circle text-green-600 mr-2"></i>All products can be fulfilled from source RDC.' 
                        : '<i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>Warning: Some products have insufficient stock at source RDC.'}
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="selectStatus('PENDING')" class="status-option-btn px-4 py-3 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
                        <i class="fas fa-clock text-blue-600 mr-2"></i>
                        <span class="font-semibold">Move to Pending</span>
                    </button>
                    <button onclick="selectStatus('CANCELLED')" class="status-option-btn px-4 py-3 border-2 border-gray-300 rounded-lg hover:border-gray-500 hover:bg-gray-50 transition">
                        <i class="fas fa-ban text-gray-600 mr-2"></i>
                        <span class="font-semibold">Cancel Request</span>
                    </button>
                </div>
            </div>
        `;
        remarksSection.classList.remove('hidden');
        
    } else if (transfer.status === 'PENDING') {
        // Waiting for approval from source RDC
        statusChangeOptions.innerHTML = `
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-clock text-blue-600 mr-2"></i>
                    This request is pending approval from <strong>${transfer.source_rdc}</strong> manager.
                </p>
            </div>
        `;
        remarksSection.classList.add('hidden');
        submitSection.classList.add('hidden');
        
    } else if (transfer.status === 'APPROVED') {
        // Can mark as RECEIVED
        statusChangeOptions.innerHTML = `
            <div class="bg-green-50 rounded-lg p-4 border border-green-200 mb-4">
                <p class="text-sm text-green-800">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    This transfer has been approved and is ready for delivery.
                </p>
            </div>
            <button onclick="selectStatus('RECEIVED')" class="w-full status-option-btn px-4 py-3 bg-purple-100 border-2 border-purple-300 rounded-lg hover:bg-purple-200 transition">
                <i class="fas fa-box-open text-purple-600 mr-2"></i>
                <span class="font-semibold text-purple-900">Mark as Received</span>
            </button>
        `;
        remarksSection.classList.remove('hidden');
        document.getElementById('status-remarks').placeholder = 'Add delivery notes (optional)...';
        
    } else if (transfer.status === 'RECEIVED') {
        // Already completed
        statusChangeOptions.innerHTML = `
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                <p class="text-sm text-purple-800">
                    <i class="fas fa-check-double text-purple-600 mr-2"></i>
                    This transfer has been completed and stock has been updated.
                </p>
            </div>
        `;
        remarksSection.classList.add('hidden');
        submitSection.classList.add('hidden');
        
    } else {
        // REJECTED or CANCELLED - no actions available
        statusChangeOptions.innerHTML = `
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <p class="text-sm text-gray-700">
                    This transfer request has been ${transfer.status.toLowerCase()}.
                </p>
            </div>
        `;
        remarksSection.classList.add('hidden');
        submitSection.classList.add('hidden');
    }
}

// Select new status
function selectStatus(status) {
    selectedNewStatus = status;
    
    // Highlight selected button
    document.querySelectorAll('.status-option-btn').forEach(btn => {
        btn.classList.remove('border-blue-500', 'bg-blue-50', 'border-purple-500', 'bg-purple-50', 'border-gray-500', 'bg-gray-100');
        btn.classList.add('border-gray-300');
    });
    
    event.target.closest('.status-option-btn').classList.remove('border-gray-300');
    if (status === 'PENDING') {
        event.target.closest('.status-option-btn').classList.add('border-blue-500', 'bg-blue-50');
    } else if (status === 'RECEIVED') {
        event.target.closest('.status-option-btn').classList.add('border-purple-500', 'bg-purple-100');
    } else {
        event.target.closest('.status-option-btn').classList.add('border-gray-500', 'bg-gray-100');
    }
    
    document.getElementById('submit-status-section').classList.remove('hidden');
}

// Submit status change
document.getElementById('submit-status-btn')?.addEventListener('click', function() {
    if (!selectedNewStatus) {
        alert('Please select a status first!');
        return;
    }
    
    const transfer = transfers[currentTransferIndex];
    const remarks = document.getElementById('status-remarks').value.trim();
    
    // Validate remarks for certain statuses
    if ((selectedNewStatus === 'CANCELLED' || selectedNewStatus === 'PENDING') && !remarks) {
        alert('Please add remarks before updating status!');
        return;
    }
    
    // In real app, this would be an AJAX call
    console.log('Updating transfer status:', {
        transfer_id: transfer.transfer_id,
        old_status: transfer.status,
        new_status: selectedNewStatus,
        remarks: remarks,
        updated_by: currentUser.user_id
    });
    
    // Update local data
    transfer.status = selectedNewStatus;
    if (remarks) {
        transfer.approval_remarks = remarks;
    }
    
    // Show success message
    alert(`Transfer status updated to ${selectedNewStatus}!`);
    
    // Close modal and refresh list
    closeTransferModal();
    
    // Update status badge in list
    updateTransferStatusInList(currentTransferIndex, selectedNewStatus);
});

// Update status in the list
function updateTransferStatusInList(index, newStatus) {
    const buttons = document.querySelectorAll('.view-transfer-btn');
    if (buttons[index]) {
        const statusBadge = buttons[index].closest('.flex').querySelector('.transfer-status');
        if (statusBadge) {
            statusBadge.textContent = newStatus.replace(/_/g, ' ');
            
            // Update colors
            const statusColors = {
                'CLERK_REQUESTED': 'bg-yellow-100 text-yellow-700',
                'PENDING': 'bg-blue-100 text-blue-700',
                'APPROVED': 'bg-green-100 text-green-700',
                'REJECTED': 'bg-red-100 text-red-700',
                'CANCELLED': 'bg-gray-100 text-gray-700',
                'RECEIVED': 'bg-purple-100 text-purple-700'
            };
            
            statusBadge.className = 'transfer-status px-3 py-1 text-xs font-semibold rounded-full ' + statusColors[newStatus];
        }
    }
}

// Close modal
function closeTransferModal() {
    document.getElementById('transfer-modal').classList.add('hidden');
    document.getElementById('transfer-modal').classList.remove('flex');
    currentTransferIndex = null;
    selectedNewStatus = null;
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // View transfer buttons
    document.querySelectorAll('.view-transfer-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.getAttribute('data-index'));
            openTransferModal(index);
        });
    });

    // Close modal buttons
    document.getElementById('modal-close').addEventListener('click', closeTransferModal);
    document.getElementById('modal-close-btn').addEventListener('click', closeTransferModal);
    
    // Close on backdrop click
    document.getElementById('transfer-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeTransferModal();
        }
    });
});

// Dummy stock data for other RDCs
const otherRDCStock = {
    1: { 1: 500, 2: 400, 3: 200, 4: 300, 6: 350 },
    3: { 1: 250, 2: 180, 3: 0, 4: 220, 6: 190 },
    4: { 1: 320, 2: 290, 3: 150, 4: 0, 6: 280 },
    5: { 1: 410, 2: 350, 3: 180, 4: 260, 6: 310 }
};

// Check Stock Button
document.getElementById('check-stock-btn').addEventListener('click', function() {
    const selectedRDC = document.getElementById('check-rdc-dropdown').value;
    
    if (!selectedRDC) {
        alert('Please select an RDC first!');
        return;
    }
    
    const rdcName = document.getElementById('check-rdc-dropdown').selectedOptions[0].text;
    document.getElementById('checked-rdc-badge').classList.remove('hidden');
    document.getElementById('checked-rdc-name').textContent = rdcName;
    
    const productCards = document.querySelectorAll('.product-card');
    const stockData = otherRDCStock[selectedRDC];
    
    productCards.forEach(card => {
        const checkbox = card.querySelector('input[type="checkbox"]');
        const productId = checkbox.value;
        const stockDiv = card.querySelector('.other-rdc-stock');
        const stockValue = stockDiv.querySelector('.stock-value');
        
        stockDiv.classList.remove('hidden');
        
        if (stockData[productId] !== undefined) {
            if (stockData[productId] > 0) {
                stockValue.textContent = stockData[productId] + ' units available';
                stockValue.classList.remove('text-red-600');
                stockValue.classList.add('text-green-600');
            } else {
                stockValue.textContent = 'Out of Stock';
                stockValue.classList.remove('text-green-600');
                stockValue.classList.add('text-red-600');
            }
        } else {
            stockValue.textContent = 'Product not found in this RDC';
            stockValue.classList.remove('text-green-600');
            stockValue.classList.add('text-red-600');
        }
    });
});

// Clear Check Button
document.getElementById('clear-check-btn').addEventListener('click', function() {
    document.getElementById('checked-rdc-badge').classList.add('hidden');
    document.getElementById('check-rdc-dropdown').value = '';
    document.querySelectorAll('.other-rdc-stock').forEach(div => div.classList.add('hidden'));
});

// Track selected products
document.querySelectorAll('input[type="checkbox"][name="selected_products[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
});

function updateSelectedCount() {
    const checkedBoxes = document.querySelectorAll('input[type="checkbox"][name="selected_products[]"]:checked');
    const count = checkedBoxes.length;
    
    document.getElementById('selected-count').textContent = count;
    
    if (count > 0) {
        let summary = [];
        checkedBoxes.forEach(box => {
            const card = box.closest('.product-card');
            const productName = card.querySelector('h3').textContent;
            summary.push(productName);
        });
        document.getElementById('selected-summary').innerHTML = summary.join(', ');
    } else {
        document.getElementById('selected-summary').textContent = 'No products selected yet. Check the boxes on product cards above.';
    }
}

// Form validation
document.getElementById('transfer-request-form').addEventListener('submit', function(e) {
    const checkedBoxes = document.querySelectorAll('input[type="checkbox"][name="selected_products[]"]:checked');
    
    if (checkedBoxes.length === 0) {
        e.preventDefault();
        alert('Please select at least one product to request!');
        return;
    }
    
    let hasQuantity = true;
    checkedBoxes.forEach(box => {
        const productId = box.value;
        const qtyInput = document.querySelector(`input[name="request_qty_${productId}"]`);
        if (!qtyInput.value || qtyInput.value <= 0) {
            hasQuantity = false;
        }
    });
    
    if (!hasQuantity) {
        e.preventDefault();
        alert('Please enter quantities for all selected products!');
        return;
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
