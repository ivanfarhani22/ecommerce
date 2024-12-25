<?php
// remove_from_cart.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;

    if ($product_id && isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['message'] = "Produk berhasil dihapus dari keranjang.";
    } else {
        $_SESSION['error'] = "Gagal menghapus produk dari keranjang.";
    }
}

header("Location: cart.php");
exit;
?>