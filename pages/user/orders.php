<?php
// orders.php - Halaman daftar pesanan user
include '../../database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    redirect('../../login.php');
}

// Hitung item di keranjang
$cart_items = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_items += $item['quantity'];
    }
}

$user_id = $_SESSION['user_id'];

// Ambil semua pesanan user
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
        
        .navbar-links {
            display: flex;
            gap: 20px;
        }
        
        .navbar-links a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .navbar-links a:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .order-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .order-id {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .order-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 14px;
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
        
        .order-body {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .order-info {
            color: #666;
        }
        
        .order-info strong {
            color: #333;
            display: block;
            margin-bottom: 5px;
        }
        
        .order-total {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
        }
        
        .view-details {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .view-details:hover {
            transform: scale(1.05);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .empty-state h3 {
            color: #666;
            font-size: 20px;
            margin-bottom: 10px;
        }
        
        .empty-state a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include "../../layout/userheader.php" ?>
    
    <div class="container">
        <h2>📦 Riwayat Pesanan Saya</h2>
        
        <?php if (mysqli_num_rows($orders) > 0): ?>
            <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">
                            📋 Order #<?php echo $order['id']; ?>
                        </div>
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
                    
                    <div class="order-body">
                        <div class="order-info">
                            <strong>📅 Tanggal Pesanan</strong>
                            <?php echo date('d F Y, H:i', strtotime($order['order_date'])); ?>
                        </div>
                        
                        <div class="order-info">
                            <strong>💳 Metode Pembayaran</strong>
                            <?php echo htmlspecialchars($order['payment_method']); ?>
                        </div>
                        
                        <div class="order-info">
                            <strong>📦 Total Item</strong>
                            <?php echo $order['total_items']; ?> item
                        </div>
                        
                        <div class="order-info">
                            <strong>💰 Total Pembayaran</strong>
                            <div class="order-total">
                                Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="view-details">
                        👁️ Lihat Detail
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>😔 Belum ada pesanan</h3>
                <p>Anda belum pernah melakukan pemesanan.</p>
                <a href="index.php">🛒 Mulai Belanja</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>