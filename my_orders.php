<?php
// Start session at the beginning
session_start();

// Check if customer session exists
if (!isset($_SESSION['customer']['user_id']) || $_SESSION['customer']['role'] !== 'customer') {
    header('Location: login.php');
    exit();
}

// Database connection
require_once('db.php');

// Check if connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
function checkLogin() {
    if (!isset($_SESSION['customer']['user_id'])) {
        header("Location: login.php");
        exit;
    }
    return $_SESSION['customer']['user_id'];
}

// Validate order belongs to user
function validateOrderOwnership($order_id, $user_id) {
    global $conn;
    $order_id = (int)$order_id;
    $user_id = (int)$user_id;
    
    $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Update order status with prepared statement
function updateOrderStatus($order_id, $status, $notes = '') {
    global $conn;
    $timestamp = date('Y-m-d H:i:s');
    
    // Update orders table
    $stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $timestamp, $order_id);
    $stmt->execute();
    
    // Insert into tracking
    $stmt = $conn->prepare("INSERT INTO order_tracking (order_id, status, notes, created_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $order_id, $status, $notes, $timestamp);
    return $stmt->execute();
}

// Validate and sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    $user_id = checkLogin();
    $action = $_GET['action'] ?? '';
    $order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
    
    if (!$order_id) {
        die("Invalid order ID");
    }
    
    if (in_array($action, ['accept_order']) && !validateOrderOwnership($order_id, $user_id)) {
        die("Unauthorized access");
    }
    
    switch($action) {
        case 'accept_order':
            updateOrderStatus($order_id, 'accepted', 'Pesanan telah diterima oleh customer');
            break;
    }
    
    header("Location: my_orders.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
<header class="bg-gradient-to-r from-gray-700 to-gray-900 text-white shadow-lg py-6 px-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Status Pesanan</h1>
            <div class="flex gap-4">
                <a href="catalog.php" class="text-white bg-gray-500 px-4 py-2 rounded-lg hover:bg-gray-600">
                    Kembali
                </a>
            </div>
        </div>
    </header>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Pesanan Berlangsung -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-6">Pesanan Berlangsung</h2>
            <?php
            $user_id = checkLogin();
            
            // Get ongoing orders (pending, confirmed, packaging, shipped)
            $ongoing_stmt = $conn->prepare("
    SELECT o.*, u.username, o.shipping_address, ot.notes, ot.created_at as tracking_time,
           GROUP_CONCAT(p.name SEPARATOR ', ') AS products,
           GROUP_CONCAT(p.image_url SEPARATOR ', ') AS images,
           GROUP_CONCAT(od.quantity SEPARATOR ', ') AS quantities
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN order_details od ON o.id = od.order_id
    LEFT JOIN products p ON od.product_id = p.id
    LEFT JOIN (
        SELECT order_id, notes, created_at, 
               ROW_NUMBER() OVER (PARTITION BY order_id ORDER BY created_at DESC) as rn
        FROM order_tracking
    ) ot ON o.id = ot.order_id AND ot.rn = 1
    WHERE o.user_id = ? 
    AND o.status IN ('pending', 'confirmed', 'packaging', 'shipped')
    GROUP BY o.id
    ORDER BY o.created_at DESC
");

            $ongoing_stmt->bind_param("i", $user_id);
            $ongoing_stmt->execute();
            $ongoing_result = $ongoing_stmt->get_result();
            
            if ($ongoing_result->num_rows > 0) {
                while ($order = $ongoing_result->fetch_assoc()) {
                    include 'order_card.php';
                }
            } else {
                echo "<p class='text-lg'>Tidak ada pesanan yang sedang berlangsung.</p>";
            }
            ?>
        </div>

        <!-- Riwayat Pesanan -->
        <div>
            <h2 class="text-2xl font-bold mb-6">Riwayat Pesanan</h2>
            <?php
            // Get completed orders (accepted, cancelled)
            $history_stmt = $conn->prepare("
    SELECT o.*, u.username, o.shipping_address, ot.notes, ot.created_at as tracking_time,
           GROUP_CONCAT(p.name SEPARATOR ', ') AS products,
           GROUP_CONCAT(p.image_url SEPARATOR ', ') AS images,
           GROUP_CONCAT(od.quantity SEPARATOR ', ') AS quantities
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN order_details od ON o.id = od.order_id
    LEFT JOIN products p ON od.product_id = p.id
    LEFT JOIN (
        SELECT order_id, notes, created_at, 
               ROW_NUMBER() OVER (PARTITION BY order_id ORDER BY created_at DESC) as rn
        FROM order_tracking
    ) ot ON o.id = ot.order_id AND ot.rn = 1
    WHERE o.user_id = ? 
    AND o.status IN ('accepted', 'cancelled')
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
            $history_stmt->bind_param("i", $user_id);
            $history_stmt->execute();
            $history_result = $history_stmt->get_result();
            
            if ($history_result->num_rows > 0) {
                while ($order = $history_result->fetch_assoc()) {
                    include 'order_card.php';
                }
            } else {
                echo "<p class='text-lg'>Tidak ada riwayat pesanan.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>