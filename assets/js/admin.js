/* =========================================================
   1. Toast Notification
   ========================================================= */
function showToast(msg, type = "success") {
    const toast = document.createElement("div");
    toast.className = `toast ${type}`;
    toast.innerText = msg;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add("show");
    }, 100);

    setTimeout(() => {
        toast.classList.remove("show");
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}

function confirmDelete(id) {
    const box = document.createElement("div");
    box.className = "confirm-overlay";

    box.innerHTML = `
        <div class="confirm-box">
            <h3>Hapus Obat?</h3>
            <p>Data yang dihapus tidak bisa dikembalikan.</p>
            <div class="confirm-actions">
                <button class="btn-cancel">Batal</button>
                <button class="btn-delete" data-id="${id}">Hapus</button>
            </div>
        </div>
    `;

    document.body.appendChild(box);

    box.querySelector(".btn-cancel").onclick = () => box.remove();

    box.querySelector(".btn-delete").onclick = () => {
        window.location = "?delete=" + id;
    };
}