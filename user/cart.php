<?php
include '../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_POST['add_to_cart'])) {
    $medicine_id = ($_POST['medicine_id']);
    $quantity = ($_POST['quantity']);
    
    $check_query = "SELECT * FROM medicines WHERE id='$medicine_id'";
    $medicine = mysqli_fetch_assoc(mysqli_query($db, $check_query));
    
    if ($medicine && $quantity <= $medicine['stock']) {
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
        $error = "Stok tidak mencukupi!"; // waduh stoknya kurang
    }
}

if (isset($_POST['update_cart'])) {
    $medicine_id = ($_POST['medicine_id']);
    $quantity = ($_POST['quantity']);
    
    if ($quantity > 0) {
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

if (isset($_GET['remove'])) {
    $medicine_id = ($_GET['remove']);
    unset($_SESSION['cart'][$medicine_id]);
    $success = "Item berhasil dihapus dari keranjang!";
}

if (isset($_POST['checkout'])) {
    if (empty($_SESSION['cart'])) {
        $error = "Keranjang belanja kosong!";
    } else {
        $payment_method = ($_POST['payment_method']);
        
        if (empty($payment_method)) {
            $error = "Pilih metode pembayaran!";
        } else {
            $total_price = 0;
            $stock_errors = [];
            
            foreach ($_SESSION['cart'] as $medicine_id => $item) {
                $check_stock = mysqli_query($db, "SELECT stock, name FROM medicines WHERE id='$medicine_id'");
                $med = mysqli_fetch_assoc($check_stock);
                
                if (!$med || $item['quantity'] > $med['stock']) {
                    $stock_errors[] = $med['name'] . " (stok: " . ($med['stock'] ?? 0) . ")";
                }
                
                $total_price += $item['price'] * $item['quantity'];
            }
            
            if (!empty($stock_errors)) {
                $error = "Stok tidak mencukupi untuk: " . implode(", ", $stock_errors);
            } else {
                mysqli_begin_transaction($db);
                
                try {
                    $order_query = "INSERT INTO orders (user_id, order_date, total_price, payment_method, status) 
                                   VALUES ('$user_id', NOW(), '$total_price', '$payment_method', 'completed')";
                    
                    if (!mysqli_query($db, $order_query)) {
                        throw new Exception("Error insert order: " . mysqli_error($db));
                    }
                    
                    $order_id = mysqli_insert_id($db);
                    
                    foreach ($_SESSION['cart'] as $medicine_id => $item) {
                        $check_final = mysqli_query($db, "SELECT stock FROM medicines WHERE id='$medicine_id'");
                        $current = mysqli_fetch_assoc($check_final);
                        
                        if ($current['stock'] < $item['quantity']) {
                            throw new Exception("Stok {$item['name']} tidak mencukupi!");
                        }
                        
                        $detail_query = "INSERT INTO order_details (order_id, medicine_id, quantity, price_at_purchase) 
                                        VALUES ('$order_id', '$medicine_id', '{$item['quantity']}', '{$item['price']}')";
                        
                        if (!mysqli_query($db, $detail_query)) {
                            throw new Exception("Error insert order detail: " . mysqli_error($db));
                        }
                        
                        $update_stock = "UPDATE medicines SET stock = stock - {$item['quantity']} WHERE id='$medicine_id'";
                        
                        if (!mysqli_query($db, $update_stock)) {
                            throw new Exception("Error update stock: " . mysqli_error($db));
                        }
                    }
                    
                    mysqli_commit($db);
                    
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
}

// Hitung total harga dan jumlah item di keranjang (buat ditampilin di ringkasan)
$cart_total = 0; // total harga
$cart_items = 0; // total item
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity']; // harga x jumlah
    $cart_items += $item['quantity']; // tambah jumlah item
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Apotek Online</title>
    <link rel="stylesheet" href="../assets/css/user.css">

    <script src="../assets/js/global.js"></script>
    <script src="../assets/js/user.js"></script>
</head>
<body>
    <?php include "../layout/userheader.php" ?>
    
    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="cart-grid">
            <div class="cart-items">
                <h2>Keranjang Belanja (<?php echo $cart_items; ?> item)</h2>
                
                <?php if (empty($_SESSION['cart'])): ?>
                    <div class="empty-cart">
                        <h3>Keranjang belanja Anda kosong</h3>
                        <p>Mulai belanja dan tambahkan obat ke keranjang Anda.</p>
                        <a href="index.php">Mulai Belanja</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($_SESSION['cart'] as $medicine_id => $item): ?>
                        <div class="cart-item">
                            <div class="item-icon"></div>
                            
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
                               title="Hapus">×</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="cart-summary">
                <h2>Ringkasan Pesanan</h2>
                
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
                        Proses Checkout
                    </button>
                </form>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                    <a href="index.php" style="color: #0d9488; text-decoration: none; display: block; text-align: center;">
                        ← Lanjutkan Belanja
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
