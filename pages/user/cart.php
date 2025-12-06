<?php
// cart.php - Halaman keranjang belanja
// Ini halaman buat ngeliat isi keranjang, update jumlah obat, sama checkout

include '../../database.php'; // koneksi database
session_start(); // mulai session buat nyimpen data user

// Cek dulu nih, udah login belum? Kalo belum, suruh login dulu
if (!isset($_SESSION['user_id'])) {
    redirect('../../login.php');
}

$user_id = $_SESSION['user_id']; // ambil ID user yang login
$error = ''; // variabel buat nyimpen pesan error
$success = ''; // variabel buat nyimpen pesan sukses

// Kalo keranjangnya belum ada, bikin dulu array kosong
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Proses tambah obat ke keranjang (kalo user klik tombol "Tambah ke Keranjang")
if (isset($_POST['add_to_cart'])) {
    $medicine_id = ($_POST['medicine_id']); // ID obat yang mau ditambah
    $quantity = ($_POST['quantity']); // jumlah yang mau dibeli
    
    // Cek dulu stoknya cukup apa nggak
    $check_query = "SELECT * FROM medicines WHERE id='$medicine_id'";
    $medicine = mysqli_fetch_assoc(mysqli_query($db, $check_query));
    
    // Kalo stok cukup, lanjut tambah ke keranjang
    if ($medicine && $quantity <= $medicine['stock']) {
        // Cek nih, obat ini udah pernah ditambah sebelumnya belum?
        if (isset($_SESSION['cart'][$medicine_id])) {
            // Kalo udah ada, tinggal tambah jumlahnya aja
            $_SESSION['cart'][$medicine_id]['quantity'] += $quantity;
        } else {
            // Kalo belum ada, tambahin data obat baru ke keranjang
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

// Proses update jumlah obat (kalo user ganti angkanya terus klik tombol centang)
if (isset($_POST['update_cart'])) {
    $medicine_id = ($_POST['medicine_id']); // ID obat yang mau diupdate
    $quantity = ($_POST['quantity']); // jumlah baru
    
    if ($quantity > 0) {
        // Kalo jumlahnya lebih dari 0, cek dulu stoknya cukup apa nggak
        $check_query = "SELECT stock FROM medicines WHERE id='$medicine_id'";
        $result = mysqli_query($db, $check_query);
        $medicine = mysqli_fetch_assoc($result);
        
        if ($medicine && $quantity <= $medicine['stock']) {
            // Update jumlahnya kalo stok cukup
            $_SESSION['cart'][$medicine_id]['quantity'] = $quantity;
            $success = "Keranjang berhasil diupdate!";
        } else {
            $error = "Stok tidak mencukupi!"; // stok kurang bro
        }
    } else {
        // Kalo jumlahnya 0 atau kurang, hapus aja item ini dari keranjang
        unset($_SESSION['cart'][$medicine_id]);
        $success = "Item dihapus dari keranjang!";
    }
}

// Proses hapus item (kalo user klik tombol X di sebelah kanan)
if (isset($_GET['remove'])) {
    $medicine_id = ($_GET['remove']); // ID obat yang mau dihapus
    unset($_SESSION['cart'][$medicine_id]); // hapus dari session
    $success = "Item berhasil dihapus dari keranjang!";
}

// Proses checkout (ini yang paling penting nih, buat nyimpen pesanan ke database)
if (isset($_POST['checkout'])) {
    // Cek dulu, keranjangnya ada isinya apa nggak
    if (empty($_SESSION['cart'])) {
        $error = "Keranjang belanja kosong!"; // waduh kosong
    } else {
        $payment_method = ($_POST['payment_method']); // metode pembayaran yang dipilih
        
        if (empty($payment_method)) {
            $error = "Pilih metode pembayaran!"; // lupa pilih metode bayar nih
        } else {
            // Hitung total harga semua item di keranjang
            $total_price = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total_price += $item['price'] * $item['quantity'];
            }
            
            // Mulai transaksi database (biar kalo ada error, bisa di-rollback)
            mysqli_begin_transaction($db);
            
            try {
                // Step 1: Bikin pesanan baru di tabel orders (ini kayak nota pembelian)
                $order_query = "INSERT INTO orders (user_id, order_date, total_price, payment_method, status) 
                               VALUES ('$user_id', NOW(), '$total_price', '$payment_method', 'completed')";
                
                if (!mysqli_query($db, $order_query)) {
                    throw new Exception("Error insert order: " . mysqli_error($db));
                }
                
                // Ambil ID pesanan yang baru aja dibuat (buat relasi ke order_details)
                $order_id = mysqli_insert_id($db);
                
                // Step 2: Masukin detail setiap obat yang dibeli ke tabel order_details
                foreach ($_SESSION['cart'] as $medicine_id => $item) {
                    // Insert detail pembelian (obat apa, berapa banyak, harga berapa)
                    $detail_query = "INSERT INTO order_details (order_id, medicine_id, quantity, price_at_purchase) 
                                    VALUES ('$order_id', '$medicine_id', '{$item['quantity']}', '{$item['price']}')";
                    
                    if (!mysqli_query($db, $detail_query)) {
                        throw new Exception("Error insert order detail: " . mysqli_error($db));
                    }
                    
                    // Step 3: Kurangin stok obat di database (biar stoknya berkurang sesuai yang dibeli)
                    $update_stock = "UPDATE medicines SET stock = stock - {$item['quantity']} WHERE id='$medicine_id'";
                    
                    if (!mysqli_query($db, $update_stock)) {
                        throw new Exception("Error update stock: " . mysqli_error($db));
                    }
                }
                
                // Kalo semua berhasil, commit transaksi (simpan semua perubahan ke database)
                mysqli_commit($db);
                
                // Kosongin keranjang karena udah checkout
                $_SESSION['cart'] = [];
                
                $success = "Pesanan berhasil dibuat! Terima kasih telah berbelanja.";
                header("refresh:2;url=orders.php"); // redirect ke halaman pesanan setelah 2 detik
                
            } catch (Exception $e) {
                // Kalo ada error, rollback semua perubahan (balik seperti semula)
                mysqli_rollback($db);
                $error = $e->getMessage();
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
    <link rel="stylesheet" href="../../assets/css/user.css">

    <script src="../../assets/js/global.js"></script>
    <script src="../../assets/js/user.js"></script>
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
