<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-800 mb-8 font-['Outfit']">Shopping Cart</h1>

        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- Cart Items List -->
            <div class="lg:w-2/3">
                <div class="glass-panel rounded-3xl overflow-hidden shadow-xl border border-white/50">
                    <div class="p-6 border-b border-gray-100/50 flex justify-between items-center bg-white/30 backdrop-blur-sm">
                        <h2 class="font-bold text-gray-700 font-['Outfit'] text-lg">Cart Items (3)</h2>
                        <button  onclick = "clearCart()"class="text-red-500 hover:text-red-700 text-sm font-semibold transition flex items-center group">
                            <span class="material-symbols-rounded text-lg mr-1 group-hover:rotate-12 transition">delete_sweep</span> 
                            Remove All
                        </button>
                    </div>

                    <div class="divide-y divide-gray-100/50">
                        <!-- Item 1 -->
                         <?php $subtotal = 0; ?>
                    <?php foreach ($cartItems as $item): 
                        $itemTotal = $item['unit_price'] * $item['quantity'];
                        $subtotal += $itemTotal;
                    ?>
                    <div class="p-6 hover:bg-white/40 transition duration-200 group cart-item"
                        data-id="<?= $item['product_id'] ?>"
                        data-price="<?= $item['unit_price'] ?>">

                        <div class="flex items-center gap-6">
                            <div class="w-24 h-24 bg-blue-50 rounded-2xl flex items-center justify-center flex-shrink-0 border border-blue-100">
                                <span class="material-symbols-rounded text-4xl text-blue-400">headphones</span>
                            </div>

                            <div class="flex-grow">
                                <h3 class="font-bold text-lg text-gray-800 font-['Outfit']">
                                    <?= $item['name'] ?>
                                </h3>

                                <p class="text-sm text-gray-500 mb-2">
                                    Color: Black | Warranty: 1 Year
                                </p>

                                <div class="flex items-center justify-between mt-4">
                                    <div class="font-bold text-teal-600 text-xl item-total">
                                        Rs. <?= number_format($itemTotal, 2) ?>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <!-- Quantity Control -->
                                        <div class="flex items-center border border-gray-200/60 rounded-xl bg-white/60 backdrop-blur-sm shadow-sm">
                                            <button class="qty-minus w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-white rounded-l-xl transition hover:text-teal-600 font-bold">
                                                -
                                            </button>

                                            <span class="item-qty px-2 text-sm font-bold text-gray-800">
                                                <?= $item['quantity'] ?>
                                            </span>

                                            <button class="qty-plus w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-white rounded-r-xl transition hover:text-teal-600 font-bold">
                                                +
                                            </button>
                                        </div>

                                        <!-- Remove -->
                                        <button class="remove-btn w-10 h-10 rounded-full bg-red-50 text-red-400 hover:bg-red-500 hover:text-white transition flex items-center justify-center shadow-sm">
                                            <span class="remove-item material-symbols-rounded text-lg">delete</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                         </div>

                     
                    <div class="p-6 bg-white/30 backdrop-blur-sm border-t border-gray-100/50">
                        <a href="index.php?page=products" class="text-teal-600 font-bold hover:text-teal-800 flex items-center transition group">
                            <span class="material-symbols-rounded mr-2 group-hover:-translate-x-1 transition">arrow_back</span> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:w-1/3">
                <div class="glass-card rounded-3xl p-6 sticky top-24 border border-white/50">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 font-['Outfit']">Order Summary</h2>
                    
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span class="order-total font-medium text-gray-800">Rs. 33,500.00</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Shipping estimate</span>
                            <span class="shipping-charges font-medium text-gray-800">Rs. 450.00</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Tax estimate</span>
                            <span class="font-medium text-green-600">Free</span>
                        </div>
                        <div class="h-px bg-gray-200/60 my-4"></div>
                        <div class="flex justify-between text-lg font-bold">
                            <span class="text-gray-800">Order Total</span>
                            <span class="order-final-total text-teal-600 text-2xl">Rs. 33,950.00</span>
                        </div>
                    </div>

                    <button id="checkoutBtn" class="w-full bg-gradient-to-r from-teal-500 to-emerald-600 text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-teal-500/30 hover:shadow-xl transform hover:scale-[1.02] transition duration-300">
                        Proceed to Checkout
                    </button>
                    
                    <div class="mt-8 flex items-center justify-center space-x-4 opacity-70 grayscale hover:grayscale-0 transition duration-300">
                        <i class="fab fa-cc-visa text-3xl"></i>
                        <i class="fab fa-cc-mastercard text-3xl"></i>
                        <i class="fab fa-cc-amex text-3xl"></i>
                    </div>
                    <p class="text-center text-xs text-gray-400 mt-2 flex items-center justify-center">
                        <span class="material-symbols-rounded text-sm mr-1">lock</span> Secure Encrypted Payment
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
