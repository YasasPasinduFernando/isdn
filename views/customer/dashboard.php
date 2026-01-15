<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="bg-gradient-to-br from-purple-50 to-blue-50 min-h-screen py-6 sm:py-8">
    <div class="container mx-auto px-4">
        
        <!-- Welcome Header -->
        <div class="bg-white rounded-2xl shadow-xl p-5 sm:p-8 mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-4xl font-bold text-gray-800">
                        Welcome Back, <span class="text-purple-600"><?php echo $_SESSION['username']; ?>!</span> 👋
                    </h1>
                    <p class="text-gray-600 mt-2">Here's what's happening with your orders today</p>
                </div>
                <div class="hidden md:block">
                    <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-4 rounded-full">
                        <i class="fas fa-chart-line text-white text-3xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php display_flash(); ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Orders Card -->
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 border-l-4 border-blue-500 hover:shadow-2xl transition transform hover:-translate-y-1">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold uppercase">Total Orders</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2">124</h3>
                        <p class="text-green-600 text-sm mt-2">
                            <i class="fas fa-arrow-up mr-1"></i>12% from last month
                        </p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <i class="fas fa-shopping-cart text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Orders Card -->
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 border-l-4 border-yellow-500 hover:shadow-2xl transition transform hover:-translate-y-1">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold uppercase">Pending</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2">8</h3>
                        <p class="text-gray-500 text-sm mt-2">
                            <i class="fas fa-clock mr-1"></i>Awaiting processing
                        </p>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded-full">
                        <i class="fas fa-hourglass-half text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- In Transit Card -->
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 border-l-4 border-purple-500 hover:shadow-2xl transition transform hover:-translate-y-1">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold uppercase">In Transit</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2">15</h3>
                        <p class="text-purple-600 text-sm mt-2">
                            <i class="fas fa-truck mr-1"></i>On the way
                        </p>
                    </div>
                    <div class="bg-purple-100 p-4 rounded-full">
                        <i class="fas fa-shipping-fast text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Delivered Card -->
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 border-l-4 border-green-500 hover:shadow-2xl transition transform hover:-translate-y-1">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold uppercase">Delivered</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2">101</h3>
                        <p class="text-green-600 text-sm mt-2">
                            <i class="fas fa-check-circle mr-1"></i>Successfully delivered
                        </p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full">
                        <i class="fas fa-check-double text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Recent Orders Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Quick Actions -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-xl p-5 sm:p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-bolt text-yellow-500 mr-3"></i>Quick Actions
                    </h2>
                    
                    <div class="space-y-4">
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=products" 
                           class="block bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 rounded-xl hover:from-blue-600 hover:to-blue-700 transition shadow-lg transform hover:scale-105">
                            <div class="flex items-center space-x-3">
                                <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                                    <i class="fas fa-shopping-bag text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg">Browse Products</h3>
                                    <p class="text-sm opacity-90">View our catalog</p>
                                </div>
                            </div>
                        </a>

                        <a href="<?php echo BASE_PATH; ?>/index.php?page=orders" 
                           class="block bg-gradient-to-r from-purple-500 to-purple-600 text-white p-4 rounded-xl hover:from-purple-600 hover:to-purple-700 transition shadow-lg transform hover:scale-105">
                            <div class="flex items-center space-x-3">
                                <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                                    <i class="fas fa-clipboard-list text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg">My Orders</h3>
                                    <p class="text-sm opacity-90">Track your orders</p>
                                </div>
                            </div>
                        </a>

                        <a href="<?php echo BASE_PATH; ?>/index.php?page=tracking" 
                           class="block bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-xl hover:from-green-600 hover:to-green-700 transition shadow-lg transform hover:scale-105">
                            <div class="flex items-center space-x-3">
                                <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                                    <i class="fas fa-map-marker-alt text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg">Track Delivery</h3>
                                    <p class="text-sm opacity-90">Real-time tracking</p>
                                </div>
                            </div>
                        </a>

                        <a href="<?php echo BASE_PATH; ?>/index.php?page=cart" 
                           class="block bg-gradient-to-r from-orange-500 to-orange-600 text-white p-4 rounded-xl hover:from-orange-600 hover:to-orange-700 transition shadow-lg transform hover:scale-105">
                            <div class="flex items-center space-x-3">
                                <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                                    <i class="fas fa-shopping-cart text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg">View Cart</h3>
                                    <p class="text-sm opacity-90">5 items in cart</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-history text-blue-600 mr-3"></i>Recent Orders
                        </h2>
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=orders" class="text-blue-600 hover:text-blue-700 font-semibold">
                            View All <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    <div class="space-y-4">
                        <!-- Order Item 1 -->
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-lg transition">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex items-center space-x-4">
                                    <div class="bg-blue-100 p-3 rounded-lg">
                                        <i class="fas fa-box text-blue-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800">Order #ORD-2025-001</h3>
                                        <p class="text-sm text-gray-600">15 items • Rs. 45,250.00</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="far fa-calendar mr-1"></i>Jan 10, 2025
                                        </p>
                                    </div>
                                </div>
                                <span class="bg-green-100 text-green-700 px-4 py-2 rounded-full text-sm font-semibold">
                                    <i class="fas fa-check-circle mr-1"></i>Delivered
                                </span>
                            </div>
                        </div>

                        <!-- Order Item 2 -->
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-lg transition">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex items-center space-x-4">
                                    <div class="bg-purple-100 p-3 rounded-lg">
                                        <i class="fas fa-box text-purple-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800">Order #ORD-2025-002</h3>
                                        <p class="text-sm text-gray-600">8 items • Rs. 28,900.00</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="far fa-calendar mr-1"></i>Jan 12, 2025
                                        </p>
                                    </div>
                                </div>
                                <span class="bg-purple-100 text-purple-700 px-4 py-2 rounded-full text-sm font-semibold">
                                    <i class="fas fa-truck mr-1"></i>In Transit
                                </span>
                            </div>
                        </div>

                        <!-- Order Item 3 -->
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-lg transition">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex items-center space-x-4">
                                    <div class="bg-yellow-100 p-3 rounded-lg">
                                        <i class="fas fa-box text-yellow-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800">Order #ORD-2025-003</h3>
                                        <p class="text-sm text-gray-600">22 items • Rs. 67,400.00</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="far fa-calendar mr-1"></i>Jan 13, 2025
                                        </p>
                                    </div>
                                </div>
                                <span class="bg-yellow-100 text-yellow-700 px-4 py-2 rounded-full text-sm font-semibold">
                                    <i class="fas fa-clock mr-1"></i>Processing
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
