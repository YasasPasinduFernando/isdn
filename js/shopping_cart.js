document.addEventListener("click", function (e) {
  /* ---------------- PLUS / MINUS ---------------- */
  const isPlus = e.target.innerText === "+";
  const isMinus = e.target.innerText === "-";

  if (isPlus || isMinus) {
    const item = e.target.closest(".cart-item");
    if (!item) return;

    const qtyEl = item.querySelector(".item-qty");
    let qty = parseInt(qtyEl.innerText);

    qty = isPlus ? qty + 1 : qty - 1;
    if (qty < 0) qty = 0;

    qtyEl.innerText = qty;

    updateCart(item.dataset.id, qty);

    if (qty === 0) {
      item.remove();
    }

    calculateTotal();
  }

  /* ---------------- REMOVE BUTTON ---------------- */
  if (e.target.closest(".material-symbols-rounded")) {
    const item = e.target.closest(".cart-item");
    if (!item) return;

    updateCart(item.dataset.id, 0);
    item.remove();
    calculateTotal();
  }
});

/* ---------------- AJAX UPDATE ---------------- */
function updateCart(productId, qty) {
  fetch("index.php?page=cart&action=update", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      product_id: productId,
      qty: qty,
    }),
  }).catch((err) => console.error("Cart update failed", err));
}

/* ---------------- TOTAL CALCULATION ---------------- */
function calculateTotal() {
  let subTotal = 0;
  let discountedTotal = 0;
  let totalDiscount = 0;

  document.querySelectorAll(".cart-item").forEach((item) => {
    const price = parseFloat(item.dataset.price) || 0;
    const discount = parseFloat(item.dataset.discount) || 0;
    const discountQty = parseFloat(item.dataset.discountQty) || 0;
    const qty = parseInt(item.querySelector(".item-qty").innerText) || 0;

    const badge = item.querySelector(".promotion-badge");

    const lineAmount = price * qty;
    const isPromo = discountQty > 0 && qty >= discountQty && discount > 0;

    const discountAmount = isPromo ? lineAmount * (discount / 100) : 0;
    const itemTotal = lineAmount - discountAmount;

    // Accumulate totals
    subTotal += lineAmount;
    discountedTotal += itemTotal;
    totalDiscount += discountAmount;

    // Update badge UI safely
    if (badge) {
      badge.classList.toggle("bg-yellow-500/90", isPromo);
      badge.classList.toggle("bg-gray-400", !isPromo);
      badge.innerText = isPromo ? discount + "% OFF" : "";
    }

    // Update per-item total
    item.querySelector(".item-total").innerText =
      "Rs. " + itemTotal.toLocaleString("en-LK", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
  });

  const taxRate = 15;
  const taxAmount = (discountedTotal * taxRate) / 100;

  const deliveryFee = 1450;

  const finalTotal = discountedTotal + taxAmount + deliveryFee;

  // Update summary
  document.querySelector(".order-total").innerText =
    "Rs. " + subTotal.toLocaleString("en-LK", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

  document.querySelector(".order-final-total").innerText =
    "Rs. " + finalTotal.toLocaleString("en-LK", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
}

/* ---------------- CLEAR CART ---------------- */
function clearCart() {
  const confirmed = confirm(
    "Are you sure you want to remove all items from your cart?",
  );

  if (!confirmed) return;
  fetch("index.php?page=cart&action=clear", { method: "POST" }).then(() =>
    location.reload(),
  );
}

document.addEventListener("DOMContentLoaded", () => {
  calculateTotal();
});
