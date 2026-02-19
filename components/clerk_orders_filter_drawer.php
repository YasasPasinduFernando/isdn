<!-- Backdrop -->
<div id="filterBackdrop"
  class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 hidden">
</div>

<!-- Drawer -->
<form
  id="filterDrawer"
  method="GET"
  class="fixed top-0 right-0 h-full w-full md:w-1/3
         bg-white/60 backdrop-blur-xl shadow-2xl z-50
         transform translate-x-full transition-transform duration-300
         flex flex-col">

  <!-- Header -->
  <div class="flex items-center justify-between p-5 border-b border-white/40">
    <button type="button" id="closeFilter"
      class="text-2xl font-bold text-gray-700 hover:text-gray-900">
      ×
    </button>
    <h2 class="text-lg font-bold text-gray-800">Order Filters</h2>
    <div></div>
  </div>

  <!-- Content -->
  <div class="flex-1 overflow-y-auto p-5 space-y-6">

    <!-- Customer -->
    <div>
      <label class="text-sm font-semibold text-gray-700">
        Customer
      </label>
      <input name="customer" type="text"
        placeholder="Enter customer name"
        class="mt-2 w-full rounded-lg border px-3 py-2
               focus:ring-2 focus:ring-teal-500 focus:outline-none">
    </div>
        <div>
      <label class="text-sm font-semibold text-gray-700">
        Sales Ref.
      </label>
      <input name="sales-ref" type="text"
        placeholder="Enter sales ref. name"
        class="mt-2 w-full rounded-lg border px-3 py-2
               focus:ring-2 focus:ring-teal-500 focus:outline-none">
    </div>

    <!-- Order Date Range -->
    <div>
      <label class="text-sm font-semibold text-gray-700">
        Order Date Range
      </label>
      <div class="grid grid-cols-2 gap-3 mt-2">
        <input name="order_date_from" type="date"
          class="rounded-lg border px-3 py-2 w-full
                 focus:ring-2 focus:ring-teal-500 focus:outline-none">
        <input name="order_date_to" type="date"
          class="rounded-lg border px-3 py-2 w-full
                 focus:ring-2 focus:ring-teal-500 focus:outline-none">
      </div>
    </div>

    <!-- Order Amount Range -->
    <div>
      <label class="text-sm font-semibold text-gray-700">
        Order Amount Range (Rs)
      </label>
      <div class="grid grid-cols-2 gap-3 mt-2">
        <input name="order_amount_min" type="number" min="0" step="0.01"
          placeholder="Min Amount"
          class="rounded-lg border px-3 py-2 w-full
                 focus:ring-2 focus:ring-teal-500 focus:outline-none">
        <input name="order_amount_max" type="number" min="0" step="0.01"
          placeholder="Max Amount"
          class="rounded-lg border px-3 py-2 w-full
                 focus:ring-2 focus:ring-teal-500 focus:outline-none">
      </div>
    </div>

    <!-- Order Status -->
    <div>
      <label class="text-sm font-semibold text-gray-700">
        Order Status
      </label>
      <select name="order_status"
        class="mt-2 w-full rounded-lg border px-3 py-2
               focus:ring-2 focus:ring-teal-500 focus:outline-none">
        <option value="">All Statuses</option>
        <option value="Pending">Pending</option>
        <option value="Processing">Processing</option>
        <option value="Ready to Deliver">Ready to Deliver</option>
        <option value="In Transit">In Transit</option>
        <option value="Delivered">Delivered</option>
        <option value="Cancelled">Cancelled</option>
      </select>
    </div>

  </div>

  <!-- Footer -->
  <div class="p-5 border-t border-white/40 flex gap-3">
    <button type="reset"
      class="w-1/2 border rounded-xl py-2 font-semibold
             hover:bg-gray-100 transition">
      Clear
    </button>
    <button type="submit"
      class="w-1/2 bg-teal-600 text-white rounded-xl py-2 font-semibold
             hover:bg-teal-700 transition">
      Apply
    </button>
  </div>

</form>
