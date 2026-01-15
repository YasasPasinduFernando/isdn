<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="manifest" href="<?php echo BASE_PATH; ?>/manifest.json">
    <meta name="theme-color" content="#764ba2">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="<?php echo BASE_PATH; ?>/assets/images/icons/icon-192.svg">
    <style>
        /* Gradient animations */
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .gradient-bg {
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #4facfe);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    
    <!-- Sticky Top Header -->
    <header class="sticky top-0 z-50 gradient-bg shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <!-- Logo Section -->
                <div class="flex items-center space-x-3">
                    <div class="bg-white p-2 rounded-lg shadow-lg">
                        <i class="fas fa-truck-fast text-purple-600 text-2xl"></i>
                    </div>
                    <div class="text-white">
                        <h1 class="text-2xl font-bold tracking-tight">ISDN</h1>
                        <p class="text-xs opacity-90">Sales Distribution Network</p>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="hidden md:flex items-center space-x-6">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=dashboard" 
                           class="text-white hover:text-yellow-300 transition duration-300 flex items-center space-x-2">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=products" 
                           class="text-white hover:text-yellow-300 transition duration-300 flex items-center space-x-2">
                            <i class="fas fa-shopping-bag"></i>
                            <span>Products</span>
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=orders" 
                           class="text-white hover:text-yellow-300 transition duration-300 flex items-center space-x-2">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Orders</span>
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=cart" 
                           class="text-white hover:text-yellow-300 transition duration-300 flex items-center space-x-2">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Cart</span>
                        </a>
                        
                        <!-- User Profile Dropdown -->
                        <div class="relative group">
                            <button class="flex items-center space-x-2 bg-white bg-opacity-20 px-4 py-2 rounded-full hover:bg-opacity-30 transition">
                                <i class="fas fa-user-circle text-2xl"></i>
                                <span class="text-white font-semibold"><?php echo $_SESSION['username']; ?></span>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300">
                                <a href="<?php echo BASE_PATH; ?>/index.php?page=profile" class="block px-4 py-3 text-gray-800 hover:bg-purple-50 rounded-t-lg">
                                    <i class="fas fa-user mr-2"></i> My Profile
                                </a>
                                <a href="<?php echo BASE_PATH; ?>/controllers/AuthController.php?action=logout" class="block px-4 py-3 text-red-600 hover:bg-red-50 rounded-b-lg">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=login" 
                           class="text-white hover:text-yellow-300 transition duration-300 flex items-center space-x-2">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=register" 
                           class="bg-white text-purple-600 px-6 py-2 rounded-full font-semibold hover:bg-yellow-300 hover:text-purple-700 transition duration-300 shadow-lg">
                            <i class="fas fa-user-plus mr-2"></i>Register
                        </a>
                    <?php endif; ?>
                </nav>

                <!-- Mobile Menu Button -->
                <button class="md:hidden text-white text-2xl" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="hidden md:hidden bg-purple-600 shadow-lg">
        <div class="container mx-auto px-4 py-4 space-y-2">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=dashboard" class="block text-white py-2 hover:bg-purple-700 rounded px-3">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=products" class="block text-white py-2 hover:bg-purple-700 rounded px-3">
                    <i class="fas fa-shopping-bag mr-2"></i> Products
                </a>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=orders" class="block text-white py-2 hover:bg-purple-700 rounded px-3">
                    <i class="fas fa-clipboard-list mr-2"></i> Orders
                </a>
                <a href="<?php echo BASE_PATH; ?>/controllers/AuthController.php?action=logout" class="block text-red-200 py-2 hover:bg-purple-700 rounded px-3">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=login" class="block text-white py-2 hover:bg-purple-700 rounded px-3">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </a>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=register" class="block text-white py-2 hover:bg-purple-700 rounded px-3">
                    <i class="fas fa-user-plus mr-2"></i> Register
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <main class="flex-grow"><?php
// Content will be inserted here from other pages
?></main>