<?php
// Koneksi database
$hostname = "localhost";
$username = "root";
$password = "";
$database_name = "tubes_pwd_apotek";

$db = mysqli_connect($hostname, $username, $password, $database_name);

if($db->connect_error) {
    die("Error: Koneksi database gagal");
}

// Helper function untuk redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}
?>
