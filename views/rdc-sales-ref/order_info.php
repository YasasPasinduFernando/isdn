<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 font-['Outfit']">Order Information</h1>
                <p class="text-gray-500 mt-1">Detailed view of customer order</p>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-2">
                <button
                    class="px-4 py-2 bg-white border rounded-xl shadow-sm hover:bg-gray-50 flex items-center gap-2 text-gray-700">
                    <i class="fa-solid fa-print"></i> Print
                </button>
                <button
                    class="px-4 py-2 bg-white border rounded-xl shadow-sm hover:bg-gray-50 flex items-center gap-2 text-gray-700">
                    <i class="fa-solid fa-file-pdf"></i> Invoice
                </button>
                <button
                    class="px-4 py-2 bg-red-500 text-white rounded-xl shadow hover:bg-red-600 flex items-center gap-2">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </button>
                <button
                    class="px-4 py-2 bg-teal-600 text-white rounded-xl shadow hover:bg-teal-700 flex items-center gap-2">
                    <span class="material-symbols-rounded">location_on</span> Track
                </button>
            </div>
        </div>

        <!-- ================= Order Info ================= -->

        <div class="bg-white rounded-3xl shadow-lg p-6 border">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                <div>
                    <p class="text-xs text-gray-500 uppercase">Order Number</p>
                    <p class="font-bold text-gray-800"><?php echo $customer_order_info1['order_number']; ?></p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Date</p>
                    <p class="font-semibold text-gray-800"><?php echo $customer_order_info1['order_date']; ?></p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Customer</p>
                    <p class="font-semibold text-gray-800"><?php echo $customer_order_info1['customer_name']; ?></p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Sales Ref</p>
                    <p class="font-semibold text-gray-800"><?php echo $customer_order_info1['sales_ref']; ?></p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Total Amount</p>
                    <p class="font-bold text-teal-600 text-lg"><?php echo $customer_order_info1['total_amount']; ?></p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Estimated Delivery</p>
                    <p class="font-semibold text-gray-800">
                        <?php echo $customer_order_info1['estimated_delivery_date']; ?>
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Last Updated</p>
                    <p class="font-semibold text-gray-800"><?php echo $customer_order_info1['last_updated']; ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Order Status</p>
                    <span class="inline-flex items-center gap-2 px-3 py-1 text-sm font-bold rounded-full
                    bg-purple-100 text-purple-700 border border-purple-200">
                        <span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                        <?php echo $customer_order_info1['order_status']; ?>
                    </span>
                </div>

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
                <div class="col-span-2">Discount</div>
                <div class="col-span-1">Line Total</div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 px-5 py-5 border-t items-center hover:bg-gray-50">

                <?php foreach ($order_items as $order_item): ?>

                    <div class="font-semibold text-gray-800 col-span-2"><?php echo $order_item['product_code']; ?></div>

                    <div class="font-semibold text-gray-800 col-span-2"><?php echo $order_item['product_name']; ?></div>

                    <div class="text-gray-600 col-span-2"><?php echo $order_item['category']; ?></div>

                    <div class="font-semibold text-gray-800 col-span-2"><?php echo $order_item['unit_price']; ?></div>
                    <div class="font-semibold text-gray-800 col-span-1"><?php echo $order_item['quantity']; ?></div>

                    <div class="text-red-500 font-semibold col-span-2"><?php echo $order_item['discount_amount']; ?></div>
                    <div class="font-semibold text-gray-800 col-span-1"><?php echo $order_item['line_total']; ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="mt-10 glass-panel rounded-3xl border border-white/50 shadow-xl p-8">
            <h2 class="text-xl font-bold text-gray-800 font-['Outfit'] mb-6 flex items-center gap-2">
                <span class="material-symbols-rounded text-teal-600">sync</span>
                Update Order Status
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">

                <!-- Status Dropdown -->
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-2">
                        Select New Status
                    </label>

                    <select
                        class="w-full bg-white/70 backdrop-blur border border-gray-200 rounded-xl px-4 py-3 text-gray-800 font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 transition">
                        <option value="">-- Choose Status --</option>
                        <option value="waiting_for_stocks">Waiting for Stocks</option>
                        <option value="ready_for_delivery">Ready for Delivery</option>
                        <option value="delivery_completed">Delivery Completed</option>
                    </select>

                    <p class="text-xs text-gray-500 mt-2">
                        Changing the status will update order tracking for the customer.
                    </p>
                </div>

                <!-- Action Button -->
                <div class="flex md:justify-end">
                    <button
                        class="bg-gradient-to-r from-teal-500 to-emerald-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-teal-500/30 hover:from-teal-600 hover:to-emerald-700 transition flex items-center gap-2">
                        <span class="material-symbols-rounded">published_with_changes</span>
                        Change Status
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>