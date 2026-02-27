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

    if (!confirm("Are you sure you want to place this order?")) {
      return;
    }

    const method = selectedPayment.value;
    const deliveryNotes = document.getElementById("deliveryNotes").value.trim();

    // If payment method is CASH → Send AJAX POST
    fetch("index.php?page=sales-orders&method=" + method, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        delivery_notes: deliveryNotes,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          //alert("Order placed successfully!");
          window.location.href = "index.php?page=payment-success";
        } else {
          alert("Something went wrong. Please try again.");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Server error. Please try again.");
      });
  });
});
