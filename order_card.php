<!-- order_card.php -->
<div class="bg-white shadow-lg rounded-2xl p-6 mb-8 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
    <!-- Header Section with Improved Layout -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="flex items-start gap-4 w-full md:w-auto">
            <!-- Enhanced Order Icon -->
<div class="rounded-xl p-3.5 shadow-sm">
    <?php
        // Memeriksa apakah gambar tersedia
        if (isset($order['images'])) {
            // Memecah gambar yang disimpan sebagai string menjadi array
            $images = explode(', ', $order['images']);
            foreach ($images as $image) {
                // Menampilkan gambar satu per satu
                echo '<img src="' . htmlspecialchars($image) . '" alt="Order Image" class="w-20 h-20 object-cover rounded-md my-2">';
            }
        }
    ?>
</div>
            <!-- Enhanced Order Details -->
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2">
                <h3 class="text-lg font-bold text-gray-900 tracking-tight">
                    <?php
                    $products = explode(', ', $order['products']);
                    echo htmlspecialchars($products[0]); // Menampilkan nama produk pertama
                    ?>
                </h3>
                    <div class="h-1 w-1 rounded-full bg-gray-300 hidden md:block"></div>
                    <p class="text-sm text-gray-500 font-medium">
                        <?php echo date('d F Y', strtotime($order['created_at'])); ?>
                    </p>
                </div>
                <p class="text-base font-semibold text-indigo-600 mt-1.5 flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?>
                </p>
                <p class="mt-3"><strong>Alamat Pengiriman:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                <p class="mt-3"><strong>No Resi:</strong> <?php echo htmlspecialchars($order['tracking_number']); ?></p>
            </div>
        </div>

        <!-- Enhanced Status Badge -->
        <div class="order-first md:order-last w-full md:w-auto">
            <?php
            $statusClasses = [
                'pending' => 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
                'confirmed' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                'packaging' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
                'shipped' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                'accepted' => 'bg-green-50 text-green-700 ring-green-600/20',
                'cancelled' => 'bg-red-50 text-red-700 ring-red-600/20'
            ];
            
            $statusIcons = [
                'pending' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />',
                'confirmed' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
                'packaging' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />',
                'shipped' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />',
                'accepted' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />',
                'cancelled' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />'
            ];

            $statusLabels = [
                'pending' => 'Menunggu Konfirmasi',
                'confirmed' => 'Dikonfirmasi',
                'packaging' => 'Sedang Dikemas',
                'shipped' => 'Dikirim',
                'accepted' => 'Diterima',
                'cancelled' => 'Dibatalkan'
            ];
            ?>
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-medium text-sm ring-1 ring-inset <?php echo $statusClasses[$order['status']] ?? 'bg-gray-100 text-gray-800'; ?> shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <?php echo $statusIcons[$order['status']] ?? ''; ?>
                </svg>
                <?php echo $statusLabels[$order['status']] ?? ucfirst($order['status']); ?>
            </div>
        </div>
    </div>

    <!-- Enhanced Timeline Section -->
    <div class="space-y-6 border-t border-gray-100 pt-6">
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
            $statusClasses = [
                'pending' => 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
                'confirmed' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                'packaging' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
                'shipped' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                'accepted' => 'bg-green-50 text-green-700 ring-green-600/20',
                'cancelled' => 'bg-red-50 text-red-700 ring-red-600/20'
            ];
            
            $statusIcons = [
                'pending' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />',
                'confirmed' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
                'packaging' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />',
                'shipped' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />',
                'accepted' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />',
                'cancelled' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />'
            ];
            
            $statusLabels = [
                'pending' => 'Menunggu Konfirmasi',
                'confirmed' => 'Dikonfirmasi',
                'packaging' => 'Sedang Dikemas',
                'shipped' => 'Dikirim',
                'accepted' => 'Diterima',
                'cancelled' => 'Dibatalkan'
            ];
            
            ?>
            <div class="flex items-start gap-4">
                <div class="relative flex h-6 w-6 flex-none items-center justify-center">
                    <div class="h-2 w-2 rounded-full bg-<?php echo $statusColor; ?>-500 ring-2 ring-<?php echo $statusColor; ?>-500 ring-offset-4 ring-offset-white"></div>
                    <?php if ($track !== $tracking->fetch_assoc()) { ?>
                        <div class="absolute top-3 bottom-0 left-3 w-px bg-gray-200"></div>
                    <?php } ?>
                </div>
                <div class="flex-auto">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:gap-2">
                        <p class="text-sm font-semibold text-gray-900">
                            <?php echo $statusLabels[$track['status']] ?? ucfirst($track['status']); ?>
                        </p>
                        <p class="text-xs text-gray-500 font-medium">
                            <?php echo date('d M Y, H:i', strtotime($track['created_at'])); ?>
                        </p>
                    </div>
                    <?php if (!empty($track['notes'])) { ?>
                        <p class="mt-1 text-sm text-gray-600"><?php echo htmlspecialchars($track['notes']); ?></p>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>

    <!-- Enhanced Action Buttons -->
    <?php if ($order['status'] === 'shipped') { ?>
        <div class="mt-6 border-t border-gray-100 pt-6">
            <button 
                onclick="confirmAcceptOrder(<?php echo htmlspecialchars($order['id']); ?>)"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Konfirmasi Penerimaan Pesanan
            </button>
        </div>
    <?php } ?>

    <?php if (isset($order['status']) && $order['status'] === 'accepted') { ?>
    <div class="mt-6 border-t border-gray-100 pt-6">
        <?php
        // Fetch products from this order that haven't been reviewed
        $review_stmt = $conn->prepare("
            SELECT 
                od.product_id,
                p.name as product_name,
                CASE WHEN r.id IS NULL THEN 0 ELSE 1 END as is_reviewed
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            LEFT JOIN reviews r ON r.order_id = od.order_id 
                AND r.product_id = od.product_id 
                AND r.user_id = ?
            WHERE od.order_id = ?
        ");
        $review_stmt->bind_param("ii", $_SESSION['customer']['user_id'], $order['id']);
        $review_stmt->execute();
        $products = $review_stmt->get_result();
        
        $hasUnreviewedProducts = false;
        while ($product = $products->fetch_assoc()) {
            if ($product['is_reviewed'] == 0) {
                $hasUnreviewedProducts = true;
                ?>
                <form method="POST" action="submit_review.php" class="space-y-4 mb-6">
                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                    
                    <div class="text-lg font-semibold text-gray-900 mb-4">
                        Review untuk: <?php echo htmlspecialchars($product['product_name']); ?>
                    </div>
                    
                    <div>
                        <label for="review_<?php echo $product['product_id']; ?>" class="block text-sm font-semibold text-gray-900 mb-2">
                            Bagaimana pengalaman Anda dengan produk ini?
                        </label>
                        <textarea 
                            id="review_<?php echo $product['product_id']; ?>"
                            name="comment"
                            rows="3" 
                            required
                            maxlength="500"
                            class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            placeholder="Tulis ulasan Anda di sini..."></textarea>
                    </div>
                    
                    <div>
                        <label for="rating_<?php echo $product['product_id']; ?>" class="block text-sm font-semibold text-gray-900 mb-2">
                            Rating (1-5)
                        </label>
                        <select 
                            id="rating_<?php echo $product['product_id']; ?>" 
                            name="rating" 
                            required 
                            class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                        >
                            <option value="" disabled selected>Pilih rating Anda</option>
                            <option value="1">1 - Sangat Buruk</option>
                            <option value="2">2 - Buruk</option>
                            <option value="3">3 - Cukup</option>
                            <option value="4">4 - Baik</option>
                            <option value="5">5 - Sangat Baik</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        Kirim Ulasan
                    </button>
                </form>
                <?php
            }
        }
        
        if (!$hasUnreviewedProducts) { ?>
            <p class="text-gray-500">Semua produk dalam pesanan ini telah direview.</p>
        <?php } ?>
    </div>
<?php } ?>
</div>

<!-- Enhanced SweetAlert2 Styling and Script -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmAcceptOrder(orderId) {
    Swal.fire({
        title: 'Konfirmasi Penerimaan',
        html: `
            <div class="text-left">
                <p class="mb-4 text-gray-600">Pastikan Anda telah:</p>
                <ul class="text-sm space-y-3">
                    <li class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full bg-green-100">
                            <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-gray-700">Menerima paket pesanan</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full bg-green-100">
                            <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-gray-700">Memeriksa kelengkapan pesanan</span>
                    </li>
                </ul>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Ya, Sudah Saya Terima',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'swal-wide',
            title: 'swal-title',
            htmlContainer: 'swal-html-container',
            confirmButton: 'swal-confirm-button',
            cancelButton: 'swal-cancel-button',
            icon: 'swal-icon'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                html: `
                    <div class="flex flex-col items-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mb-4"></div>
                        <p class="text-gray-600">Mohon tunggu sebentar</p>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal-loading'
                },
                didOpen: () => {
                    window.location.href = `?action=accept_order&order_id=${orderId}`;
                }
            });
        }
    });
}
</script>

