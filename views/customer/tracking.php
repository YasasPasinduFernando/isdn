<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="bg-gradient-to-br from-purple-50 to-blue-50 min-h-screen py-8">
    <div class="container mx-auto px-4 max-w-4xl">
        
        <!-- text header -->
        <div class="text-center mb-10">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Track Your Delivery 🚚</h1>
            <p class="text-gray-600">Enter your order ID to see the current status of your package</p>
        </div>

        <!-- Search Box -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-10 transform hover:scale-[1.01] transition duration-300">
            <form action="" method="GET" class="flex flex-col md:flex-row gap-4">
                <input type="hidden" name="page" value="tracking">
                <div class="flex-grow relative">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           name="order_id" 
                           value="<?php echo isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : ''; ?>"
                           placeholder="Enter Order ID (e.g., ORD-2025-001)" 
                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition bg-gray-50 hover:bg-white"
                           required>
                </div>
                <button type="submit" 
                        class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-8 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition duration-300 flex items-center justify-center space-x-2">
                    <span>Track Order</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>

        <?php if (isset($_GET['order_id'])): ?>
            <!-- Mock Result Section -->
            <div class="space-y-6 animate-fade-in-up">
                
                <!-- Order Status Card -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-6 text-white">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <p class="text-sm opacity-80 mb-1">Order ID</p>
                                <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($_GET['order_id']); ?></h2>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-lg px-4 py-2 backdrop-blur-sm">
                                <p class="text-sm font-semibold">
                                    <i class="fas fa-clock mr-2"></i>Estimated Delivery: <span class="text-yellow-300">Feb 15, 2025</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 md:p-8">
                        <!-- Progress Bar -->
                        <div class="relative mb-12">
                            <div class="absolute top-1/2 left-0 w-full h-1 bg-gray-200 -translate-y-1/2 rounded-full z-0"></div>
                            <div class="absolute top-1/2 left-0 w-3/4 h-1 bg-gradient-to-r from-purple-500 to-blue-500 -translate-y-1/2 rounded-full z-0"></div>
                            
                            <div class="relative z-10 flex justify-between">
                                <!-- Step 1: Placed -->
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center shadow-lg border-4 border-white">
                                        <i class="fas fa-check text-white text-sm"></i>
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-gray-800">Order Placed</p>
                                    <p class="text-xs text-gray-500">Jan 10, 10:30 AM</p>
                                </div>

                                <!-- Step 2: Processing -->
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center shadow-lg border-4 border-white">
                                        <i class="fas fa-check text-white text-sm"></i>
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-gray-800">Processing</p>
                                    <p class="text-xs text-gray-500">Jan 11, 02:15 PM</p>
                                </div>

                                <!-- Step 3: Shipped -->
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center shadow-lg border-4 border-white animate-pulse">
                                        <i class="fas fa-truck text-white text-sm"></i>
                                    </div>
                                    <p class="mt-3 text-sm font-bold text-blue-600">In Transit</p>
                                    <p class="text-xs text-gray-500">Jan 12, 09:00 AM</p>
                                </div>

                                <!-- Step 4: Delivered -->
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center shadow border-4 border-white">
                                        <i class="fas fa-box text-gray-400 text-sm"></i>
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-gray-400">Delivered</p>
                                    <p class="text-xs text-gray-400">Pending</p>
                                </div>
                            </div>
                        </div>

                        <!-- Current Location Details -->
                        <div class="bg-blue-50 rounded-xl p-6 border border-blue-100">
                            <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-map-marker-alt text-red-500 mr-2"></i> Latest Update
                            </h3>
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-2 h-full bg-blue-200 rounded-full mx-auto relative">
                                        <div class="w-4 h-4 bg-blue-500 rounded-full absolute top-0 -left-1 ring-4 ring-blue-100"></div>
                                    </div>
                                </div>
                                <div class="pb-6">
                                    <p class="font-semibold text-gray-800">Arrived at Distribution Center</p>
                                    <p class="text-sm text-gray-600 mt-1">Colombo Central Hub</p>
                                    <p class="text-xs text-gray-500 mt-1">Today at 09:42 AM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Details Summary -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-4">Shipment Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Recipient</p>
                            <p class="font-medium text-gray-800">John Doe</p>
                            <p class="text-sm text-gray-600">123, Main Street, Colombo 03</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Carrier Info</p>
                            <p class="font-medium text-gray-800">IslandLink Express</p>
                            <p class="text-sm text-gray-600">Standard Delivery</p>
                        </div>
                    </div>
                </div>

            </div>
        <?php elseif(isset($_GET['page']) && $_GET['page'] === 'tracking'): ?>
            <!-- Fallback text when no ID entered but page loaded -->
            <!-- (Actually the form above covers this state, but we can add a helper image or text here) -->
            <div class="flex flex-col items-center justify-center py-10 opacity-60">
                <i class="fas fa-map-marked-alt text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">Enter your order ID above to start tracking</p>
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
    animation: fadeInUp 0.5s ease-out forwards;
}
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
