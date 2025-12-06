<?php
session_start();
include "../../database.php";

$id = $_POST['id'];
$qty = $_POST['qty'];

$med = $db->query("SELECT * FROM medicines WHERE id='$id'")->fetch_assoc();

if (!$med) {
    echo "ERROR";
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Jika barang sudah ada di keranjang
if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]['quantity'] += $qty;
} else {
    $_SESSION['cart'][$id] = [
        'id' => $id,
        'name' => $med['name'],
        'price' => $med['price'],
        'category' => $med['category'],
        'quantity' => $qty
    ];

}

echo "OK";

$total = 0;

if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['quantity'];
    }
}

echo $total;
