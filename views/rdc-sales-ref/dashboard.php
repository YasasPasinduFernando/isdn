<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="bg-gradient-to-br from-teal-50 to-blue-50 min-h-screen py-6 sm:py-8">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-xl p-5 sm:p-8 mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-4xl font-bold text-gray-800">
                        Welcome, RDC Sales Rep <span class="text-teal-600"><?php echo $_SESSION['username']; ?>!</span>
                    </h1>
                    <p class="text-gray-600 mt-2">Create and follow up orders for your retailers.</p>
                </div>
                <div class="hidden md:block">
                    <div class="bg-gradient-to-r from-teal-600 to-blue-600 p-4 rounded-full">
                        <span class="material-symbols-rounded text-white text-3xl">handshake</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="<?php echo BASE_PATH; ?>/index.php?page=products" class="bg-white rounded-xl p-5 shadow hover:shadow-lg transition border border-teal-100">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-rounded text-teal-600">shopping_bag</span>
                    <div>
                        <p class="text-sm text-gray-500">Action</p>
                        <p class="font-semibold text-gray-800">Browse Products</p>
                    </div>
                </div>
            </a>
            <a href="<?php echo BASE_PATH; ?>/index.php?page=orders" class="bg-white rounded-xl p-5 shadow hover:shadow-lg transition border border-teal-100">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-rounded text-teal-600">receipt_long</span>
                    <div>
                        <p class="text-sm text-gray-500">Action</p>
                        <p class="font-semibold text-gray-800">View Orders</p>
                    </div>
                </div>
            </a>
            <a href="<?php echo BASE_PATH; ?>/index.php?page=tracking" class="bg-white rounded-xl p-5 shadow hover:shadow-lg transition border border-teal-100">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-rounded text-teal-600">location_on</span>
                    <div>
                        <p class="text-sm text-gray-500">Action</p>
                        <p class="font-semibold text-gray-800">Track Deliveries</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
