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
  let total = 0;

  document.querySelectorAll(".cart-item").forEach((item) => {
    const price = parseFloat(item.dataset.price) || 0;
    const discount = parseFloat(item.dataset.discount) || 0;
    const qty = parseInt(item.querySelector(".item-qty").innerText) || 0;

    // Subtotal before discount
    const subtotal = price * qty;

    // Discount amount
    const discountAmount = subtotal * (discount / 100);

    // Final item total after discount
    const itemTotal = subtotal - discountAmount;

    total += itemTotal;

    // Update per-item total UI
    item.querySelector(".item-total").innerText =
      "Rs. " +
      itemTotal.toLocaleString("en-LK", {
        minimumFractionDigits: 2,
      });
  });
  const tax_amount = (total * 15) / 100;
  const deliveryFee = 1450;
  const final_total = total + tax_amount + deliveryFee;

  // Update order summary
  document.querySelector(".order-total").innerText =
    "Rs. " +
    total.toLocaleString("en-LK", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

  document.querySelector(".order-final-total").innerText =
    "Rs. " +
    final_total.toLocaleString("en-LK", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
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
