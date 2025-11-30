 <?php

$hostname = "localhost";
$username = "root";
$password = "";
$database_name = "tubes_pwd_apotek";

$db = mysqli_connect($hostname, $username, $password, $database_name);

if($db->connect_error) {
    echo "koneksi database rusak";
    die("error!");
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}
?>