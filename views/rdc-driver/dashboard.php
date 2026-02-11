<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="bg-gradient-to-br from-slate-50 to-emerald-50 min-h-screen py-6 sm:py-8">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-xl p-5 sm:p-8 mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-4xl font-bold text-gray-800">
                        Welcome, RDC Driver <span class="text-emerald-600"><?php echo $_SESSION['username']; ?>!</span>
                    </h1>
                    <p class="text-gray-600 mt-2">Check your deliveries and update status on the go.</p>
                </div>
                <div class="hidden md:block">
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-4 rounded-full">
                        <span class="material-symbols-rounded text-white text-3xl">local_shipping</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="<?php echo BASE_PATH; ?>/index.php?page=tracking" class="bg-white rounded-xl p-5 shadow hover:shadow-lg transition border border-emerald-100">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-rounded text-emerald-600">route</span>
                    <div>
                        <p class="text-sm text-gray-500">Action</p>
                        <p class="font-semibold text-gray-800">My Delivery Route</p>
                    </div>
                </div>
            </a>
            <div class="bg-white rounded-xl p-5 shadow border border-emerald-100">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-rounded text-emerald-600">task_alt</span>
                    <div>
                        <p class="text-sm text-gray-500">Today</p>
                        <p class="font-semibold text-gray-800">Pending Deliveries</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
