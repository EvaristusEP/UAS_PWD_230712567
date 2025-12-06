document.addEventListener("submit", function (e) {
    if (!e.target.classList.contains("add-to-cart-form")) return;

    e.preventDefault();

    const form = e.target;
    const id = form.querySelector("input[name='medicine_id']").value;
    const qty = form.querySelector("input[name='quantity']").value;

    fetch("../api/add_to_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}&qty=${qty}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "OK") {

            const badge = document.getElementById("cart-count");
            if (badge) badge.innerText = data.count;

            showPopup("Berhasil ditambahkan ke keranjang", "success");

            form.querySelector("input[name='quantity']").value = 1;
        } else {
            showPopup(data.message || "Gagal menambah ke keranjang", "error");
        }
    })
    .catch(err => {
        console.error(err);
        showPopup("Terjadi kesalahan", "error");
    });
});
