<?php
// Start session at the beginning
session_start();

// Periksa apakah session customer ada
if (!isset($_SESSION['customer']['user_id']) || $_SESSION['customer']['role'] !== 'customer') {
    header('Location: login.php');
    exit();
}

// Database connection
require_once('db.php');

// Cek jika koneksi berhasil
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

// Update status pesanan dengan prepared statement
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
    
    // Validate order ownership for customer actions
    if (in_array($action, ['accept_order']) && !validateOrderOwnership($order_id, $user_id)) {
        die("Unauthorized access");
    }
    
    switch($action) {
        case 'confirm_order':
            if ($_SESSION['customer']['role'] === 'admin') {
                updateOrderStatus($order_id, 'confirmed', 'Pesanan dikonfirmasi oleh admin');
            }
            break;
            
        case 'package_order':
            if ($_SESSION['customer']['role'] === 'admin') {
                updateOrderStatus($order_id, 'packaging', 'Pesanan sedang dikemas');
            }
            break;
            
        case 'ship_order':
            if ($_SESSION['customer']['role'] === 'admin') {
                $tracking_number = sanitizeInput($_POST['tracking_number'] ?? '');
                $shipping_notes = "Nomor Resi: " . $tracking_number;
                updateOrderStatus($order_id, 'shipped', $shipping_notes);
            }
            break;
            
        case 'accept_order':
            updateOrderStatus($order_id, 'accepted', 'Pesanan telah diterima oleh customer');
            break;
            
        case 'cancel_order':
            if ($_SESSION['customer']['role'] === 'admin') {
                updateOrderStatus($order_id, 'cancelled', 'Pesanan dibatalkan');
            }
            break;
    }
    
    // Redirect based on user role
    $redirect_page = ($_SESSION['customer']['role'] === 'admin') ? 'manage_orders.php' : 'my_orders.php';
    header("Location: $redirect_page");
    exit;
}

// Display orders page
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
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold mb-6">Pesanan Saya</h1>
        
        <?php
        $user_id = checkLogin();
        
        // Prepared statement untuk mengambil pesanan hanya milik user yang login
        $stmt = $conn->prepare("
            SELECT o.*, ot.notes, ot.created_at as tracking_time
            FROM orders o
            LEFT JOIN order_tracking ot ON o.id = ot.order_id
            WHERE o.user_id = ?  -- Pastikan hanya pesanan milik user yang login yang ditampilkan
            ORDER BY o.created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($order = $result->fetch_assoc()) {
                ?>
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold">Order #<?php echo htmlspecialchars($order['id']); ?></h2>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                            <?php
                            $statusClasses = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'confirmed' => 'bg-blue-100 text-blue-800',
                                'packaging' => 'bg-purple-100 text-purple-800',
                                'shipped' => 'bg-indigo-100 text-indigo-800',
                                'accepted' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800'
                            ];
                            echo $statusClasses[$order['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>">
                            <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                        </span>
                    </div>

                    <!-- Timeline tracking -->
                    <div class="space-y-4">
                        <?php
                        $track_stmt = $conn->prepare("
                            SELECT * FROM order_tracking 
                            WHERE order_id = ? 
                            ORDER BY created_at DESC
                        ");
                        $track_stmt->bind_param("i", $order['id']);
                        $track_stmt->execute();
                        $tracking = $track_stmt->get_result();
                        
                        while ($track = $tracking->fetch_assoc()) {
                            ?>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="h-4 w-4 rounded-full bg-blue-500"></div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium"><?php echo ucfirst(htmlspecialchars($track['status'])); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($track['notes']); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo date('d/m/Y H:i', strtotime($track['created_at'])); ?></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Tombol terima pesanan -->
                    <?php if (isset($order['status']) && $order['status'] === 'shipped') { ?>
                        <div class="mt-6">
                            <a href="?action=accept_order&order_id=<?php echo htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8'); ?>" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            aria-label="Konfirmasi bahwa pesanan telah diterima">
                                Pesanan Diterima
                            </a>
                        </div>
                    <?php } ?>

                    <!-- Form review -->
                    <?php if ($order['status'] === 'accepted' && empty($order['review'])) { ?>
                        <div class="mt-6">
                            <form method="POST" action="submit_review.php">
                                <textarea name="review" class="w-full border rounded-md p-2" placeholder="Tulis ulasan pesanan"></textarea>
                                <button type="submit" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded-md">Kirim Ulasan</button>
                            </form>
                        </div>
                    <?php } ?>
                </div>
                <?php
            }
        } else {
            echo "<p class='text-lg'>Anda belum melakukan pesanan.</p>";
        }
        ?>
    </div>
</body>
</html>
