<?php
session_start();
include('db.php'); // Koneksi ke database

// Cek apakah session pelanggan ada, jika tidak arahkan ke halaman login
if (!isset($_SESSION['customer']['user_id']) || $_SESSION['customer']['role'] !== 'customer') {
    header('Location: login.php');
    exit();
}

// Ambil ID produk dan quantity dari URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1; // Default quantity to 1

// Validasi produk dan quantity
if ($product_id <= 0 || $quantity <= 0) {
    $_SESSION['error'] = "Produk tidak valid.";
    header('Location: catalog.php');
    exit();
}

// Periksa apakah produk ada di database
$product_query = "SELECT * FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $product_query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    // Produk ditemukan
    $product = mysqli_fetch_assoc($result);

    // Inisialisasi keranjang jika belum ada
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Tambahkan produk ke keranjang (atau tambahkan jumlah jika sudah ada)
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = [
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity
        ];
    }

    $_SESSION['message'] = "Produk berhasil ditambahkan ke keranjang!";
} else {
    $_SESSION['error'] = "Produk tidak ditemukan.";
}

mysqli_stmt_close($stmt);

// Jika menggunakan AJAX, tidak perlu redirect
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    echo "success";
    exit();
}

// Redirect ke halaman keranjang jika tidak menggunakan AJAX
header('Location: catalog.php');
exit();
?>
