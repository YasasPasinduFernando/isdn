<?php
require_once __DIR__ . '/../../includes/header.php';
?>
<div id="toastContainer" class="mx-auto max-w-6xl px-4 mt-3"></div>
<div class="min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div class="flex items-center gap-3 mb-8">
                <a href="<?php echo BASE_PATH; ?>/index.php?page=rdc-clerk-sales-orders"
                    class="w-10 h-10 rounded-xl bg-white/50 border border-white/60 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-white/70 transition">
                    <span class="material-symbols-rounded">arrow_back</span>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 font-['Outfit']">Order Information</h1>
                    <p class="text-gray-500 mt-1">Detailed view of customer order</p>
                </div>
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
                    <span id="order_id" data-order-id="<?= (int) $customer_order_info['id']; ?>" class="hidden"></span>
                    <p class="text-xs text-gray-500 uppercase">Order Number</p>
                    <p class="font-bold text-gray-800"><?php echo $customer_order_info['order_number']; ?></p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Date</p>
                    <p class="font-semibold text-gray-800"><?= (new DateTime($customer_order_info['order_date']))->format('d M, Y')
                        ?></p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Customer</p>
                    <p class="font-semibold text-gray-800"><?php echo $customer_order_info['customer']; ?></p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Sales Ref</p>
                    <p class="font-semibold text-gray-800"><?= $customer_order_info['sales_ref']; ?></p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Total Amount</p>
                    <p class="font-bold text-teal-600 text-lg">
                        <?=
                            number_format($customer_order_info['total_amount'], 2);
                        ?>
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Estimated Delivery</p>
                    <p class="font-semibold text-gray-800">
                        <?= (new DateTime($customer_order_info['estimated_date']))->format('d M, Y')
                            ?>
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Last Updated</p>
                    <p class="font-semibold text-gray-800"> <?= (new DateTime($customer_order_info['updated_at']))->format('d M, Y h:i A');
                    ?></p>
                </div>
                <div>
                    <?php

                    $order_status = strtolower($customer_order_info['status'] ?? '');

                    $statusStyles = [
                        'pending' => [
                            'container' => 'bg-purple-100 text-purple-700 border-purple-200',
                            'dot' => 'bg-purple-500'
                        ],
                        'processing' => [
                            'container' => 'bg-blue-100 text-blue-700 border-blue-200',
                            'dot' => 'bg-blue-500'
                        ],
                        'delivered' => [
                            'container' => 'bg-green-100 text-green-700 border-green-200',
                            'dot' => 'bg-green-500'
                        ],
                        'in transit' => [
                            'container' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'dot' => 'bg-yellow-500'
                        ],
                        'cancelled' => [
                            'container' => 'bg-red-100 text-red-700 border-red-200',
                            'dot' => 'bg-red-500'
                        ]
                    ];

                    // Default fallback
                    $style = $statusStyles[$order_status] ?? [
                        'container' => 'bg-gray-100 text-gray-700 border-gray-200',
                        'dot' => 'bg-gray-500'
                    ];
                    ?>
                    <p class="text-xs text-gray-500 uppercase">Order Status</p>
                    <span
                        class="inline-flex items-center gap-2 px-3 py-1 text-sm font-bold rounded-full border <?= $style['container']; ?>">
                        <span class="w-2 h-2 <?= $style['dot']; ?> rounded-full"></span>
                        <?= ucwords($order_status); ?>
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
                <div class="col-span-1">RDC Stocks</div>
                <div class="col-span-1">Discount</div>
                <div class="col-span-1">Line Total</div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 px-5 py-5 border-t items-center hover:bg-gray-50">

                <?php foreach ($order_items as $order_item): ?>

                    <div class="font-semibold text-gray-800 col-span-2"><?php echo $order_item['product_code']; ?></div>

                    <div class="font-semibold text-gray-800 col-span-2"><?php echo $order_item['product_name']; ?></div>

                    <div class="text-gray-600 col-span-2"><?php echo $order_item['product_category']; ?></div>

                    <div class="font-semibold text-gray-800 col-span-2">Rs. <?php echo $order_item['unit_price']; ?></div>
                    <div class="font-semibold text-gray-800 col-span-1"><?php echo $order_item['quantity']; ?></div>
                    <div class="font-semibold text-gray-800 col-span-1"><?php echo $order_item['available_quantity']; ?>
                    </div>

                    <div class="text-red-500 font-semibold col-span-1">Rs. <?php echo $order_item['discount_amount']; ?>
                    </div>
                    <div class="font-semibold text-gray-800 col-span-1">Rs.
                        <?php echo number_format($order_item['discounted_line_amount'], 2); ?>
                    </div>
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

                    <select <?php $order_status = $customer_order_info['status'];
                    if ($order_status == 'delivered' || $order_status == 'cancelled') {
                        echo 'disabled';
                    }
                    ?> id="order_status"
                        class="w-full bg-white/70 backdrop-blur border border-gray-200 rounded-xl px-4 py-3 text-gray-800 font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 transition">
                        <option value="">-- Choose Status --</option>
                        <option value="processing">Processing</option>
                        <option value="in_transit">In transit</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>

                    <p class="text-xs text-gray-500 mt-2">
                        Changing the status will update order tracking for the customer.
                    </p>
                </div>

                <!-- Action Button -->
                <div class="flex md:justify-end">
                    <button type="button" id="changeStatusBtn"
                        class="bg-gradient-to-r from-teal-500 to-emerald-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-teal-500/30 hover:from-teal-600 hover:to-emerald-700 transition flex items-center gap-2">
                        <span class="material-symbols-rounded">published_with_changes</span>
                        Change Status
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
<?php if (!empty($_SESSION['flash_success'])): ?>
<<<<<<< HEAD
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const msg = <?= json_encode($_SESSION['flash_success']); ?>;
            const container = document.getElementById("toastContainer");
            if (container) {
                const el = document.createElement("div");
                el.className = "mt-3 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 shadow-sm";
                el.innerHTML = `
          <span class="material-symbols-rounded text-emerald-600">check_circle</span>
          <div class="flex-1"><p class="text-sm font-semibold">${msg}</p></div>
        `;
                container.appendChild(el);
                setTimeout(() => el.remove(), 5000);
            }
        });
    </script>
    <?php unset($_SESSION['flash_success']); ?>
=======
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const msg = <?= json_encode($_SESSION['flash_success']); ?>;
      const container = document.getElementById("toastContainer");
      if (container) {
        const el = document.createElement("div");
        el.className = "mt-3 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 shadow-sm";
        el.innerHTML = `
          <span class="material-symbols-rounded text-emerald-600">check_circle</span>
          <div class="flex-1"><p class="text-sm font-semibold">${msg}</p></div>
        `;
        container.appendChild(el);
        setTimeout(() => el.remove(), 5000);
      }
    });
  </script>
  <?php unset($_SESSION['flash_success']); ?>
>>>>>>> 1b724ad (Order and payment status update flow)
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>