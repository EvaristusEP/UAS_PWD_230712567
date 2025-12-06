document.addEventListener("submit", function (e) {
    if (!e.target.classList.contains("add-to-cart-form")) return;

    e.preventDefault();

    const form = e.target;
    const id = form.querySelector("input[name='medicine_id']").value;
    const qty = form.querySelector("input[name='quantity']").value;

    fetch("ajax_add_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}&qty=${qty}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "OK") {

            // Update badge secara sederhana
            const badge = document.getElementById("cart-count");
            if (badge) badge.innerText = data.count;

            // Pesan sederhana
            alert("Berhasil ditambahkan ke keranjang");

            // Reset quantity
            form.querySelector("input[name='quantity']").value = 1;
        } else {
            alert("Gagal menambah ke keranjang");
        }
    })
    .catch(err => console.error(err));
});