<style>
/* Enhanced SweetAlert2 Styling */
.swal-wide {
    width: 480px !important;
    padding: 2rem !important;
    border-radius: 1.25rem !important;
    background: linear-gradient(to bottom, #ffffff, #f8fafc) !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
}

.swal-title {
    font-size: 1.5rem !important;
    font-weight: 600 !important;
    color: #1F2937 !important;
    padding: 0 !important;
    margin-bottom: 1.5rem !important;
}

.swal-html-container {
    text-align: left !important;
    margin: 0 !important;
    padding: 0 !important;
    color: #4B5563 !important;
}

.swal-icon {
    border-color: #10B981 !important;
    color: #10B981 !important;
}

.swal-confirm-button {
    padding: 0.75rem 1.5rem !important;
    background: linear-gradient(to right, #10B981, #059669) !important;
    color: white !important;
    border-radius: 0.75rem !important;
    font-weight: 600 !important;
    font-size: 0.875rem !important;
    transition: all 0.2s !important;
    box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.1), 0 2px 4px -1px rgba(16, 185, 129, 0.06) !important;
}

.swal-confirm-button:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 8px -1px rgba(16, 185, 129, 0.15), 0 3px 6px -1px rgba(16, 185, 129, 0.1) !important;
}

.swal-cancel-button {
    padding: 0.75rem 1.5rem !important;
    background: #F3F4F6 !important;
    color: #4B5563 !important;
    border-radius: 0.75rem !important;
    font-weight: 600 !important;
    font-size: 0.875rem !important;
    transition: all 0.2s !important;
}

