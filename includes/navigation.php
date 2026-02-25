<nav class="bg-blue-600 text-white shadow-lg">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between py-4">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold">ISDN</h1>
            </div>
            <div class="flex items-center space-x-6">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_PATH; ?>/index.php?page=dashboard" class="hover:text-blue-200">Dashboard</a>
                    <a href="<?php echo BASE_PATH; ?>/index.php?page=products" class="hover:text-blue-200">Products</a>
                    <a href="<?php echo BASE_PATH; ?>/index.php?page=customer-sales-orders" class="hover:text-blue-200">Orders</a>
                    <a href="<?php echo BASE_PATH; ?>/controllers/AuthController.php?action=logout" class="hover:text-blue-200">Logout</a>
                <?php else: ?>
                    <a href="<?php echo BASE_PATH; ?>/index.php?page=login" class="hover:text-blue-200">Login</a>
                    <a href="<?php echo BASE_PATH; ?>/index.php?page=register" class="hover:text-blue-200">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
