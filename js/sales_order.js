document.addEventListener("DOMContentLoaded", () => {
  // ---------- Checkout Button ----------
  const checkoutBtn = document.getElementById("checkoutBtn");

  if (checkoutBtn) {
    checkoutBtn.addEventListener("click", (e) => {
      e.preventDefault(); // if inside a form

      const items = Array.from(document.querySelectorAll(".cart-item"))
        .map((item) => {
          const qtyEl = item.querySelector(".item-qty");
          const qtyText = qtyEl ? qtyEl.textContent.trim() : "0";

          return {
            product_id: Number(item.dataset.id),
            price: Number(item.dataset.price),
            qty: parseInt(qtyText, 10) || 0,
          };
        })
        .filter((i) => i.product_id > 0 && i.qty > 0);

      if (items.length === 0) {
        alert("Cart is empty");
        return;
      }

      fetch("index.php?page=sales-orders&action=place", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ items }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            window.location.href =
              "views/customer/payment.php?order_id=" +
              encodeURIComponent(data.order_id);
          } else {
            alert(data.message || "Order failed");
          }
        })
        .catch((err) => {
          console.error(err);
          alert("Something went wrong");
        });
    });
  }

  // ---------- Change Status Button ----------
  (function initChangeStatus() {
    const btn = document.getElementById("changeStatusBtn");
    const statusSelect = document.getElementById("order_status");
    const orderIdEl = document.getElementById("order_id");
    const toastContainer = document.getElementById("toastContainer");

    if (!btn || !statusSelect || !orderIdEl || !toastContainer) return;

    function scrollToTopSmooth() {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    }

    function showToast(type, message, duration = 4000) {
      const styles =
        type === "success"
          ? "border-emerald-200 bg-emerald-50 text-emerald-800"
          : "border-red-200 bg-red-50 text-red-800";

      const icon = type === "success" ? "check_circle" : "error";

      const el = document.createElement("div");
      el.className = `mt-3 flex items-start gap-3 rounded-xl border px-4 py-3 shadow-sm ${styles}`;
      el.innerHTML = `
      <span class="material-symbols-rounded">${icon}</span>
      <div class="flex-1">
        <p class="text-sm font-semibold">${message}</p>
      </div>
    `;

      toastContainer.appendChild(el);

      // Scroll to top ONLY on error
      if (type === "error") {
        scrollToTopSmooth();
      }

      setTimeout(() => el.remove(), duration);
    }

    async function updateOrderStatus(e) {
      e.preventDefault();

      const orderId = orderIdEl.dataset.orderId || orderIdEl.textContent.trim();
      const orderStatus = statusSelect.value.trim();

      if (!orderId) {
        showToast("error", "Order ID not found.");
        return;
      }

      if (!orderStatus) {
        showToast("error", "Please select a status.");
        return;
      }

      btn.disabled = true;
      btn.classList.add("opacity-70", "cursor-not-allowed");

      try {
        const response = await fetch(
          "index.php?page=rdc-clerk-sales-orders&action=update",
          {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              order_id: Number(orderId),
              order_status: orderStatus,
            }),
          },
        );

        const data = await response.json().catch(() => null);

        if (!data) {
          showToast("error", "Unexpected server response.");
          return;
        }

        if (data.success) {
          window.location.href = "index.php?page=rdc-clerk-sales-orders";
        } else {
          showToast("error", data.message || "Failed to update order.");
        }
      } catch (error) {
        console.error(error);
        showToast("error", "Network error. Please try again.");
      } finally {
        btn.disabled = false;
        btn.classList.remove("opacity-70", "cursor-not-allowed");
      }
    }

    btn.addEventListener("click", updateOrderStatus);
  })();
});
