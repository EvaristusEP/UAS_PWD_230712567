<?php
// profile.php - Halaman profil dan update profil
// Disini user bisa liat dan edit profil, ganti password, upload foto, bahkan hapus akun

include '../../database.php'; // koneksi database
session_start(); // mulai session

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// Hitung item di keranjang (buat badge)
$cart_items = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_items += $item['quantity'];
    }
}

$user_id = $_SESSION['user_id'];
$error = ''; // pesan error
$success = ''; // pesan sukses

// Ambil data user dari database
$query = "SELECT * FROM users WHERE id='$user_id'";
$result = mysqli_query($db, $query);
$user = mysqli_fetch_assoc($result);

// Proses update profil (kalo user klik tombol "Update Profil")
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        // Ambil data dari form
        $full_name = ($_POST['full_name']);
        $email = ($_POST['email']);
        $address = ($_POST['address']);
        
        // Cek apakah email ini udah dipake user lain (selain user ini sendiri)
        $check_email = mysqli_query($db, "SELECT id FROM users WHERE email='$email' AND id != '$user_id'");
        
        if (mysqli_num_rows($check_email) > 0) {
            $error = "Email sudah digunakan oleh user lain!"; // email udah ada
        } else {
            // Update data profil di database
            $update_query = "UPDATE users SET full_name='$full_name', email='$email', address='$address' WHERE id='$user_id'";
            
            if (mysqli_query($db, $update_query)) {
                $success = "Profil berhasil diupdate!";
                $_SESSION['full_name'] = $full_name; // update session juga
                // Refresh data user dari database
                $result = mysqli_query($db, "SELECT * FROM users WHERE id='$user_id'");
                $user = mysqli_fetch_assoc($result);
            } else {
                $error = "Update gagal: " . mysqli_error($db);
            }
        }
    }
    
    // Proses upload foto profil
    if (isset($_POST['update_photo'])) {
        if (isset($_FILES['photo_profile']) && $_FILES['photo_profile']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['photo_profile']['name'];
            $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $filesize = $_FILES['photo_profile']['size'];
            
            if (!in_array($filetype, $allowed)) {
                $error = "Format file tidak diizinkan! Hanya JPG, JPEG, PNG, GIF.";
            } elseif ($filesize > 2 * 1024 * 1024) { // Max 2MB
                $error = "Ukuran file terlalu besar! Maksimal 2MB.";
            } else {
                // Buat nama file unik
                $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $filetype;
                $upload_path = 'uploads/profiles/' . $new_filename;
                
                // Buat folder jika belum ada
                if (!file_exists('uploads/profiles')) {
                    mkdir('uploads/profiles', 0777, true);
                }
                
                // Upload file
                if (move_uploaded_file($_FILES['photo_profile']['tmp_name'], $upload_path)) {

                    // Path file lama
                    $old_file = 'uploads/profiles/' . $user['photo_profile'];

                    // Hapus foto lama jika valid
                    if (
                        !empty($user['photo_profile']) &&           // tidak kosong
                        $user['photo_profile'] != 'default.jpg' &&  // bukan default
                        file_exists($old_file) &&                   // file benar ada
                        is_file($old_file)                          // benar-benar file
                    ) {
                        unlink($old_file);
                    }

                    // Update database
                    $update_photo = "UPDATE users SET photo_profile='$new_filename' WHERE id='$user_id'";
                    if (mysqli_query($db, $update_photo)) {
                        $success = "Foto profil berhasil diupdate!";
                        
                        // Refresh data user
                        $result = mysqli_query($db, "SELECT * FROM users WHERE id='$user_id'");
                        $user = mysqli_fetch_assoc($result);
                    }

                } else {
                    $error = "Upload file gagal!";
                }

            }
        } else {
            $error = "Pilih file terlebih dahulu!";
        }
    }
    
    // Proses update password
    if (isset($_POST['update_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
            $error = "Semua field password harus diisi!";
        } elseif (!password_verify($old_password, $user['password'])) {
            $error = "Password lama salah!";
        } elseif ($new_password !== $confirm_password) {
            $error = "Password baru tidak cocok!";
        } elseif (strlen($new_password) < 6) {
            $error = "Password minimal 6 karakter!";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pass = "UPDATE users SET password='$hashed_password' WHERE id='$user_id'";
            
            if (mysqli_query($db, $update_pass)) {
                $success = "Password berhasil diubah!";
            } else {
                $error = "Update password gagal!";
            }
        }
    }
    
    // Proses hapus akun
    if (isset($_POST['delete_account'])) {
        $confirm_password = $_POST['confirm_delete_password'];
        
        if (empty($confirm_password)) {
            $error = "Masukkan password untuk konfirmasi!";
        } elseif (!password_verify($confirm_password, $user['password'])) {
            $error = "Password salah!";
        } else {
            // Mulai transaksi
            mysqli_begin_transaction($db);
            
            try {
                // Hapus foto profil jika ada
                if ($user['photo_profile'] != 'default.jpg' && file_exists('uploads/profiles/' . $user['photo_profile'])) {
                    unlink('uploads/profiles/' . $user['photo_profile']);
                }
                
                // Hapus semua pesanan user (akan cascade ke order_details)
                $delete_orders = "DELETE FROM orders WHERE user_id='$user_id'";
                mysqli_query($db, $delete_orders);
                
                // Hapus user
                $delete_user = "DELETE FROM users WHERE id='$user_id'";
                if (!mysqli_query($db, $delete_user)) {
                    throw new Exception("Gagal menghapus akun!");
                }
                
                // Commit transaksi
                mysqli_commit($db);
                
                // Hancurkan session
                session_destroy();
                
                // Redirect ke register dengan pesan
                header("Location: ../../login.php");
                exit();
                
            } catch (Exception $e) {
                mysqli_rollback($db);
                $error = $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Apotek Online</title>
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
        
        <div class="profile-grid">
            <div class="profile-card">
                <?php
                $photo_path = $user['photo_profile'] != 'default.jpg' 
                    ? 'uploads/profiles/' . $user['photo_profile'] 
                    : 'https://via.placeholder.com/150';
                ?>
                <img src="<?php echo $photo_path; ?>" alt="Foto Profil" class="profile-image">
                <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                <p>@<?php echo htmlspecialchars($user['username']); ?></p>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            
            <div class="main-content">
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="showTab('profile')">Informasi Profil</button>
                    <button class="tab-button" onclick="showTab('photo')">Foto Profil</button>
                    <button class="tab-button" onclick="showTab('password')">Ubah Password</button>
                    <button class="tab-button" onclick="showTab('delete')">Hapus Akun</button>
                </div>
                
                <!-- Tab Informasi Profil -->
                <div id="profile" class="tab-content active">
                    <h2>Edit Informasi Profil</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small style="color: #999;">Username tidak dapat diubah</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="full_name">Nama Lengkap *</label>
                            <input type="text" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Alamat</label>
                            <textarea id="address" name="address"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_profile">Simpan Perubahan</button>
                    </form>
                </div>
                
                <!-- Tab Foto Profil -->
                <div id="photo" class="tab-content">
                    <h2>Upload Foto Profil</h2>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="photo_profile">Pilih Foto (Max 2MB, JPG/PNG/GIF)</label>
                            <input type="file" id="photo_profile" name="photo_profile" accept="image/*" required>
                        </div>
                        
                        <button type="submit" name="update_photo">Upload Foto</button>
                    </form>
                </div>
                
                <!-- Tab Ubah Password -->
                <div id="password" class="tab-content">
                    <h2>Ubah Password</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="old_password">Password Lama *</label>
                            <input type="password" id="old_password" name="old_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Password Baru * (min. 6 karakter)</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" name="update_password">Ubah Password</button>
                    </form>
                </div>
                
                <!-- Tab Hapus Akun -->
                <div id="delete" class="tab-content">
                    <h2 style="color: #dc3545;">Hapus Akun</h2>
                    
                    <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                        <h3 style="color: #856404; margin-bottom: 10px;">Peringatan!</h3>
                        <p style="color: #856404; line-height: 1.6;">
                            Menghapus akun akan menghapus secara permanen:
                        </p>
                        <ul style="color: #856404; margin: 10px 0 0 20px;">
                            <li>Semua data profil Anda</li>
                            <li>Riwayat pesanan Anda</li>
                            <li>Foto profil Anda</li>
                        </ul>
                        <p style="color: #dc3545; font-weight: 600; margin-top: 15px;">
                            Tindakan ini tidak dapat dibatalkan!
                        </p>
                    </div>
                    
                    <form method="POST" action="" onsubmit="return confirmDelete()">
                        <div class="form-group">
                            <label for="confirm_delete_password">Masukkan Password Anda *</label>
                            <input type="password" id="confirm_delete_password" name="confirm_delete_password" required 
                                   placeholder="Masukkan password untuk konfirmasi">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_delete_text">Ketik "HAPUS" untuk konfirmasi *</label>
                            <input type="text" id="confirm_delete_text" name="confirm_delete_text" required 
                                   placeholder="Ketik kata HAPUS (huruf besar)">
                            <small style="color: #999;">Ketik kata "HAPUS" (tanpa tanda petik) untuk melanjutkan</small>
                        </div>
                        
                        <button type="submit" name="delete_account" 
                                style="background: #dc3545; width: 100%;">
                            Hapus Akun Saya
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(btn => btn.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
        
        function confirmDelete() {
            return confirm('APAKAH ANDA YAKIN?\n\nAnda akan menghapus akun Anda secara permanen!\nSemua data akan hilang dan tidak dapat dikembalikan.\n\nKlik OK untuk melanjutkan atau Cancel untuk membatalkan.');
        }
    </script>
</body>
</html>