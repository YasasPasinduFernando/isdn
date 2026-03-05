<?php
require_once __DIR__ . '/../../includes/header.php';
$sub_total = 0;
$discount_total = 0;
$discounted_line_total = 0;
$delivery_fee = 1450;
$tax_amount = 0;
// print_r(value: $customer_list);

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
            <div class="hidden md:grid grid-cols-12 bg-gray-50 px-6 py-4 text-sm font-bold text-gray-600 uppercase">
                <div class="col-span-2">Product Code</div>
                <div class="col-span-2">Product Name</div>
                <div class="col-span-2">Category</div>
                <div class="col-span-2">Selling Price</div>
                <div class="col-span-1">Qty</div>
                <div class="col-span-2 text-align:right">Discount</div>
                <div class="col-span-1">Line Total</div>
            </div>

            <!-- Item Row -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 px-5 py-5 border-t items-center hover:bg-gray-50">

                <?php foreach ($order_items as $order_item): ?>

                    <div class="font-semibold text-gray-800 col-span-2"><?php echo $order_item['product_code']; ?></div>

                    <div class="font-semibold text-gray-800 col-span-2"><?php echo $order_item['product_name']; ?></div>

                    <div class="text-gray-600 col-span-2"><?php echo $order_item['category_name']; ?></div>

                    <div class="font-semibold text-gray-800 col-span-2">Rs.
                        <?php echo number_format($order_item['unit_price'], decimals: 2); ?>
                    </div>
                    <div class="font-semibold text-gray-800 col-span-1"><?php echo $order_item['quantity']; ?></div>

                    <div class="text-red-500 font-semibold col-span-2  pr-8">Rs. <?php
                    $discount = $order_item['line_amount'] - $order_item['discounted_line_amount'];
                    echo number_format($discount, 2);
                    ?></div>
                    <div class="font-semibold text-gray-800 col-span-1">Rs.
                        <?php echo number_format($order_item['discounted_line_amount'], 2); ?>
                    </div>
                    <?php
                    $sub_total += $order_item['line_amount'];
                    $discount_total += $discount;
                    $discounted_line_total += $order_item['discounted_line_amount'];

                    ?>
                <?php endforeach; ?>
                <?php
                $tax_amount = $discounted_line_total * 15 / 100;
                $grand_total = $discounted_line_total + $tax_amount + $delivery_fee;
                ?>
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
                        <input type="text" id="customerInput" list="customerList" placeholder="Choose a Customer.."
                            class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white/80 backdrop-blur" />

                        <datalist id="customerList">
                            <?php foreach ($customer_list as $customer): ?>
                                <option value="<?= htmlspecialchars($customer['customer_name']); ?>"
                                    data-id="<?= (int) $customer['id']; ?>"
                                    data-address="<?= htmlspecialchars($customer['address']); ?>">
                                <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Delivery Address</label>
                        <p id="deliveryAddress" class="mt-1 font-medium text-gray-800">
                            <?php //echo $delivery_info['delivery_address']; ?>
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-600">Estimated Delivery Date</label>
                        <p class="mt-1 font-medium text-gray-800">
                            <?php
                            $date = new DateTime();
                            $date->modify('+2 days');

                            echo $date->format('d M, Y');
                            ?>
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-600">Delivery Notes (Optional)</label>
                        <textarea rows="3"
                            id="deliveryNotes" class="w-full border rounded-xl px-4 py-3 mt-1 focus:ring-2 focus:ring-teal-500"
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
                        <span><?php echo 'Rs. ' . number_format($sub_total, 2); ?></span>
                    </div>
                    <div class="flex justify-between text-red-500">
                        <span>Discount</span>
                        <span><?php echo '- Rs. ' . number_format($discount_total, 2) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>VAT (15%)</span>
                        <span><?= number_format($tax_amount, 2); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Delivery Fee</span>
                        <span><?php echo 'Rs. ' . number_format($delivery_fee, 2) ?></span>
                    </div>

                    <hr>

                    <div class="flex justify-between font-bold text-lg text-teal-700">
                        <span>Grand Total</span>
                        <span><?php echo 'Rs. ' . number_format($grand_total, 2); ?></span>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="mt-6">
                    <label class="text-sm font-semibold text-gray-600">Payment Method</label>

                    <div class="space-y-3 mt-3">
                        <label class="flex items-center gap-3">
                            <input type="radio" name="payment" value="cash" class="payment-method">
                            Cash on Delivery
                        </label>

                        <label class="flex items-center gap-3">
                            <input type="radio" name="payment" value="card" class="payment-method">
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
            <a href="index.php?page=cart"
                class="px-6 py-3 bg-white border rounded-xl shadow hover:bg-gray-50 font-semibold">
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

