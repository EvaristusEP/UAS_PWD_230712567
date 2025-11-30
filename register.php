<?php
include "database.php";
session_start();

$register_message = "";
$register_success = "";

// Jika user sudah login
if (isset($_SESSION["is_login"])) {
    header("location: index.php");
    exit;
}

// Jika form disubmit
if (isset($_POST["register"])) {

    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $full_name = $_POST["full_name"];
    $address = $_POST["address"];

    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $register_message = "Semua field wajib diisi!";
    } elseif ($password !== $confirm_password) {
        $register_message = "Password tidak cocok!";
    } elseif (strlen($password) < 8) {
        $register_message = "Password minimal 8 karakter!";
    } else {
        try {
            // Cek username sudah dipakai
            $check_username = $db->query("SELECT id FROM users WHERE username='$username'");
            // Cek email sudah dipakai
            $check_email = $db->query("SELECT id FROM users WHERE email='$email'");

            if ($check_username->num_rows > 0) {
                $register_message = "Username sudah digunakan!";
            } elseif ($check_email->num_rows > 0) {
                $register_message = "Email sudah digunakan!";
            } else {

                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Query insert
                $sql = "
                    INSERT INTO users (username, email, password, full_name, address, role)
                    VALUES ('$username', '$email', '$hashed_password', '$full_name', '$address', 'user')
                ";

                if ($db->query($sql)) {
                    $register_success = "Registrasi berhasil!";
                    header("refresh:2;url=login.php");
                } else {
                    $register_message = "Registrasi gagal, silakan coba lagi.";
                }
            }

        } catch (mysqli_sql_exception $e) {
            $register_message = "Terjadi kesalahan pada sistem.";
        }

        $db->close();
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Apotek Online</title>
    <link rel="stylesheet" href="assets/css/global.css">
    
</head>
<body>
    <div class="container">
        <h2>📝 Registrasi Akun</h2>
        
        <?php if ($register_message): ?>
            <div class="alert alert-error"><?php echo $register_message; ?></div>
        <?php endif; ?>

        <?php if ($register_success): ?>
            <div class="alert alert-success"><?php echo $register_success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="full_name">Nama Lengkap *</label>
                <input type="text" id="full_name" name="full_name" required 
                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password * (min. 8 karakter)</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
                <label for="address">Alamat</label>
                <textarea id="address" name="address"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
            </div>
            
            <button type="submit" name="register">Daftar Sekarang</button>
        </form>
        
        <div class="login-link">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>
</body>
</html>