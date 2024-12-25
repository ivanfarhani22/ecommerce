<?php
session_start();
include('db.php');

// Cek apakah session pelanggan ada, jika tidak arahkan ke halaman login
if (!isset($_SESSION['customer']['user_id']) || $_SESSION['customer']['role'] !== 'customer') {
    header('Location: login.php');
    exit();
}

// Validasi input
$product_id = isset($_POST['product_id']) ? filter_var($_POST['product_id'], FILTER_VALIDATE_INT) : 0;
$user_id = filter_var($_SESSION['customer']['user_id'], FILTER_VALIDATE_INT); // Perbaikan di sini
$rating = isset($_POST['rating']) ? filter_var($_POST['rating'], FILTER_VALIDATE_INT, [
    "options" => ["min_range" => 1, "max_range" => 5]
]) : 5;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

// Validasi kelengkapan data
if (!$product_id || !$user_id || !$comment) {
    echo "<script>alert('Semua field harus diisi'); window.history.back();</script>";
    exit;
}

try {
    // Mulai transaksi
    $conn->begin_transaction();

    // Cek apakah user sudah memesan produk
    $order_check_query = "
        SELECT o.id as order_id 
        FROM orders o 
        JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'completed'
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($order_check_query);
    if (!$stmt) {
        throw new Exception("Error preparing order check statement: " . $conn->error);
    }

    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $order_result = $stmt->get_result();
    $order_data = $order_result->fetch_assoc();

    if (!$order_data) {
        throw new Exception("Anda harus membeli produk ini terlebih dahulu sebelum dapat memberikan review.");
    }

    // Cek apakah user sudah memberikan review untuk produk ini
    $check_review = "SELECT id FROM reviews WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($check_review);
    if (!$stmt) {
        throw new Exception("Error preparing check review statement: " . $conn->error);
    }

    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $existing_review = $stmt->get_result()->fetch_assoc();

    if ($existing_review) {
        // Update review yang sudah ada
        $update_query = "UPDATE reviews 
                       SET rating = ?, 
                           comment = ?, 
                           updated_at = CURRENT_TIMESTAMP 
                       WHERE user_id = ? AND product_id = ?";
        
        $stmt = $conn->prepare($update_query);
        if (!$stmt) {
            throw new Exception("Error preparing update statement: " . $conn->error);
        }

        $stmt->bind_param("isii", $rating, $comment, $user_id, $product_id);
        if (!$stmt->execute()) {
            throw new Exception("Error updating review: " . $stmt->error);
        }
    } else {
        // Insert review baru
        $insert_query = "INSERT INTO reviews (order_id, product_id, user_id, rating, comment) 
                       VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($insert_query);
        if (!$stmt) {
            throw new Exception("Error preparing insert statement: " . $conn->error);
        }

        $stmt->bind_param("iiiis", $order_data['order_id'], $product_id, $user_id, $rating, $comment);
        if (!$stmt->execute()) {
            throw new Exception("Error inserting review: " . $stmt->error);
        }
    }

    // Commit transaksi
    $conn->commit();

    // Redirect kembali ke halaman produk dengan pesan sukses
    echo "<script>
            alert('Review berhasil ditambahkan');
            window.location.href='product_detail.php?id=" . $product_id . "';
          </script>";
    exit;

} catch (Exception $e) {
    // Rollback transaksi jika terjadi error
    $conn->rollback();
    echo "<script>
            alert('Error: " . addslashes($e->getMessage()) . "');
            window.history.back();
          </script>";
    exit;
}

// Jika bukan request POST, redirect ke halaman utama
header("Location: index.php");
exit;
?>
