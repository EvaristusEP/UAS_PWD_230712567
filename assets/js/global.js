/* -----------------------------------
   AJAX CEK USERNAME & EMAIL
----------------------------------- */

function checkAvailability(field, value, targetElementId) {
    if (value.trim() === "") {
        document.getElementById(targetElementId).innerText = "";
        return;
    }

    fetch(`check_user.php?field=${field}&value=${encodeURIComponent(value)}`)
        .then(res => res.text())
        .then(response => {
            const targetElement = document.getElementById(targetElementId);
            if (response.trim() === "EXISTS") {
                targetElement.style.color = "red";
                targetElement.innerText = `${field} sudah digunakan`;
            } else {
                targetElement.style.color = "green";
                targetElement.innerText = `${field} tersedia`;
            }
        })
        .catch(error => {
            console.error('Error checking availability:', error);
        });
}