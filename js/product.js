
function changeQty(button, delta) {
        const qtySpan = button.parentElement.querySelector(".qty");
        let qty = parseInt(qtySpan.innerText, 10);

        qty = qty + delta;
        if (qty < 1) qty = 1;

        qtySpan.innerText = qty;
    }


document.querySelectorAll('.add-to-cart-btn').forEach(button => {
    button.addEventListener('click', function () {

        // Find the product card
        const card = this.closest('.glass-card');
        if (!card) return;

        // Find quantity inside that card
        const qtyEl = card.querySelector('.qty');
        const qty = qtyEl ? parseInt(qtyEl.innerText) : 1;

        const data = {
            product_id: this.dataset.id,
            qty: qty
        };

        fetch('index.php?page=cart&action=add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                alert('Item added to cart 🛒');

                if (document.getElementById('cartCount')) {
                    document.getElementById('cartCount').innerText = response.cartCount;
                }
            }
        })
        .catch(err => console.error(err));
    });
});
