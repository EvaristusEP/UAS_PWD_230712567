/* -----------------------------------
     AJAX ADD TO CART
----------------------------------- */

document.addEventListener("submit", function (e) {
    if (e.target.classList.contains("add-to-cart-form")) {
        e.preventDefault();

        let form = e.target;
        let id = form.querySelector("input[name='medicine_id']").value;
        let qty = form.querySelector("input[name='quantity']").value;

        fetch("ajax_add_cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${id}&qty=${qty}`
        })
        .then(res => res.text())
        .then(response => {
            if (response === "OK") {
                showToast("Berhasil ditambahkan ke keranjang");
            } else {
                showToast("Gagal menambah ke keranjang!");
            }
        });
    }
});

function updateCartBadge() {
    fetch("ajax_add_cart.php")
        .then(res => res.text())
        .then(count => {
            document.getElementById("cart-count").innerText = count;
        });
}
