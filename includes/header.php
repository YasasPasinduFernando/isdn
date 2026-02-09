<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com "></script>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/custom.css?v=<?= time() ?>">
    <!-- Google Material Symbols Rounded -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0" />
    <!-- Keep FontAwesome for Social Brand Icons if needed, otherwise optional. Retaining for safety if referenced elsewhere for brands. -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css?v=<?= time() ?>">
    <link rel="manifest" href="<?php echo BASE_PATH; ?>/manifest.json">
    <meta name="theme-color" content="#0d9488">
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
            background: linear-gradient(-45deg, #14b8a6, #0d9488, #2dd4bf, #059669);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        /* Material Symbols Align */
        .material-symbols-rounded {
            vertical-align: middle;
            font-size: 1.25rem; /* Default size adjustment */
        }
    </style>
</head>
<body class="bg-slate-50 flex flex-col min-h-screen">

    <!-- Page Loader (Truck Animation) -->
    <div id="page-loader">
        <svg class="truck" viewBox="0 0 48 24" width="48px" height="24px">
            <g fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" transform="translate(0,2)">
                <g class="truck__body">
                    <g stroke-dasharray="105 105">
                        <polyline class="truck__outside1" points="2 17,1 17,1 11,5 9,7 1,39 1,39 6" />
                        <polyline class="truck__outside2" points="39 12,39 17,31.5 17" />
                        <polyline class="truck__outside3" points="22.5 17,11 17" />
                        <polyline class="truck__window1" points="6.5 4,8 4,8 9,5 9" />
                        <polygon class="truck__window2" points="10 4,10 9,14 9,14 4" />
                    </g>
                    <polyline class="truck__line" points="43 8,31 8" stroke-dasharray="10 2 10 2 10 2 10 2 10 2 10 26" />
                    <polyline class="truck__line" points="47 10,31 10" stroke-dasharray="14 2 14 2 14 2 14 2 14 18" />
                </g>
                <g stroke-dasharray="15.71 15.71">
                    <g class="truck__wheel">
                        <circle class="truck__wheel-spin" r="2.5" cx="6.5" cy="17" />
                    </g>
                    <g class="truck__wheel">
                        <circle class="truck__wheel-spin" r="2.5" cx="27" cy="17" />
                    </g>
                </g>
            </g>
        </svg>
        <p class="mt-4 text-teal-600 font-bold font-['Outfit'] animate-pulse">Loading ISDN...</p>
    </div>

    <script>
        // Force loader for at least 2 seconds
        window.addEventListener('load', () => {
             setTimeout(() => {
                 const loader = document.getElementById('page-loader');
                 if(loader) {
                    loader.style.opacity = '0';
                    loader.style.visibility = 'hidden'; 
                    // Remove from DOM after transition
                    setTimeout(() => {
                        loader.style.display = 'none';
                    }, 500); 
                 }
             }, 2000); // 2000ms = 2 seconds
        });
    </script>
    
    <!-- Sticky Top Header -->
    <header class="sticky top-0 z-50 glass-header shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <!-- Logo Section -->
                <div class="flex items-center space-x-3">
                    <div class="bg-gradient-to-br from-teal-500 to-emerald-600 p-2 rounded-xl shadow-lg text-white flex items-center justify-center">
                        <span class="material-symbols-rounded">local_shipping</span>
                    </div>
                    <div class="text-gray-800">
                        <h1 class="text-2xl font-bold tracking-tight font-['Outfit']">ISDN</h1>
                        <p class="text-[10px] uppercase tracking-wider font-semibold text-teal-600">Distribution Network</p>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="hidden md:flex items-center space-x-2 bg-gray-100/50 p-1 rounded-full backdrop-blur-sm border border-gray-200/50">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php 
                        $currentPage = $_GET['page'] ?? 'home';
                        $navItems = [
                            'dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
                            'products' => ['icon' => 'shopping_bag', 'label' => 'Products'],
                            'orders' => ['icon' => 'receipt_long', 'label' => 'Orders'],
                            'cart' => ['icon' => 'shopping_cart', 'label' => 'Cart'],
                        ];
                        
                        foreach ($navItems as $page => $item): 
                            $isActive = $currentPage === $page;
                            $activeClass = $isActive ? 'bg-white shadow text-teal-700' : 'text-gray-500 hover:text-gray-900 hover:bg-white/50';
                        ?>
                            <a href="<?php echo BASE_PATH; ?>/index.php?page=<?php echo $page; ?>" 
                               class="<?php echo $activeClass; ?> px-4 py-2 rounded-full transition-all duration-300 flex items-center space-x-2 text-sm font-medium">
                                <span class="material-symbols-rounded text-lg"><?php echo $item['icon']; ?></span>
                                <span><?php echo $item['label']; ?></span>
                            </a>
                        <?php endforeach; ?>
                        
                        <!-- User Profile Dropdown Trigger (Separate from nav pill) -->
                        <div class="relative group ml-2 px-2">
                        <div class="relative group ml-2 px-2">
                             <a href="<?php echo BASE_PATH; ?>/index.php?page=profile" class="flex items-center space-x-2 text-gray-700 hover:text-teal-600 transition">
                                <span class="material-symbols-rounded text-3xl">account_circle</span>
                            </a>
                             <div class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform origin-top-right z-50">
                                <a href="<?php echo BASE_PATH; ?>/index.php?page=profile" class="flex items-center px-4 py-3 text-gray-600 hover:bg-teal-50 hover:text-teal-700 rounded-t-xl transition">
                                    <span class="material-symbols-rounded mr-2 text-lg">person</span> My Profile
                                </a>
                                <a href="<?php echo BASE_PATH; ?>/controllers/AuthController.php?action=logout" class="flex items-center px-4 py-3 text-red-500 hover:bg-red-50 rounded-b-xl transition">
                                    <span class="material-symbols-rounded mr-2 text-lg">logout</span> Logout
                                </a>
                            </div>
                        </div>

                    <?php else: ?>
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=login" 
                           class="px-5 py-2 text-gray-600 font-medium hover:text-teal-600 transition">
                            Login
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=register" 
                           class="bg-teal-600 text-white px-6 py-2 rounded-full font-medium hover:bg-teal-700 transition shadow-lg shadow-teal-200">
                            Register
                        </a>
                    <?php endif; ?>
                </nav>

                <!-- Mobile Menu Button -->
                <button class="md:hidden text-gray-600 focus:outline-none p-2" onclick="toggleMobileMenu()">
                    <span class="material-symbols-rounded text-3xl">menu</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="hidden md:hidden bg-teal-600 shadow-lg" style="background: linear-gradient(-45deg, #14b8a6, #0d9488);">
        <div class="container mx-auto px-4 py-4 space-y-2">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=dashboard" class="flex items-center text-white py-2 hover:bg-teal-700 rounded px-3">
                    <span class="material-symbols-rounded mr-3">dashboard</span> Dashboard
                </a>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=products" class="flex items-center text-white py-2 hover:bg-teal-700 rounded px-3">
                    <span class="material-symbols-rounded mr-3">shopping_bag</span> Products
                </a>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=orders" class="flex items-center text-white py-2 hover:bg-teal-700 rounded px-3">
                    <span class="material-symbols-rounded mr-3">receipt_long</span> Orders
                </a>
                <a href="<?php echo BASE_PATH; ?>/controllers/AuthController.php?action=logout" class="flex items-center text-red-200 py-2 hover:bg-teal-700 rounded px-3">
                    <span class="material-symbols-rounded mr-3">logout</span> Logout
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=login" class="flex items-center text-white py-2 hover:bg-teal-700 rounded px-3">
                    <span class="material-symbols-rounded mr-3">login</span> Login
                </a>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=register" class="flex items-center text-white py-2 hover:bg-teal-700 rounded px-3">
                    <span class="material-symbols-rounded mr-3">person_add</span> Register
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <main class="flex-grow"><?php
// Content will be inserted here from other pages
?></main>
