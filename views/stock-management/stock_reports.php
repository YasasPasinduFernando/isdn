<?php
require_once __DIR__ . '/../../includes/header.php';

// Logged-in user data (from session)
$current_user = [
    'user_id' => $_SESSION['user_id'] ?? null,
    'name' => $_SESSION['username'] ?? 'User',
    'role' => 'rdc_manager', // Options: rdc_manager, head_office_manager, rdc_clerk, logistics_officer, system_admin
    'rdc_id' => $_SESSION['rdc_id'] ?? null,
    'rdc_name' => $_SESSION['rdc_name'] ?? 'NORTH RDC',
    'rdc_code' => $_SESSION['rdc_code'] ?? null
];

// Role-based access control
$role_permissions = [
    'rdc_manager' => [
        'reports' => ['current_stock', 'low_stock_alerts', 'transfer_summary'],
        'view_scope' => 'own_rdc',
        'can_export' => true
    ],
    'head_office_manager' => [
        'reports' => ['current_stock', 'low_stock_alerts', 'transfer_summary', 'stock_valuation'],
        'view_scope' => 'all_rdcs',
        'can_export' => true
    ],
    'rdc_clerk' => [
        'reports' => ['current_stock', 'low_stock_alerts'],
        'view_scope' => 'own_rdc',
        'can_export' => false
    ],
    'logistics_officer' => [
        'reports' => ['current_stock'],
        'view_scope' => 'own_rdc',
        'can_export' => false
    ],
    'system_admin' => [
        'reports' => ['current_stock', 'low_stock_alerts', 'transfer_summary'],
        'view_scope' => 'all_rdcs',
        'can_export' => true
    ]
];

$user_permissions = $role_permissions[$current_user['role']] ?? $role_permissions['rdc_clerk'];

// All RDCs
$all_rdcs = [
    ['rdc_id' => 1, 'rdc_name' => 'North RDC', 'rdc_code' => 'NORTH'],
    ['rdc_id' => 2, 'rdc_name' => 'South RDC', 'rdc_code' => 'SOUTH'],
    ['rdc_id' => 3, 'rdc_name' => 'East RDC', 'rdc_code' => 'EAST'],
    ['rdc_id' => 4, 'rdc_name' => 'West RDC', 'rdc_code' => 'WEST'],
    ['rdc_id' => 5, 'rdc_name' => 'Central RDC', 'rdc_code' => 'CENTRAL']
];

