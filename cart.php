<?php 
// Start session to check login status
session_start();

// Check if customer session exists, if not redirect to login page
if (!isset($_SESSION['customer']['user_id']) || $_SESSION['customer']['role'] !== 'customer') {
    header('Location: login.php');
    exit();
}

require_once 'config.php'; // Buat file config.php untuk database connection

// Fungsi untuk membuat order baru
function createOrder($pdo, $user_id, $total_price, $shipping_address) {
    try {
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, status, shipping_address) VALUES (?, ?, 'pending', ?)");
        $stmt->execute([$user_id, $total_price, $shipping_address]);
        return $pdo->lastInsertId();
    } catch(PDOException $e) {
        return false;
    }
}


// Fungsi untuk menyimpan detail order
function saveOrderDetails($pdo, $order_id, $cart_items) {
    try {
        $stmt = $pdo->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($cart_items as $product_id => $item) {
            $stmt->execute([$order_id, $product_id, $item['quantity'], $item['price']]);
        }
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

if (isset($_POST['checkout']) && !empty($_SESSION['cart'])) {
    if (!isset($_SESSION['customer']['user_id'])) {
        $_SESSION['error'] = "Silakan login terlebih dahulu untuk melakukan checkout.";
        header("Location: login.php");
        exit;
    }

    if (empty($_POST['shipping_address'])) {
        $_SESSION['error'] = "Alamat pengiriman wajib diisi.";
        header("Location: cart.php");
        exit;
    }

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=toko_online", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $shipping_address = $_POST['shipping_address'];

        // Hitung total
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // Buat order baru
        $order_id = createOrder($pdo, $_SESSION['customer']['user_id'], $total, $shipping_address);

        if ($order_id && saveOrderDetails($pdo, $order_id, $_SESSION['cart'])) {
            // Reset cart setelah order berhasil dibuat
            unset($_SESSION['cart']);
            // Redirect ke halaman checkout
            header("Location: checkout.php?order_id=" . $order_id);
            exit;
        } else {
            $_SESSION['error'] = "Terjadi kesalahan saat memproses pesanan Anda.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan pada sistem.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 font-[Inter]">
    <header class="bg-gradient-to-r from-gray-700 to-gray-900 text-white shadow-lg py-6 px-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Keranjang Belanja</h1>
            <div class="flex gap-4">
                <a href="catalog.php" class="text-white bg-gray-500 px-4 py-2 rounded-lg hover:bg-gray-600">
                    Kembali ke Katalog
                </a>
                <?php if (!isset($_SESSION['customer']['user_id'])): ?>
                    <a href="login.php" class="text-white bg-green-500 px-4 py-2 rounded-lg hover:bg-green-600">
                        Login untuk Checkout
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-3xl font-bold mb-6">Keranjang Anda</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['cart'])): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama Produk
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Harga
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jumlah
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Subtotal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        $total = 0;
                        foreach ($_SESSION['cart'] as $product_id => $item): 
                            $subtotal = $item['price'] * $item['quantity'];
                            $total += $subtotal;
                        ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    Rp <?php echo number_format($item['price'], 2, ',', '.'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form action="update_cart.php" method="POST" class="flex items-center space-x-2">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                               min="1" class="w-20 rounded border-gray-300 shadow-sm">
                                        <button type="submit" class="text-blue-600 hover:text-blue-800">
                                            Update
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    Rp <?php echo number_format($subtotal, 2, ',', '.'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form action="remove_from_cart.php" method="POST" class="inline">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right font-bold">Total:</td>
                            <td class="px-6 py-4 font-bold">
                                Rp <?php echo number_format($total, 2, ',', '.'); ?>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="mt-6">
                    <form method="POST" class="space-y-4">
                        <div>
                            <label for="shipping_address" class="block text-sm font-medium text-gray-700">Alamat Pengiriman</label>
                            <textarea id="shipping_address" name="shipping_address" rows="4" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <input type="hidden" name="checkout" value="1">
                        <div class="flex justify-end">
                        <button class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">
                            Lanjut ke Pembayaran
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <p class="text-center text-lg">Keranjang Anda kosong.</p>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
