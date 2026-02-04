<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen py-6 sm:py-8">
    <div class="container mx-auto px-4">
        
        <!-- Page Header -->
        <div class="glass-panel rounded-3xl p-6 sm:p-8 mb-8 relative overflow-hidden">
             <div class="absolute top-0 right-0 p-8 opacity-10 pointer-events-none">
                <span class="material-symbols-rounded text-9xl text-teal-900">shopping_bag</span>
            </div>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 relative z-10">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 flex items-center font-['Outfit']">
                        <span class="material-symbols-rounded text-teal-600 text-4xl mr-3">storefront</span>
                        Our Products
                    </h1>
                    <p class="text-gray-600 mt-2 text-lg">Browse our wide range of premium Fast-Moving Consumer Goods</p>
                </div>
                <div class="hidden md:flex items-center space-x-3 bg-teal-50/50 border border-teal-100/50 px-6 py-3 rounded-2xl backdrop-blur-sm">
                    <div class="p-2 bg-teal-100 rounded-lg text-teal-700">
                        <span class="material-symbols-rounded">shopping_cart</span>
                    </div>
                    <div>
                        <p class="text-xs text-teal-600 font-semibold uppercase tracking-wider">Your Cart</p>
                        <span class="font-bold text-teal-800">5 items - Rs. 1,250</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Section -->
        <div class="glass-card rounded-2xl p-4 sm:p-6 mb-8 relative z-30">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2 relative">
                    <span class="material-symbols-rounded absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">search</span>
                    <input type="text" placeholder="Search products..." 
                        class="w-full pl-12 pr-4 py-3 bg-white/50 backdrop-blur-sm border border-white/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 transition-all placeholder-gray-400 text-gray-700 shadow-sm">
                </div>

                <!-- Custom Category Dropdown -->
                <div class="relative group">
                    <button onclick="toggleDropdown('category-dropdown')" class="w-full pl-12 pr-4 py-3 bg-white/50 backdrop-blur-sm border border-white/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 transition-all text-left text-gray-700 shadow-sm flex justify-between items-center cursor-pointer">
                        <span class="material-symbols-rounded absolute left-4 text-gray-400">filter_alt</span>
                        <span id="category-selected">All Categories</span>
                        <span class="material-symbols-rounded text-gray-400 transition-transform duration-300" id="category-arrow">expand_more</span>
                    </button>
                    <!-- Dropdown Menu -->
                    <div id="category-dropdown" class="absolute w-full mt-2 bg-white/80 backdrop-blur-xl border border-white/60 rounded-xl shadow-2xl hidden z-50 overflow-hidden transform origin-top transition-all duration-200">
                        <div class="py-1">
                            <div onclick="selectOption('category', 'All Categories')" class="px-4 py-3 hover:bg-teal-50/80 hover:text-teal-700 cursor-pointer transition flex items-center">
                                <span class="material-symbols-rounded text-sm mr-2 opacity-0 text-teal-600 check-icon">check</span> All Categories
                            </div>
                            <div onclick="selectOption('category', 'Beverages')" class="px-4 py-3 hover:bg-teal-50/80 hover:text-teal-700 cursor-pointer transition flex items-center">
                                <span class="material-symbols-rounded text-sm mr-2 opacity-0 text-teal-600 check-icon">check</span> Beverages
                            </div>
                            <div onclick="selectOption('category', 'Food Items')" class="px-4 py-3 hover:bg-teal-50/80 hover:text-teal-700 cursor-pointer transition flex items-center">
                                <span class="material-symbols-rounded text-sm mr-2 opacity-0 text-teal-600 check-icon">check</span> Food Items
                            </div>
                            <div onclick="selectOption('category', 'Personal Care')" class="px-4 py-3 hover:bg-teal-50/80 hover:text-teal-700 cursor-pointer transition flex items-center">
                                <span class="material-symbols-rounded text-sm mr-2 opacity-0 text-teal-600 check-icon">check</span> Personal Care
                            </div>
                            <div onclick="selectOption('category', 'Home Cleaning')" class="px-4 py-3 hover:bg-teal-50/80 hover:text-teal-700 cursor-pointer transition flex items-center">
                                <span class="material-symbols-rounded text-sm mr-2 opacity-0 text-teal-600 check-icon">check</span> Home Cleaning
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom Sort Dropdown -->
                <div class="relative group">
                    <button onclick="toggleDropdown('sort-dropdown')" class="w-full pl-12 pr-4 py-3 bg-white/50 backdrop-blur-sm border border-white/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 transition-all text-left text-gray-700 shadow-sm flex justify-between items-center cursor-pointer">
                        <span class="material-symbols-rounded absolute left-4 text-gray-400">sort</span>
                        <span id="sort-selected">Sort By</span>
                        <span class="material-symbols-rounded text-gray-400 transition-transform duration-300" id="sort-arrow">expand_more</span>
                    </button>
                    <!-- Dropdown Menu -->
                    <div id="sort-dropdown" class="absolute w-full mt-2 bg-white/80 backdrop-blur-xl border border-white/60 rounded-xl shadow-2xl hidden z-50 overflow-hidden transform origin-top transition-all duration-200">
                        <div class="py-1">
                            <div onclick="selectOption('sort', 'Price: Low to High')" class="px-4 py-3 hover:bg-teal-50/80 hover:text-teal-700 cursor-pointer transition flex items-center">
                                <span class="material-symbols-rounded text-sm mr-2 opacity-0 text-teal-600 check-icon">check</span> Price: Low to High
                            </div>
                            <div onclick="selectOption('sort', 'Price: High to Low')" class="px-4 py-3 hover:bg-teal-50/80 hover:text-teal-700 cursor-pointer transition flex items-center">
                                <span class="material-symbols-rounded text-sm mr-2 opacity-0 text-teal-600 check-icon">check</span> Price: High to Low
                            </div>
                            <div onclick="selectOption('sort', 'Name: A-Z')" class="px-4 py-3 hover:bg-teal-50/80 hover:text-teal-700 cursor-pointer transition flex items-center">
                                <span class="material-symbols-rounded text-sm mr-2 opacity-0 text-teal-600 check-icon">check</span> Name: A-Z
                            </div>
                            <div onclick="selectOption('sort', 'Newest First')" class="px-4 py-3 hover:bg-teal-50/80 hover:text-teal-700 cursor-pointer transition flex items-center">
                                <span class="material-symbols-rounded text-sm mr-2 opacity-0 text-teal-600 check-icon">check</span> Newest First
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 relative z-10">
            
            <!-- Product Card 1 -->
            <div class="glass-card rounded-2xl overflow-hidden hover-lift group border border-white/40">
                <div class="relative">
                    <div class="bg-gradient-to-br from-red-400 to-red-600 h-56 flex items-center justify-center group-hover:scale-105 transition duration-700">
                        <span class="material-symbols-rounded text-white text-7xl drop-shadow-lg">liquor</span>
                    </div>
                    <span class="absolute top-3 right-3 bg-emerald-500/90 backdrop-blur text-white px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                        In Stock
                    </span>
                    <span class="absolute top-3 left-3 bg-yellow-400/90 backdrop-blur text-gray-900 px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                        -15% OFF
                    </span>
                </div>
                <div class="p-5">
                    <span class="text-xs font-bold text-teal-600 uppercase tracking-wider bg-teal-50 px-2 py-1 rounded-md">Beverages</span>
                    <h3 class="font-bold text-xl text-gray-800 mt-3 font-['Outfit']">Coca Cola 1L</h3>
                    <p class="text-sm text-gray-500 mt-1">Original Coca-Cola soft drink in 1 liter bottle</p>
                    
                    <div class="flex items-center mt-3 space-x-1 text-yellow-400">
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="material-symbols-rounded text-lg">star_half</span>
                        <span class="text-gray-400 text-xs ml-2 font-medium">(4.5)</span>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <div>
                            <span class="text-gray-400 line-through text-sm">Rs. 295</span>
                            <span class="text-2xl font-bold text-teal-600 block leading-none mt-1">Rs. 250</span>
                        </div>
                        <button class="w-10 h-10 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center hover:bg-teal-600 hover:text-white transition duration-300 shadow-sm border border-teal-100">
                            <span class="material-symbols-rounded">favorite</span>
                        </button>
                    </div>

                    <button class="w-full bg-gradient-to-r from-teal-500 to-emerald-600 text-white py-3 rounded-xl font-bold mt-5 hover:from-teal-600 hover:to-emerald-700 transition duration-300 shadow-lg shadow-teal-500/20 flex items-center justify-center group-hover:translate-y-[-2px]">
                        <span class="material-symbols-rounded mr-2">add_shopping_cart</span> Add to Cart
                    </button>
                </div>
            </div>

            <!-- Product Card 2 -->
            <div class="glass-card rounded-2xl overflow-hidden hover-lift group border border-white/40">
                <div class="relative">
                    <div class="bg-gradient-to-br from-blue-400 to-blue-600 h-56 flex items-center justify-center group-hover:scale-105 transition duration-700">
                        <span class="material-symbols-rounded text-white text-7xl drop-shadow-lg">cookie</span>
                    </div>
                    <span class="absolute top-3 right-3 bg-emerald-500/90 backdrop-blur text-white px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                        In Stock
                    </span>
                </div>
                <div class="p-5">
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-wider bg-blue-50 px-2 py-1 rounded-md">Food Items</span>
                    <h3 class="font-bold text-xl text-gray-800 mt-3 font-['Outfit']">Marie Biscuits 400g</h3>
                    <p class="text-sm text-gray-500 mt-1">Delicious marie biscuits family pack</p>
                    
                    <div class="flex items-center mt-3 space-x-1 text-yellow-400">
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="material-symbols-rounded text-lg text-gray-300">star</span>
                        <span class="text-gray-400 text-xs ml-2 font-medium">(4.0)</span>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <div>
                            <span class="text-2xl font-bold text-teal-600 block leading-none mt-1">Rs. 380</span>
                        </div>
                        <button class="w-10 h-10 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center hover:bg-teal-600 hover:text-white transition duration-300 shadow-sm border border-teal-100">
                            <span class="material-symbols-rounded">favorite</span>
                        </button>
                    </div>

                    <button class="w-full bg-gradient-to-r from-teal-500 to-emerald-600 text-white py-3 rounded-xl font-bold mt-5 hover:from-teal-600 hover:to-emerald-700 transition duration-300 shadow-lg shadow-teal-500/20 flex items-center justify-center group-hover:translate-y-[-2px]">
                        <span class="material-symbols-rounded mr-2">add_shopping_cart</span> Add to Cart
                    </button>
                </div>
            </div>

            <!-- Product Card 3 -->
            <div class="glass-card rounded-2xl overflow-hidden hover-lift group border border-white/40">
                <div class="relative">
                    <div class="bg-gradient-to-br from-green-400 to-green-600 h-56 flex items-center justify-center group-hover:scale-105 transition duration-700">
                        <span class="material-symbols-rounded text-white text-7xl drop-shadow-lg">soap</span>
                    </div>
                    <span class="absolute top-3 right-3 bg-emerald-500/90 backdrop-blur text-white px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                        In Stock
                    </span>
                    <span class="absolute top-3 left-3 bg-red-500/90 backdrop-blur text-white px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                        HOT
                    </span>
                </div>
                <div class="p-5">
                    <span class="text-xs font-bold text-green-600 uppercase tracking-wider bg-green-50 px-2 py-1 rounded-md">Personal Care</span>
                    <h3 class="font-bold text-xl text-gray-800 mt-3 font-['Outfit']">Lux Soap 100g</h3>
                    <p class="text-sm text-gray-500 mt-1">Premium beauty soap with moisturizing cream</p>
                    
                    <div class="flex items-center mt-3 space-x-1 text-yellow-400">
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="text-gray-400 text-xs ml-2 font-medium">(5.0)</span>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <div>
                            <span class="text-2xl font-bold text-teal-600 block leading-none mt-1">Rs. 120</span>
                        </div>
                        <button class="w-10 h-10 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center hover:bg-teal-600 hover:text-white transition duration-300 shadow-sm border border-teal-100">
                            <span class="material-symbols-rounded">favorite</span>
                        </button>
                    </div>

                    <button class="w-full bg-gradient-to-r from-teal-500 to-emerald-600 text-white py-3 rounded-xl font-bold mt-5 hover:from-teal-600 hover:to-emerald-700 transition duration-300 shadow-lg shadow-teal-500/20 flex items-center justify-center group-hover:translate-y-[-2px]">
                        <span class="material-symbols-rounded mr-2">add_shopping_cart</span> Add to Cart
                    </button>
                </div>
            </div>

            <!-- Product Card 4 -->
            <div class="glass-card rounded-2xl overflow-hidden hover-lift group border border-white/40">
                <div class="relative">
                    <div class="bg-gradient-to-br from-orange-400 to-orange-600 h-56 flex items-center justify-center group-hover:scale-105 transition duration-700">
                        <span class="material-symbols-rounded text-white text-7xl drop-shadow-lg">cleaning_services</span>
                    </div>
                    <span class="absolute top-3 right-3 bg-red-500/90 backdrop-blur text-white px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                        Low Stock
                    </span>
                </div>
                <div class="p-5">
                    <span class="text-xs font-bold text-orange-600 uppercase tracking-wider bg-orange-50 px-2 py-1 rounded-md">Home Cleaning</span>
                    <h3 class="font-bold text-xl text-gray-800 mt-3 font-['Outfit']">Harpic Cleaner 500ml</h3>
                    <p class="text-sm text-gray-500 mt-1">Powerful toilet bowl cleaner</p>
                    
                    <div class="flex items-center mt-3 space-x-1 text-yellow-400">
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="material-symbols-rounded text-lg">star</span>
                        <span class="material-symbols-rounded text-lg">star_half</span>
                        <span class="text-gray-400 text-xs ml-2 font-medium">(4.7)</span>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <div>
                            <span class="text-2xl font-bold text-teal-600 block leading-none mt-1">Rs. 450</span>
                        </div>
                        <button class="w-10 h-10 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center hover:bg-teal-600 hover:text-white transition duration-300 shadow-sm border border-teal-100">
                            <span class="material-symbols-rounded">favorite</span>
                        </button>
                    </div>

                    <button class="w-full bg-gradient-to-r from-teal-500 to-emerald-600 text-white py-3 rounded-xl font-bold mt-5 hover:from-teal-600 hover:to-emerald-700 transition duration-300 shadow-lg shadow-teal-500/20 flex items-center justify-center group-hover:translate-y-[-2px]">
                        <span class="material-symbols-rounded mr-2">add_shopping_cart</span> Add to Cart
                    </button>
                </div>
            </div>
            
        </div>

        <!-- Pagination -->
        <div class="flex justify-center mt-12 pb-8">
            <div class="glass-panel p-2 rounded-2xl flex items-center gap-2">
                <button class="px-3 py-2 sm:px-4 bg-white/50 border border-white/60 rounded-xl hover:bg-white text-gray-600 transition">
                    <span class="material-symbols-rounded">chevron_left</span>
                </button>
                <button class="px-4 py-2 bg-teal-600 text-white rounded-xl shadow-lg shadow-teal-500/30 font-bold">1</button>
                <button class="px-4 py-2 bg-white/50 border border-white/60 rounded-xl hover:bg-white text-gray-600 transition">2</button>
                <button class="px-4 py-2 bg-white/50 border border-white/60 rounded-xl hover:bg-white text-gray-600 transition">3</button>
                <button class="px-3 py-2 sm:px-4 bg-white/50 border border-white/60 rounded-xl hover:bg-white text-gray-600 transition">
                    <span class="material-symbols-rounded">chevron_right</span>
                </button>
            </div>
        </div>

    </div>
