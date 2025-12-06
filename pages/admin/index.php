<?php
// admin/index.php - Dashboard admin
// Ini halaman utama admin, isinya statistik dan ringkasan sistem

include '../../database.php'; // koneksi database

session_start(); // mulai session

// Cek dulu, yang login admin apa bukan? Kalo bukan admin, gak boleh masuk!
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    redirect('../login.php');
}

// Ambil statistik buat ditampilin di card-card
$total_users = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM users WHERE role='user'"))['total']; // jumlah user biasa (bukan admin)
$total_medicines = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM medicines"))['total']; // jumlah obat
$total_orders = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM orders"))['total']; // jumlah pesanan
$total_revenue = mysqli_fetch_assoc(mysqli_query($db, "SELECT SUM(total_price) as total FROM orders WHERE status='completed'"))['total'] ?? 0; // total pendapatan (cuma yang udah completed)

// Ambil 5 pesanan terbaru buat ditampilin di tabel
$recent_orders = mysqli_query($db, "SELECT o.*, u.username, u.full_name 
                                      FROM orders o 
                                      JOIN users u ON o.user_id = u.id 
                                      ORDER BY o.order_date DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Apotek Online</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <?php include "../../layout/adminHeader.html" ?>
    
    <div class="container">
        <div class="welcome">
            <h2>Selamat Datang, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
            <p>Berikut adalah ringkasan sistem apotek online Anda.</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon"></div>
                <div class="stat-label">Total Pengguna</div>
                <div class="stat-value"><?php echo $total_users; ?></div>
            </div>
            
            <div class="stat-card green">
                <div class="stat-icon"></div>
                <div class="stat-label">Total Obat</div>
                <div class="stat-value"><?php echo $total_medicines; ?></div>
            </div>
            
            <div class="stat-card orange">
                <div class="stat-icon"></div>
                <div class="stat-label">Total Pesanan</div>
                <div class="stat-value"><?php echo $total_orders; ?></div>
            </div>
            
            <div class="stat-card purple">
                <div class="stat-icon"></div>
                <div class="stat-label">Total Pendapatan</div>
                <div class="stat-value">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></div>
            </div>
        </div>
        
        <div class="recent-orders">
            <h3>Pesanan Terbaru</h3>
            
            <?php if (mysqli_num_rows($recent_orders) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($order['full_name']); ?>
                                    <br>
                                    <small style="color: #999;">@<?php echo htmlspecialchars($order['username']); ?></small>
                                </td>
                                <td><?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></td>
                                <td><strong>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <a href="orders.php" class="view-all">Lihat Semua Pesanan →</a>
            <?php else: ?>
                <p style="color: #999; text-align: center; padding: 40px 0;">Belum ada pesanan.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>