.swal-cancel-button:hover {
    background: #E5E7EB !important;
}

.swal-loading {
    width: 320px !important;
    padding: 2rem !important;
    border-radius: 1.25rem !important;
    background: linear-gradient(to bottom, #ffffff, #f8fafc) !important;
}

/* Custom Animation Classes */
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Responsive Adjustments */
@media (max-width: 640px) {
    .swal-wide {
        width: 90% !important;
        padding: 1.5rem !important;
    }
    
    .swal-loading {
        width: 90% !important;
        padding: 1.5rem !important;
    }
    
    .swal-title {
        font-size: 1.25rem !important;
        margin-bottom: 1rem !important;
    }
    
    .swal-confirm-button,
    .swal-cancel-button {
        padding: 0.625rem 1.25rem !important;
        font-size: 0.875rem !important;
    }
}

/* Enhanced Timeline Styles */
.timeline-dot {
    position: relative;
    width: 1rem;
    height: 1rem;
    border-radius: 9999px;
}

.timeline-line {
    position: absolute;
    left: 50%;
    top: 1rem;
    bottom: -1rem;
    width: 2px;
    background-color: #E5E7EB;
    transform: translateX(-50%);
}

/* Enhanced Button Hover Effects */
.btn-hover-effect {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-hover-effect:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Enhanced Form Elements */
textarea:focus {
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
}

.form-input-shadow {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>