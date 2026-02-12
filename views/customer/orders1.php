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
                <button
                    class="bg-white/60 backdrop-blur-sm border border-white/60 px-4 py-2 rounded-xl shadow-sm text-teal-700 font-semibold hover:bg-white transition flex items-center">
                    <span class="material-symbols-rounded mr-2 text-lg">filter_alt</span>Filter
                </button>
                <button
                    class="bg-teal-600 px-4 py-2 rounded-xl shadow-lg shadow-teal-500/30 text-white font-semibold hover:bg-teal-700 transition flex items-center">
                    <span class="material-symbols-rounded mr-2 text-lg">download</span>Report
                </button>
            </div>
        </div>

        <div
            class="hidden md:grid grid-cols-8 gap-4 bg-white/30 backdrop-blur-sm p-5 border-b border-gray-100 text-sm font-bold text-gray-600 uppercase tracking-wider">
            <div>Code</div>
            <div>Customer</div>
            <div>Date</div>
            <div>Amount</div>
            <div>Payment</div>
            <div>Status</div>
            <div>Estimated Delivery</div>
            <div class="text-center">Action</div>
        </div>
        <!-- Order Items Mock List -->
        <div class="divide-y divide-gray-100/50">


            <div class="p-5 hover:bg-white/40 transition">
                <div class="grid grid-cols-1 md:grid-cols-8 gap-4 items-center">

                    <?php foreach ($userOrders as $userOrder): ?>
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                                <span class="material-symbols-rounded">inventory_2</span>
                            </div>
                            <div>
                                <div class="font-bold text-gray-800">#<?php echo $userOrder['order_number'] ?></div>
                                <div class="text-xs text-gray-500">15 Items</div>
                            </div>
                        </div>

                        <!-- Customer -->
                        <div class="font-medium text-gray-800">
                            <?php echo $userOrder['customer_id'] ?>
                        </div>

                        <!-- Date -->
                        <div class="text-sm text-gray-600">
                            <?php
                            $orderDate = date('Y-m-d', strtotime($userOrder['order_date']));
                            echo $orderDate;
                            ?>
                        </div>

                        <!-- Amount -->
                        <div class="font-bold text-gray-800">
                            <?php echo $userOrder['total_amount'] ?>
                        </div>

                        <!-- Payment -->
                        <div>
                            <span
                                class="text-xs font-bold bg-green-100 text-green-700 px-3 py-1 rounded-full border border-green-200">
                                Paid
                            </span>
                        </div>

                        <!-- Status -->
                        <div>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                                <span class="w-2 h-2 mr-2 bg-green-500 rounded-full"></span>
                                Delivered
                            </span>
                        </div>

                        <!-- Estimated Delivery -->
                        <div class="text-sm text-gray-600">
                            <?php
                            $estimatedDate = date('Y-m-d', strtotime($userOrder['estimated_date']));
                            echo $estimatedDate;
                            ?>
                        </div>

                        <!-- Action -->
                        <div class="flex justify-center gap-2">
                            <a href="index.php?page=tracking&order_id=ORD-2025-001"
                                class="text-teal-600 hover:text-teal-800 hover:bg-teal-50 p-2 rounded-full transition"
                                title="Track Order">
                                <span class="material-symbols-rounded">location_on</span>
                            </a>
                            <a href="#"
                                class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition"
                                title="View Details">
                                <span class="material-symbols-rounded">visibility</span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div
            class="bg-white/30 backdrop-blur-sm px-6 py-4 border-t border-gray-100/50 flex items-center justify-between">
            <span class="text-sm text-gray-600 font-medium">Showing 1-3 of 12 orders</span>
            <div class="flex space-x-2">
                <button
                    class="px-3 py-1 bg-white/50 border border-white/60 rounded-lg hover:bg-white text-gray-600 disabled:opacity-50 transition"
                    disabled>Previous</button>
                <button class="px-3 py-1 bg-teal-600 text-white rounded-lg shadow-md shadow-teal-500/20">1</button>
                <button
                    class="px-3 py-1 bg-white/50 border border-white/60 rounded-lg hover:bg-white text-gray-600 transition">2</button>
                <button
                    class="px-3 py-1 bg-white/50 border border-white/60 rounded-lg hover:bg-white text-gray-600 transition">3</button>
                <button
                    class="px-3 py-1 bg-white/50 border border-white/60 rounded-lg hover:bg-white text-gray-600 transition">Next</button>
            </div>
        </div>

    </div>
</div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>