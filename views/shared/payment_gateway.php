<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Payment Details</title>
    <!-- Tailwind CDN (remove if Tailwind already bundled in your project) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white text-slate-800">
    <main class="min-h-screen flex items-center justify-center px-4 py-10">
        <section class="w-full max-w-2xl">
            <div class="rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
                <h1 class="text-2xl font-bold tracking-tight">Payment Details</h1>
                <p class="mt-1 text-sm text-slate-500">Enter your card details to complete the payment.</p>

                <form id="paymentForm" class="mt-6 space-y-6" method="POST" action="" autocomplete="on" novalidate>
                    <!-- CSRF token (server-side: replace value) -->
                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES) ?>">

                    <!-- Card type -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Card Type</label>
                        <div class="mt-2 grid grid-cols-2 gap-3">
                            <label
                                class="cursor-pointer rounded-xl border border-slate-200 p-4 flex items-center gap-3 hover:border-slate-300">
                                <input type="radio" name="card_type" value="VISA" class="h-4 w-4" required>
                                <span class="font-semibold">Visa</span>
                                <img src="<?php echo BASE_PATH . '/assets/images/cards/visa_card.jpg'; ?>" alt="Visa"
                                    class="h-8 mr-3">
                            </label>
                            <label
                                class="cursor-pointer rounded-xl border border-slate-200 p-4 flex items-center gap-3 hover:border-slate-300">
                                <input type="radio" name="card_type" value="MASTERCARD" class="h-4 w-4" required>
                                <span class="font-semibold">Mastercard</span>
                                <img src="<?php echo BASE_PATH . '/assets/images/cards/master_card.png' ?>"
                                    alt="MasterCard" class="h-8 mr-3">
                            </label>
                        </div>
                    </div>

                    <!-- Card number -->
                    <div>
                        <label for="cardNumber" class="block text-sm font-semibold text-slate-700">Card Number</label>
                        <div class="mt-2">
                            <input id="cardNumber" name="card_number" type="text" inputmode="numeric"
                                autocomplete="cc-number" placeholder="1234 5678 9012 3456" maxlength="19"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500"
                                required />
                            <p class="mt-1 text-xs text-slate-500">Digits only. Spaces are allowed.</p>
                        </div>
                    </div>

                    <!-- Expiration + CVN -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="expMonth" class="block text-sm font-semibold text-slate-700">Expiration
                                Month</label>
                            <select id="expMonth" name="exp_month" autocomplete="cc-exp-month"
                                class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 bg-white outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500"
                                required>
                                <option value="" selected disabled>MM</option>
                                <option value="01">01</option>
                                <option value="02">02</option>
                                <option value="03">03</option>
                                <option value="04">04</option>
                                <option value="05">05</option>
                                <option value="06">06</option>
                                <option value="07">07</option>
                                <option value="08">08</option>
                                <option value="09">09</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                            </select>
                        </div>

                        <div>
                            <label for="expYear" class="block text-sm font-semibold text-slate-700">Expiration
                                Year</label>
                            <select id="expYear" name="exp_year" autocomplete="cc-exp-year"
                                class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 bg-white outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500"
                                required>
                                <option value="" selected disabled>YYYY</option>
                                <!-- You can generate these with PHP dynamically -->
                                <option>2026</option>
                                <option>2027</option>
                                <option>2028</option>
                                <option>2029</option>
                                <option>2030</option>
                                <option>2031</option>
                                <option>2032</option>
                                <option>2033</option>
                                <option>2034</option>
                                <option>2035</option>
                            </select>
                        </div>

                        <div>
                            <label for="cvn" class="block text-sm font-semibold text-slate-700">CVN</label>
                            <div class="mt-2 relative">
                                <input id="cvn" name="cvn" type="password" inputmode="numeric" autocomplete="cc-csc"
                                    placeholder="123" maxlength="4"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 pr-10 outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500"
                                    required />
                                <!-- simple lock icon -->
                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"
                                    viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M7 10V8a5 5 0 0 1 10 0v2" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" />
                                    <path d="M6 10h12a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2Z"
                                        stroke="currentColor" stroke-width="2" />
                                </svg>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">3 digits (Visa/Mastercard). Some cards use 4.</p>
                        </div>
                    </div>

                    <!-- Amount -->
                    <div class="rounded-2xl border border-slate-200 p-5">
                        <h2 class="text-lg font-bold">Your Amount</h2>

                        <div class="mt-4 rounded-xl bg-emerald-100 px-4 py-3 flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-700">Total Amount</span>
                            <span class="text-base font-bold text-emerald-900">
                                <span
                                    id="amountText"><?= htmlspecialchars(number_format($checkout_info['cart_grand_total'], 2)) ?></span>
                                Rs
                            </span>
                        </div>

                        <!-- Keep amount in hidden input for server -->
                        <input type="hidden" name="amount" value="5000.00">
                        <input type="hidden" name="currency" value="LKR">
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <a href="/cart.php"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3 font-semibold text-slate-700 hover:bg-slate-50">
                            Cancel
                        </a>

                        <button type="button" id="payBtn"
                            class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-6 py-3 font-semibold text-white hover:bg-emerald-700">
                            Pay
                        </button>
                    </div>

                    <p class="text-xs text-slate-500">
                        Tip: For PCI compliance, send card details directly to your payment gateway (tokenization)
                        rather than posting raw card data to your PHP server.
                    </p>
                </form>
            </div>
        </section>
        <div id="pageLoader" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
            <div class="flex flex-col items-center gap-3 rounded-xl bg-white px-6 py-5 shadow-lg">
                <div class="h-10 w-10 animate-spin rounded-full border-4 border-gray-300 border-t-teal-600"></div>
                <p class="text-sm font-semibold text-gray-700">Processing...</p>
            </div>
        </div>
    </main>

    <script>
        // Format card number as "#### #### #### ####" while typing (client-side only)
        const cardNumber = document.getElementById('cardNumber');
        cardNumber.addEventListener('input', () => {
            const digits = cardNumber.value.replace(/\D/g, '').slice(0, 16);
            cardNumber.value = digits.replace(/(\d{4})(?=\d)/g, '$1 ');
        });

        // Light client-side guard: prevent submitting obvious invalid inputs
        document.getElementById('paymentForm').addEventListener('submit', (e) => {
            const num = cardNumber.value.replace(/\s/g, '');
            const cvn = document.getElementById('cvn').value.replace(/\D/g, '');

            if (num.length < 13 || num.length > 16) {
                e.preventDefault();
                alert('Please enter a valid card number.');
                return;
            }
            if (cvn.length < 3 || cvn.length > 4) {
                e.preventDefault();
                alert('Please enter a valid CVN.');
                return;
            }
        });
    </script>
    <script src="js/payment.js?v=<?= time() ?>"></script>

</body>

</html>