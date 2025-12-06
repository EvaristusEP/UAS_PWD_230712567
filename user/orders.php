<?php
include '../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    redirect('../auth/login.php');
}

$cart_items = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_items += $item['quantity'];
    }
}

$user_id = $_SESSION['user_id'];

$query = "SELECT o.*, 
          (SELECT COUNT(*) FROM order_details WHERE order_id = o.id) as total_items
          FROM orders o 
          WHERE o.user_id = '$user_id' 
          ORDER BY o.order_date DESC";
$orders = mysqli_query($db, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Apotek Online</title>
    <link rel="stylesheet" href="../assets/css/user.css">

     <script src="../assets/js/global.js"></script>
    <script src="../assets/js/user.js"></script>
</head>
<body>
    <?php include "../layout/userheader.php" ?>
    
    <div class="container">
        <h2>Riwayat Pesanan Saya</h2>
        
        <?php if (mysqli_num_rows($orders) > 0): ?>
            <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">
                            Order #<?php echo $order['id']; ?>
                        </div>
                        <div class="order-status status-<?php echo $order['status']; ?>">
                            <?php 
                            $status_text = [
                                'completed' => 'Selesai',
                                'pending' => 'Menunggu',
                                'cancelled' => 'Dibatalkan'
                            ];
                            echo $status_text[$order['status']] ?? $order['status'];
                            ?>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <div class="order-info">
                            <strong>Tanggal Pesanan</strong>
                            <?php echo date('d F Y, H:i', strtotime($order['order_date'])); ?>
                        </div>
                        
                        <div class="order-info">
                            <strong>Metode Pembayaran</strong>
                            <?php echo htmlspecialchars($order['payment_method']); ?>
                        </div>
                        
                        <div class="order-info">
                            <strong>Total Item</strong>
                            <?php echo $order['total_items']; ?> item
                        </div>
                        
                        <div class="order-info">
                            <strong>Total Pembayaran</strong>
                            <div class="order-total">
                                Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="view-details">
                        Lihat Detail
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>Belum ada pesanan</h3>
                <p>Anda belum pernah melakukan pemesanan.</p>
                <a href="index.php">Mulai Belanja</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>