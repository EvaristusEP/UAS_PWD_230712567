<?php
// order-detail.php - Halaman detail pesanan
include '../../database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Ambil ID order dari URL
if (!isset($_GET['id'])) {
    redirect('orders.php');
}

$order_id = ($_GET['id']);

// Ambil data order
$order_query = "SELECT * FROM orders WHERE id='$order_id' AND user_id='$user_id'";
$order_result = mysqli_query($db, $order_query);

if (mysqli_num_rows($order_result) == 0) {
    redirect('orders.php');
}

$order = mysqli_fetch_assoc($order_result);

// Ambil detail items order
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
            max-width: 1200px;
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
        
        .navbar-links a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
        }
        
        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .detail-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        
        .order-id {
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }
        
        .order-status {
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-item strong {
            display: block;
            color: #666;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .info-item span {
            color: #333;
            font-size: 16px;
        }
        
        h3 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .items-table th {
            background: #f9f9f9;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #eee;
        }
        
        .items-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #eee;
            color: #666;
        }
        
        .item-name {
            color: #333;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .item-category {
            font-size: 12px;
            color: #999;
        }
        
        .total-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .total-row.grand {
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
            padding-top: 10px;
            border-top: 2px solid #ddd;
        }
        
        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .back-button:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include "../../layout/userheader.php" ?>
    
    <div class="container">
        <div class="detail-card">
            <div class="order-header">
                <div class="order-id">📋 Order #<?php echo $order['id']; ?></div>
                <div class="order-status status-<?php echo $order['status']; ?>">
                    <?php 
                    $status_text = [
                        'completed' => '✅ Selesai',
                        'pending' => '⏳ Menunggu',
                        'cancelled' => '❌ Dibatalkan'
                    ];
                    echo $status_text[$order['status']] ?? $order['status'];
                    ?>
                </div>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <strong>📅 Tanggal Pesanan</strong>
                    <span><?php echo date('d F Y, H:i', strtotime($order['order_date'])); ?></span>
                </div>
                
                <div class="info-item">
                    <strong>💳 Metode Pembayaran</strong>
                    <span><?php echo htmlspecialchars($order['payment_method']); ?></span>
                </div>
            </div>
            
            <h3>🛒 Item Pesanan</h3>
            
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