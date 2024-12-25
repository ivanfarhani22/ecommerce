<?php
session_start();
// Database connection
$host = "localhost";
$dbname = "toko_online";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// First, alter the payments table to include PayPal and Bank Mandiri
try {
    $alter_payments = "ALTER TABLE payments 
        MODIFY COLUMN payment_method ENUM('credit_card', 'bank_transfer', 'e-wallet', 'paypal', 'bank_mandiri') NOT NULL,
        ADD COLUMN transaction_id VARCHAR(100) AFTER payment_method,
        ADD COLUMN amount DECIMAL(10,2) NOT NULL AFTER transaction_id";
    $pdo->exec($alter_payments);
} catch(PDOException $e) {
    // Table might already be altered, continue
}

// Process payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $order_id = $_POST['order_id'] ?? null;
    $payment_method = $_POST['payment_method'];
    $amount = $_POST['amount'] ?? 0;

    if ($order_id && $amount) {
        try {
            // Insert payment record
            $stmt = $pdo->prepare("INSERT INTO payments (order_id, payment_method, amount, payment_status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$order_id, $payment_method, $amount]);
            $payment_id = $pdo->lastInsertId();

            // Process based on payment method
            if ($payment_method === 'paypal') {
                $response = [
                    'success' => true,
                    'payment_method' => 'paypal',
                    'redirect_url' => 'https://www.sandbox.paypal.com' // Replace with actual PayPal integration
                ];
            } elseif ($payment_method === 'bank_mandiri') {
                $va_number = "8888" . str_pad($order_id, 8, "0", STR_PAD_LEFT);
                $response = [
                    'success' => true,
                    'payment_method' => 'bank_mandiri',
                    'virtual_account' => $va_number,
                    'amount' => $amount,
                    'expires_in' => '24 hours'
                ];
            }

            if (isset($response)) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
        } catch(PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Payment processing failed']);
            exit;
        }
    }
}

// Get order details if order_id is provided
$order_details = null;
$order_id = $_GET['order_id'] ?? null;

if ($order_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, u.username, u.email 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$order_id]);
        $order_details = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Error fetching order details: " . $e->getMessage();
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Toko Online</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <?php if ($order_details): ?>
            <div class="max-w-2xl mx-auto">
                <h1 class="text-2xl font-bold mb-6">Checkout</h1>
                
                <!-- Order Summary -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">Ringkasan Pesanan</h2>
                    <div class="space-y-2">
                        <p><span class="font-medium">Order ID:</span> #<?php echo $order_details['id']; ?></p>
                        <p><span class="font-medium">Total:</span> Rp <?php echo number_format($order_details['total_price'], 2); ?></p>
                        <p><span class="font-medium">Status:</span> <?php echo ucfirst($order_details['status']); ?></p>
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Pilih Metode Pembayaran</h2>
                    
                    <form id="paymentForm" class="space-y-4">
                        <input type="hidden" name="order_id" value="<?php echo $order_details['id']; ?>">
                        <input type="hidden" name="amount" value="<?php echo $order_details['total_price']; ?>">
                        
                        <!-- PayPal Option -->
                        <div class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="paypal" id="paypal" class="mr-3">
                            <label for="paypal" class="flex items-center cursor-pointer">
                                <span class="font-medium">PayPal</span>
                            </label>
                        </div>
                        
                        <!-- Bank Mandiri Option -->
                        <div class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="bank_mandiri" id="mandiri" class="mr-3">
                            <label for="mandiri" class="flex items-center cursor-pointer">
                                <span class="font-medium">Transfer Bank Mandiri</span>
                            </label>
                        </div>
                        
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                            Lanjutkan Pembayaran
                        </button>
                    </form>
                </div>

                <!-- Payment Instructions -->
                <div id="paymentInstructions" class="mt-6 bg-white rounded-lg shadow-md p-6 hidden"></div>
            </div>
        <?php else: ?>
            <div class="text-center">
                <p class="text-red-600">Order tidak ditemukan.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('paymentForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('checkout.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    if (data.payment_method === 'paypal') {
                        window.location.href = data.redirect_url;
                    } else if (data.payment_method === 'bank_mandiri') {
                        displayMandiriInstructions(data);
                    }
                } else {
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            }
        });
        
        function displayMandiriInstructions(data) {
            const instructions = document.getElementById('paymentInstructions');
            instructions.innerHTML = `
                <h2 class="text-xl font-bold mb-4">Instruksi Pembayaran Bank Mandiri</h2>
                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="font-bold">Nomor Virtual Account:</p>
                        <p class="text-xl">${data.virtual_account}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="font-bold">Jumlah yang harus dibayar:</p>
                        <p class="text-xl">Rp ${parseFloat(data.amount).toLocaleString('id-ID', {minimumFractionDigits: 2})}</p>
                    </div>
                    <div>
                        <p class="font-bold mb-2">Langkah-langkah pembayaran:</p>
                        <ol class="list-decimal list-inside space-y-2">
                            <li>Login ke Mobile Banking atau Internet Banking Mandiri</li>
                            <li>Pilih menu Pembayaran</li>
                            <li>Pilih Virtual Account</li>
                            <li>Masukkan nomor Virtual Account: ${data.virtual_account}</li>
                            <li>Konfirmasi detail pembayaran</li>
                            <li>Masukkan PIN/Password</li>
                        </ol>
                    </div>
                    <div class="text-sm text-gray-600">
                        <p>* Pembayaran akan kadaluarsa dalam ${data.expires_in}</p>
                    </div>
                </div>
            `;
            instructions.classList.remove('hidden');
        }
    </script>
</body>
</html>