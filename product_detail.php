<?php
// Mulai sesi untuk memeriksa status login
session_start();

// Enable error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Koneksi ke database
require_once('db.php');

// Pastikan koneksi database berhasil
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Koneksi ke database gagal. Silakan coba lagi nanti.");
}

try {
    // Ambil product_id dari URL dengan validasi
    $product_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : 0;

    if (!$product_id) {
        throw new Exception("Invalid product ID");
    }

    // Fetch detail produk dengan validasi error handling
    $product_query = "SELECT * FROM products WHERE id = ?";
    
    // Prepare statement dengan error handling
    $stmt = mysqli_prepare($conn, $product_query);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    // Bind parameter dan execute dengan error handling
    if (!mysqli_stmt_bind_param($stmt, "i", $product_id)) {
        throw new Exception("Error binding parameters: " . mysqli_stmt_error($stmt));
    }

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error executing query: " . mysqli_stmt_error($stmt));
    }

    // Get result dengan error handling
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        throw new Exception("Error getting result: " . mysqli_stmt_error($stmt));
    }

    $product = mysqli_fetch_assoc($result);

    // Periksa apakah produk ada
    if (!$product) {
        throw new Exception("Product not found");
    }

    // Clean up
    mysqli_stmt_close($stmt);

} catch (Exception $e) {
    // Log error
    error_log("Error in product_detail.php: " . $e->getMessage());
    
    // Redirect ke halaman utama dengan pesan error
    header("Location: index.php?error=" . urlencode("Produk tidak ditemukan atau terjadi kesalahan"));
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<style>
    .fab{
        color:rgb(0, 255, 68)
    }
</style>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <header class="bg-gradient-to-r from-gray-700 to-gray-900 text-white shadow-lg py-6 px-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Detail Produk</h1>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="container mx-auto px-6 py-4">
        <div class="flex items-center space-x-2 text-gray-600">
            <a href="index.php" class="hover:text-gray-900">Home</a>
            <i class="fas fa-chevron-right text-sm"></i>
            <a href="catalog.php" class="hover:text-gray-900"><?php echo htmlspecialchars($product['category'] ?? 'Produk'); ?></a>
            <i class="fas fa-chevron-right text-sm"></i>
            <span class="text-gray-900"><?php echo htmlspecialchars($product['name']); ?></span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="md:flex">
                <!-- Product Image Section -->
                <div class="md:w-1/2">
                    <div class="relative h-96 md:h-[600px] group">
                        <img class="w-full h-full object-cover transition duration-300 group-hover:scale-105" 
                             src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="absolute inset-0 bg-black bg-opacity-20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>
                </div>
                
                <!-- Product Info Section -->
                <div class="md:w-1/2 p-8 md:p-12">
                    <div class="space-y-6">
                        <!-- Category Badge -->
                        <span class="inline-block px-3 py-1 bg-indigo-100 text-indigo-600 rounded-full text-sm font-semibold tracking-wide">
                            <?php echo htmlspecialchars($product['category'] ?? 'Produk'); ?>
                        </span>

                        <!-- Product Title -->
                        <h1 class="text-4xl font-bold text-gray-900 leading-tight">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h1>

                        <!-- Price -->
                        <div class="flex items-center space-x-4">
                            <span class="text-3xl font-bold text-gray-900">
                                Rp <?php echo number_format($product['price'], 2, ',', '.'); ?>
                            </span>
                            <?php if(isset($product['old_price'])): ?>
                                <span class="text-xl text-gray-500 line-through">
                                    Rp <?php echo number_format($product['old_price'], 2, ',', '.'); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Description -->
                        <div class="prose max-w-none text-gray-600">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        </div>

                        <!-- Stock Status -->
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span class="text-green-500">Stok tersedia</span>
                        </div>

                        <!-- Add to Cart Section -->
<div class="space-y-4">
    <div class="flex items-center space-x-4">
        <div class="flex items-center border rounded-lg">
            <button class="px-4 py-2 hover:bg-gray-100" onclick="updateQuantity(-1)">-</button>
            <input type="number" id="quantity" value="1" min="1" class="w-16 text-center border-x py-2 focus:outline-none">
            <button class="px-4 py-2 hover:bg-gray-100" onclick="updateQuantity(1)">+</button>
        </div>
        <button onclick="addToCart(<?php echo $product['id']; ?>)"
                class="flex-1 bg-indigo-600 text-white px-8 py-3 rounded-lg hover:bg-indigo-700 
                       transition duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 
                       focus:ring-indigo-500 focus:ring-offset-2 shadow-lg">
            <i class="fas fa-shopping-cart mr-2"></i>
            Tambah ke Keranjang
        </button>
    </div>
</div>
                        <!-- Additional Info -->
                        <div class="border-t pt-6 space-y-4">
                            <div class="flex items-center space-x-4 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <i class="fas fa-truck mr-2"></i>
                                    Pengiriman Gratis
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-shield-alt mr-2"></i>
                                    Garansi 100%
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-undo mr-2"></i>
                                    30 Hari Pengembalian
                                </div>
                                <a href="https://wa.me/628123456789" target="_blank">
                                <div class="flex-1 bg-gray-700 text-white px-8 py-3 rounded-lg hover:bg-gray-800 
                       transition duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 
                       focus:ring-gray-600 focus:ring-offset-2 shadow-lg items-center">
                                    <i class="fab fa-whatsapp mr-2"></i>
                                    Chat Penjual    
                                </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
// Check if user is logged in - updated to match your login system
$is_logged_in = isset($_SESSION['customer']) || isset($_SESSION['admin']);
$current_user_id = null;

if (isset($_SESSION['customer'])) {
    $current_user_id = $_SESSION['customer']['user_id'];
} elseif (isset($_SESSION['admin'])) {
    $current_user_id = $_SESSION['admin']['user_id'];
}

// Reviews Query
$reviews_query = "
    SELECT r.*, u.username 
    FROM reviews r 
    INNER JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
";

$stmt = mysqli_prepare($conn, $reviews_query);
if (!$stmt) {
    die("Error preparing statement: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$reviews_result = mysqli_stmt_get_result($stmt);

// Check if user has ordered this product
$can_review = false;
if ($is_logged_in && $current_user_id) {
    $order_check_query = "
        SELECT COUNT(*) as has_ordered 
        FROM orders o 
        JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'completed'
    ";
    
    $order_stmt = mysqli_prepare($conn, $order_check_query);
    if ($order_stmt) {
        mysqli_stmt_bind_param($order_stmt, "ii", $current_user_id, $product_id);
        mysqli_stmt_execute($order_stmt);
        $order_result = mysqli_stmt_get_result($order_stmt);
        $order_data = mysqli_fetch_assoc($order_result);
        $can_review = $order_data['has_ordered'] > 0;
        mysqli_stmt_close($order_stmt);
    }
}
?>

<div class="container mx-auto px-6 py-8">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden p-8">
        <h2 class="text-2xl font-bold mb-6">Reviews Produk</h2>
        
        <!-- Review Form -->
        <?php if ($is_logged_in): ?>
            <?php if ($can_review): ?>
                <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Tulis Review</h3>
                    <form action="add_review.php" method="POST" class="space-y-4">
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($current_user_id); ?>">
                        
                        <!-- Rating -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                            <div class="flex space-x-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button" 
                                        class="text-2xl star-rating hover:text-yellow-500 text-gray-400 transition duration-200" 
                                        onclick="setRating(<?php echo $i; ?>)" 
                                        data-rating="<?php echo $i; ?>">★</button>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="rating" value="5">
                        </div>
                        
                        <!-- Comment -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Komentar</label>
                            <textarea name="comment" required rows="4" 
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                        
                        <button type="submit" 
                            class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 
                                   transition duration-300 focus:outline-none focus:ring-2 
                                   focus:ring-indigo-500 focus:ring-offset-2">
                            Kirim Review
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="mb-8 p-6 bg-gray-50 rounded-lg text-center">
                    <p>Anda harus membeli produk ini terlebih dahulu sebelum dapat memberikan review.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="mb-8 p-6 bg-gray-50 rounded-lg text-center">
                <p>Silakan <a href="login.php" class="text-indigo-600 hover:text-indigo-800">login</a> untuk menulis review</p>
            </div>
        <?php endif; ?>

        <!-- Reviews List -->
        <div class="space-y-6">
            <?php if ($reviews_result && mysqli_num_rows($reviews_result) > 0): ?>
                <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                    <div class="border-b pb-6 last:border-b-0">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <span class="font-semibold mr-2"><?php echo htmlspecialchars($review['username']); ?></span>
                                <div class="flex text-yellow-400">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span><?php echo $i <= $review['rating'] ? '★' : '☆'; ?></span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <span class="text-sm text-gray-500">
                                <?php echo date('d M Y', strtotime($review['created_at'])); ?>
                            </span>
                        </div>
                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    Belum ada review untuk produk ini
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-y-full transition-transform duration-300 opacity-0">
        Produk berhasil ditambahkan ke keranjang
    </div>
    
    <script>
    // Function to update quantity
    function updateQuantity(change) {
        var quantityInput = document.getElementById('quantity');
        var newQuantity = parseInt(quantityInput.value) + change;
        if (newQuantity >= 1) {
            quantityInput.value = newQuantity;
        }
    }


    function showToast(message) {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
        setTimeout(() => {
            toast.style.transform = 'translateY(100%)';
            toast.style.opacity = '0';
        }, 3000);
    }

    // Function to add product to cart using AJAX
function addToCart(productId) {
    var quantity = document.getElementById('quantity').value;

    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'add_to_cart.php?id=' + productId + '&quantity=' + quantity, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            // Show the success notification
            var notification = document.getElementById('toast');
            notification.classList.remove('opacity-0', 'translate-y-5'); // Make the notification visible
            notification.classList.add('opacity-100', 'translate-y-0'); // Add animation for smooth appearance

            // Hide the notification after 3 seconds
            setTimeout(function() {
                notification.classList.remove('opacity-100', 'translate-y-0');
                notification.classList.add('opacity-0', 'translate-y-5');
            }, 3000); // Hide after 3 seconds
        }
    };
    xhr.send();
}


    function setRating(rating) {
        const stars = document.querySelectorAll('.star-rating');
        document.getElementById('rating').value = rating;
        stars.forEach(star => {
            if (parseInt(star.getAttribute('data-rating')) <= rating) {
                star.classList.add('text-yellow-500');
                star.classList.remove('text-gray-400');
            } else {
                star.classList.add('text-gray-400');
                star.classList.remove('text-yellow-500');
            }
        });
    }

    // Initialize rating stars
    document.addEventListener('DOMContentLoaded', function() {
        setRating(5); // Default rating
    });
    </script>
</body>
</html>