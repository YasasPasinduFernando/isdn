document.addEventListener('DOMContentLoaded', () => {

    const paymentRadios = document.querySelectorAll('.payment-method');
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const paymentError = document.getElementById('paymentError');

    function checkPaymentSelection() {
        const selected = [...paymentRadios].some(r => r.checked);

        if (selected) {
            placeOrderBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            paymentError.classList.add('hidden');
        } else {
            placeOrderBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    paymentRadios.forEach(radio => {
        radio.addEventListener('change', checkPaymentSelection);
    });

    placeOrderBtn.addEventListener('click', () => {
        const selected = [...paymentRadios].some(r => r.checked);

        if (!selected) {
            paymentError.classList.remove('hidden');
            return;
        }

        if (!confirm('Are you sure you want to place this order?')) {
            return;
        }

        // TODO: AJAX call to place order
        alert('Order placed successfully!');
        window.location.href = 'payment.php';
    });

});
