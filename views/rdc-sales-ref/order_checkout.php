<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 font-['Outfit']">Checkout</h1>
                <p class="text-gray-500 mt-1">Review your order before placing it</p>
            </div>
        </div>

        <!-- ================= Item List ================= -->

        <div class="mt-8 bg-white rounded-3xl shadow-lg overflow-hidden p-6 border ">
            <h2 class="text-xl font-bold text-gray-800 font-['Outfit'] mb-6 flex items-center gap-2">
                <span class="material-symbols-rounded text-teal-600">
                    inventory_2
                </span>
                Order Products
            </h2>

            <!-- Table Header -->
            <div class="hidden md:grid grid-cols-7 bg-gray-50 px-6 py-4 text-sm font-bold text-gray-600 uppercase">
                <div>Product Code</div>
                <div>Product Name</div>
                <div>Category</div>
                <div>Selling Price</div>
                <div>Qty</div>
                <div>Discount</div>
                <div>Line Total</div>
            </div>

            <!-- Item Row -->
            <div class="grid grid-cols-1 md:grid-cols-7 gap-4 px-6 py-5 border-t items-center hover:bg-gray-50">

                <div class="font-semibold text-gray-800">PRD-001</div>

                <div class="font-semibold text-gray-800">Wireless Headphones</div>

                <div class="text-gray-600">Electronics</div>

                <div class="font-semibold text-gray-800">Rs. 12,500.00</div>
                <div class="font-semibold text-gray-800">10</div>

                <div class="text-red-500 font-semibold">Rs. 500.00</div>
                <div class="font-semibold text-gray-800">Rs. 13,500.00</div>
            </div>
        </div>
        <!-- ================= Shipping & Payment ================= -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 flex flex-wrap justify-between items-center mt-5 gap-4">

            <!-- Shipping Info -->
            <div
                class="glass-panel bg-white/70 backdrop-blur rounded-3xl shadow-xl border border-white/50 p-6 lg:col-span-2">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800 font-['Outfit'] flex items-center gap-2">
                        <span class="material-symbols-rounded text-teal-600">local_shipping</span>
                        Delivery Information
                    </h2>

                    <!-- Searchable Customer Dropdown -->
                    <div class="relative w-80">
                        <input type="text" list="customerList" placeholder="Choose a Customer.."
                            class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white/80 backdrop-blur" />

                        <datalist id="customerList">
                            <option value="Wijitha Stores">
                            <option value="Amal Stores">
                            <option value="Vijaya Stores">
                            <option value="Shanthi Stores">
                        </datalist>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Delivery Address</label>
                        <p class="mt-1 font-medium text-gray-800">
                            No 45, Galle Road, Colombo 03, Sri Lanka
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-600">Estimated Delivery Date</label>
                        <p class="mt-1 font-medium text-gray-800">Jan 15, 2025</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-600">Delivery Notes (Optional)</label>
                        <textarea rows="3"
                            class="w-full border rounded-xl px-4 py-3 mt-1 focus:ring-2 focus:ring-teal-500"
                            placeholder="Any special delivery instructions..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="glass-panel bg-white/70 backdrop-blur rounded-3xl shadow-xl border border-white/50 p-4">
                <h2 class="text-xl font-bold text-gray-800 font-['Outfit'] mb-6 flex items-center gap-2">
                    <span class="material-symbols-rounded text-teal-600">receipt_long</span>
                    Payment Summary
                </h2>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span>Rs. 45,750.00</span>
                    </div>
                    <div class="flex justify-between text-red-500">
                        <span>Discount</span>
                        <span>- Rs. 500.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>VAT (15%)</span>
                        <span>Rs. 6,787.50</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Delivery Fee</span>
                        <span>Rs. 500.00</span>
                    </div>

                    <hr>

                    <div class="flex justify-between font-bold text-lg text-teal-700">
                        <span>Grand Total</span>
                        <span>Rs. 52,537.50</span>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="mt-6">
                    <label class="text-sm font-semibold text-gray-600">Payment Method</label>

                    <div class="space-y-3 mt-3">
                        <label class="flex items-center gap-3">
                            <input type="radio" name="payment" class="payment-method">
                            Cash on Delivery
                        </label>

                        <label class="flex items-center gap-3">
                            <input type="radio" name="payment" class="payment-method">
                            Card Payment
                        </label>
                    </div>

                    <p id="paymentError" class="text-red-500 text-xs mt-2 hidden">
                        Please select a payment method
                    </p>
                </div>
            </div>
        </div>
        <!-- ================= Actions ================= -->
        <div class="flex flex-wrap justify-between items-center mt-10 gap-4">
            <a href="cart.php" class="px-6 py-3 bg-white border rounded-xl shadow hover:bg-gray-50 font-semibold">
                Back to Cart
            </a>

            <div class="flex gap-3">
                <button class="px-6 py-3 bg-gray-200 rounded-xl font-semibold hover:bg-gray-300">
                    Cancel
                </button>

                <button id="placeOrderBtn"
                    class="px-8 py-3 bg-teal-600 text-white rounded-xl font-bold shadow-lg shadow-teal-500/30 opacity-50 cursor-not-allowed">
                    Place Order
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>