// Dummy Data: Current Stock Levels
$current_stock_data = [
    // North RDC
    ['rdc_id' => 1, 'rdc_name' => 'North RDC', 'product_code' => 'BEV001', 'product_name' => 'Coca Cola 1L', 'category' => 'Beverages', 'current_stock' => 10, 'minimum_level' => 20, 'unit_price' => 150.00, 'status' => 'LOW'],
    ['rdc_id' => 1, 'rdc_name' => 'North RDC', 'product_code' => 'BEV002', 'product_name' => 'Sprite 1L', 'category' => 'Beverages', 'current_stock' => 2, 'minimum_level' => 100, 'unit_price' => 150.00, 'status' => 'CRITICAL'],
    ['rdc_id' => 1, 'rdc_name' => 'North RDC', 'product_code' => 'FOOD001', 'product_name' => 'Rice 5kg', 'category' => 'Packaged Foods', 'current_stock' => 200, 'minimum_level' => 50, 'unit_price' => 850.00, 'status' => 'OUT_OF_STOCK'],
    ['rdc_id' => 1, 'rdc_name' => 'North RDC', 'product_code' => 'FOOD002', 'product_name' => 'Bread Loaf', 'category' => 'Packaged Foods', 'current_stock' => 300, 'minimum_level' => 200, 'unit_price' => 120.00, 'status' => 'LOW'],
    ['rdc_id' => 1, 'rdc_name' => 'North RDC', 'product_code' => 'CLEAN001', 'product_name' => 'Detergent 500g', 'category' => 'Home Cleaning', 'current_stock' => 280, 'minimum_level' => 80, 'unit_price' => 280.00, 'status' => 'OK'],
    ['rdc_id' => 1, 'rdc_name' => 'North RDC', 'product_code' => 'CARE001', 'product_name' => 'Toothpaste 100ml', 'category' => 'Personal Care', 'current_stock' => 350, 'minimum_level' => 150, 'unit_price' => 180.00, 'status' => 'OK'],
    
    // South RDC (Low stock)
    ['rdc_id' => 2, 'rdc_name' => 'South RDC', 'product_code' => 'BEV001', 'product_name' => 'Coca Cola 1L', 'category' => 'Beverages', 'current_stock' => 20, 'minimum_level' => 100, 'unit_price' => 150.00, 'status' => 'CRITICAL'],
    ['rdc_id' => 2, 'rdc_name' => 'South RDC', 'product_code' => 'BEV002', 'product_name' => 'Sprite 1L', 'category' => 'Beverages', 'current_stock' => 15, 'minimum_level' => 100, 'unit_price' => 150.00, 'status' => 'CRITICAL'],
    ['rdc_id' => 2, 'rdc_name' => 'South RDC', 'product_code' => 'FOOD001', 'product_name' => 'Rice 5kg', 'category' => 'Packaged Foods', 'current_stock' => 5, 'minimum_level' => 50, 'unit_price' => 850.00, 'status' => 'CRITICAL'],
    ['rdc_id' => 2, 'rdc_name' => 'South RDC', 'product_code' => 'FOOD002', 'product_name' => 'Bread Loaf', 'category' => 'Packaged Foods', 'current_stock' => 30, 'minimum_level' => 200, 'unit_price' => 120.00, 'status' => 'CRITICAL'],
    ['rdc_id' => 2, 'rdc_name' => 'South RDC', 'product_code' => 'CLEAN001', 'product_name' => 'Detergent 500g', 'category' => 'Home Cleaning', 'current_stock' => 150, 'minimum_level' => 80, 'unit_price' => 280.00, 'status' => 'OK'],
    ['rdc_id' => 2, 'rdc_name' => 'South RDC', 'product_code' => 'CARE001', 'product_name' => 'Toothpaste 100ml', 'category' => 'Personal Care', 'current_stock' => 80, 'minimum_level' => 150, 'unit_price' => 180.00, 'status' => 'LOW'],
    
    // East RDC
    ['rdc_id' => 3, 'rdc_name' => 'East RDC', 'product_code' => 'BEV001', 'product_name' => 'Coca Cola 1L', 'category' => 'Beverages', 'current_stock' => 250, 'minimum_level' => 100, 'unit_price' => 150.00, 'status' => 'OK'],
    ['rdc_id' => 3, 'rdc_name' => 'East RDC', 'product_code' => 'BEV002', 'product_name' => 'Sprite 1L', 'category' => 'Beverages', 'current_stock' => 180, 'minimum_level' => 100, 'unit_price' => 150.00, 'status' => 'OK'],
    ['rdc_id' => 3, 'rdc_name' => 'East RDC', 'product_code' => 'FOOD001', 'product_name' => 'Rice 5kg', 'category' => 'Packaged Foods', 'current_stock' => 0, 'minimum_level' => 50, 'unit_price' => 850.00, 'status' => 'OUT_OF_STOCK'],
    ['rdc_id' => 3, 'rdc_name' => 'East RDC', 'product_code' => 'FOOD002', 'product_name' => 'Bread Loaf', 'category' => 'Packaged Foods', 'current_stock' => 220, 'minimum_level' => 200, 'unit_price' => 120.00, 'status' => 'OK'],
    ['rdc_id' => 3, 'rdc_name' => 'East RDC', 'product_code' => 'CLEAN001', 'product_name' => 'Detergent 500g', 'category' => 'Home Cleaning', 'current_stock' => 180, 'minimum_level' => 80, 'unit_price' => 280.00, 'status' => 'OK'],
    ['rdc_id' => 3, 'rdc_name' => 'East RDC', 'product_code' => 'CARE001', 'product_name' => 'Toothpaste 100ml', 'category' => 'Personal Care', 'current_stock' => 190, 'minimum_level' => 150, 'unit_price' => 180.00, 'status' => 'OK'],
    
    // West RDC
    ['rdc_id' => 4, 'rdc_name' => 'West RDC', 'product_code' => 'BEV001', 'product_name' => 'Coca Cola 1L', 'category' => 'Beverages', 'current_stock' => 320, 'minimum_level' => 100, 'unit_price' => 150.00, 'status' => 'OK'],
    ['rdc_id' => 4, 'rdc_name' => 'West RDC', 'product_code' => 'BEV002', 'product_name' => 'Sprite 1L', 'category' => 'Beverages', 'current_stock' => 290, 'minimum_level' => 100, 'unit_price' => 150.00, 'status' => 'OK'],
    ['rdc_id' => 4, 'rdc_name' => 'West RDC', 'product_code' => 'FOOD001', 'product_name' => 'Rice 5kg', 'category' => 'Packaged Foods', 'current_stock' => 150, 'minimum_level' => 50, 'unit_price' => 850.00, 'status' => 'OK'],
    ['rdc_id' => 4, 'rdc_name' => 'West RDC', 'product_code' => 'FOOD002', 'product_name' => 'Bread Loaf', 'category' => 'Packaged Foods', 'current_stock' => 0, 'minimum_level' => 200, 'unit_price' => 120.00, 'status' => 'OUT_OF_STOCK'],
    ['rdc_id' => 4, 'rdc_name' => 'West RDC', 'product_code' => 'CLEAN001', 'product_name' => 'Detergent 500g', 'category' => 'Home Cleaning', 'current_stock' => 240, 'minimum_level' => 80, 'unit_price' => 280.00, 'status' => 'OK'],
    ['rdc_id' => 4, 'rdc_name' => 'West RDC', 'product_code' => 'CARE001', 'product_name' => 'Toothpaste 100ml', 'category' => 'Personal Care', 'current_stock' => 280, 'minimum_level' => 150, 'unit_price' => 180.00, 'status' => 'OK'],
    
    // Central RDC
    ['rdc_id' => 5, 'rdc_name' => 'Central RDC', 'product_code' => 'BEV001', 'product_name' => 'Coca Cola 1L', 'category' => 'Beverages', 'current_stock' => 410, 'minimum_level' => 100, 'unit_price' => 150.00, 'status' => 'OK'],
    ['rdc_id' => 5, 'rdc_name' => 'Central RDC', 'product_code' => 'BEV002', 'product_name' => 'Sprite 1L', 'category' => 'Beverages', 'current_stock' => 350, 'minimum_level' => 100, 'unit_price' => 150.00, 'status' => 'OK'],
    ['rdc_id' => 5, 'rdc_name' => 'Central RDC', 'product_code' => 'FOOD001', 'product_name' => 'Rice 5kg', 'category' => 'Packaged Foods', 'current_stock' => 180, 'minimum_level' => 50, 'unit_price' => 850.00, 'status' => 'OK'],
    ['rdc_id' => 5, 'rdc_name' => 'Central RDC', 'product_code' => 'FOOD002', 'product_name' => 'Bread Loaf', 'category' => 'Packaged Foods', 'current_stock' => 260, 'minimum_level' => 200, 'unit_price' => 120.00, 'status' => 'OK'],
    ['rdc_id' => 5, 'rdc_name' => 'Central RDC', 'product_code' => 'CLEAN001', 'product_name' => 'Detergent 500g', 'category' => 'Home Cleaning', 'current_stock' => 300, 'minimum_level' => 80, 'unit_price' => 280.00, 'status' => 'OK'],
    ['rdc_id' => 5, 'rdc_name' => 'Central RDC', 'product_code' => 'CARE001', 'product_name' => 'Toothpaste 100ml', 'category' => 'Personal Care', 'current_stock' => 310, 'minimum_level' => 150, 'unit_price' => 180.00, 'status' => 'OK']
];

