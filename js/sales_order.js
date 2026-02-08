document.addEventListener('DOMContentLoaded', () => {

    const checkoutBtn = document.getElementById('checkoutBtn');
    if (!checkoutBtn) return;

    checkoutBtn.addEventListener('click', () => {

        const items = [];

        document.querySelectorAll('.cart-item').forEach(item => {
            items.push({
                product_id: item.dataset.id,
                price: parseFloat(item.dataset.price),
                qty: parseInt(item.querySelector('.item-qty').innerText)
            });
        });

        if (items.length === 0) {
            alert('Cart is empty');
            return;
        }

        fetch('index.php?page=sales-orders&action=place', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ items })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'views/customer/payment.php?order_id=' + data.order_id;
            } else {
                alert(data.message || 'Order failed');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Something went wrong');
        });
    });
});