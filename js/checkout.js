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
      "index.php?page=sales-orders&method=" + method + "&action=" + action,
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          delivery_notes: deliveryNotes,
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
