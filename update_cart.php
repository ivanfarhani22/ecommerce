<?php
// update_cart.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? null;

    if ($product_id && $quantity > 0 && isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        $_SESSION['message'] = "Keranjang berhasil diupdate.";
    } else {
        $_SESSION['error'] = "Gagal mengupdate keranjang.";
    }
}

header("Location: cart.php");
exit;
?>