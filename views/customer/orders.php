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
                <div class="flex-1">
                    <input type="text" name="product_name" required placeholder="Search Order Code.."
                        class="w-full md:w-96 border border-gray-200 rounded-lg px-3 py-3 text-sm text-gray-800 font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
                </div>
                <button id="openFilter" class="bg-white/60 backdrop-blur-sm border border-white/60 px-4 py-2 rounded-xl shadow-sm
         text-teal-700 font-semibold hover:bg-white transition flex items-center">
                    <span class="material-symbols-rounded mr-2 text-lg">filter_alt</span>
                    Filter
                </button>
                <button
                    class="bg-teal-600 px-4 py-2 rounded-xl shadow-lg shadow-teal-500/30 text-white font-semibold hover:bg-teal-700 transition flex items-center">
                    <span class="material-symbols-rounded mr-2 text-lg">download</span>Report
                </button>
            </div>
        </div>

        <!-- Order List Container -->
        <div class="glass-panel rounded-3xl overflow-hidden shadow-xl border border-white/50">

            <!-- Desktop Table Header -->
            <div
                class="hidden md:grid grid-cols-9 gap-5 bg-white/30 backdrop-blur-sm p-5 border-b border-gray-100 text-sm font-bold text-gray-600 uppercase tracking-wider">
                <div class="col-span-2">Code</div>
                <div class="col-span-1">Customer</div>
                <div class="col-span-1">Date</div>
                <div class="col-span-1">Amount</div>
                <div class="col-span-1">Payment</div>
                <div class="col-span-1">Status</div>
                <div class="col-span-1">Estimated Delivery</div>
                <div class="col-span-1 text-center">Action</div>
            </div>
            <!-- Order Items Mock List -->
            <div class="p-5 hover:bg-white/40 transition duration-200 group">
                <div class="grid grid-cols-1 md:grid-cols-9 gap-5 items-center">
                    <!-- Mobile Label -->
                    <?php foreach ($userOrders as $userOrder): ?>

                        <div class="md:hidden text-sm font-bold text-gray-500 mb-1">Order Details</div>

                        <div class="col-span-2 flex items-center space-x-4">
                            <div>
                                <h3 class="font-bold text-gray-800 font-['Outfit']">
                                    #<?php echo $userOrder['order_number'] ?></h3>
                                <p class="text-xs text-gray-500"> <?php echo $userOrder['item_count'] ?> Items</p>
                            </div>
                        </div>

                        <div class="col-span-1 font-bold text-gray-800">
                            <span class="md:hidden font-semibold mr-2">Date:</span>
                            <?php echo $userOrder['customer'] ?>
                        </div>

                        <div class="col-span-1 text-sm text-gray-600 font-medium">
                            <span class="md:hidden font-semibold mr-2 text-gray-500 font-normal">Amount:</span>
                            <?php echo $userOrder['order_date'] ?>
                        </div>
                        <div class="col-span-1 font-bold text-gray-800">
                            <span class="md:hidden font-semibold mr-2 text-gray-500 font-normal">Amount:</span>
                            Rs. <?php echo number_format($userOrder['total_amount'], 2) ?>
                        </div>
                        <?php
                        $orderStatus = strtolower($userOrder['status'] ?? '');
                        $paymentStatus = strtolower($userOrder['payment_status'] ?? '');

                        $statusStyles = [
                            'pending' => [
                                'container' => 'bg-purple-100 text-purple-700 border-purple-200',
                                'dot' => 'bg-purple-500'
                            ],
                            'processing' => [
                                'container' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'dot' => 'bg-blue-500'
                            ],
                            'delivered' => [
                                'container' => 'bg-green-100 text-green-700 border-green-200',
                                'dot' => 'bg-green-500'
                            ],
                            'in transit' => [
                                'container' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                'dot' => 'bg-yellow-500'
                            ],
                            'cancelled' => [
                                'container' => 'bg-red-100 text-red-700 border-red-200',
                                'dot' => 'bg-red-500'
                            ],
                            'paid' => [
                                'container' => 'bg-green-100 text-green-700 border-green-200',
                                'dot' => 'bg-green-500'
                            ],
                            'unpaid' => [
                                'container' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                'dot' => 'bg-yellow-500'
                            ]
                        ];

                        $orderStyle = $statusStyles[$orderStatus] ?? [
                            'container' => 'bg-gray-100 text-gray-700 border-gray-200',
                            'dot' => 'bg-gray-500'
                        ];

                        $paymentStyle = $statusStyles[$paymentStatus] ?? [
                            'container' => 'bg-gray-100 text-gray-700 border-gray-200',
                            'dot' => 'bg-gray-500'
                        ];
                        ?>
                        <div class="col-span-1">
                            <span class="md:hidden font-semibold mr-2 text-gray-600">Payment:</span>
                            <span
                                class="inline-flex items-center gap-2 px-3 py-1 text-xs font-bold rounded-full border <?= $paymentStyle['container']; ?>">
                                <span class="w-2 h-2 <?= $paymentStyle['dot']; ?> rounded-full"></span>
                                <?= ucwords($paymentStatus); ?>
                            </span>
                        </div>


                        <div class="col-span-1">
                            <span class="md:hidden font-semibold mr-2 text-gray-600">Status:</span>

                            <span
                                class="inline-flex items-center gap-2 px-3 py-1 text-xs font-bold rounded-full border <?= $orderStyle['container']; ?>">
                                <span class="w-2 h-2 <?= $orderStyle['dot']; ?> rounded-full"></span>
                                <?= ucwords($orderStatus); ?>
                            </span>
                        </div>

                        <div class="col-span-1 text-sm text-gray-600 font-medium">
                            <span class="md:hidden font-semibold mr-2 text-gray-500 font-normal">Amount:</span>
                            <?php echo $userOrder['estimated_date'] ?>
                        </div>

                        <div class="col-span-1 flex md:justify-center">
                            <a href="index.php?page=tracking&order_id=<?php echo urlencode($userOrder['order_number']); ?>"
                                class="text-teal-600 hover:text-teal-800 hover:bg-teal-50 p-2 rounded-full transition relative group/icon"
                                title="Track Order">
                                <span class="material-symbols-rounded">location_on</span>
                            </a>
                            <a href="index.php?page=order-info&id=<?= $userOrder['order_id']; ?>"
                                class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition ml-2"
                                title="View Details">
                                <span class="material-symbols-rounded">visibility</span>
                            </a>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>


            <!-- Pagination -->
            <div
                class="bg-white/30 backdrop-blur-sm px-6 py-4 border-t border-gray-100/50 flex items-center justify-between">
                <span class="text-sm text-gray-600 font-medium">Showing 1-5 of 12 orders</span>
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

<?php require_once __DIR__ . '/../../components/customer_orders_filter_drawer.php'; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>