<?php
// cart.php - Halaman keranjang belanja
include '../../database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    redirect('../../login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Proses tambah ke keranjang
if (isset($_POST['add_to_cart'])) {
    $medicine_id = ($_POST['medicine_id']);
    $quantity = ($_POST['quantity']);
    
    // Cek stok obat
    $check_query = "SELECT * FROM medicines WHERE id='$medicine_id'";
    $medicine = mysqli_fetch_assoc(mysqli_query($db, $check_query));
    
    if ($medicine && $quantity <= $medicine['stock']) {
        // Cek apakah obat sudah ada di keranjang
        if (isset($_SESSION['cart'][$medicine_id])) {
            $_SESSION['cart'][$medicine_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$medicine_id] = [
                'name' => $medicine['name'],
                'price' => $medicine['price'],
                'quantity' => $quantity,
                'category' => $medicine['category']
            ];
        }
        $success = "Obat berhasil ditambahkan ke keranjang!";
    } else {
        $error = "Stok tidak mencukupi!";
    }
}

// Proses update quantity
if (isset($_POST['update_cart'])) {
    $medicine_id = ($_POST['medicine_id']);
    $quantity = ($_POST['quantity']);
    
    if ($quantity > 0) {
        // Cek stok
        $check_query = "SELECT stock FROM medicines WHERE id='$medicine_id'";
        $result = mysqli_query($db, $check_query);
        $medicine = mysqli_fetch_assoc($result);
        
        if ($medicine && $quantity <= $medicine['stock']) {
            $_SESSION['cart'][$medicine_id]['quantity'] = $quantity;
            $success = "Keranjang berhasil diupdate!";
        } else {
            $error = "Stok tidak mencukupi!";
        }
    } else {
        unset($_SESSION['cart'][$medicine_id]);
        $success = "Item dihapus dari keranjang!";
    }
}

// Proses hapus item
if (isset($_GET['remove'])) {
    $medicine_id = ($_GET['remove']);
    unset($_SESSION['cart'][$medicine_id]);
    $success = "Item berhasil dihapus dari keranjang!";
}

// Proses checkout
if (isset($_POST['checkout'])) {
    if (empty($_SESSION['cart'])) {
        $error = "Keranjang belanja kosong!";
    } else {
        $payment_method = ($_POST['payment_method']);
        
        if (empty($payment_method)) {
            $error = "Pilih metode pembayaran!";
        } else {
            // Hitung total
            $total_price = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total_price += $item['price'] * $item['quantity'];
            }
            
            // Mulai transaksi
            mysqli_begin_transaction($db);
            
            try {
                // Insert ke tabel orders
                $order_query = "INSERT INTO orders (user_id, order_date, total_price, payment_method, status) 
                               VALUES ('$user_id', NOW(), '$total_price', '$payment_method', 'completed')";
                
                if (!mysqli_query($db, $order_query)) {
                    throw new Exception("Error insert order: " . mysqli_error($db));
                }
                
                $order_id = mysqli_insert_id($db);
                
                // Insert setiap item ke order_details dan update stok
                foreach ($_SESSION['cart'] as $medicine_id => $item) {
                    // Insert detail
                    $detail_query = "INSERT INTO order_details (order_id, medicine_id, quantity, price_at_purchase) 
                                    VALUES ('$order_id', '$medicine_id', '{$item['quantity']}', '{$item['price']}')";
                    
                    if (!mysqli_query($db, $detail_query)) {
                        throw new Exception("Error insert order detail: " . mysqli_error($db));
                    }
                    
                    // Update stok
                    $update_stock = "UPDATE medicines SET stock = stock - {$item['quantity']} WHERE id='$medicine_id'";
                    
                    if (!mysqli_query($db, $update_stock)) {
                        throw new Exception("Error update stock: " . mysqli_error($db));
                    }
                }
                
                // Commit transaksi
                mysqli_commit($db);
                
                // Kosongkan keranjang
                $_SESSION['cart'] = [];
                
                $success = "Pesanan berhasil dibuat! Terima kasih telah berbelanja.";
                header("refresh:2;url=orders.php");
                
            } catch (Exception $e) {
                mysqli_rollback($db);
                $error = $e->getMessage();
            }
        }
    }
}

