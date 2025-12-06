<?php
// index.php - Halaman utama untuk user (daftar obat)
include 'database.php';

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
    <link rel="stylesheet" href="../../assets/css/user.css">
</head>
<body>
    <?php include "layout/indexHeader.html" ?>
    
    <div class="container">
        <div class="welcome-banner">
            <h2>Selamat Datang di Apotek Online!</h2>
            <p>Temukan obat dan produk kesehatan yang Anda butuhkan dengan mudah dan cepat.</p>
        </div>
        
        <div class="filter-section">
            <form method="GET" action="" style="display: flex; gap: 15px; width: 100%; flex-wrap: wrap;">
                <input type="text" name="search" placeholder="Cari obat..." 
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
                                <form method="POST" action="login.php" style="display: flex; gap: 10px; margin-top: 10px;">
                                    <input type="hidden" name="medicine_id" value="<?php echo $medicine['id']; ?>">
                                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $medicine['stock']; ?>" 
                                           style="width: 60px; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                                    <button type="submit" name="add_to_cart" class="buy-button" style="flex: 1; margin: 0;">
                                        Tambah ke Keranjang
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
                <h3>Tidak ada obat yang ditemukan</h3>
                <p>Coba kata kunci atau kategori lain.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
