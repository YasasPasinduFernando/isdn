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
                        Welcome, RDC Manager <span class="text-purple-600"><?php echo $_SESSION['username']; ?>!</span>
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
        <div>
            <a href="<?php echo BASE_PATH; ?>/index.php?page=request-product-units" class="inline-block px-6 py-3 bg-purple-600 text-white rounded-lg shadow-md hover:bg-purple-700 transition">Request Product Units</a>
            <a href="<?php echo BASE_PATH; ?>/index.php?page=send-product-units" class="inline-block px-6 py-3 bg-purple-600 text-white rounded-lg shadow-md hover:bg-purple-700 transition">Send Product Units</a>
            <a href="<?php echo BASE_PATH; ?>/index.php?page=stock-reports" class="inline-block px-6 py-3 bg-purple-600 text-white rounded-lg shadow-md hover:bg-purple-700 transition">Stock Reports</a>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
