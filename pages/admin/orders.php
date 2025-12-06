<?php
// admin/orders.php - Manajemen Pesanan
// Disini admin bisa liat semua pesanan dan ubah statusnya (pending, completed, dll)

include '../../database.php'; // koneksi database
session_start(); // mulai session

// Cek admin atau bukan
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    redirect('../login.php');
}

$success = ''; // pesan sukses
$error = ''; // pesan error

// Ambil pesan dari session (setelah redirect)
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Proses update status pesanan (misal dari pending jadi completed)
if (isset($_POST['update_status'])) {
    $order_id = ($_POST['order_id']); // ID pesanan yang mau diupdate
    $status = ($_POST['status']); // status baru (pending, completed, cancelled)
    
    // Update status di database
    $query = "UPDATE orders SET status='$status' WHERE id='$order_id'";
    if (mysqli_query($db, $query)) {
        $_SESSION['success'] = "Status pesanan berhasil diupdate!";
        header('Location: orders.php');
        exit();
    } else {
        $_SESSION['error'] = "Gagal update status: " . mysqli_error($db);
        header('Location: orders.php');
        exit();
    }
}

// Ambil semua pesanan dari database, join sama data user yang pesan
$orders = mysqli_query($db, "SELECT o.*, u.username, u.full_name, u.email,
                               (SELECT COUNT(*) FROM order_details WHERE order_id = o.id) as total_items
                               FROM orders o 
                               JOIN users u ON o.user_id = u.id 
                               ORDER BY o.order_date DESC"); // urutkan dari yang terbaru
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan - Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <?php include "../../layout/adminHeader.html" ?>
    
    <div class="container">        
        <div class="card">
            <h2>Daftar Pesanan</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                        <tr>
                            <td><strong>#<?php echo $order['id']; ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($order['full_name']); ?>
                                <br>
                                <small style="color: #999;">@<?php echo htmlspecialchars($order['username']); ?></small>
                            </td>
                            <td><?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></td>
                            <td><?php echo $order['total_items']; ?> item</td>
                            <td><strong>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></strong></td>
                            <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="#" class="btn-detail" 
                                   onclick="viewOrderDetail(<?php echo $order['id']; ?>)">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal Detail Order -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detail Pesanan</h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <div id="orderDetails"></div>
        </div>
    </div>
    
    <script src="../../assets/js/admin.js"></script>
    <script>
        // Show toast notification if there's a message
        <?php if ($success): ?>
            showToast('<?php echo addslashes($success); ?>', 'success');
        <?php endif; ?>
        
        <?php if ($error): ?>
            showToast('<?php echo addslashes($error); ?>', 'error');
        <?php endif; ?>
        
        function viewOrderDetail(orderId) {
            // Fetch order details via AJAX
            fetch('get_order_detail.php?id=' + orderId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('orderDetails').innerHTML = data;
                    document.getElementById('orderModal').classList.add('active');
                })
                .catch(error => console.error('Error:', error));
        }
        
        function closeModal() {
            document.getElementById('orderModal').classList.remove('active');
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
