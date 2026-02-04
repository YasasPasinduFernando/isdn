<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen py-8">
    <div class="container mx-auto px-4">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 font-['Outfit']">My Orders</h1>
                <p class="text-gray-600 mt-1">Manage and track your recent purchases</p>
            </div>
            <div class="flex gap-2">
                <button class="bg-white/60 backdrop-blur-sm border border-white/60 px-4 py-2 rounded-xl shadow-sm text-teal-700 font-semibold hover:bg-white transition flex items-center">
                    <span class="material-symbols-rounded mr-2 text-lg">filter_alt</span>Filter
                </button>
                <button class="bg-teal-600 px-4 py-2 rounded-xl shadow-lg shadow-teal-500/30 text-white font-semibold hover:bg-teal-700 transition flex items-center">
                    <span class="material-symbols-rounded mr-2 text-lg">download</span>Report
                </button>
            </div>
        </div>

        <!-- Order List Container -->
        <div class="glass-panel rounded-3xl overflow-hidden shadow-xl border border-white/50">
            
            <!-- Desktop Table Header -->
            <div class="hidden md:grid grid-cols-12 gap-4 bg-white/30 backdrop-blur-sm p-5 border-b border-gray-100 text-sm font-bold text-gray-600 uppercase tracking-wider">
                <div class="col-span-3">Order Details</div>
                <div class="col-span-2">Date</div>
                <div class="col-span-2">Amount</div>
                <div class="col-span-2">Payment</div>
                <div class="col-span-2">Status</div>
                <div class="col-span-1 text-center">Action</div>
            </div>

            <!-- Order Items Mock List -->
            <div class="divide-y divide-gray-100/50">
                
                <!-- Order 1 -->
                <div class="p-5 hover:bg-white/40 transition duration-200 group">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                        <!-- Mobile Label -->
                        <div class="md:hidden text-sm font-bold text-gray-500 mb-1">Order Details</div>
                        
                        <div class="col-span-3 flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 group-hover:scale-110 transition duration-300">
                                <span class="material-symbols-rounded text-xl">inventory_2</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 font-['Outfit']">#ORD-2025-001</h3>
                                <p class="text-xs text-gray-500">15 Items</p>
                            </div>
                        </div>

                        <div class="col-span-2 text-sm text-gray-600 font-medium">
                            <span class="md:hidden font-semibold mr-2">Date:</span>
                            Jan 10, 2025
                        </div>

                        <div class="col-span-2 font-bold text-gray-800">
                            <span class="md:hidden font-semibold mr-2 text-gray-500 font-normal">Amount:</span>
                            Rs. 45,250.00
                        </div>

                        <div class="col-span-2">
                             <span class="md:hidden font-semibold mr-2 text-gray-600">Payment:</span>
                             <span class="text-xs font-bold bg-green-100 text-green-700 px-3 py-1 rounded-full border border-green-200">Paid</span>
                        </div>

                        <div class="col-span-2">
                            <span class="md:hidden font-semibold mr-2 text-gray-600">Status:</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                                <span class="w-2 h-2 mr-2 bg-green-500 rounded-full"></span>
                                Delivered
                            </span>
                        </div>

                        <div class="col-span-1 flex md:justify-center">
                            <a href="index.php?page=tracking&order_id=ORD-2025-001" 
                               class="text-teal-600 hover:text-teal-800 hover:bg-teal-50 p-2 rounded-full transition relative group/icon" 
                               title="Track Order">
                                <span class="material-symbols-rounded">location_on</span>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition ml-2" title="View Details">
                                <span class="material-symbols-rounded">visibility</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Order 2 -->
                <div class="p-5 hover:bg-white/40 transition duration-200 group">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                        <div class="col-span-3 flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-100 group-hover:scale-110 transition duration-300">
                                <span class="material-symbols-rounded text-xl">checkroom</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 font-['Outfit']">#ORD-2025-002</h3>
                                <p class="text-xs text-gray-500">8 Items</p>
                            </div>
                        </div>

                        <div class="col-span-2 text-sm text-gray-600 font-medium">
                            <span class="md:hidden font-semibold mr-2">Date:</span> Jan 12, 2025
                        </div>

                        <div class="col-span-2 font-bold text-gray-800">
                            <span class="md:hidden font-semibold mr-2 text-gray-500 font-normal">Amount:</span> Rs. 28,900.00
                        </div>

                        <div class="col-span-2">
                            <span class="text-xs font-bold bg-gray-100 text-gray-600 px-3 py-1 rounded-full border border-gray-200">COD</span>
                        </div>

                        <div class="col-span-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">
                                <span class="w-2 h-2 mr-2 bg-blue-500 rounded-full animate-pulse"></span>
                                In Transit
                            </span>
                        </div>

                        <div class="col-span-1 flex md:justify-center">
                            <a href="index.php?page=tracking&order_id=ORD-2025-002" 
                               class="text-teal-600 hover:text-teal-800 hover:bg-teal-50 p-2 rounded-full transition"
                               title="Track Order">
                                <span class="material-symbols-rounded">location_on</span>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition ml-2">
                                <span class="material-symbols-rounded">visibility</span>
                            </a>
                        </div>
                    </div>
                </div>

                 <!-- Order 3 -->
                <div class="p-5 hover:bg-white/40 transition duration-200 group">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                        <div class="col-span-3 flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center border border-yellow-100 group-hover:scale-110 transition duration-300">
                                <span class="material-symbols-rounded text-xl">smartphone</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 font-['Outfit']">#ORD-2025-003</h3>
                                <p class="text-xs text-gray-500">1 Item</p>
                            </div>
                        </div>

                        <div class="col-span-2 text-sm text-gray-600 font-medium">
                            <span class="md:hidden font-semibold mr-2">Date:</span> Jan 14, 2025
                        </div>

                        <div class="col-span-2 font-bold text-gray-800">
                            <span class="md:hidden font-semibold mr-2 text-gray-500 font-normal">Amount:</span> Rs. 125,000.00
                        </div>

                        <div class="col-span-2">
                            <span class="text-xs font-bold bg-green-100 text-green-700 px-3 py-1 rounded-full border border-green-200">Paid</span>
                        </div>

                        <div class="col-span-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                <span class="w-2 h-2 mr-2 bg-yellow-500 rounded-full"></span>
                                Processing
                            </span>
                        </div>

                        <div class="col-span-1 flex md:justify-center">
                            <a href="index.php?page=tracking&order_id=ORD-2025-003" 
                               class="text-teal-600 hover:text-teal-800 hover:bg-teal-50 p-2 rounded-full transition"
                               title="Track Order">
                                <span class="material-symbols-rounded">location_on</span>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition ml-2">
                                <span class="material-symbols-rounded">visibility</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Pagination -->
            <div class="bg-white/30 backdrop-blur-sm px-6 py-4 border-t border-gray-100/50 flex items-center justify-between">
                <span class="text-sm text-gray-600 font-medium">Showing 1-3 of 12 orders</span>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 bg-white/50 border border-white/60 rounded-lg hover:bg-white text-gray-600 disabled:opacity-50 transition" disabled>Previous</button>
                    <button class="px-3 py-1 bg-teal-600 text-white rounded-lg shadow-md shadow-teal-500/20">1</button>
                    <button class="px-3 py-1 bg-white/50 border border-white/60 rounded-lg hover:bg-white text-gray-600 transition">2</button>
                    <button class="px-3 py-1 bg-white/50 border border-white/60 rounded-lg hover:bg-white text-gray-600 transition">3</button>
                    <button class="px-3 py-1 bg-white/50 border border-white/60 rounded-lg hover:bg-white text-gray-600 transition">Next</button>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
