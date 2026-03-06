<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../models/Profile.php';
require_once __DIR__ . '/../../models/RetailCustomer.php';
require_once __DIR__ . '/../../models/SalesOrder.php';
$role = current_user_role();
$userId = (int) $_SESSION['user_id'];
$profileModel = new Profile($pdo);
$retailCustomer = new RetailCustomer($pdo);
$profile = $profileModel->getProfile($userId, $role);
$customerId = $retailCustomer->findByUserId($userId);
$customer_ordrs = $retailCustomer->getCustomerOrderStatusCounts($customerId['id']);
//get orders by customers
$orderModel = new SalesOrder($pdo);
$userOrders = $orderModel->getCustomerOrders($customerId['id']);
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        <!-- Welcome Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-10">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 font-['Outfit']">Dashboard</h1>
                <p class="text-gray-500 mt-1">Welcome back, <span
                        class="text-teal-600 font-semibold"><?= $profile['name']; ?></span></p>
            </div>

            <div class="flex items-center space-x-4 mt-4 md:mt-0">
                <div class="flex items-center gap-3">
                    <span
                        class="px-4 py-2 rounded-full bg-white/60 text-sm font-semibold text-gray-600 shadow-sm border border-white/50">
                        <?= date('l, F j, Y') ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- 1. Stats Cards (Top Row) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <!-- Total Orders -->
            <div
                class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-blue-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Orders</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']">
                            <?= (int) ($customer_ordrs['total_orders'] ?? 0); ?>
                        </h3>
                        <p class="text-green-600 text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">trending_up</span> 12% from last month
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-blue-100/50 flex items-center justify-center text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">shopping_cart</span>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div
                class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-yellow-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pending</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']">
                            <?= (int) ($customer_ordrs['pending_count'] ?? 0); ?>
                        </h3>
                        <p class="text-gray-500 text-xs font-medium mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">hourglass_top</span> Awaiting processing
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-yellow-100/50 flex items-center justify-center text-yellow-600 group-hover:bg-yellow-400 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">pending</span>
                    </div>
                </div>
            </div>

            <!-- In Transit -->
            <div
                class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-purple-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">In Transit</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']">
                            <?= (int) ($customer_ordrs['in_transit_count'] ?? 0); ?>
                        </h3>
                        <p class="text-purple-600 text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">local_shipping</span> On the way
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-purple-100/50 flex items-center justify-center text-purple-600 group-hover:bg-purple-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">local_shipping</span>
                    </div>
                </div>
            </div>

            <!-- Delivered -->
            <div
                class="glass-card p-6 rounded-3xl relative overflow-hidden group hover-lift border-l-4 border-green-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Delivered</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2 font-['Outfit']">
                            <?= (int) ($customer_ordrs['delivered_count'] ?? 0) ?>
                        </h3>
                        <p class="text-green-600 text-xs font-semibold mt-2 flex items-center">
                            <span class="material-symbols-rounded text-sm mr-1">check_circle</span> Successfully
                            delivered
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-green-100/50 flex items-center justify-center text-green-600 group-hover:bg-green-500 group-hover:text-white transition-colors duration-300 backdrop-blur-sm">
                        <span class="material-symbols-rounded">done_all</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Main content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left Column: Recent Orders List -->
            <div class="lg:col-span-2">
                <div class="glass-panel rounded-3xl p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center space-x-3">
                            <span class="material-symbols-rounded text-gray-500 text-2xl">history</span>
                            <h2 class="text-xl font-bold text-gray-800 font-['Outfit']">Recent Orders</h2>
                        </div>
                        <a href="index.php?page=customer-sales-orders"
                            class="text-sm font-semibold text-teal-600 hover:text-teal-700 flex items-center transition">
                            View All <span class="material-symbols-rounded text-sm ml-1">arrow_forward</span>
                        </a>
                    </div>

                    <div class="space-y-4">
                        <!-- Order Item 1 -->
                        <?php
                        $counter = 0;
                        foreach ($userOrders as $order):
                            if ($counter >= 5) {
                                break;
                            }
                            ?>
                            <div
                                class="bg-white/40 border border-white/60 backdrop-blur-sm rounded-2xl p-5 hover:bg-white/60 transition duration-300 group shadow-sm">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center space-x-4">
                                        <div
                                            class="w-12 h-12 rounded-xl bg-blue-100/50 text-blue-600 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition duration-300 border border-blue-100">
                                            <span class="material-symbols-rounded">inventory_2</span>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-800 font-['Outfit']">
                                                <?= htmlspecialchars($order['order_number'] ?? '') ?>
                                            </h3>
                                            <div class="flex items-center text-xs text-gray-600 mt-1 space-x-3">
                                                <span><?= (int) ($order['item_count'] ?? 0) ?> Items</span>
                                                <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                                                <span>Rs.
                                                    <?= number_format((float) ($order['total_amount'] ?? 0), 2) ?></span>
                                            </div>
                                            <div class="flex items-center text-xs text-gray-500 mt-1">
                                                <span class="material-symbols-rounded text-sm mr-1">calendar_today</span>
                                                <?= htmlspecialchars($order['order_date'] ?? '') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    $orderStatus = strtolower(trim($order['status'] ?? ''));

                                    $statusStyles = [
                                        'pending' => [
                                            'container' => 'bg-purple-100 text-purple-700 border border-purple-200',
                                            'dot' => 'bg-purple-500'
                                        ],
                                        'processing' => [
                                            'container' => 'bg-blue-100 text-blue-700 border border-blue-200',
                                            'dot' => 'bg-blue-500'
                                        ],
                                        'delivered' => [
                                            'container' => 'bg-green-100 text-green-700 border border-green-200',
                                            'dot' => 'bg-green-500'
                                        ],
                                        'in transit' => [
                                            'container' => 'bg-yellow-100 text-yellow-700 border border-yellow-200',
                                            'dot' => 'bg-yellow-500'
                                        ],
                                        'cancelled' => [
                                            'container' => 'bg-red-100 text-red-700 border border-red-200',
                                            'dot' => 'bg-red-500'
                                        ],
                                        'paid' => [
                                            'container' => 'bg-green-100 text-green-700 border border-green-200',
                                            'dot' => 'bg-green-500'
                                        ],
                                        'unpaid' => [
                                            'container' => 'bg-yellow-100 text-yellow-700 border border-yellow-200',
                                            'dot' => 'bg-yellow-500'
                                        ]
                                    ];

                                    $orderStyle = $statusStyles[$orderStatus] ?? [
                                        'container' => 'bg-gray-100 text-gray-700 border border-gray-200',
                                        'dot' => 'bg-gray-500'
                                    ];
                                    ?>

                                    <span
                                        class="px-4 py-2 rounded-xl <?= $orderStyle['container']; ?> text-sm font-bold flex items-center justify-center self-start sm:self-center">
                                        <span class="w-2 h-2 rounded-full <?= $orderStyle['dot']; ?> mr-2"></span>
                                        <?= htmlspecialchars(ucwords($orderStatus)) ?>
                                    </span>
                                </div>
                            </div>
                            <?php
                            $counter++;
                        endforeach;
                        ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Quick Actions -->
            <div class="space-y-8">
                <div class="glass-card rounded-3xl p-6 sm:p-8">
                    <div class="flex items-center space-x-2 mb-6">
                        <span class="material-symbols-rounded text-yellow-500 text-2xl">bolt</span>
                        <h2 class="text-xl font-bold text-gray-800 font-['Outfit']">Quick Actions</h2>
                    </div>

                    <div class="space-y-4">
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=products"
                            class="flex items-center p-4 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-200/50 transform hover:scale-[1.02] transition duration-300 group border border-white/20">
                            <div
                                class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center mr-4 group-hover:bg-white/30 transition backdrop-blur-sm">
                                <span class="material-symbols-rounded">shopping_bag</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm">Browse Products</h3>
                                <p class="text-xs opacity-90">View our catalog</p>
                            </div>
                        </a>

                        <a href="<?php echo BASE_PATH; ?>/index.php?page=customer-sales-orders"
                            class="flex items-center p-4 rounded-xl bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-200/50 transform hover:scale-[1.02] transition duration-300 group border border-white/20">
                            <div
                                class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center mr-4 group-hover:bg-white/30 transition backdrop-blur-sm">
                                <span class="material-symbols-rounded">receipt_long</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm">My Orders</h3>
                                <p class="text-xs opacity-90">Track your orders</p>
                            </div>
                        </a>

                        <a href="<?php echo BASE_PATH; ?>/index.php?page=tracking"
                            class="flex items-center p-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-lg shadow-teal-200/50 transform hover:scale-[1.02] transition duration-300 group border border-white/20">
                            <div
                                class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center mr-4 group-hover:bg-white/30 transition backdrop-blur-sm">
                                <span class="material-symbols-rounded">location_on</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm">Track Delivery</h3>
                                <p class="text-xs opacity-90">Real-time tracking</p>
                            </div>
                        </a>

                        <a href="<?php echo BASE_PATH; ?>/index.php?page=cart"
                            class="flex items-center p-4 rounded-xl bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-200/50 transform hover:scale-[1.02] transition duration-300 group border border-white/20">
                            <div
                                class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center mr-4 group-hover:bg-white/30 transition backdrop-blur-sm">
                                <span class="material-symbols-rounded">shopping_cart</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm">View Cart</h3>
                                <p class="text-xs opacity-90">5 items in cart</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Promo / CTA Small -->
                <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-yellow-100/50 to-orange-100/50 opacity-0 group-hover:opacity-100 transition duration-500">
                    </div>

                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Loyalty</p>
                            <h3 class="text-2xl font-bold text-gray-800 font-['Outfit']">450 <span
                                    class="text-sm font-normal text-gray-500">pts</span></h3>
                        </div>
                        <div
                            class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 shadow-sm border border-yellow-200">
                            <span class="material-symbols-rounded text-xl">workspace_premium</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>


<?php require_once __DIR__ . '/../../includes/footer.php'; ?>