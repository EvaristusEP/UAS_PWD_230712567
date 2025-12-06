<?php
session_start();
include "../../database.php";

$id = $_POST['id'];
$qty = $_POST['qty'];

// Ambil data obat dari database
$med = $db->query("SELECT * FROM medicines WHERE id='$id'")->fetch_assoc();

if (!$med) {
    echo json_encode(["status" => "ERROR"]);
    exit;
}

// Jika keranjang belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Jika barang sudah ada di keranjang → update qty
if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]['quantity'] += $qty;
} else {
    // Tambahkan data lengkap (termasuk kategori)
    $_SESSION['cart'][$id] = [
        'id' => $med['id'],
        'name' => $med['name'],
        'price' => $med['price'],
        'category' => $med['category'],   // ← kategori ikut disimpan
        'quantity' => $qty
    ];
}

// Hitung total item
$total_items = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_items += $item['quantity'];
}

// Kirim response JSON
echo json_encode([
    "status" => "OK",
    "count" => $total_items
]);
