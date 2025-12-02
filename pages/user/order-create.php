<?php
// order-create.php - Form untuk membuat pesanan
include '../../database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Ambil ID obat dari URL
if (!isset($_GET['medicine_id'])) {
    redirect('index.php');
}

$medicine_id = ($_GET['medicine_id']);

// Ambil data obat
$query = "SELECT * FROM medicines WHERE id='$medicine_id'";
$result = mysqli_query($db, $query);

if (mysqli_num_rows($result) == 0) {
    redirect('index.php');
}

$medicine = mysqli_fetch_assoc($result);

// Proses pembuatan pesanan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quantity = ($_POST['quantity']);
    $payment_method = ($_POST['payment_method']);
    
    // Validasi
    if (empty($quantity) || $quantity <= 0) {
        $error = "Jumlah harus lebih dari 0!";
    } elseif ($quantity > $medicine['stock']) {
        $error = "Stok tidak mencukupi! Stok tersedia: " . $medicine['stock'];
    } elseif (empty($payment_method)) {
        $error = "Pilih metode pembayaran!";
    } else {
        // Hitung total harga
        $total_price = $medicine['price'] * $quantity;
        
        // Mulai transaksi
        mysqli_begin_transaction($db);
        
        try {
            // Insert ke tabel orders
            $order_query = "INSERT INTO orders (user_id, order_date, total_price, payment_method, status) 
                           VALUES ('$user_id', NOW(), '$total_price', '$payment_method', 'completed')";
            
            if (!mysqli_query($db, $order_query)) {
                throw new Exception("Error insert order: " . mysqli_error($db));
            }
            
            // Ambil ID order yang baru dibuat
            $order_id = mysqli_insert_id($db);
            
            // Insert ke tabel order_details
            $detail_query = "INSERT INTO order_details (order_id, medicine_id, quantity, price_at_purchase) 
                            VALUES ('$order_id', '$medicine_id', '$quantity', '{$medicine['price']}')";
            
            if (!mysqli_query($db, $detail_query)) {
                throw new Exception("Error insert order detail: " . mysqli_error($db));
            }
            
            // Update stok obat
            $new_stock = $medicine['stock'] - $quantity;
            $update_stock = "UPDATE medicines SET stock='$new_stock' WHERE id='$medicine_id'";
            
            if (!mysqli_query($db, $update_stock)) {
                throw new Exception("Error update stock: " . mysqli_error($db));
            }
            
            // Commit transaksi
            mysqli_commit($db);
            
            $success = "Pesanan berhasil dibuat! Terima kasih telah berbelanja.";
            header("refresh:2;url=orders.php");
            
        } catch (Exception $e) {
            // Rollback jika ada error
            mysqli_rollback($db);
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pesanan - Apotek Online</title>
    <link rel="stylesheet" href="../../assets/css/user.css">
</head>
<body>
    <?php include "../../layout/userheader.php" ?>
    
    <div class="container">
        <div class="order-card">
            <h2>Buat Pesanan Baru</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="medicine-info">
                <h3><?php echo htmlspecialchars($medicine['name']); ?></h3>
                <p><strong>Kategori:</strong> <?php echo htmlspecialchars($medicine['category']); ?></p>
                <p><strong>Deskripsi:</strong> <?php echo htmlspecialchars($medicine['description']); ?></p>
                <p><strong>Stok Tersedia:</strong> <?php echo $medicine['stock']; ?> unit</p>
                <div class="price-big">Rp <?php echo number_format($medicine['price'], 0, ',', '.'); ?> / unit</div>
            </div>
            
            <form method="POST" action="" id="orderForm">
                <div class="form-group">
                    <label for="quantity">Jumlah *</label>
                    <input type="number" id="quantity" name="quantity" min="1" 
                           max="<?php echo $medicine['stock']; ?>" value="1" required 
                           onchange="updateTotal()">
                    <small style="color: #999;">Maksimal: <?php echo $medicine['stock']; ?> unit</small>
                </div>
                
                <div class="form-group">
                    <label for="payment_method">Metode Pembayaran *</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="">-- Pilih Metode Pembayaran --</option>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="E-Wallet">E-Wallet (OVO/GoPay/Dana)</option>
                        <option value="COD">Bayar di Tempat (COD)</option>
                        <option value="Kartu Kredit">Kartu Kredit</option>
                    </select>
                </div>
                
                <div class="total-section">
                    <h3>Total Pembayaran:</h3>
                    <div class="total-price" id="totalPrice">
                        Rp <?php echo number_format($medicine['price'], 0, ',', '.'); ?>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="button" class="btn-secondary" onclick="window.location.href='index.php'">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary">
                        Buat Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const pricePerUnit = <?php echo $medicine['price']; ?>;
        
        function updateTotal() {
            const quantity = document.getElementById('quantity').value;
            const total = pricePerUnit * quantity;
            
            document.getElementById('totalPrice').textContent = 
                'Rp ' + total.toLocaleString('id-ID');
        }
    </script>
</body>
</html>