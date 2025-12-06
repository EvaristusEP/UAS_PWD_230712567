<?php
// admin/medicines.php - CRUD Obat (Create, Read, Update, Delete)
// Disini admin bisa tambah obat baru, edit obat, sama hapus obat

include '../../database.php'; // koneksi database
session_start(); // mulai session

// Cek admin atau bukan? Kalo bukan, keluar!
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    redirect('../../login.php');
}

$error = ''; // pesan error
$success = ''; // pesan sukses

// Ambil pesan dari session (setelah redirect)
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Proses tambah obat baru (kalo admin klik tombol "Tambah Obat")
if (isset($_POST['add_medicine'])) {
    // Ambil data dari form
    $name = ($_POST['name']);
    $description = ($_POST['description']);
    $price = ($_POST['price']);
    $stock = ($_POST['stock']);
    $category = ($_POST['category']);
    
    // Validasi: field penting harus diisi
    if (empty($name) || empty($price) || empty($stock)) {
        $error = "Nama, harga, dan stok wajib diisi!";
    } else {
        // Insert obat baru ke database
        $query = "INSERT INTO medicines (name, description, price, stock, category) 
                 VALUES ('$name', '$description', '$price', '$stock', '$category')";
        
        if (mysqli_query($db, $query)) {
            $_SESSION['success'] = "Obat berhasil ditambahkan!";
            header('Location: medicines.php');
            exit();
        } else {
            $error = "Gagal menambahkan obat: " . mysqli_error($db);
        }
    }
}

// Proses edit obat (kalo admin edit data obat yang udah ada)
if (isset($_POST['edit_medicine'])) {
    // Ambil data dari form edit
    $id = ($_POST['id']);
    $name = ($_POST['name']);
    $description = ($_POST['description']);
    $price = ($_POST['price']);
    $stock = ($_POST['stock']);
    $category = ($_POST['category']);
    
    // Update data obat di database
    $query = "UPDATE medicines SET 
             name='$name', description='$description', price='$price', 
             stock='$stock', category='$category' 
             WHERE id='$id'";
    
    if (mysqli_query($db, $query)) {
        $_SESSION['success'] = "Obat berhasil diupdate!";
        header('Location: medicines.php');
        exit();
    } else {
        $error = "Gagal update obat: " . mysqli_error($db);
    }
}

// Proses hapus obat (kalo admin klik tombol hapus)
if (isset($_GET['delete'])) {
    $id = ($_GET['delete']); // ID obat yang mau dihapus
    
    // Cek apakah obat sudah diorder
    $check = mysqli_query($db, "SELECT COUNT(*) as count FROM order_details WHERE medicine_id='$id'");
    $result = mysqli_fetch_assoc($check);
    
    if ($result['count'] > 0) {
        $_SESSION['error'] = "Obat tidak bisa dihapus karena sudah ada dalam pesanan!";
        header('Location: medicines.php');
        exit();
    }
    
    $query = "DELETE FROM medicines WHERE id='$id'";
    
    if (mysqli_query($db, $query)) {
        $_SESSION['success'] = "Obat berhasil dihapus!";
        header('Location: medicines.php');
        exit();
    } else {
        $error = "Gagal hapus obat: " . mysqli_error($db);
    }
}

// Ambil semua data obat dari database buat ditampilin di tabel
$medicines = mysqli_query($db, "SELECT * FROM medicines ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Obat - Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <?php include "../../layout/adminHeader.html" ?>
    
    <div class="container">
        <!-- Form Tambah Obat -->
        <div class="card">
            <h2>Tambah Obat Baru</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Obat *</label>
                        <input type="text" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category">
                            <option value="Obat Bebas">Obat Bebas</option>
                            <option value="Obat Bebas Terbatas">Obat Bebas Terbatas</option>
                            <option value="Obat Keras">Obat Keras</option>
                            <option value="Suplemen">Suplemen</option>
                            <option value="Obat Herbal">Obat Herbal</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Harga (Rp) *</label>
                        <input type="number" name="price" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Stok *</label>
                        <input type="number" name="stock" required>
                    </div>
                    
                    <div class="form-group full">
                        <label>Deskripsi</label>
                        <textarea name="description"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Gambar Obat</label>
                        <input type="file" name="image" accept="image/*">
                    </div>

                </div>
                
                <button type="submit" name="add_medicine">Tambah Obat</button>
            </form>
        </div>
        
        <!-- Daftar Obat -->
        <div class="card">
            <h2>Daftar Obat</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($medicine = mysqli_fetch_assoc($medicines)): ?>
                        <tr>
                            <td><?php echo $medicine['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($medicine['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($medicine['category']); ?></td>
                            <td>Rp <?php echo number_format($medicine['price'], 0, ',', '.'); ?></td>
                            <td><?php echo $medicine['stock']; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="#" class="btn-edit" 
                                       onclick="openEditModal(<?php echo htmlspecialchars(json_encode($medicine)); ?>)">
                                        Edit
                                    </a>
                                    <a href="javascript:void(0)" 
                                        class="btn-delete"
                                        onclick="confirmDelete(<?php echo $medicine['id']; ?>)">
                                        Hapus
                                    </a>

                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal Edit -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Obat</h2>
                <span class="close-modal" onclick="closeEditModal()">&times;</span>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Obat *</label>
                        <input type="text" name="name" id="edit_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category" id="edit_category">
                            <option value="Obat Bebas">Obat Bebas</option>
                            <option value="Obat Bebas Terbatas">Obat Bebas Terbatas</option>
                            <option value="Obat Keras">Obat Keras</option>
                            <option value="Suplemen">Suplemen</option>
                            <option value="Obat Herbal">Obat Herbal</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Harga (Rp) *</label>
                        <input type="number" name="price" id="edit_price" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Stok *</label>
                        <input type="number" name="stock" id="edit_stock" required>
                    </div>
                    
                    <div class="form-group full">
                        <label>Deskripsi</label>
                        <textarea name="description" id="edit_description"></textarea>
                    </div>
                </div>
                
                <button type="submit" name="edit_medicine">Update Obat</button>
            </form>
        </div>
    </div>
    
    <script src="../../assets/js/admin.js"></script>
    <script>
        // Show toast notification if there's a message
        <?php if ($success): ?>
            <?php if (strpos($success, 'dihapus') !== false): ?>
                showToast('<?php echo addslashes($success); ?>', 'delete');
            <?php else: ?>
                showToast('<?php echo addslashes($success); ?>', 'success');
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if ($error): ?>
            showToast('<?php echo addslashes($error); ?>', 'error');
        <?php endif; ?>
        
        function openEditModal(medicine) {
            document.getElementById('edit_id').value = medicine.id;
            document.getElementById('edit_name').value = medicine.name;
            document.getElementById('edit_description').value = medicine.description;
            document.getElementById('edit_price').value = medicine.price;
            document.getElementById('edit_stock').value = medicine.stock;
            document.getElementById('edit_category').value = medicine.category;
            
            document.getElementById('editModal').classList.add('active');
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>