// Hitung total keranjang
$cart_total = 0;
$cart_items = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
    $cart_items += $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Apotek Online</title>
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
            align-items: center;
        }
        
        .navbar-links a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s;
            position: relative;
        }
        
        .navbar-links a:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }
        
        .container {
            max-width: 1200px;
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
        
        .cart-grid {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 20px;
        }
        
        .cart-items {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .cart-summary {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 80px 1fr 120px 80px 40px;
            gap: 20px;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        
        .item-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
        }
        
        .item-info h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .item-category {
            color: #999;
            font-size: 13px;
        }
        
        .item-price {
            color: #667eea;
            font-weight: 700;
            font-size: 16px;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-control input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
        }
        
        .btn-update {
            padding: 6px 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .btn-remove {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 20px;
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-cart h3 {
            color: #666;
            margin-bottom: 10px;
        }
        
        .empty-cart a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: #666;
        }
        
        .summary-row.total {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            padding-top: 15px;
            border-top: 2px solid #eee;
        }
        
        .summary-row.total .amount {
            color: #667eea;
        }
        
        .payment-method {
            margin: 20px 0;
        }
        
        .payment-method label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .payment-method select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .checkout-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }
        
        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .checkout-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        @media (max-width: 968px) {
            .cart-grid {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                grid-template-columns: 60px 1fr;
                gap: 15px;
            }
            
            .item-price, .quantity-control {
                grid-column: 2;
            }
        }
    </style>
</head>
<body>
    <?php include "../../layout/userheader.php" ?>
    
    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="cart-grid">
            <div class="cart-items">
                <h2>🛒 Keranjang Belanja (<?php echo $cart_items; ?> item)</h2>
                
                <?php if (empty($_SESSION['cart'])): ?>
                    <div class="empty-cart">
                        <h3>😔 Keranjang belanja Anda kosong</h3>
                        <p>Mulai belanja dan tambahkan obat ke keranjang Anda.</p>
                        <a href="index.php">🛍️ Mulai Belanja</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($_SESSION['cart'] as $medicine_id => $item): ?>
                        <div class="cart-item">
                            <div class="item-icon">💊</div>
                            
                            <div class="item-info">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <div class="item-category"><?php echo htmlspecialchars($item['category']); ?></div>
                                <div class="item-price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></div>
                            </div>
                            
                            <form method="POST" action="" class="quantity-control">
                                <input type="hidden" name="medicine_id" value="<?php echo $medicine_id; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0" required>
                                <button type="submit" name="update_cart" class="btn-update">✓</button>
                            </form>
                            
                            <div class="item-price">
                                Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
                            </div>
                            
                            <a href="?remove=<?php echo $medicine_id; ?>" class="btn-remove" 
                               onclick="return confirm('Hapus item ini dari keranjang?')"
                               title="Hapus">🗑️</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="cart-summary">
                <h2>📋 Ringkasan Pesanan</h2>
                
                <div class="summary-row">
                    <span>Subtotal (<?php echo $cart_items; ?> item)</span>
                    <span>Rp <?php echo number_format($cart_total, 0, ',', '.'); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Ongkir</span>
                    <span>Rp 0</span>
                </div>
                
                <div class="summary-row total">
                    <span>Total</span>
                    <span class="amount">Rp <?php echo number_format($cart_total, 0, ',', '.'); ?></span>
                </div>
                
                <form method="POST" action="">
                    <div class="payment-method">
                        <label for="payment_method">Metode Pembayaran *</label>
                        <select id="payment_method" name="payment_method" required <?php echo empty($_SESSION['cart']) ? 'disabled' : ''; ?>>
                            <option value="">-- Pilih Metode --</option>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="E-Wallet">E-Wallet (OVO/GoPay/Dana)</option>
                            <option value="COD">Bayar di Tempat (COD)</option>
                            <option value="Kartu Kredit">Kartu Kredit</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="checkout" class="checkout-btn" 
                            <?php echo empty($_SESSION['cart']) ? 'disabled' : ''; ?>>
                        💳 Proses Checkout
                    </button>
                </form>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                    <a href="index.php" style="color: #667eea; text-decoration: none; display: block; text-align: center;">
                        ← Lanjutkan Belanja
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
