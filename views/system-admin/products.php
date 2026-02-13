<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen py-8">
    <div class="container mx-auto px-4">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 font-['Outfit']">All Products</h1>
                <p class="text-gray-600 mt-1">Manage All ISDN Products</p>
            </div>
            <div class="flex gap-2">
                <div class="flex-1">
                    <input type="text" name="product_name" required placeholder="Search products..."
                        class="w-full md:w-96 border border-gray-200 rounded-lg px-3 py-3 text-sm text-gray-800 font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
                </div>
                <button id="openFilter" class="bg-white/60 backdrop-blur-sm border border-white/60 px-4 py-2 rounded-xl shadow-sm
         text-teal-700 font-semibold hover:bg-white transition flex items-center">
                    <span class="material-symbols-rounded mr-2 text-lg">filter_alt</span>
                    Filter
                </button>
                <button
                    class="bg-teal-600 px-4 py-2 rounded-xl shadow-lg shadow-teal-500/30 text-white font-semibold hover:bg-teal-700 transition flex items-center">
                    <span class="material-symbols-rounded mr-2 text-lg">add</span>New
                </button>
                <button
                    class="bg-teal-600 px-4 py-2 rounded-xl shadow-lg shadow-teal-500/30 text-white font-semibold hover:bg-teal-700 transition flex items-center">
                    <span class="material-symbols-rounded mr-2 text-lg">download</span>Report
                </button>
            </div>
        </div>

        <!-- Order List Container -->
        <div class="glass-panel rounded-3xl overflow-hidden shadow-xl border border-white/50">

            <!-- Desktop Table Header -->
            <div
                class="hidden md:grid grid-cols-7 gap-5 bg-white/30 backdrop-blur-sm p-5 border-b border-gray-100 text-sm font-bold text-gray-600 uppercase tracking-wider">
                <div class="col-span-1">Photo</div>
                <div class="col-span-1">Name</div>
                <div class="col-span-1">Code</div>
                <div class="col-span-1">Category</div>
                <div class="col-span-1">Description </div>
                <div class="col-span-1 text-center">Unit Price</div>
                <div class="col-span-1 text-center">Action</div>
            </div>
            <!-- Order Items Mock List -->
            <div class="p-5 hover:bg-white/40 transition duration-200 group">
                <div class="grid grid-cols-1 md:grid-cols-7 gap-5 items-center">
                    <!-- Mobile Label -->
                    <?php foreach ($products as $product): ?>

                        <div class="md:hidden text-sm font-bold text-gray-500 mb-1">Order Details</div>

                        <div class="col-span-1 flex items-center space-x-4">
                            <div>
                                <img src="<?php echo BASE_PATH . $product['image_url'] ?>"
                                    alt="<?php echo $product['product_name']; ?>"
                                    class="max-h-20 w-auto object-contain transition duration-500">

                            </div>
                        </div>
                        <div class="col-span-1 flex items-center space-x-4">
                            <div>
                                <h3 class="font-bold text-gray-800 font-['Outfit']">
                                    <?php echo $product['product_name'] ?>
                                </h3>
                            </div>
                        </div>

                        <div class="col-span-1 font-bold text-gray-800">
                            <span class="md:hidden font-semibold mr-2">Date:</span>
                            <?php echo $product['product_code'] ?>
                        </div>

                        <div class="col-span-1 text-sm text-gray-600 font-medium">
                            <span class="md:hidden font-semibold mr-2 text-gray-500 font-normal">Amount:</span>
                            <?php echo $product['category'] ?>
                        </div>
                        <div class="col-span-1 font-bold text-gray-800">
                            <span class="md:hidden font-semibold mr-2 text-gray-500 font-normal">Amount:</span>
                            <?php echo $product['description'] ?>
                        </div>

                        <div class="col-span-1 text-center font-bold text-gray-800">
                            <span class="md:hidden font-semibold mr-2 text-gray-500 font-normal">Amount:</span>
                            Rs. <?php echo number_format($product['unit_price'], 2); ?>
                        </div>

                        <div class="col-span-1 flex md:justify-center pr-6 space-x-2">

                            <!-- Edit -->
                            <a href="#"
                                class="text-blue-600 hover:text-white hover:bg-blue-600 p-2 rounded-full transition duration-200 shadow-sm border border-blue-100"
                                title="Edit Product">
                                <span class="material-symbols-rounded text-[20px]">edit</span>
                            </a>

                            <!-- Delete -->
                            <a href="#"
                                class="text-red-500 hover:text-white hover:bg-red-500 p-2 rounded-full transition duration-200 shadow-sm border border-red-100"
                                title="Delete Product">
                                <span class="material-symbols-rounded text-[20px]">delete</span>
                            </a>

                        </div>
                    <?php endforeach; ?>

                </div>
            </div>


            <!-- Pagination -->
            <div
                class="bg-white/30 backdrop-blur-sm px-6 py-4 border-t border-gray-100/50 flex items-center justify-between">
                <span class="text-sm text-gray-600 font-medium">Showing 1-8 of 45 Products</span>
                <div class="flex space-x-2">
                    <button
                        class="px-3 py-1 bg-white/50 border border-white/60 rounded-lg hover:bg-white text-gray-600 disabled:opacity-50 transition"
                        disabled>Previous</button>
                    <button class="px-3 py-1 bg-teal-600 text-white rounded-lg shadow-md shadow-teal-500/20">1</button>
                    <button
                        class="px-3 py-1 bg-white/50 border border-white/60 rounded-lg hover:bg-white text-gray-600 transition">2</button>
                    <button
                        class="px-3 py-1 bg-white/50 border border-white/60 rounded-lg hover:bg-white text-gray-600 transition">3</button>
                    <button
                        class="px-3 py-1 bg-white/50 border border-white/60 rounded-lg hover:bg-white text-gray-600 transition">Next</button>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../components/filter_drawer.php'; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>