<div id="pageLoader" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="flex flex-col items-center gap-3 rounded-xl bg-white px-6 py-5 shadow-lg">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-gray-300 border-t-teal-600"></div>
        <p class="text-sm font-semibold text-gray-700">Processing...</p>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const paymentRadios = document.querySelectorAll(".payment-method");
        const placeOrderBtn = document.getElementById("placeOrderBtn");
        const paymentError = document.getElementById("paymentError");
        const form = document.getElementById("checkoutForm");

        function checkPaymentSelection() {
            const selected = [...paymentRadios].some((r) => r.checked);

            if (selected) {
                placeOrderBtn.classList.remove("opacity-50", "cursor-not-allowed");
                paymentError.classList.add("hidden");
            } else {
                placeOrderBtn.classList.add("opacity-50", "cursor-not-allowed");
            }
        }

        paymentRadios.forEach((radio) => {
            radio.addEventListener("change", checkPaymentSelection);
        });

        placeOrderBtn.addEventListener("click", function () {
            const selectedPayment = document.querySelector(
                'input[name="payment"]:checked',
            );

            if (!selectedPayment) {
                paymentError.classList.remove("hidden");
                return;
            }

            paymentError.classList.add("hidden");
            if (!validateCustomerSelection()) {
                return;
            }

            if (!confirm("Are you sure you want to place this order?")) {
                return;
            }


            let action = "";
            const method = selectedPayment.value;
            const deliveryNotes = document.getElementById("deliveryNotes").value.trim();
            showLoader();
            // If payment method is CASH → Send AJAX POST
            if (method == "cash") {
                action = "place";
            } else if (method == "card") {
                action = "pay";
            }
            fetch(
                "index.php?page=rdc-sales-ref-sales-orders&method=" + method + "&action=" + action,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        delivery_notes: deliveryNotes,
                        customer_id: getSelectedCustomerId(),
                    }),
                },
            )
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        hideLoader();
                        //alert("Order placed successfully!");
                        window.location.href = data.redirect;
                    } else {
                        alert("Something went wrong. Please try again.");
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    alert("Server error. Please try again.");
                });
        });

        function showLoader() {
            const loader = document.getElementById("pageLoader");
            document.body.style.pointerEvents = "none";
            loader.classList.remove("hidden");
            loader.classList.add("flex"); // make it visible and centered
        }

        function hideLoader() {
            const loader = document.getElementById("pageLoader");
            document.body.style.pointerEvents = "auto";
            loader.classList.add("hidden");
            loader.classList.remove("flex");
        }
    });

    function validateCustomerSelection() {

        const input = document.getElementById("customerInput");
        const list = document.getElementById("customerList");
        const selectedValue = input.value.trim();

        let valid = false;

        for (let option of list.options) {
            if (option.value === selectedValue) {
                valid = true;
                break;
            }
        }

        if (!valid) {
            alert("Please select a valid customer from the list.");
            input.focus();
            return false;
        }

        return true;
    }
    function getSelectedCustomerId() {
        const input = document.getElementById("customerInput");
        const list = document.getElementById("customerList");
        const selectedValue = input.value;

        let customerId = null;

        for (let option of list.options) {
            if (option.value === selectedValue) {
                customerId = option.dataset.id;
                break;
            }
        }

        return customerId;
    }
    document.getElementById("customerInput").addEventListener("change", function () {

        const selectedName = this.value;
        const options = document.querySelectorAll("#customerList option");
        const deliveryAddress = document.getElementById("deliveryAddress");

        let found = false;

        options.forEach(option => {
            if (option.value === selectedName) {
                console.log(option.dataset.address);
                const address = option.dataset.address;
                deliveryAddress.textContent = "" + address;
                found = true;
            }
        });

        if (!found) {
            deliveryAddress.textContent = "";
        }

    });
</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>