// Dummy Data: Transfer Summary
$transfer_summary_data = [
    ['transfer_number' => 'TRF-NORTH-SOUTH-001', 'requested_date' => '2026-02-02', 'source_rdc' => 'North RDC', 'destination_rdc' => 'South RDC', 'product_count' => 3, 'total_items' => 350, 'status' => 'APPROVED', 'is_urgent' => true],
    ['transfer_number' => 'TRF-EAST-SOUTH-002', 'requested_date' => '2026-02-01', 'source_rdc' => 'East RDC', 'destination_rdc' => 'South RDC', 'product_count' => 2, 'total_items' => 150, 'status' => 'PENDING', 'is_urgent' => false],
    ['transfer_number' => 'TRF-WEST-SOUTH-003', 'requested_date' => '2026-02-03', 'source_rdc' => 'West RDC', 'destination_rdc' => 'South RDC', 'product_count' => 1, 'total_items' => 80, 'status' => 'RECEIVED', 'is_urgent' => true],
    ['transfer_number' => 'TRF-CENTRAL-NORTH-004', 'requested_date' => '2026-01-31', 'source_rdc' => 'Central RDC', 'destination_rdc' => 'North RDC', 'product_count' => 2, 'total_items' => 150, 'status' => 'RECEIVED', 'is_urgent' => false],
    ['transfer_number' => 'TRF-NORTH-EAST-005', 'requested_date' => '2026-01-30', 'source_rdc' => 'North RDC', 'destination_rdc' => 'East RDC', 'product_count' => 1, 'total_items' => 100, 'status' => 'REJECTED', 'is_urgent' => false],
    ['transfer_number' => 'TRF-SOUTH-CENTRAL-006', 'requested_date' => '2026-01-29', 'source_rdc' => 'South RDC', 'destination_rdc' => 'Central RDC', 'product_count' => 2, 'total_items' => 120, 'status' => 'CANCELLED', 'is_urgent' => false],
];

