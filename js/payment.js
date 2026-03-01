document.addEventListener("DOMContentLoaded", () => {
  document
    .getElementById("proceedToPayBtn")
    .addEventListener("click", function () {
      window.location.href = "index.php?page=payment-gateway";
    });
});

document.getElementById("payBtn").addEventListener("click", async function () {
  try {
    showLoader();

    // Step 1 → Process payment
    const paymentResponse = await fetch("index.php?page=payment&action=pay", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        // Add necessary payment data here
        confirm: true,
      }),
    });

    const paymentResult = await paymentResponse.json();

    if (!paymentResult.success) {
      alert(paymentResult.message || "Payment failed");
      return;
    }

    // Step 2 → Place order
    const orderResponse = await fetch(
      "index.php?page=sales-orders&method=card&action=place",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          transaction_id: paymentResult.transaction_id,
        }),
      },
    );

    const orderResult = await orderResponse.json();

    if (orderResult.success) {
      // Redirect to success page
      window.location.href = orderResult.redirect;
    } else {
      alert(orderResult.message || "Order placement failed");
    }
  } catch (error) {
    console.error(error);
    alert("Network error. Please try again.");
  } finally {
    hideLoader();
  }
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
