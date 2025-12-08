<?php
    session_start();
    include '../config/database.php';

    $login_message = "";

    if(isset($_SESSION["user_id"])) {
        if($_SESSION["role"] == "admin") {
            header("location: ../admin/index.php");
        } else {
            header("location: ../user/index.php");
        }
        exit();
    }

    if(isset($_POST["login"])) {
        $identifier = $_POST["identifier"]; 
        $password   = $_POST["password"];

        if(empty($identifier) || empty($password)) {
            $_SESSION['login_error'] = "Username atau password tidak boleh kosong";
        } else {

            $sql = "SELECT * FROM users WHERE username='$identifier' OR email='$identifier'";
            $result = $db->query($sql);

            if($result->num_rows > 0) {
                $data = $result->fetch_assoc();

                if(password_verify($password, $data["password"])) {

                    $_SESSION["user_id"] = $data["id"];
                    $_SESSION["username"]   = $data["username"];
                    $_SESSION["email"]      = $data["email"];
                    $_SESSION["full_name"]  = $data["full_name"];
                    $_SESSION["role"]       = $data["role"];
                    $_SESSION["is_login"]   = true;

                    if($_SESSION["role"] == "admin") {
                        header("location: ../admin/index.php");
                    } else {
                        header("location: ../user/index.php");
                    }
                    exit();

                } else {
                    $_SESSION['login_error'] = "Username atau Password salah!";
                }

            } else {
                $_SESSION['login_error'] = "Akun tidak ditemukan!";
            }

            $db->close();
        }
        header("location: login.php");
        exit();
    }

    $error = '';
    if(isset($_SESSION['login_error'])) {
        $error = $_SESSION['login_error'];
        unset($_SESSION['login_error']);
    }
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Apotek Online</title>
    <link rel="stylesheet" href="../assets/css/global.css">
    <script src="../assets/js/global.js"></script>
</head>
<body class="login">
    <div class="container">
        <h2>Login</h2>
        
        <form action="login.php" method="post">
            <input type="text" placeholder="username atau email" name="identifier" required>
            <input type="password" placeholder="password" name="password" required>
            <button type="submit" name="login">Login</button>
        </form>
        
        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div>

    <script>
        <?php if(!empty($error)): ?>
        window.addEventListener('DOMContentLoaded', function() {
            showPopup('<?php echo addslashes($error); ?>', 'error');
        });
        <?php endif; ?>
    </script>
</body>
</html>