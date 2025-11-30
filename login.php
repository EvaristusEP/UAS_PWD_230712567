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
    <!-- <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #004085;
        }
    </style> -->
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