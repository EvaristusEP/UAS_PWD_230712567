<?php
// admin/get_order_detail.php - Fetch order detail for modal
include '../../database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die('Unauthorized');
}

if (!isset($_GET['id'])) {
    die('Invalid request');
}

$order_id = ($_GET['id']);

// Get order info
$order_query = "SELECT o.*, u.username, u.full_name, u.email, u.address 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = '$order_id'";
$order_result = mysqli_query($db, $order_query);
$order = mysqli_fetch_assoc($order_result);

// Get order items
$items_query = "SELECT od.*, m.name, m.category 
                FROM order_details od 
                JOIN medicines m ON od.medicine_id = m.id 
                WHERE od.order_id = '$order_id'";
$items = mysqli_query($db, $items_query);
?>

<div class="order-info-grid">
    <div class="info-item">
        <strong>👤 Nama Pelanggan</strong>
        <span><?php echo htmlspecialchars($order['full_name']); ?></span>
    </div>
    
    <div class="info-item">
        <strong>📧 Email</strong>
        <span><?php echo htmlspecialchars($order['email']); ?></span>
    </div>
    
    <div class="info-item">
        <strong>📅 Tanggal Pesanan</strong>
        <span><?php echo date('d F Y, H:i', strtotime($order['order_date'])); ?></span>
    </div>
    
    <div class="info-item">
        <strong>💳 Metode Pembayaran</strong>
        <span><?php echo htmlspecialchars($order['payment_method']); ?></span>
    </div>
    
    <div class="info-item" style="grid-column: 1 / -1;">
        <strong>📍 Alamat</strong>
        <span><?php echo htmlspecialchars($order['address'] ?? 'Tidak ada alamat'); ?></span>
    </div>
</div>

<h3 style="margin: 20px 0 10px 0; color: #333;">🛒 Item Pesanan</h3>

<table>
    <thead>
        <tr>
            <th>Obat</th>
            <th style="text-align: center;">Jumlah</th>
            <th style="text-align: right;">Harga</th>
            <th style="text-align: right;">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $total = 0;
        while ($item = mysqli_fetch_assoc($items)): 
            $subtotal = $item['quantity'] * $item['price_at_purchase'];
            $total += $subtotal;
        ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                    <br>
                    <small style="color: #999;"><?php echo htmlspecialchars($item['category']); ?></small>
                </td>
                <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                <td style="text-align: right;">Rp <?php echo number_format($item['price_at_purchase'], 0, ',', '.'); ?></td>
                <td style="text-align: right;"><strong>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></strong></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
    <tfoot>
        <tr style="background: #f9f9f9;">
            <td colspan="3" style="text-align: right;"><strong>Total Pembayaran:</strong></td>
            <td style="text-align: right; font-size: 18px; font-weight: 700; color: #667eea;">
                Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?>
            </td>
        </tr>
    </tfoot>
</table>

<div class="status-form">
    <strong>Update Status Pesanan:</strong>
    <form method="POST" action="orders.php" style="margin-top: 10px;">
        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
        <select name="status">
            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        </select>
        <button type="submit" name="update_status">💾 Update Status</button>
    </form>
</div>

<style>
    .order-info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 8px;
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
    
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
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
    
    .status-form {
        background: #e7f3ff;
        padding: 15px;
        border-radius: 5px;
        margin-top: 20px;
    }
</style>
