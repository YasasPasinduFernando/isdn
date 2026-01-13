<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">🛍️ Products</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Sample Product Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gray-200 h-48 flex items-center justify-center">
                <span class="text-6xl">🥤</span>
            </div>
            <div class="p-4">
                <h3 class="font-bold text-lg">Coca Cola 1L</h3>
                <p class="text-gray-600 text-sm">Beverages</p>
                <div class="flex items-center justify-between mt-4">
                    <span class="text-xl font-bold text-blue-600">Rs. 250.00</span>
                    <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>

        <!-- Add more product cards here -->
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