// Categories
$categories = ['Beverages', 'Packaged Foods', 'Home Cleaning', 'Personal Care'];
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Reports - ISDN</title>
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
        
        .slide-down {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        /* Tab styles */
        .tab-btn {
            transition: all 0.2s ease;
        }

        .tab-btn.active {
            border-bottom: 3px solid #3b82f6;
            color: #3b82f6;
        }

        /* Stock status colors */
        .status-ok {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-low {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-critical {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-out {
            background-color: #fecaca;
            color: #7f1d1d;
        }

        /* Print styles */
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                font-size: 12px;
            }
            
            table {
                page-break-inside: avoid;
            }
        }

        /* Table hover */
        .data-table tbody tr {
            transition: background-color 0.15s ease;
        }

        .data-table tbody tr:hover {
            background-color: #f8fafc;
        }
    </style>
</head>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Stock Reports</h1>
                <p class="text-gray-600 mt-1">Real-time inventory analytics and insights</p>
            </div>
        </div>
    </div>

    <!-- Access Level Notice -->
    <?php if ($user_permissions['view_scope'] === 'own_rdc'): ?>
    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-500 mr-3"></i>
            <span class="text-sm text-blue-800">You can view reports for <strong><?php echo $current_user['rdc_name']; ?></strong> only.</span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Report Type Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 overflow-x-auto">
                <?php if (in_array('current_stock', $user_permissions['reports'])): ?>
                <button onclick="showReport('current_stock')" id="tab-current_stock" class="tab-btn active whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-boxes mr-2"></i>Current Stock Levels
                </button>
                <?php endif; ?>
                
                <?php if (in_array('low_stock_alerts', $user_permissions['reports'])): ?>
                <button onclick="showReport('low_stock_alerts')" id="tab-low_stock_alerts" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Low Stock Alerts
                </button>
                <?php endif; ?>
                
                <?php if (in_array('transfer_summary', $user_permissions['reports'])): ?>
                <button onclick="showReport('transfer_summary')" id="tab-transfer_summary" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-exchange-alt mr-2"></i>Transfer Summary
                </button>
                <?php endif; ?>
                
                <?php if (in_array('stock_valuation', $user_permissions['reports'])): ?>
                <button onclick="showReport('stock_valuation')" id="tab-stock_valuation" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-dollar-sign mr-2"></i>Stock Valuation
                </button>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 no-print">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-filter mr-2 text-blue-600"></i>
            Filters
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- RDC Filter -->
            <?php if ($user_permissions['view_scope'] === 'all_rdcs'): ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">RDC</label>
                <select id="filter-rdc" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="all">All RDCs</option>
                    <?php foreach ($all_rdcs as $rdc): ?>
                    <option value="<?php echo $rdc['rdc_id']; ?>"><?php echo $rdc['rdc_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">RDC</label>
                <input type="text" readonly value="<?php echo $current_user['rdc_name']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
            </div>
            <?php endif; ?>
            
            <!-- Category Filter -->
            <div id="filter-category-wrapper">
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select id="filter-category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="all">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Status Filter (for stock reports) -->
            <div id="filter-status-wrapper">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="filter-status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="all">All Status</option>
                    <option value="OK">OK</option>
                    <option value="LOW">Low Stock</option>
                    <option value="CRITICAL">Critical</option>
                    <option value="OUT_OF_STOCK">Out of Stock</option>
                </select>
            </div>
            
            <!-- Date Range (for transfers) -->
            <div id="filter-date-wrapper" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                <select id="filter-date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="7">Last 7 Days</option>
                    <option value="30" selected>Last 30 Days</option>
                    <option value="90">Last 90 Days</option>
                    <option value="all">All Time</option>
                </select>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex items-end space-x-2">
                <button onclick="applyFilters()" class="flex-1 px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700 transition shadow-lg">
                    <i class="fas fa-sync mr-2"></i>Apply
                </button>
                <button onclick="resetFilters()" class="px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                    <i class="fas fa-redo"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Report Content Sections -->
    
    <!-- Current Stock Levels Report -->
    <div id="report-current_stock" class="report-section">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Report Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4 flex items-center justify-between no-print">
                <div>
                    <h2 class="text-xl font-bold text-white">Current Stock Levels Report</h2>
                    <p class="text-blue-100 text-sm mt-1">Real-time inventory across all products</p>
                </div>
                <?php if ($user_permissions['can_export']): ?>
                <div class="flex space-x-2">
                    <button onclick="exportToPDF('current_stock')" class="px-4 py-2 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition flex items-center">
                        <i class="fas fa-file-pdf mr-2"></i>PDF
                    </button>
                    <button onclick="exportToExcel('current_stock')" class="px-4 py-2 bg-white text-green-600 font-semibold rounded-lg hover:bg-green-50 transition flex items-center">
                        <i class="fas fa-file-excel mr-2"></i>Excel
                    </button>
                    <button onclick="window.print()" class="px-4 py-2 bg-white text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 border-b border-gray-200 bg-gray-50">
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Total Products</div>
                    <div id="stat-total-products" class="text-2xl font-bold text-gray-900">30</div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-green-200">
                    <div class="text-xs text-green-600 uppercase tracking-wider mb-1">OK Status</div>
                    <div id="stat-ok" class="text-2xl font-bold text-green-600">22</div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-yellow-200">
                    <div class="text-xs text-yellow-600 uppercase tracking-wider mb-1">Low Stock</div>
                    <div id="stat-low" class="text-2xl font-bold text-yellow-600">5</div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-red-200">
                    <div class="text-xs text-red-600 uppercase tracking-wider mb-1">Critical</div>
                    <div id="stat-critical" class="text-2xl font-bold text-red-600">3</div>
                </div>
            </div>
            
            <!-- Data Table -->
            <div class="overflow-x-auto">
                <table class="data-table min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RDC</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Min Level</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock %</th>
                        </tr>
                    </thead>
                    <tbody id="current-stock-tbody" class="bg-white divide-y divide-gray-200">
                        <?php 
                        $filtered_data = $current_stock_data;
                        if ($user_permissions['view_scope'] === 'own_rdc') {
                            $filtered_data = array_filter($current_stock_data, function($item) use ($current_user) {
                                return $item['rdc_id'] == $current_user['rdc_id'];
                            });
                        }
                        
                        foreach ($filtered_data as $item): 
                            $stock_percent = ($item['current_stock'] / $item['minimum_level']) * 100;
                        ?>
                        <tr data-rdc="<?php echo $item['rdc_id']; ?>" data-category="<?php echo $item['category']; ?>" data-status="<?php echo $item['status']; ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $item['rdc_name']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm mono text-gray-600"><?php echo $item['product_code']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $item['product_name']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $item['category']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900"><?php echo number_format($item['current_stock']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500"><?php echo number_format($item['minimum_level']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full status-<?php echo strtolower(str_replace('_', '-', $item['status'])); ?>">
                                    <?php echo str_replace('_', ' ', $item['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full <?php 
                                        if ($stock_percent >= 100) echo 'bg-green-500';
                                        elseif ($stock_percent >= 50) echo 'bg-yellow-500';
                                        else echo 'bg-red-500';
                                    ?>" style="width: <?php echo min($stock_percent, 100); ?>%"></div>
                                </div>
                                <div class="text-xs text-center mt-1 text-gray-500"><?php echo round($stock_percent); ?>%</div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts Report -->
    <div id="report-low_stock_alerts" class="report-section hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Report Header -->
            <div class="bg-gradient-to-r from-orange-600 to-red-600 px-6 py-4 flex items-center justify-between no-print">
                <div>
                    <h2 class="text-xl font-bold text-white">Low Stock Alerts Report</h2>
                    <p class="text-orange-100 text-sm mt-1">Products requiring immediate attention</p>
                </div>
                <?php if ($user_permissions['can_export']): ?>
                <div class="flex space-x-2">
                    <button onclick="exportToPDF('low_stock_alerts')" class="px-4 py-2 bg-white text-orange-600 font-semibold rounded-lg hover:bg-orange-50 transition flex items-center">
                        <i class="fas fa-file-pdf mr-2"></i>PDF
                    </button>
                    <button onclick="exportToExcel('low_stock_alerts')" class="px-4 py-2 bg-white text-green-600 font-semibold rounded-lg hover:bg-green-50 transition flex items-center">
                        <i class="fas fa-file-excel mr-2"></i>Excel
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Alert Summary -->
            <div class="grid grid-cols-3 gap-4 p-6 border-b border-gray-200 bg-gray-50">
                <div class="bg-white rounded-lg p-4 border border-red-200">
                    <div class="text-xs text-red-600 uppercase tracking-wider mb-1">Out of Stock</div>
                    <div id="alert-out" class="text-2xl font-bold text-red-600">2</div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-orange-200">
                    <div class="text-xs text-orange-600 uppercase tracking-wider mb-1">Critical (< 30%)</div>
                    <div id="alert-critical" class="text-2xl font-bold text-orange-600">4</div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-yellow-200">
                    <div class="text-xs text-yellow-600 uppercase tracking-wider mb-1">Low (< Min)</div>
                    <div id="alert-low" class="text-2xl font-bold text-yellow-600">5</div>
                </div>
            </div>
            
            <!-- Alert Table -->
            <div class="overflow-x-auto">
                <table class="data-table min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RDC</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Current</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Required</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Shortage</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody id="low-stock-tbody" class="bg-white divide-y divide-gray-200">
                        <?php 
                        $low_stock_filtered = array_filter($current_stock_data, function($item) {
                            return in_array($item['status'], ['LOW', 'CRITICAL', 'OUT_OF_STOCK']);
                        });
                        
                        if ($user_permissions['view_scope'] === 'own_rdc') {
                            $low_stock_filtered = array_filter($low_stock_filtered, function($item) use ($current_user) {
                                return $item['rdc_id'] == $current_user['rdc_id'];
                            });
                        }
                        
                        // Sort by urgency
                        usort($low_stock_filtered, function($a, $b) {
                            $priority = ['OUT_OF_STOCK' => 1, 'CRITICAL' => 2, 'LOW' => 3];
                            return $priority[$a['status']] - $priority[$b['status']];
                        });
                        
                        foreach ($low_stock_filtered as $item): 
                            $shortage = $item['minimum_level'] - $item['current_stock'];
                        ?>
                        <tr data-rdc="<?php echo $item['rdc_id']; ?>" data-category="<?php echo $item['category']; ?>" data-status="<?php echo $item['status']; ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($item['status'] === 'OUT_OF_STOCK'): ?>
                                <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">
                                    <i class="fas fa-exclamation-circle mr-1"></i>URGENT
                                </span>
                                <?php elseif ($item['status'] === 'CRITICAL'): ?>
                                <span class="px-3 py-1 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>CRITICAL
                                </span>
                                <?php else: ?>
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">
                                    <i class="fas fa-exclamation mr-1"></i>LOW
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $item['rdc_name']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $item['product_name']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $item['category']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-red-600"><?php echo number_format($item['current_stock']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500"><?php echo number_format($item['minimum_level']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-red-700"><?php echo number_format($shortage); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="request_product_units.php" class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition">
                                    <i class="fas fa-plus mr-1"></i>Request
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Transfer Summary Report -->
    <div id="report-transfer_summary" class="report-section hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Report Header -->
            <div class="bg-gradient-to-r from-green-600 to-teal-600 px-6 py-4 flex items-center justify-between no-print">
                <div>
                    <h2 class="text-xl font-bold text-white">Transfer Summary Report</h2>
                    <p class="text-green-100 text-sm mt-1">Inter-RDC stock movements and status</p>
                </div>
                <?php if ($user_permissions['can_export']): ?>
                <div class="flex space-x-2">
                    <button onclick="exportToPDF('transfer_summary')" class="px-4 py-2 bg-white text-green-600 font-semibold rounded-lg hover:bg-green-50 transition flex items-center">
                        <i class="fas fa-file-pdf mr-2"></i>PDF
                    </button>
                    <button onclick="exportToExcel('transfer_summary')" class="px-4 py-2 bg-white text-green-600 font-semibold rounded-lg hover:bg-green-50 transition flex items-center">
                        <i class="fas fa-file-excel mr-2"></i>Excel
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Transfer Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 p-6 border-b border-gray-200 bg-gray-50">
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Total Transfers</div>
                    <div id="transfer-total" class="text-2xl font-bold text-gray-900">6</div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-blue-200">
                    <div class="text-xs text-blue-600 uppercase tracking-wider mb-1">Pending</div>
                    <div id="transfer-pending" class="text-2xl font-bold text-blue-600">1</div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-green-200">
                    <div class="text-xs text-green-600 uppercase tracking-wider mb-1">Approved</div>
                    <div id="transfer-approved" class="text-2xl font-bold text-green-600">1</div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-purple-200">
                    <div class="text-xs text-purple-600 uppercase tracking-wider mb-1">Received</div>
                    <div id="transfer-received" class="text-2xl font-bold text-purple-600">2</div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-red-200">
                    <div class="text-xs text-red-600 uppercase tracking-wider mb-1">Rejected</div>
                    <div id="transfer-rejected" class="text-2xl font-bold text-red-600">2</div>
                </div>
            </div>
            
            <!-- Transfer Table -->
            <div class="overflow-x-auto">
                <table class="data-table min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transfer #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From → To</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Items</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        </tr>
                    </thead>
                    <tbody id="transfer-summary-tbody" class="bg-white divide-y divide-gray-200">
                        <?php 
                        $transfer_filtered = $transfer_summary_data;
                        
                        foreach ($transfer_filtered as $transfer): 
                            $statusColors = [
                                'CLERK_REQUESTED' => 'bg-yellow-100 text-yellow-700',
                                'PENDING' => 'bg-blue-100 text-blue-700',
                                'APPROVED' => 'bg-green-100 text-green-700',
                                'REJECTED' => 'bg-red-100 text-red-700',
                                'CANCELLED' => 'bg-gray-100 text-gray-700',
                                'RECEIVED' => 'bg-purple-100 text-purple-700'
                            ];
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm mono font-semibold text-gray-900"><?php echo $transfer['transfer_number']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($transfer['requested_date'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <span class="font-medium"><?php echo $transfer['source_rdc']; ?></span>
                                    <i class="fas fa-arrow-right mx-2 text-gray-400"></i>
                                    <span class="font-medium"><?php echo $transfer['destination_rdc']; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900"><?php echo $transfer['product_count']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900"><?php echo number_format($transfer['total_items']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $statusColors[$transfer['status']]; ?>">
                                    <?php echo str_replace('_', ' ', $transfer['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if ($transfer['is_urgent']): ?>
                                <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                                    <i class="fas fa-bolt"></i> URGENT
                                </span>
                                <?php else: ?>
                                <span class="text-xs text-gray-500">Normal</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Stock Valuation Report (HEAD_OFFICE_MANAGER & SYSTEM_ADMIN only) -->
    <?php if (in_array('stock_valuation', $user_permissions['reports'])): ?>
    <div id="report-stock_valuation" class="report-section hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Report Header -->
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4 flex items-center justify-between no-print">
                <div>
                    <h2 class="text-xl font-bold text-white">Stock Valuation Report</h2>
                    <p class="text-purple-100 text-sm mt-1">Financial overview of inventory assets</p>
                </div>
                <div class="flex space-x-2">
                    <button onclick="exportToPDF('stock_valuation')" class="px-4 py-2 bg-white text-purple-600 font-semibold rounded-lg hover:bg-purple-50 transition flex items-center">
                        <i class="fas fa-file-pdf mr-2"></i>PDF
                    </button>
                    <button onclick="exportToExcel('stock_valuation')" class="px-4 py-2 bg-white text-green-600 font-semibold rounded-lg hover:bg-green-50 transition flex items-center">
                        <i class="fas fa-file-excel mr-2"></i>Excel
                    </button>
                </div>
            </div>
            
            <!-- Valuation Summary -->
            <div class="p-6 border-b border-gray-200 bg-gradient-to-br from-purple-50 to-pink-50">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-xl p-6 shadow-sm border-2 border-purple-200">
                        <div class="text-sm text-purple-600 font-medium mb-2">Total Stock Value</div>
                        <div class="text-3xl font-bold text-purple-900">
                            LKR <?php 
                                $total_value = 0;
                                foreach ($current_stock_data as $item) {
                                    $total_value += ($item['current_stock'] * $item['unit_price']);
                                }
                                echo number_format($total_value, 2);
                            ?>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border-2 border-blue-200">
                        <div class="text-sm text-blue-600 font-medium mb-2">Total Units</div>
                        <div class="text-3xl font-bold text-blue-900">
                            <?php echo number_format(array_sum(array_column($current_stock_data, 'current_stock'))); ?>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border-2 border-green-200">
                        <div class="text-sm text-green-600 font-medium mb-2">Average Value/Unit</div>
                        <div class="text-3xl font-bold text-green-900">
                            LKR <?php echo number_format($total_value / max(array_sum(array_column($current_stock_data, 'current_stock')), 1), 2); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Valuation Table -->
            <div class="overflow-x-auto">
                <table class="data-table min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RDC</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price (LKR)</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value (LKR)</th>
                        </tr>
                    </thead>
                    <tbody id="valuation-tbody" class="bg-white divide-y divide-gray-200">
                        <?php 
                        foreach ($current_stock_data as $item): 
                            $item_value = $item['current_stock'] * $item['unit_price'];
                        ?>
                        <tr data-rdc="<?php echo $item['rdc_id']; ?>" data-category="<?php echo $item['category']; ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $item['rdc_name']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $item['product_name']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $item['category']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900"><?php echo number_format($item['current_stock']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900"><?php echo number_format($item['unit_price'], 2); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-purple-900"><?php echo number_format($item_value, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-100">
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-right text-sm font-bold text-gray-900">Grand Total:</td>
                            <td class="px-6 py-4 text-right text-lg font-bold text-purple-900">LKR <?php echo number_format($total_value, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- JavaScript -->
<script>
// Current active report
let currentReport = 'current_stock';

// Show specific report
function showReport(reportType) {
    // Hide all reports
    document.querySelectorAll('.report-section').forEach(section => {
        section.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active', 'border-blue-600', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected report
    document.getElementById('report-' + reportType).classList.remove('hidden');
    
    // Add active class to selected tab
    const activeTab = document.getElementById('tab-' + reportType);
    activeTab.classList.add('active', 'border-blue-600', 'text-blue-600');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    
    currentReport = reportType;
    
    // Adjust filter visibility
    if (reportType === 'transfer_summary') {
        document.getElementById('filter-date-wrapper').classList.remove('hidden');
        document.getElementById('filter-status-wrapper').classList.add('hidden');
    } else {
        document.getElementById('filter-date-wrapper').classList.add('hidden');
        document.getElementById('filter-status-wrapper').classList.remove('hidden');
    }
    
    // Hide category filter for valuation report
    if (reportType === 'stock_valuation') {
        document.getElementById('filter-category-wrapper').classList.remove('hidden');
    }
}

// Apply filters
function applyFilters() {
    const rdcFilter = document.getElementById('filter-rdc') ? document.getElementById('filter-rdc').value : 'own';
    const categoryFilter = document.getElementById('filter-category').value;
    const statusFilter = document.getElementById('filter-status').value;
    
    // Filter tables
    const tables = ['current-stock-tbody', 'low-stock-tbody', 'valuation-tbody'];
    
    tables.forEach(tableId => {
        const tbody = document.getElementById(tableId);
        if (!tbody) return;
        
        const rows = tbody.querySelectorAll('tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            let show = true;
            
            // RDC filter
            if (rdcFilter !== 'all' && rdcFilter !== 'own') {
                if (row.dataset.rdc !== rdcFilter) show = false;
            }
            
            // Category filter
            if (categoryFilter !== 'all') {
                if (row.dataset.category !== categoryFilter) show = false;
            }
            
            // Status filter
            if (statusFilter !== 'all') {
                if (row.dataset.status !== statusFilter) show = false;
            }
            
            if (show) {
                row.classList.remove('hidden');
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });
        
        // Update stats
        updateStats();
    });
}

// Reset filters
function resetFilters() {
    if (document.getElementById('filter-rdc')) {
        document.getElementById('filter-rdc').value = 'all';
    }
    document.getElementById('filter-category').value = 'all';
    document.getElementById('filter-status').value = 'all';
    document.getElementById('filter-date').value = '30';
    
    // Show all rows
    document.querySelectorAll('tbody tr').forEach(row => {
        row.classList.remove('hidden');
    });
    
    updateStats();
}

// Update statistics
function updateStats() {
    // Current stock stats
    const currentStockRows = document.querySelectorAll('#current-stock-tbody tr:not(.hidden)');
    let okCount = 0, lowCount = 0, criticalCount = 0, outCount = 0;
    
    currentStockRows.forEach(row => {
        const status = row.dataset.status;
        if (status === 'OK') okCount++;
        else if (status === 'LOW') lowCount++;
        else if (status === 'CRITICAL') criticalCount++;
        else if (status === 'OUT_OF_STOCK') outCount++;
    });
    
    if (document.getElementById('stat-total-products')) {
        document.getElementById('stat-total-products').textContent = currentStockRows.length;
        document.getElementById('stat-ok').textContent = okCount;
        document.getElementById('stat-low').textContent = lowCount;
        document.getElementById('stat-critical').textContent = criticalCount + outCount;
    }
    
    // Low stock stats
    if (document.getElementById('alert-out')) {
        document.getElementById('alert-out').textContent = outCount;
        document.getElementById('alert-critical').textContent = criticalCount;
        document.getElementById('alert-low').textContent = lowCount;
    }
}

// Export to PDF (placeholder - would use a library like jsPDF)
function exportToPDF(reportType) {
    alert('PDF export functionality would be implemented using a library like jsPDF or server-side generation.\n\nFor now, please use the Print button as an alternative.');
}

// Export to Excel (placeholder - would use a library like SheetJS)
function exportToExcel(reportType) {
    alert('Excel export functionality would be implemented using SheetJS (xlsx) library.\n\nReport: ' + reportType.replace('_', ' ').toUpperCase());
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateStats();
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
