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
    <h2 class="text-lg font-bold text-gray-800">Filters</h2>
    <div></div>
  </div>

  <!-- Content -->
  <div class="flex-1 overflow-y-auto p-5 space-y-6">

    <div>
      <label class="text-sm font-semibold text-gray-700">Category</label>
      <select name="category"
        class="w-full mt-2 rounded-lg border border-gray-200 px-3 py-2">
        <option value="">All</option>
        <option value="Grocery & Food Items">Grocery & Food Items</option>
        <option value="Beverages">Beverages</option>
        <option value="Household Essentials">Household Essentials</option>
        <option value="Home Cleaning Products">Home Cleaning Products</option>
        <option value="Health Care Products">Health Care Products</option>
        <option value="Personal Care">Personal Care</option>
        <option value="Beauty & Skincare">Beauty & Skincare</option>
        <option value="Baby Care Products">Baby Care Products</option>
      </select>
    </div>

    <div>
      <label class="text-sm font-semibold text-gray-700">Price Range</label>
      <div class="grid grid-cols-2 gap-3 mt-2">
        <input name="min_price" type="number" min="0"
          placeholder="Min (Rs)"
          class="rounded-lg border px-3 py-2">
        <input name="max_price" type="number" min="0"
          placeholder="Max (Rs)"
          class="rounded-lg border px-3 py-2">
      </div>
    </div>

    <!-- <div>
      <label class="text-sm font-semibold text-gray-700">
        Minimum Stock
      </label>
      <div class="flex items-center gap-3 mt-2">
        <button type="button" class="step-down px-3 py-2 border rounded">−</button>
        <input name="min_stock" value="0" min="0"
          class="w-20 text-center border rounded">
        <button type="button" class="step-up px-3 py-2 border rounded">+</button>
      </div>
    </div> -->

  </div>

  <!-- Footer -->
  <div class="p-5 border-t flex gap-3">
    <button type="reset"
      class="w-1/2 border rounded-xl py-2 font-semibold">
      Clear
    </button>
    <button type="submit"
      class="w-1/2 bg-teal-600 text-white rounded-xl py-2 font-semibold">
      Apply
    </button>
  </div>
</form>