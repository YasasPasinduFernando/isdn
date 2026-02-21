<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/tracking_modal.php';


// ============================================
// SEND PRODUCT UNITS (APPROVAL) PAGE
// For: RDC_MANAGER only
// ============================================

// Use controller-provided data when available; otherwise fall back to safe 
if (!isset($current_user)) {
    $role = $_SESSION['role'] ?? '';
    $role_upper = strtoupper($role);
    $current_user = [
        'user_id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['username'] ?? 'User',
        'role' => $role_upper,
        'rdc_id' => $_SESSION['rdc_id'] ?? null,
        'rdc_name' => $_SESSION['rdc_name'] ?? '',
        'rdc_code' => $_SESSION['rdc_code'] ?? ''
    ];
}

// Use controller-provided data when available; otherwise fall back to empty arrays
if (!isset($pending_transfers) || !is_array($pending_transfers)) {
    $pending_transfers = [];
}
echo '<pre>';
print_r($pending_transfers);
echo '</pre>';
if (!isset($processed_transfers) || !is_array($processed_transfers)) {
    $processed_transfers = [];
}
?>
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
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
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
        
        .slide-in {
            animation: slideIn 0.4s ease-out;
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        
        .pulse-slow {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        /* Transfer card styles */
        .transfer-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        
        .transfer-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
        }
        
        .transfer-card.expanded {
            transform: translateY(0);
            cursor: default;
        }
        
        /* Detail view overlay */
        .detail-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 40;
            display: none;
        }
        
        .detail-overlay.active {
            display: block;
        }
        
        .detail-panel {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.95);
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            z-index: 50;
            overflow-y: auto;
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .detail-panel.active {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
        
        /* Badge animations */
        .badge-urgent {
            animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        /* Status button styles */
        .status-btn {
            transition: all 0.2s ease;
        }
        
        .status-btn:not(:disabled):hover {
            transform: scale(1.05);
        }
        
        .status-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Stock comparison bar */
        .stock-bar {
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }
        
        .stock-bar-fill {
            height: 100%;
            transition: width 0.3s ease;
        }
        
        /* Success checkmark animation */
        @keyframes checkmark {
            0% {
                transform: scale(0) rotate(-45deg);
            }
            50% {
                transform: scale(1.1) rotate(-45deg);
            }
            100% {
                transform: scale(1) rotate(-45deg);
            }
        }
        
        .checkmark {
            animation: checkmark 0.4s ease-out;
        }

        .flex-grow{
            flex-grow: 0 !important;
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Page Title & Stats -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Transfer Approval Requests</h1>
            <p class="text-gray-600 mb-6">Review and approve stock transfer requests to your RDC</p>
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Pending Approvals</p>
                            <p class="text-2xl font-bold text-yellow-600"><?php echo count($pending_transfers); ?></p>
                        </div>
                        <div class="h-12 w-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Urgent Requests</p>
                            <p class="text-2xl font-bold text-red-600">
                                <?php echo count(array_filter($pending_transfers, function($t) { return $t['is_urgent']; })); ?>
                            </p>
                        </div>
                        <div class="h-12 w-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-bolt text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Total Items</p>
                            <p class="text-2xl font-bold text-blue-600">
                                <?php echo array_sum(array_column($pending_transfers, 'total_items')); ?>
                            </p>
                        </div>
                        <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-boxes text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Your RDC</p>
                            <p class="text-lg font-bold text-gray-900"><?php echo $current_user['rdc_code']; ?></p>
                        </div>
                        <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-warehouse text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="showTab('pending')" id="tab-pending" class="tab-btn active border-b-2 border-blue-600 py-4 px-1 text-sm font-semibold text-blue-600">
                        Pending Requests
                        <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-600 text-xs font-bold rounded-full"><?php echo count($pending_transfers); ?></span>
                    </button>
                    <button onclick="showTab('history')" id="tab-history" class="tab-btn border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        History
                        <span class="ml-2 px-2 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full"><?php echo count($processed_transfers); ?></span>
                    </button>
                </nav>
            </div>
        </div>

        <!-- Pending Requests Section -->
        <div id="pending-section" class="tab-content">
            <?php if (empty($pending_transfers)): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <div class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">All Caught Up!</h3>
                    <p class="text-gray-600">No pending transfer requests at the moment.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-6">
                    <?php foreach ($pending_transfers as $transfer): ?>
                    <div class="transfer-card bg-white rounded-xl shadow-sm border border-gray-200 hover:border-blue-300 overflow-hidden slide-in"
                         onclick="openDetailView(<?php echo htmlspecialchars(json_encode($transfer), ENT_QUOTES, 'UTF-8'); ?>)">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <span class="mono text-sm font-bold text-gray-900"><?php echo $transfer['transfer_number']; ?></span>
                                        <?php if ($transfer['is_urgent']): ?>
                                        <span class="badge-urgent px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full flex items-center">
                                            <i class="fas fa-bolt text-xs mr-1"></i> URGENT
                                        </span>
                                        <?php endif; ?>
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">
                                            <?php echo str_replace('_', ' ', $transfer['status']); ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                                        <span class="flex items-center">
                                            <i class="fas fa-building mr-2 text-gray-400"></i>
                                            From: <strong class="ml-1 text-gray-900"><?php echo $transfer['destination_rdc']; ?></strong>
                                        </span>
                                        <span class="text-gray-400">•</span>
                                        <span class="flex items-center">
                                            <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                            <?php echo date('M d, Y h:i A', strtotime($transfer['requested_date'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <button class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center">
                                    View Details
                                    <i class="fas fa-chevron-right ml-2"></i>
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="text-xs text-gray-500 mb-1">Requested By</div>
                                    <div class="text-sm font-semibold text-gray-900"><?php echo $transfer['requested_by_name']; ?></div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="text-xs text-gray-500 mb-1">Products</div>
                                    <div class="text-sm font-semibold text-gray-900"><?php echo $transfer['product_count']; ?> items</div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="text-xs text-gray-500 mb-1">Total Quantity</div>
                                    <div class="text-sm font-semibold text-gray-900"><?php echo $transfer['total_items']; ?> units</div>
                                </div>
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <div class="text-xs font-medium text-blue-700 mb-1">Reason</div>
                                <div class="text-sm text-gray-700"><?php echo $transfer['request_reason']; ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- History Section -->
        <div id="history-section" class="tab-content hidden">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transfer Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source RDC</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved By</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($processed_transfers as $transfer): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="mono text-sm font-semibold text-gray-900"><?php echo $transfer['transfer_number']; ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $transfer['source_rdc_name']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($transfer['requested_date'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $transfer['product_count']; ?> products (<?php echo $transfer['total_items']; ?> units)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php 
                                    echo $transfer['approval_status'] === 'APPROVED' 
                                        ? 'bg-green-100 text-green-700' 
                                        : 'bg-red-100 text-red-700';
                                ?>">
                                    <?php echo $transfer['approval_status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $transfer['approved_by']; ?>
                                <div class="text-xs text-gray-400"><?php echo date('M d, h:i A', strtotime($transfer['approval_date'])); ?></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Detail View Overlay -->
    <div id="detail-overlay" class="detail-overlay" onclick="closeDetailView()"></div>
    
    <!-- Detail View Panel -->
    <div id="detail-panel" class="detail-panel">
        <!-- Content will be dynamically inserted here -->
    </div>

    <!-- JavaScript -->
    <script>
        // Tab switching
        function showTab(tab) {
            // Hide all sections
            document.querySelectorAll('.tab-content').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active', 'border-blue-600', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected section
            document.getElementById(`${tab}-section`).classList.remove('hidden');
            
            // Add active class to selected tab
            const activeTab = document.getElementById(`tab-${tab}`);
            activeTab.classList.add('active', 'border-blue-600', 'text-blue-600');
            activeTab.classList.remove('border-transparent', 'text-gray-500');
        }
        
        // Open detail view
        function openDetailView(transfer) {
            const overlay = document.getElementById('detail-overlay');
            const panel = document.getElementById('detail-panel');
            
            // Generate products HTML
            let productsHtml = '';
            transfer.items.forEach((item, index) => {
                const stockPercentage = (item.available_stock_here / (item.available_stock_here + item.requested_quantity)) * 100;
                const canFulfill = item.available_stock_here >= item.requested_quantity;
                
                productsHtml += `
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="mono text-xs text-gray-500">${item.product_code}</span>
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-medium rounded">${item.category}</span>
                                </div>
                                <h4 class="text-lg font-bold text-gray-900">${item.product_name}</h4>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500">Unit Price</div>
                                <div class="text-lg font-bold text-gray-900">LKR ${item.unit_price.toFixed(2)}</div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-4 mb-3">
                            <div class="bg-red-50 rounded-lg p-3">
                                <div class="text-xs text-red-600 font-medium mb-1">Their Current Stock</div>
                                <div class="text-2xl font-bold text-red-600">${item.current_stock_source}</div>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-3">
                                <div class="text-xs text-blue-600 font-medium mb-1">Requested Quantity</div>
                                <div class="text-2xl font-bold text-blue-600">${item.requested_quantity}</div>
                            </div>
                            <div class="bg-green-50 rounded-lg p-3">
                                <div class="text-xs text-green-600 font-medium mb-1">Your Available Stock</div>
                                <div class="text-2xl font-bold text-green-600">${item.available_stock_here}</div>
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span>Stock Availability</span>
                                <span class="font-semibold">${canFulfill ? 'Can Fulfill' : 'Insufficient Stock'}</span>
                            </div>
                            <div class="stock-bar">
                                <div class="stock-bar-fill ${canFulfill ? 'bg-green-500' : 'bg-red-500'}" 
                                     style="width: ${Math.min(stockPercentage, 100)}%"></div>
                            </div>
                        </div>
                        
                        ${canFulfill 
                            ? `<div class="flex items-center text-sm text-green-700 bg-green-50 rounded px-3 py-2">
                                   <i class="fas fa-check-circle mr-2"></i>
                                   Sufficient stock available to fulfill this request
                               </div>`
                            : `<div class="flex items-center text-sm text-red-700 bg-red-50 rounded px-3 py-2">
                                   <i class="fas fa-exclamation-triangle mr-2"></i>
                                   Warning: Insufficient stock. You have only ${item.available_stock_here} units available.
                               </div>`
                        }
                    </div>
                `;
            });
            
            // Build detail view HTML
            panel.innerHTML = `
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Transfer Request Details</h2>
                        <p class="text-sm text-gray-600 mono">${transfer.transfer_number}</p>
                    </div>
                    <button onclick="closeDetailView()" class="h-10 w-10 rounded-full hover:bg-gray-100 flex items-center justify-center transition">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>
                
                <div class="p-6">
                    <!-- Transfer Info -->
                    <div class="mb-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4">
                                <div class="text-xs text-blue-600 font-medium mb-1">Source RDC</div>
                                <div class="text-lg font-bold text-blue-900">${transfer.source_rdc_name}</div>
                            </div>
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4">
                                <div class="text-xs text-purple-600 font-medium mb-1">Destination</div>
                                <div class="text-lg font-bold text-purple-900">${transfer.destination_rdc_name}</div>
                            </div>
                            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4">
                                <div class="text-xs text-orange-600 font-medium mb-1">Requested Date</div>
                                <div class="text-sm font-bold text-orange-900">${new Date(transfer.requested_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</div>
                                <div class="text-xs text-orange-700">${new Date(transfer.requested_date).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</div>
                            </div>
                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4">
                                <div class="text-xs text-green-600 font-medium mb-1">Total Items</div>
                                <div class="text-lg font-bold text-green-900">${transfer.total_items} units</div>
                                <div class="text-xs text-green-700">${transfer.product_count} products</div>
                            </div>
                        </div>      
                        
                              <!-- ============================================
                    ✨ INSERT THIS ENTIRE SECTION
                    ============================================ -->
                <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-purple-50 border-2 border-blue-200 rounded-xl">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900 mb-1 flex items-center">
                                <i class="fas fa-shipping-fast mr-2 text-blue-600"></i>
                                Track Transfer Progress
                            </div>
                            <div class="text-xs text-gray-600">
                                View detailed timeline and status history for this transfer request
                            </div>
                        </div>
                        <button onclick="event.stopPropagation(); openTrackingModal(${JSON.stringify(transfer).replace(/"/g, '&quot;')})" 
                                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl flex items-center space-x-2 whitespace-nowrap">
                            <i class="fas fa-route"></i>
                            <span>Track Transfer</span>
                        </button>
                    </div>
                </div>
                <!-- ============================================
                    ✨ END OF TRACKING SECTION
                    ============================================ -->
         
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user text-yellow-600 text-lg"></i>
                                </div>
                                <div class="ml-3 flex-1">
                                    <div class="text-xs text-yellow-700 font-medium mb-1">Requested By</div>
                                    <div class="text-sm font-semibold text-gray-900">${transfer.requested_by}</div>
                                </div>
                                ${transfer.is_urgent ? `
                                    <span class="badge-urgent px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full flex items-center">
                                        <i class="fas fa-bolt mr-1"></i> URGENT
                                    </span>
                                ` : ''}
                            </div>
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="text-xs text-blue-700 font-medium mb-2 flex items-center">
                                <i class="fas fa-comment-alt mr-2"></i>
                                Request Reason
                            </div>
                            <div class="text-sm text-gray-700">${transfer.request_reason}</div>
                        </div>
                    </div>
                    
                    <!-- Products List -->
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-boxes mr-2 text-blue-600"></i>
                            Requested Products & Stock Comparison
                        </h3>
                        <div class="space-y-4">
                            ${productsHtml}
                        </div>
                    </div>
                    
                    <!-- Approval Section -->
                    <div id="approval-section-${transfer.transfer_id}" class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-clipboard-check mr-2 text-purple-600"></i>
                            Approval Decision
                        </h3>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Approval Remarks <span class="text-red-500">*</span>
                            </label>
                            <textarea id="remarks-${transfer.transfer_id}" 
                                      rows="3" 
                                      placeholder="Add your comments or reasons for approval/rejection..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Select Status</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button onclick="selectStatus(${transfer.transfer_id}, 'APPROVED')" 
                                        class="status-btn status-btn-approve px-4 py-3 border-2 border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition flex items-center justify-center">
                                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                    <span class="font-semibold text-gray-700">Approve</span>
                                </button>
                                <button onclick="selectStatus(${transfer.transfer_id}, 'REJECTED')" 
                                        class="status-btn status-btn-reject px-4 py-3 border-2 border-gray-300 rounded-lg hover:border-red-500 hover:bg-red-50 transition flex items-center justify-center">
                                    <i class="fas fa-times-circle text-red-600 mr-2"></i>
                                    <span class="font-semibold text-gray-700">Reject</span>
                                </button>
                            </div>
                        </div>
                        
                        <div id="submit-section-${transfer.transfer_id}" class="hidden">
                            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-gray-600">Selected Status:</div>
                                        <div id="selected-status-${transfer.transfer_id}" class="text-lg font-bold"></div>
                                    </div>
                                    <button onclick="clearStatus(${transfer.transfer_id})" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                        Change
                                    </button>
                                </div>
                            </div>
                            
                            <button onclick="submitApproval(${transfer.transfer_id})" 
                                    class="w-full px-6 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
                                <i class="fas fa-paper-plane"></i>
                                <span>Submit Decision</span>
                            </button>
                        </div>
                    </div>
                    
                </div>
            `;
            
            // Show overlay and panel
            overlay.classList.add('active');
            setTimeout(() => {
                panel.classList.add('active');
            }, 10);
        }
        
        // Close detail view
        function closeDetailView() {
            const overlay = document.getElementById('detail-overlay');
            const panel = document.getElementById('detail-panel');
            
            panel.classList.remove('active');
            setTimeout(() => {
                overlay.classList.remove('active');
            }, 300);
        }
        
        // Track selected status per transfer
        let selectedStatuses = {};
        
        // Select status
        function selectStatus(transferId, status) {
            selectedStatuses[transferId] = status;
            
            // Update UI
            const submitSection = document.getElementById(`submit-section-${transferId}`);
            const selectedStatusDisplay = document.getElementById(`selected-status-${transferId}`);
            
            // Highlight selected button
            const approvalSection = document.getElementById(`approval-section-${transferId}`);
            approvalSection.querySelectorAll('.status-btn').forEach(btn => {
                btn.classList.remove('border-green-500', 'bg-green-50', 'border-red-500', 'bg-red-50', 'border-gray-500', 'bg-gray-100');
                btn.classList.add('border-gray-300');
            });
            
            const statusColors = {
                'APPROVED': { border: 'border-green-500', bg: 'bg-green-50', text: 'text-green-700' },
                'REJECTED': { border: 'border-red-500', bg: 'bg-red-50', text: 'text-red-700' },
                'CANCELLED': { border: 'border-gray-500', bg: 'bg-gray-100', text: 'text-gray-700' }
            };
            
            const color = statusColors[status];
            event.target.closest('.status-btn').classList.remove('border-gray-300');
            event.target.closest('.status-btn').classList.add(color.border, color.bg);
            
            // Show submit section
            submitSection.classList.remove('hidden');
            selectedStatusDisplay.textContent = status;
            selectedStatusDisplay.className = `text-lg font-bold ${color.text}`;
        }
        
        // Clear status selection
        function clearStatus(transferId) {
            delete selectedStatuses[transferId];
            
            const submitSection = document.getElementById(`submit-section-${transferId}`);
            submitSection.classList.add('hidden');
            
            // Reset button styles
            const approvalSection = document.getElementById(`approval-section-${transferId}`);
            approvalSection.querySelectorAll('.status-btn').forEach(btn => {
                btn.classList.remove('border-green-500', 'bg-green-50', 'border-red-500', 'bg-red-50', 'border-gray-500', 'bg-gray-100');
                btn.classList.add('border-gray-300');
            });
        }
        
        // Submit approval decision
        async function submitApproval(transferId) {
            const remarksEl = document.getElementById(`remarks-${transferId}`);
            const remarks = remarksEl ? remarksEl.value.trim() : '';
            const status = selectedStatuses[transferId];

            // Inline alert area (create if not exists)
            let alertEl = document.getElementById(`approval-alert-${transferId}`);
            if (!alertEl) {
                alertEl = document.createElement('div');
                alertEl.id = `approval-alert-${transferId}`;
                alertEl.className = 'form-alert error mb-4 hidden';
                alertEl.innerHTML = `<span id="approval-alert-text-${transferId}"></span>`;
                const approvalSection = document.getElementById(`approval-section-${transferId}`);
                approvalSection.insertBefore(alertEl, approvalSection.firstChild);
            }

            const showAlert = (msg) => {
                alertEl.classList.remove('hidden');
                alertEl.classList.add('show');
                alertEl.classList.remove('success');
                alertEl.classList.add('error');
                document.getElementById(`approval-alert-text-${transferId}`).textContent = msg;
                setTimeout(() => { alertEl.classList.remove('show'); alertEl.classList.add('hidden'); }, 4000);
            };

            if (!remarks) { showAlert('Please add approval remarks before submitting!'); return; }
            if (!status) { showAlert('Please select a status!'); return; }

            // Build form data
            const form = new FormData();
            form.append('action', 'submit_approval');
            form.append('transfer_id', transferId);
            form.append('new_status', status);
            form.append('remarks', remarks);

            try {
                const resp = await fetch('/index.php?page=send-product-units', {
                    method: 'POST',
                    body: form,
                    credentials: 'same-origin'
                });
                const data = await resp.json();
                if (!resp.ok || !data.success) {
                    showAlert(data.message || 'Failed to submit decision.');
                    return;
                }

                // Success: replace approval section with confirmation (reuse previous UI)
                const approvalSection = document.getElementById(`approval-section-${transferId}`);
                const statusColors = {
                    'APPROVED': { bg: 'bg-green-50', border: 'border-green-500', text: 'text-green-700', icon: 'fa-check-circle' },
                    'REJECTED': { bg: 'bg-red-50', border: 'border-red-500', text: 'text-red-700', icon: 'fa-times-circle' }
                };
                const color = statusColors[status] || { bg: 'bg-gray-50', border: 'border-gray-300', text: 'text-gray-700', icon: 'fa-info-circle' };

                approvalSection.innerHTML = `
                    <div class="text-center py-8">
                        <div class="h-16 w-16 ${color.bg} rounded-full flex items-center justify-center mx-auto mb-4 checkmark">
                            <i class="fas ${color.icon} ${color.text} text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold ${color.text} mb-2">Decision Submitted!</h3>
                        <p class="text-gray-600 mb-4">Transfer status has been updated to <strong>${status}</strong></p>
                        <div class="${color.bg} ${color.border} border rounded-lg p-4 mb-4">
                            <div class="text-sm font-medium ${color.text} mb-2">Your Remarks:</div>
                            <div class="text-sm text-gray-700">${remarks}</div>
                        </div>
                        <button onclick="closeDetailView()" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                            Close
                        </button>
                    </div>
                `;

                // Optionally update the Pending/History tab counts and move this transfer to history in the UI
                // (a full refresh would be more robust; left minimal here)

            } catch (err) {
                showAlert('Error submitting decision: ' + (err.message || err));
            }
        }
    </script>

