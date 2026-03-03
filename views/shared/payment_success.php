<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen py-8">
    <div class="container mx-auto px-4">

        <div class="min-h-[70vh] flex items-center justify-center px-6">

            <div
                class="glass-panel bg-white/70 backdrop-blur rounded-3xl shadow-xl border border-white/50 p-10 w-full max-w-2xl text-center animate-fade-in">

                <!-- Success Icon -->
                <div class="flex justify-center mb-6">
                    <div class="w-20 h-20 flex items-center justify-center rounded-full bg-teal-100 shadow-inner">
                        <span class="material-symbols-rounded text-teal-600 text-5xl">
                            check_circle
                        </span>
                    </div>
                </div>

                <!-- Greeting -->
                <h1 class="text-3xl font-bold text-gray-800 font-['Outfit'] mb-2">
                    Thank You, <?php echo $cash_payment_info['customer_name'] ?>! 🎉
                </h1>

                <p class="text-gray-600 mb-6">
                    Your payment has been successfully completed. We truly appreciate your business.
                </p>

                <!-- Invoice Details -->
                <div class="bg-white/60 rounded-2xl p-6 border border-white/50 mb-8 text-left space-y-4">

                    <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                        <span class="text-gray-500 font-medium">Invoice No</span>
                        <span class="font-semibold text-gray-800 break-all sm:text-right">
                            <?php echo $cash_payment_info['invoice_no'] ?>
                        </span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                        <span class="text-gray-500 font-medium">Payment Amount</span>
                        <span class="font-semibold text-gray-800 sm:text-right">
                            Rs. <?php echo $cash_payment_info['payment_amount'] ?>
                        </span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                        <span
                            class="text-gray-500 font-medium"><?php echo $cash_payment_info['payment_date_label'] ?></span>
                        <span class="font-semibold text-gray-800 sm:text-right">
                            <?php echo $cash_payment_info['payment_date'] ?>
                        </span>
                    </div>

                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">

                    <!-- Download Invoice -->
                    <a href="<?php echo BASE_PATH. $cash_payment_info['invoice_path'] ?>" download
                        class="bg-teal-600 px-6 py-3 rounded-xl shadow-lg shadow-teal-500/30 text-white font-semibold hover:bg-teal-700 transition flex items-center justify-center">
                        <span class="material-symbols-rounded mr-2 text-lg">download</span>
                        Download Invoice
                    </a>

                    <!-- Go to Dashboard -->
                    <a href="index.php?page=dashboard"
                        class="bg-white border border-gray-200 px-6 py-3 rounded-xl shadow text-gray-700 font-semibold hover:bg-gray-50 transition flex items-center justify-center">
                        <span class="material-symbols-rounded mr-2 text-lg">dashboard</span>
                        Go to Dashboard
                    </a>

                </div>

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
                animation: fadeIn 0.6s ease-out forwards;
            }
        </style>

    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>