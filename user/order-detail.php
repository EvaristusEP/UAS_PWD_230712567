<?php
include '../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    redirect('orders.php');
}

$order_id = ($_GET['id']);

$order_query = "SELECT * FROM orders WHERE id='$order_id' AND user_id='$user_id'";
$order_result = mysqli_query($db, $order_query);

if (mysqli_num_rows($order_result) == 0) {
    redirect('orders.php');
}

$order = mysqli_fetch_assoc($order_result);

$details_query = "SELECT od.*, m.name, m.category, m.description 
                 FROM order_details od
                 JOIN medicines m ON od.medicine_id = m.id
                 WHERE od.order_id = '$order_id'";
$details = mysqli_query($db, $details_query);

// Hitung item di keranjang
$cart_items = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_items += $item['quantity'];
    }
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?php echo $order_id; ?> - Apotek Online</title>
    <link rel="stylesheet" href="../assets/css/user.css">

     <script src="../assets/js/global.js"></script>
    <script src="../assets/js/user.js"></script>
</head>
<body>
    <?php include "../layout/userheader.php" ?>
    
    <div class="container">
        <div class="detail-card">
            <div class="order-header">
                <div class="order-id">Order #<?php echo $order['id']; ?></div>
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
            
            <div class="info-grid">
                <div class="info-item">
                    <strong>Tanggal Pesanan</strong>
                    <span><?php echo date('d F Y, H:i', strtotime($order['order_date'])); ?></span>
                </div>
                
                <div class="info-item">
                    <strong>Metode Pembayaran</strong>
                    <span><?php echo htmlspecialchars($order['payment_method']); ?></span>
                </div>
            </div>
            
            <h3>Item Pesanan</h3>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Obat</th>
                        <th style="text-align: center;">Jumlah</th>
                        <th style="text-align: right;">Harga Satuan</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $subtotal_all = 0;
                    while ($item = mysqli_fetch_assoc($details)): 
                        $subtotal = $item['quantity'] * $item['price_at_purchase'];
                        $subtotal_all += $subtotal;
                    ?>
                        <tr>
                            <td>
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-category"><?php echo htmlspecialchars($item['category']); ?></div>
                            </td>
                            <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                            <td style="text-align: right;">
                                Rp <?php echo number_format($item['price_at_purchase'], 0, ',', '.'); ?>
                            </td>
                            <td style="text-align: right; font-weight: 600;">
                                Rp <?php echo number_format($subtotal, 0, ',', '.'); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div class="total-section">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>Rp <?php echo number_format($subtotal_all, 0, ',', '.'); ?></span>
                </div>
                <div class="total-row grand">
                    <span>Total Pembayaran:</span>
                    <span>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></span>
                </div>
            </div>
            
            <a href="orders.php" class="back-button">← Kembali ke Daftar Pesanan</a>
        </div>
    </div>
</body>
</html>