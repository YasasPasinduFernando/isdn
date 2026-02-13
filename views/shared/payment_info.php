<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen py-8">
    <div class="container mx-auto px-4">

        <div class="min-h-[70vh] flex items-center justify-center px-6">

            <div
                class="glass-panel bg-white/70 backdrop-blur rounded-3xl shadow-xl border border-white/50 p-10 w-full max-w-2xl animate-fade-in">

                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 font-['Outfit'] flex items-center gap-2">
                        <span class="material-symbols-rounded text-teal-600">lock</span>
                        Secure Payment
                    </h2>

                    <div class="flex items-center text-sm text-green-600 font-medium">
                        <span class="material-symbols-rounded text-lg mr-1">verified</span>
                        SSL Secured
                    </div>
                </div>

                <!-- Total Amount -->
                <div class="mb-6">
                    <label class="text-sm font-semibold text-gray-600">
                        Total Payment Amount (Rs.)
                    </label>
                    <input type="text" value="Rs. 45,000.00" readonly
                        class="w-full border rounded-xl px-4 py-3 mt-1 bg-gray-100 font-semibold text-gray-800 focus:outline-none">
                </div>

                <!-- Mobile Number -->
                <div class="mb-6">
                    <label class="text-sm font-semibold text-gray-600">
                        Mobile Number
                    </label>
                    <input type="tel" placeholder="Enter your mobile number"
                        class="w-full border rounded-xl px-4 py-3 mt-1 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>

                <!-- Email Address -->
                <div class="mb-6">
                    <label class="text-sm font-semibold text-gray-600">
                        Email Address
                    </label>
                    <input type="email" placeholder="Enter your email address"
                        class="w-full border rounded-xl px-4 py-3 mt-1 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>

                <!-- Payment Method Selection -->
                <div class="mb-8">
                    <label class="text-sm font-semibold text-gray-600 block mb-3">
                        Select Payment Method
                    </label>

                    <div class="grid grid-cols-2 gap-4">

                        <!-- Visa -->
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="visa" class="hidden peer">
                            <div
                                class="border rounded-2xl p-4 flex items-center justify-center font-semibold text-gray-700 bg-white hover:border-teal-500 peer-checked:border-teal-600 peer-checked:ring-2 peer-checked:ring-teal-500 transition">
                                <img src="<?php echo BASE_PATH . '/assets/images/cards/visa_card.jpg'; ?>" alt="Visa" class="h-8 mr-3">
                                Visa
                            </div>
                        </label>

                        <!-- MasterCard -->
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="mastercard" class="hidden peer">
                            <div
                                class="border rounded-2xl p-4 flex items-center justify-center font-semibold text-gray-700 bg-white hover:border-teal-500 peer-checked:border-teal-600 peer-checked:ring-2 peer-checked:ring-teal-500 transition">
                                <img src="<?php echo BASE_PATH . '/assets/images/cards/master_card.png' ?>" alt="MasterCard" class="h-8 mr-3">
                                MasterCard
                            </div>
                        </label>

                    </div>
                </div>

                <!-- Payment Button -->
                <button
                    class="w-full bg-teal-600 px-6 py-4 rounded-xl shadow-lg shadow-teal-500/30 text-white font-semibold hover:bg-teal-700 transition flex items-center justify-center">
                    <span class="material-symbols-rounded mr-2">payments</span>
                    Proceed to Payment
                </button>

                <!-- Security Note -->
                <p class="text-xs text-gray-500 mt-6 text-center">
                    Your payment is encrypted and securely processed. We do not store your card information.
                </p>

            </div>
        </div>

        <!-- Subtle Fade-in Animation -->
        <style>
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-fade-in {
                animation: fadeIn 0.5s ease-out forwards;
            }
        </style>


    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>