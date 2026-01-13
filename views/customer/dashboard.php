<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">🏠 Customer Dashboard</h1>

    <?php display_flash(); ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600">Total Orders</p>
                    <h3 class="text-3xl font-bold text-blue-600">24</h3>
                </div>
                <div class="text-4xl">📦</div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600">Pending Orders</p>
                    <h3 class="text-3xl font-bold text-yellow-600">5</h3>
                </div>
                <div class="text-4xl">⏳</div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600">Delivered</p>
                    <h3 class="text-3xl font-bold text-green-600">19</h3>
                </div>
                <div class="text-4xl">✅</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="<?php echo BASE_PATH; ?>/index.php?page=products" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-8 rounded-lg shadow-lg hover:shadow-xl transition transform hover:scale-105">
            <div class="text-center">
                <div class="text-5xl mb-4">🛒</div>
                <h3 class="text-2xl font-bold">Browse Products</h3>
                <p class="mt-2">View and order products</p>
            </div>
        </a>

        <a href="<?php echo BASE_PATH; ?>/index.php?page=orders" class="bg-gradient-to-r from-green-500 to-green-600 text-white p-8 rounded-lg shadow-lg hover:shadow-xl transition transform hover:scale-105">
            <div class="text-center">
                <div class="text-5xl mb-4">📋</div>
                <h3 class="text-2xl font-bold">My Orders</h3>
                <p class="mt-2">Track your orders</p>
            </div>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
