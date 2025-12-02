<?php
    session_start();
    include "database.php";

    $login_message = "";

    // jika user sudah login
    if(isset($_SESSION["user_id"])) {
        if($_SESSION["role"] == "admin") {
            header("location: pages/admin/index.php");
        } else {
            header("location: pages/user/index.php");
        }
        exit();
    }

    // jika tombol login ditekan
    if(isset($_POST["login"])) {
        $identifier = $_POST["identifier"]; 
        $password   = $_POST["password"];

        if(empty($identifier) || empty($password)) {
            $login_message = "Username atau password tidak boleh kosong";
        } else {

            // cek username atau email
            $sql = "SELECT * FROM users WHERE username='$identifier' OR email='$identifier'";
            $result = $db->query($sql);

            if($result->num_rows > 0) {
                $data = $result->fetch_assoc();

                // verifikasi password
                if(password_verify($password, $data["password"])) {

                    // simpan session
                    $_SESSION["user_id"]    = $data["id"];
                    $_SESSION["username"]   = $data["username"];
                    $_SESSION["email"]      = $data["email"];
                    $_SESSION["full_name"]  = $data["full_name"];
                    $_SESSION["role"]       = $data["role"];
                    $_SESSION["is_login"]   = true;

                    // arahkan sesuai role
                    if($data["role"] == "admin") {
                        header("location: pages/admin/index.php");
                    } else {
                        header("location: pages/user/index.php");
                    }
                    exit();

                } else {
                    $login_message = "PASSWORD SALAH";
                }

            } else {
                $login_message = "AKUN TIDAK ADA";
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
    <title>Login - Apotek Online</title>
    <link rel="stylesheet" href="assets/css/global.css">
</head>
<body class="login">
    <div class="container">
        <h2>Login</h2>
        
        <i><?= $login_message ?></i>
        
        <form action="login.php" method="post">
        <input type="text" placeholder="username atau email" name="identifier">
        <input type="password" placeholder="password" name="password">
        <button type="submit" name="login">Login</button>
    </form>
        
        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div>
</body>
</html>