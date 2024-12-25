<?php
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600);
session_start();

// Check authentication
if (!isset($_SESSION['customer']['user_id']) || $_SESSION['customer']['role'] !== 'customer') {
    header('Location: login.php');
    exit();
}

require_once('db.php');

// Database error handling
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Utility functions
function checkLogin() {
    if (!isset($_SESSION['customer']['user_id'])) {
        header("Location: login.php");
        exit;
    }
    return $_SESSION['customer']['user_id'];
}

function validateOrderOwnership($order_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ? AND status = 'accepted'");
    if (!$stmt) {
        die("Error preparing SQL: " . $conn->error);
    }
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function validateProductInOrder($order_id, $product_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT od.id 
                           FROM order_details od
                           WHERE od.order_id = ? 
                           AND od.product_id = ?");
    if (!$stmt) {
        die("Error preparing SQL: " . $conn->error);
    }
    $stmt->bind_param("ii", $order_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function checkExistingReview($order_id, $product_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM reviews 
                           WHERE order_id = ? 
                           AND product_id = ? 
                           AND user_id = ?");
    if (!$stmt) {
        die("Error preparing SQL: " . $conn->error);
    }
    $stmt->bind_param("iii", $order_id, $product_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function insertReview($order_id, $product_id, $user_id, $rating, $comment) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO reviews 
                           (order_id, product_id, user_id, rating, comment, created_at) 
                           VALUES (?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        die("Error preparing SQL: " . $conn->error);
    }
    $stmt->bind_param("iiiis", $order_id, $product_id, $user_id, $rating, $comment);
    return $stmt->execute();
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = checkLogin();
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = isset($_POST['comment']) ? sanitizeInput($_POST['comment']) : '';

    // Validate input
    if (!$order_id || !$product_id || $rating < 1 || $rating > 5 || empty($comment)) {
        $_SESSION['error'] = "Mohon lengkapi semua field dengan benar.";
        header("Location: my_orders.php");
        exit();
    }

    // Validate order ownership
    if (!validateOrderOwnership($order_id, $user_id)) {
        $_SESSION['error'] = "Anda tidak memiliki akses untuk memberikan ulasan pada pesanan ini.";
        header("Location: my_orders.php");
        exit();
    }

    // Validate product in order
    if (!validateProductInOrder($order_id, $product_id)) {
        $_SESSION['error'] = "Produk tidak ditemukan dalam pesanan ini.";
        header("Location: my_orders.php");
        exit();
    }

    // Check if review already exists
    if (checkExistingReview($order_id, $product_id, $user_id)) {
        $_SESSION['error'] = "Anda sudah memberikan ulasan untuk produk ini.";
        header("Location: my_orders.php");
        exit();
    }

    // Insert review
    if (insertReview($order_id, $product_id, $user_id, $rating, $comment)) {
        $_SESSION['success'] = "Terima kasih atas ulasan Anda!";
    } else {
        $_SESSION['error'] = "Terjadi kesalahan saat menyimpan ulasan. Silakan coba lagi.";
    }
    
    header("Location: my_orders.php");
    exit();
}
?>