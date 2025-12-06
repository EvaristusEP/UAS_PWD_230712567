<?php
session_start();
include "../config/database.php";

$id = $_POST['id'];
$qty = $_POST['qty'];

$med = $db->query("SELECT * FROM medicines WHERE id='$id'")->fetch_assoc();

if (!$med) {
    echo json_encode(["status" => "ERROR", "message" => "Obat tidak ditemukan"]);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$current_qty = isset($_SESSION['cart'][$id]) ? $_SESSION['cart'][$id]['quantity'] : 0;
$new_total_qty = $current_qty + $qty;

if ($new_total_qty > $med['stock']) {
    echo json_encode([
        "status" => "ERROR", 
        "message" => "Stok tidak mencukupi! Stok tersedia: " . $med['stock']
    ]);
    exit;
}

if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]['quantity'] += $qty;
} else {
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