</div>

<script>
    function toggleDropdown(id) {
        // Close all other dropdowns
        document.querySelectorAll('[id$="-dropdown"]').forEach(el => {
            if (el.id !== id && !el.classList.contains('hidden')) {
                el.classList.add('hidden');
                // Reset arrows
                const arrowId = el.id.replace('-dropdown', '-arrow');
                const arrow = document.getElementById(arrowId);
                if(arrow) arrow.style.transform = 'rotate(0deg)';
            }
        });

        const dropdown = document.getElementById(id);
        const arrowId = id.replace('-dropdown', '-arrow');
        const arrow = document.getElementById(arrowId);
        
        if (dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('hidden');
            if(arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            dropdown.classList.add('hidden');
            if(arrow) arrow.style.transform = 'rotate(0deg)';
        }
        
        // Stop propagation to prevent closing on toggle click
        event.stopPropagation();
    }

    function selectOption(type, value) {
        document.getElementById(type + '-selected').textContent = value;
        toggleDropdown(type + '-dropdown');
        
        // Handle visual selection (blue checkmark or highlighting)
        const dropdown = document.getElementById(type + '-dropdown');
        const options = dropdown.querySelectorAll('.px-4'); // Selector for options
        
        options.forEach(opt => {
           // Reset all options text style (optional, if you want specific highlighting)
           // But here we just update the text and close
        });
        
        // Log selection (or trigger actual form/ajax)
        console.log(type + ' changed to: ' + value);
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const dropdowns = document.querySelectorAll('[id$="-dropdown"]');
        dropdowns.forEach(dropdown => {
            if (!dropdown.classList.contains('hidden')) {
                 dropdown.classList.add('hidden');
                 const arrowId = dropdown.id.replace('-dropdown', '-arrow');
                 const arrow = document.getElementById(arrowId);
                 if(arrow) arrow.style.transform = 'rotate(0deg)';
            }
        });
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
