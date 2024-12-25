<?php
include('db.php');
if (isset($_GET['query'])) {
    $query = $_GET['query'];
    $search_query = "SELECT * FROM products WHERE name LIKE '%$query%'";
    $search_result = mysqli_query($conn, $search_query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 font-[Inter]">
    <!-- Header with Search -->
    <header class="bg-gradient-to-r from-gray-700 to-gray-900 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                <h1 class="text-2xl font-bold text-white-900">Pencarian Produk</h1>
                <form action="search.php" method="get" class="w-full sm:w-96 text-black">
                    <div class="relative">
                        <input type="text" 
                               name="query" 
                               placeholder="Cari produk..." 
                               value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>"
                               class="w-full px-4 py-2 pl-10 pr-12 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <button type="submit" class="absolute inset-y-0 right-0 px-4 py-2 bg-gray-600 text-white rounded-r-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative">
    <?php if (isset($search_result)) { ?>
        <!-- Container untuk Search Results dan Tombol Kembali -->
        <div class="flex items-center mb-6">
            <!-- Tombol Kembali di luar background -->
            <a href="javascript:history.back()" class="absolute -left-10 text-gray-600 hover:text-gray-800 transition duration-300 flex items-center">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-8 h-8 mr-2 transform hover:scale-110 transition-transform duration-300 ease-in-out">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5l-7 7 7 7"></path>
    </svg>
    <span class="text-sm font-semibold"></span>
</a>
            <!-- Search Results -->
            <h2 class="text-lg font-semibold text-gray-900">
                Hasil Pencarian untuk "<?php echo htmlspecialchars($_GET['query']); ?>"
            </h2>
        </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <?php while($product = mysqli_fetch_assoc($search_result)) { ?>
        <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition duration-300 transform hover:-translate-y-1 hover:scale-105">
            <!-- Wrap product content with link -->
            <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="block">
                <!-- Product Image -->
                <div class="aspect-w-16 aspect-h-9 w-full overflow-hidden bg-gray-200">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         class="w-full h-48 object-cover">
                </div>
                
                <!-- Product Info -->
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h3>
                    
                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                        <?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...
                    </p>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-bold text-gray-900">
                            Rp <?php echo number_format($product['price'], 2, ',', '.'); ?>
                        </span>
                                
                                <a href="add_to_cart.php?id=<?php echo $product['id']; ?>" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    Tambah ke Keranjang
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <?php if (mysqli_num_rows($search_result) === 0) { ?>
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada hasil</h3>
                    <p class="mt-1 text-sm text-gray-500">Coba cari dengan kata kunci lain.</p>
                </div>
            <?php } ?>
        <?php } else { ?>
            <!-- Initial State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Mulai pencarian</h3>
                <p class="mt-1 text-sm text-gray-500">Ketik kata kunci untuk menemukan produk yang Anda cari.</p>
            </div>
        <?php } ?>
    </main>
</body>
</html>
