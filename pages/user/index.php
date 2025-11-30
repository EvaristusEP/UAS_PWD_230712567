

<?php
// index.php - Halaman utama untuk user (daftar obat)
include '../../database.php';
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// Redirect admin ke dashboard admin
if ($_SESSION['role'] == 'admin') {
    redirect('admin/index.php');
}

// Hitung item di keranjang
$cart_items = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_items += $item['quantity'];
    }
}

// Ambil data obat dari database
$search = '';
$category_filter = '';

if (isset($_GET['search'])) {
    $search = ($_GET['search']);
}

if (isset($_GET['category'])) {
    $category_filter = ($_GET['category']);
}

// Query untuk mengambil obat
$query = "SELECT * FROM medicines WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}

if (!empty($category_filter)) {
    $query .= " AND category='$category_filter'";
}

$query .= " ORDER BY name ASC";
$medicines = mysqli_query($db, $query);

// Query untuk mengambil kategori
$categories = mysqli_query($db, "SELECT DISTINCT category FROM medicines ORDER BY category");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apotek Online - Beranda</title>
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
        
        .navbar-links a {
            position: relative;
        }
        
        .user-info {
            color: white;
            font-size: 14px;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .welcome-banner {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .welcome-banner h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-section input,
        .filter-section select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            flex: 1;
            min-width: 200px;
        }
        
        .filter-section button {
            padding: 10px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .medicine-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .medicine-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .medicine-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .medicine-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .medicine-content {
            padding: 20px;
        }
        
        .medicine-category {
            display: inline-block;
            background: #e7f3ff;
            color: #0066cc;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .medicine-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .medicine-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .medicine-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .medicine-price {
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
        }
        
        .medicine-stock {
            font-size: 14px;
            color: #666;
        }
        
        .buy-button {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin-top: 10px;
            text-align: center;
            transition: transform 0.2s;
        }
        
        .buy-button:hover {
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
        }
    </style>
</head>
<body>
    <?php include "../../layout/userheader.php" ?>
    
    <div class="container">
        <div class="welcome-banner">
            <h2>Selamat Datang di Apotek Online! 🏥</h2>
            <p>Temukan obat dan produk kesehatan yang Anda butuhkan dengan mudah dan cepat.</p>
        </div>
        
        <div class="filter-section">
            <form method="GET" action="" style="display: flex; gap: 15px; width: 100%; flex-wrap: wrap;">
                <input type="text" name="search" placeholder="🔍 Cari obat..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                
                <select name="category">
                    <option value="">Semua Kategori</option>
                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?php echo $cat['category']; ?>" 
                                <?php echo $category_filter == $cat['category'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <button type="submit">Filter</button>
                <?php if (!empty($search) || !empty($category_filter)): ?>
                    <a href="index.php" style="padding: 10px 20px; background: #ccc; color: #333; text-decoration: none; border-radius: 5px;">Reset</a>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if (mysqli_num_rows($medicines) > 0): ?>
            <div class="medicine-grid">
                <?php while ($medicine = mysqli_fetch_assoc($medicines)): ?>
                    <div class="medicine-card">
                        <div class="medicine-image" style="display: flex; align-items: center; justify-content: center; color: white; font-size: 48px;">
                            💊
                        </div>
                        <div class="medicine-content">
                            <span class="medicine-category"><?php echo htmlspecialchars($medicine['category']); ?></span>
                            <div class="medicine-name">
                                <a href="medicine-detail.php?id=<?php echo $medicine['id']; ?>" style="color: inherit; text-decoration: none;">
                                    <?php echo htmlspecialchars($medicine['name']); ?>
                                </a>
                            </div>
                            <div class="medicine-description">
                                <?php echo htmlspecialchars(substr($medicine['description'], 0, 80)) . '...'; ?>
                            </div>
                            <div class="medicine-footer">
                                <div>
                                    <div class="medicine-price">Rp <?php echo number_format($medicine['price'], 0, ',', '.'); ?></div>
                                    <div class="medicine-stock">Stok: <?php echo $medicine['stock']; ?></div>
                                </div>
                            </div>
                            <?php if ($medicine['stock'] > 0): ?>
                                <form method="POST" action="cart.php" style="display: flex; gap: 10px; margin-top: 10px;">
                                    <input type="hidden" name="medicine_id" value="<?php echo $medicine['id']; ?>">
                                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $medicine['stock']; ?>" 
                                           style="width: 60px; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                                    <button type="submit" name="add_to_cart" class="buy-button" style="flex: 1; margin: 0;">
                                        🛒 Tambah ke Keranjang
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="buy-button" style="background: #ccc; cursor: not-allowed;" disabled>
                                    Stok Habis
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>😔 Tidak ada obat yang ditemukan</h3>
                <p>Coba kata kunci atau kategori lain.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
