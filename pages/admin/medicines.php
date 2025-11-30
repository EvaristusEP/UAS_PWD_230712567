<?php
// admin/medicines.php - CRUD Obat
include '../../database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    redirect('../../login.php');
}

$error = '';
$success = '';

// Proses Tambah Obat
if (isset($_POST['add_medicine'])) {
    $name = ($_POST['name']);
    $description = ($_POST['description']);
    $price = ($_POST['price']);
    $stock = ($_POST['stock']);
    $category = ($_POST['category']);
    
    if (empty($name) || empty($price) || empty($stock)) {
        $error = "Nama, harga, dan stok wajib diisi!";
    } else {
        $query = "INSERT INTO medicines (name, description, price, stock, category) 
                 VALUES ('$name', '$description', '$price', '$stock', '$category')";
        
        if (mysqli_query($db, $query)) {
            $success = "Obat berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan obat: " . mysqli_error($db);
        }
    }
}

// Proses Edit Obat
if (isset($_POST['edit_medicine'])) {
    $id = ($_POST['id']);
    $name = ($_POST['name']);
    $description = ($_POST['description']);
    $price = ($_POST['price']);
    $stock = ($_POST['stock']);
    $category = ($_POST['category']);
    
    $query = "UPDATE medicines SET 
             name='$name', description='$description', price='$price', 
             stock='$stock', category='$category' 
             WHERE id='$id'";
    
    if (mysqli_query($db, $query)) {
        $success = "Obat berhasil diupdate!";
    } else {
        $error = "Gagal update obat: " . mysqli_error($db);
    }
}

// Proses Hapus Obat
if (isset($_GET['delete'])) {
    $id = ($_GET['delete']);
    $query = "DELETE FROM medicines WHERE id='$id'";
    
    if (mysqli_query($db, $query)) {
        $success = "Obat berhasil dihapus!";
    } else {
        $error = "Gagal hapus obat: " . mysqli_error($db);
    }
}

// Ambil semua obat
$medicines = mysqli_query($db, "SELECT * FROM medicines ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Obat - Admin</title>
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
            max-width: 1400px;
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
        }
        
        .navbar-links a:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .container {
            max-width: 1400px;
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
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group.full {
            grid-column: 1 / -1;
        }
        
        label {
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        
        input, textarea, select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        button {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
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
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-edit {
            padding: 6px 12px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 13px;
        }
        
        .btn-delete {
            padding: 6px 12px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 13px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal.active {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .close-modal {
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }
    </style>
</head>
<body>
    <?php include "../../layout/adminHeader.html" ?>
    
    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Form Tambah Obat -->
        <div class="card">
            <h2>➕ Tambah Obat Baru</h2>
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
                
                <button type="submit" name="add_medicine">💾 Tambah Obat</button>
            </form>
        </div>
        
        <!-- Daftar Obat -->
        <div class="card">
            <h2>📋 Daftar Obat</h2>
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
                                        ✏️ Edit
                                    </a>
                                    <a href="?delete=<?php echo $medicine['id']; ?>" 
                                       class="btn-delete"
                                       onclick="return confirm('Yakin hapus obat ini?')">
                                        🗑️ Hapus
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
                <h2>✏️ Edit Obat</h2>
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
                
                <button type="submit" name="edit_medicine">💾 Update Obat</button>
            </form>
        </div>
    </div>
    
    <script>
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