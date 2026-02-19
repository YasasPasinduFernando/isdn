<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen py-10">

    <div class="container mx-auto px-5 max-w-4xl">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 font-['Outfit']">
                Product Registration
            </h1>
            <p class="text-gray-500 mt-1">
                Add a new product to the system
            </p>
        </div>

        <!-- Form Card -->
        <form method="POST" action="index.php?page=products&action=store" enctype="multipart/form-data"
            class="bg-white rounded-3xl shadow-lg border p-8 space-y-6">

            <!-- Product Name -->
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-2">
                    Product Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="product_name" required placeholder="e.g. Coca Cola 1L"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
            </div>

            <!-- Product Code -->
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-2">
                    Product Code <span class="text-red-500">*</span>
                </label>
                <input type="text" name="product_code" required placeholder="e.g. PRD-001"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />

            </div>

            <!-- Category -->
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-2">
                    Product Category <span class="text-red-500">*</span>
                </label>
                <select name="category" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">-- Select Category --</option>
                    <option value="Beverages">Beverages</option>
                    <option value="Food Items">Food Items</option>
                    <option value="Personal Care">Personal Care</option>
                    <option value="Home Cleaning">Home Cleaning</option>
                </select>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-2">
                    Description
                </label>
                <textarea name="description" rows="4" placeholder="Brief product description"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500"></textarea>
                </div>

            <!-- Unit Price -->
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-2">
                    Unit Price <span class="text-red-500">*</span>
                </label>
                <div
                    class="flex items-center border border-gray-200 rounded-xl overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-teal-500">
                    <span class="px-4 bg-gray-100 text-gray-600 font-bold">
                        Rs.
                    </span>
                    <input type="number" name="unit_price" required min="0" step="0.01" placeholder="0.00"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Numbers only. Example: 250.00
                </p>
            </div>

            <!-- Product Image -->
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-2">
                    Product Image
                </label>
                <input type="file" name="product_image" accept=".jpg,.jpeg,.png" class="block w-full text-sm text-gray-600
                           file:mr-4 file:py-2 file:px-4
                           file:rounded-xl file:border-0
                           file:bg-teal-50 file:text-teal-700
                           hover:file:bg-teal-100 transition" />
                <p class="text-xs text-gray-500 mt-2">
                    JPG or PNG only. Maximum size: 5MB.
                </p>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-4 pt-6 border-t">
                <a href="index.php?page=products"
                    class="px-6 py-3 rounded-xl border border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 transition">
                    Cancel
                </a>
                <button type="submit"
                    class="px-8 py-3 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-bold shadow-lg shadow-teal-500/30 hover:from-teal-600 hover:to-emerald-700 transition">
                    Save Product
                </button>
            </div>

        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>