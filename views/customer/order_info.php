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
                    class="px-4 py-2 bg-teal-600 text-white rounded-xl shadow hover:bg-teal-700 flex items-center gap-2">
                    <span class="material-symbols-rounded">location_on</span> Track
                </button>
            </div>
        </div>

        <!-- ================= Order Info ================= -->

        <div class="bg-white rounded-3xl shadow-lg p-6 border">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">

                <div>
                    <p class="text-xs text-gray-500 uppercase">Order Number</p>
                    <p class="font-bold text-gray-800"><?php echo $customer_order_info['order_number']; ?></p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Date</p>
                    <p class="font-semibold text-gray-800">
                        <?= (new DateTime($customer_order_info['order_date']))->format('d M, Y')
                            ?>
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Customer</p>
                    <p class="font-semibold text-gray-800"><?php echo $customer_order_info['name']; ?></p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Sales Ref</p>
                    <p class="font-semibold text-gray-800"><?php //echo $customer_order_info['sales_ref']; ?></p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 uppercase">Total Amount</p>
                    <p class="font-bold text-teal-600 text-lg">Rs.
                        <?php
                        echo number_format($customer_order_info['total_amount'], 2);
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
                    <p class="font-semibold text-gray-800">
                        <?= (new DateTime($customer_order_info['updated_at']))->format('d M, Y h:i A');
                        ?>
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
                <div class="col-span-2">Discount</div>
                <div class="col-span-1">Line Total</div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 px-5 py-5 border-t items-center hover:bg-gray-50">

                <?php foreach ($order_items as $order_item): ?>

                    <div class="font-semibold text-gray-800 col-span-2"><?php echo $order_item['product_code']; ?></div>

                    <div class="font-semibold text-gray-800 col-span-2"><?php echo $order_item['product_name']; ?></div>

                    <div class="text-gray-600 col-span-2"><?php echo $order_item['product_category']; ?></div>

                    <div class="font-semibold text-gray-800 col-span-2">Rs. <?php echo $order_item['unit_price']; ?></div>
                    <div class="font-semibold text-gray-800 col-span-1"><?php echo $order_item['quantity']; ?></div>

                    <div class="text-red-500 font-semibold col-span-2">Rs. <?php echo $order_item['discount_amount']; ?>
                    </div>
                    <div class="font-semibold text-gray-800 col-span-1">Rs.
                        <?php echo number_format($order_item['discounted_line_amount'], 2); ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>