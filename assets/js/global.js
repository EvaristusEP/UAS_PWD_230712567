/* -----------------------------------
   AJAX CEK USERNAME & EMAIL
----------------------------------- */

function checkAvailability(field, value, targetElementId) {
    if (value.trim() === "") {
        document.getElementById(targetElementId).innerText = "";
        return;
    }

    fetch(`../api/check_user.php?field=${field}&value=${encodeURIComponent(value)}`)
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

/* -----------------------------------
   POPUP NOTIFICATION
----------------------------------- */

function showPopup(message, type) {
    const popup = document.createElement('div');
    popup.className = 'popup-message ' + type;
    popup.textContent = message;
    popup.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: ${type === 'error' ? '#dc3545' : '#28a745'};
        color: white;
        padding: 20px 30px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        z-index: 9999;
        animation: slideDown 0.3s ease-out;
        max-width: 400px;
        text-align: center;
        font-size: 16px;
    `;
    
    document.body.appendChild(popup);
    
    setTimeout(() => {
        popup.style.animation = 'slideUp 0.3s ease-out';
        setTimeout(() => popup.remove(), 300);
    }, 3000);
}
document.head.appendChild(style);