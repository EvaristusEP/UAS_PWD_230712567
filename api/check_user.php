<?php
include "../config/database.php";

$field = $_GET['field']; 
$value = $_GET['value'];

if ($field == "username") {
    $check = $db->query("SELECT id FROM users WHERE username='$value'");
} else if ($field == "email") {
    $check = $db->query("SELECT id FROM users WHERE email='$value'");
}

echo ($check->num_rows > 0) ? "EXISTS" : "OK";
