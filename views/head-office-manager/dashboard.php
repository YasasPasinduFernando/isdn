<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="bg-gradient-to-br from-indigo-50 to-sky-50 min-h-screen py-6 sm:py-8">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-xl p-5 sm:p-8 mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-4xl font-bold text-gray-800">
                        Welcome, Head Office Manager <span class="text-indigo-600"><?php echo $_SESSION['username']; ?>!</span>
                    </h1>
                    <p class="text-gray-600 mt-2">Overview of island-wide stock and performance.</p>
                </div>
                <div class="hidden md:block">
                    <div class="bg-gradient-to-r from-indigo-600 to-sky-600 p-4 rounded-full">
                        <span class="material-symbols-rounded text-white text-3xl">insights</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="<?php echo BASE_PATH; ?>/index.php?page=stock-reports" class="bg-white rounded-xl p-5 shadow hover:shadow-lg transition border border-indigo-100">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-rounded text-indigo-600">bar_chart</span>
                    <div>
                        <p class="text-sm text-gray-500">Action</p>
                        <p class="font-semibold text-gray-800">Stock Reports</p>
                    </div>
                </div>
            </a>
            <div class="bg-white rounded-xl p-5 shadow border border-indigo-100">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-rounded text-indigo-600">shopping_cart</span>
                    <div>
                        <p class="text-sm text-gray-500">KPI</p>
                        <p class="font-semibold text-gray-800">Total Orders</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow border border-indigo-100">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-rounded text-indigo-600">factory</span>
                    <div>
                        <p class="text-sm text-gray-500">KPI</p>
                        <p class="font-semibold text-gray-800">Active RDCs</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
