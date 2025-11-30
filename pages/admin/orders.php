<?php
// admin/orders.php - Manajemen Pesanan
include '../../database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    redirect('../login.php');
}

$success = '';
$error = '';

// Update status pesanan
if (isset($_POST['update_status'])) {
    $order_id = ($_POST['order_id']);
    $status = ($_POST['status']);
    
    $query = "UPDATE orders SET status='$status' WHERE id='$order_id'";
    if (mysqli_query($db, $query)) {
        $success = "Status pesanan berhasil diupdate!";
    } else {
        $error = "Gagal update status: " . mysqli_error($db);
    }
}

// Ambil semua pesanan dengan informasi user
$orders = mysqli_query($db, "SELECT o.*, u.username, u.full_name, u.email,
                               (SELECT COUNT(*) FROM order_details WHERE order_id = o.id) as total_items
                               FROM orders o 
                               JOIN users u ON o.user_id = u.id 
                               ORDER BY o.order_date DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .navbar-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar h1 {
            color: white;
            font-size: 24px;
        }
        
        .navbar-links {
            display: flex;
            gap: 20px;
        }
        
        .navbar-links a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
        }
        
        .navbar-links a:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background: #f9f9f9;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #eee;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            color: #666;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn-detail {
            padding: 6px 12px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 13px;
            display: inline-block;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal.active {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .close-modal {
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }
        
        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item strong {
            display: block;
            color: #666;
            margin-bottom: 5px;
        }
        
        .status-form {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .status-form select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
        }
        
        .status-form button {
            padding: 8px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include "../../layout/adminHeader.html" ?>
    
    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>📦 Daftar Pesanan</h2>
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
                                    👁️ Detail
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
                <h2>📋 Detail Pesanan</h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <div id="orderDetails"></div>
        </div>
    </div>
    
    <script>
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
