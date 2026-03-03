document.addEventListener("DOMContentLoaded", () => {
  const btn = document.getElementById("proceedToPayBtn");
  const mobileEl = document.getElementById("mobileNumber");
  const emailEl = document.getElementById("emailAddress");

  function setFieldError(el, message) {
    el.classList.add("border-red-400", "ring-2", "ring-red-200");
    el.setCustomValidity(message);
  }

  function clearFieldError(el) {
    el.classList.remove("border-red-400", "ring-2", "ring-red-200");
    el.setCustomValidity("");
  }

  function getSelectedPaymentMethod() {
    const selected = document.querySelector(
      'input[name="payment_method"]:checked',
    );
    return selected ? selected.value : "";
  }

  function validate() {
    const mobile = mobileEl.value.trim();
    const email = emailEl.value.trim();
    const paymentMethod = getSelectedPaymentMethod();

    let ok = true;

    // Clear previous UI errors
    clearFieldError(mobileEl);
    clearFieldError(emailEl);

    // Mobile validation (Sri Lanka friendly: 07XXXXXXXX or +94...)
    const mobileDigits = mobile.replace(/\s+/g, "");
    const mobileValid =
      /^07\d{8}$/.test(mobileDigits) ||
      /^\+94\d{9}$/.test(mobileDigits) ||
      /^94\d{9}$/.test(mobileDigits);

    if (!mobile) {
      setFieldError(mobileEl, "Mobile number is required.");
      ok = false;
    } else if (!mobileValid) {
      setFieldError(
        mobileEl,
        "Enter a valid mobile number (e.g., 07XXXXXXXX).",
      );
      ok = false;
    }

    // Email validation
    if (!email) {
      setFieldError(emailEl, "Email address is required.");
      ok = false;
    } else if (!emailEl.checkValidity()) {
      setFieldError(emailEl, "Enter a valid email address.");
      ok = false;
    }

    // Payment method required
    if (!paymentMethod) {
      alert("Please select a payment method (Visa or MasterCard).");
      ok = false;
    }

    if (!ok) {
      // Trigger native tooltip (optional)
      if (!mobileEl.checkValidity()) mobileEl.reportValidity();
      else if (!emailEl.checkValidity()) emailEl.reportValidity();
    }

    return { ok, mobile: mobileDigits, email, paymentMethod };
  }

  btn.addEventListener("click", async () => {
    const { ok, mobile, email, paymentMethod } = validate();
    if (!ok) return;

    // Disable button to prevent double clicks
    btn.disabled = true;
    btn.classList.add("opacity-70", "cursor-not-allowed");

    try {
      const res = await fetch("index.php?page=payment&action=proceed", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
        body: new URLSearchParams({
          mobile_number: mobile,
          email: email,
          payment_method: paymentMethod,
          // Add csrf_token here if you use it:
          // csrf_token: document.querySelector('input[name="csrf_token"]')?.value ?? ''
        }),
      });

      // If your endpoint returns JSON
      const data = await res.json();

      if (data.success) {
        window.location.href = "index.php?page=payment-gateway";
      } else {
        alert(data.message || "Unable to proceed. Please try again.");
      }
    } catch (err) {
      console.error(err);
      alert("Network error. Please try again.");
    } finally {
      btn.disabled = false;
      btn.classList.remove("opacity-70", "cursor-not-allowed");
    }
  });

  // Remove red styles as user types
  mobileEl.addEventListener("input", () => clearFieldError(mobileEl));
  emailEl.addEventListener("input", () => clearFieldError(emailEl));
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
    window.location.href = paymentResult.redirect;
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
