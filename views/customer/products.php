<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="bg-gradient-to-br from-blue-50 to-purple-50 min-h-screen py-6 sm:py-8">
    <div class="container mx-auto px-4">
        
        <!-- Page Header -->
        <div class="bg-white rounded-2xl shadow-xl p-5 sm:p-8 mb-6 sm:mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-shopping-bag text-purple-600 mr-4"></i>
                        Our Products
                    </h1>
                    <p class="text-gray-600 mt-2">Browse our wide range of Fast-Moving Consumer Goods</p>
                </div>
                <div class="hidden md:flex items-center space-x-3 bg-purple-100 px-6 py-3 rounded-full">
                    <i class="fas fa-shopping-cart text-purple-600 text-xl"></i>
                    <span class="font-bold text-purple-600">5 items in cart</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Section -->
        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 mb-6 sm:mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2 relative">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" placeholder="Search products..." 
                        class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                <!-- Category Filter -->
                <div class="relative">
                    <i class="fas fa-filter absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <select class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 appearance-none bg-white">
                        <option>All Categories</option>
                        <option>Beverages</option>
                        <option>Food Items</option>
                        <option>Personal Care</option>
                        <option>Home Cleaning</option>
                    </select>
                </div>

                <!-- Sort -->
                <div class="relative">
                    <i class="fas fa-sort absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <select class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 appearance-none bg-white">
                        <option>Sort By</option>
                        <option>Price: Low to High</option>
                        <option>Price: High to Low</option>
                        <option>Name: A-Z</option>
                        <option>Newest First</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Product Card 1 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition transform hover:-translate-y-2">
                <div class="relative">
                    <div class="bg-gradient-to-br from-red-400 to-red-600 h-48 flex items-center justify-center">
                        <i class="fas fa-wine-bottle text-white text-6xl"></i>
                    </div>
                    <span class="absolute top-3 right-3 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                        In Stock
                    </span>
                    <span class="absolute top-3 left-3 bg-yellow-400 text-gray-800 px-3 py-1 rounded-full text-xs font-bold">
                        -15% OFF
                    </span>
                </div>
                <div class="p-5">
                    <span class="text-xs font-semibold text-purple-600 uppercase">Beverages</span>
                    <h3 class="font-bold text-lg text-gray-800 mt-1">Coca Cola 1L</h3>
                    <p class="text-sm text-gray-600 mt-2">Original Coca-Cola soft drink in 1 liter bottle</p>
                    
                    <div class="flex items-center mt-3 space-x-1 text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span class="text-gray-600 text-sm ml-2">(4.5)</span>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <div>
                            <span class="text-gray-400 line-through text-sm">Rs. 295</span>
                            <span class="text-2xl font-bold text-purple-600 ml-2">Rs. 250</span>
                        </div>
                    </div>

                    <button class="w-full bg-gradient-to-r from-purple-600 to-blue-600 text-white py-3 rounded-lg font-semibold hover:from-purple-700 hover:to-blue-700 transition mt-4 transform hover:scale-105">
                        <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                    </button>
                </div>
            </div>

            <!-- Product Card 2 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition transform hover:-translate-y-2">
                <div class="relative">
                    <div class="bg-gradient-to-br from-blue-400 to-blue-600 h-48 flex items-center justify-center">
                        <i class="fas fa-cookie-bite text-white text-6xl"></i>
                    </div>
                    <span class="absolute top-3 right-3 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                        In Stock
                    </span>
                </div>
                <div class="p-5">
                    <span class="text-xs font-semibold text-blue-600 uppercase">Food Items</span>
                    <h3 class="font-bold text-lg text-gray-800 mt-1">Marie Biscuits 400g</h3>
                    <p class="text-sm text-gray-600 mt-2">Delicious marie biscuits family pack</p>
                    
                    <div class="flex items-center mt-3 space-x-1 text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                        <span class="text-gray-600 text-sm ml-2">(4.0)</span>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <div>
                            <span class="text-2xl font-bold text-blue-600">Rs. 380</span>
                        </div>
                    </div>

                    <button class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 text-white py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-cyan-700 transition mt-4 transform hover:scale-105">
                        <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                    </button>
                </div>
            </div>

            <!-- Product Card 3 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition transform hover:-translate-y-2">
                <div class="relative">
                    <div class="bg-gradient-to-br from-green-400 to-green-600 h-48 flex items-center justify-center">
                        <i class="fas fa-soap text-white text-6xl"></i>
                    </div>
                    <span class="absolute top-3 right-3 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                        In Stock
                    </span>
                    <span class="absolute top-3 left-3 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                        HOT
                    </span>
                </div>
                <div class="p-5">
                    <span class="text-xs font-semibold text-green-600 uppercase">Personal Care</span>
                    <h3 class="font-bold text-lg text-gray-800 mt-1">Lux Soap 100g</h3>
                    <p class="text-sm text-gray-600 mt-2">Premium beauty soap with moisturizing cream</p>
                    
                    <div class="flex items-center mt-3 space-x-1 text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <span class="text-gray-600 text-sm ml-2">(5.0)</span>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <div>
                            <span class="text-2xl font-bold text-green-600">Rs. 120</span>
                        </div>
                    </div>

                    <button class="w-full bg-gradient-to-r from-green-600 to-teal-600 text-white py-3 rounded-lg font-semibold hover:from-green-700 hover:to-teal-700 transition mt-4 transform hover:scale-105">
                        <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                    </button>
                </div>
            </div>

            <!-- Product Card 4 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition transform hover:-translate-y-2">
                <div class="relative">
                    <div class="bg-gradient-to-br from-orange-400 to-orange-600 h-48 flex items-center justify-center">
                        <i class="fas fa-spray-can text-white text-6xl"></i>
                    </div>
                    <span class="absolute top-3 right-3 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                        Low Stock
                    </span>
                </div>
                <div class="p-5">
                    <span class="text-xs font-semibold text-orange-600 uppercase">Home Cleaning</span>
                    <h3 class="font-bold text-lg text-gray-800 mt-1">Harpic Cleaner 500ml</h3>
                    <p class="text-sm text-gray-600 mt-2">Powerful toilet bowl cleaner</p>
                    
                    <div class="flex items-center mt-3 space-x-1 text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span class="text-gray-600 text-sm ml-2">(4.7)</span>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <div>
                            <span class="text-2xl font-bold text-orange-600">Rs. 450</span>
                        </div>
                    </div>

                    <button class="w-full bg-gradient-to-r from-orange-600 to-red-600 text-white py-3 rounded-lg font-semibold hover:from-orange-700 hover:to-red-700 transition mt-4 transform hover:scale-105">
                        <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                    </button>
                </div>
            </div>

            <!-- Add 4 more similar product cards to make it 8 total -->
            <!-- You can copy and modify the above cards -->
            
        </div>

        <!-- Pagination -->
        <div class="flex justify-center mt-12">
            <div class="flex flex-wrap justify-center gap-2">
                <button class="px-3 py-2 sm:px-4 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="px-3 py-2 sm:px-4 bg-purple-600 text-white rounded-lg">1</button>
                <button class="px-3 py-2 sm:px-4 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50">2</button>
                <button class="px-3 py-2 sm:px-4 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50">3</button>
                <button class="px-3 py-2 sm:px-4 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
