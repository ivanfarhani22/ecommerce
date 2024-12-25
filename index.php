<?php 
// Start session to check login status
session_start();

// Remove or adjust the login check for index.php
// This will allow users to access index.php even if they are not logged in

// Connect to the database
include('db.php');

// Check if connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the latest products from the database
$products_query = "SELECT * FROM products ORDER BY id DESC LIMIT 10";
$products_result = mysqli_query($conn, $products_query);

// Check if products are found
if (mysqli_num_rows($products_result) == 0) {
    $error = "No products found.";
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 font-[Inter]">
   <!-- Header with gradient background -->
   <header class="bg-gradient-to-r from-gray-700 to-gray-900 text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <!-- Mobile Menu Button -->
        <div class="flex justify-between items-center lg:hidden">
            <h1 class="text-2xl font-bold">
                <a href="index.php" class="hover:text-blue-200 transition duration-300">
                    Toko Online
                </a>
            </h1>
            <button id="menuBtn" class="p-2 rounded-lg hover:bg-white/10 transition duration-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-16 6h16"></path>
                </svg>
            </button>
        </div>

        <!-- Desktop Navigation -->
        <div class="hidden lg:flex lg:justify-between lg:items-center">
            <h1 class="text-3xl font-bold">
                <a href="index.php" class="hover:text-blue-200 transition duration-300">
                    Toko Online
                </a>
            </h1>
            
            <nav class="flex items-center space-x-6">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition duration-300">
                        Halaman Utama
                    </a>
                    <a href="catalog.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition duration-300">
                        Katalog Produk
                    </a>
                    <a href="cart.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition duration-300">
                        Keranjang Belanja
                    </a>
                    <?php if (isset($_SESSION['customer']['user_id'])) { ?>
                        <a href="my_orders.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition duration-300">
                            Status Pesanan
                        </a>
                    <?php } ?>
                </div>

                <!-- Search Form -->
                <form action="search.php" method="GET" class="flex items-center">
                    <div class="relative">
                        <input type="text" 
                               name="query" 
                               placeholder="Cari Produk..." 
                               class="w-64 px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-black"
                        >
                        <button type="submit" 
                                class="absolute right-0 top-0 h-full px-4 bg-gray-500 text-white rounded-r-lg hover:bg-gray-600 transition duration-300"
                        >
                            Cari
                        </button>
                    </div>
                </form>

                <!-- Auth Buttons -->
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['customer']['user_id'])) { ?>
                        <div class="flex items-center space-x-2">
                            <a href="profile.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition duration-300">
                                Profil
                            </a>
                            <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition duration-300">
                                Logout
                            </a>
                        </div>
                    <?php } else { ?>
                        <div class="flex items-center space-x-2">
                            <a href="login.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition duration-300">
                                Login
                            </a>
                            <a href="signup.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-300">
                                Signup
                            </a> 
                        </div>
                    <?php } ?>
                </div>
            </nav>
        </div>

        <!-- Mobile Navigation Menu -->
        <div id="mobileMenu" class="hidden lg:hidden mt-4">
            <nav class="flex flex-col space-y-4">
                <a href="index.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition duration-300">
                    Halaman Utama
                </a>
                <a href="catalog.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition duration-300">
                    Katalog Produk
                </a>
                <a href="cart.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition duration-300">
                    Keranjang Belanja
                </a>
                <?php if (isset($_SESSION['customer']['user_id'])) { ?>
                    <a href="my_orders.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition duration-300">
                        Status Pesanan
                    </a>
                <?php } ?>
                
                <!-- Mobile Search Form -->
                <form action="search.php" method="GET" class="flex flex-col space-y-2">
                    <input type="text" 
                           name="query" 
                           placeholder="Cari Produk..." 
                           class="w-full px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-black"
                    >
                    <button type="submit" 
                            class="w-full bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition duration-300"
                    >
                        Cari
                    </button>
                </form>

                <!-- Mobile Auth Buttons -->
                <?php if (isset($_SESSION['customer']['user_id'])) { ?>
                    <div class="flex flex-col space-y-2">
                        <a href="profile.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition duration-300">
                            Profil
                        </a>
                        <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition duration-300 text-center">
                            Logout
                        </a>
                    </div>
                <?php } else { ?>
                    <div class="flex flex-col space-y-2">
                        <a href="login.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition duration-300 text-center">
                            Login
                        </a>
                        <a href="signup.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-300 text-center">
                            Signup
                        </a>
                    </div>
                <?php } ?>
            </nav>
        </div>
    </div>
        <!-- JavaScript for Mobile Menu Toggle -->
        <script>
        const menuBtn = document.getElementById('menuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        
        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</header>


    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
       <!-- Hero Banner Section -->
    <section class="relative bg-gradient-to-r from-gray-700 to-gray-900 text-white shadow-lg">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSIjZmZmIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxjaXJjbGUgY3g9IjIwIiBjeT0iMjAiIHI9IjIiLz48L2c+PC9zdmc+')] bg-center"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 relative">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold mb-6">Temukan Produk Terbaik untuk Anda</h1>
                    <p class="text-xl text-blue-100 mb-8">Berbagai pilihan produk berkualitas dengan harga terbaik untuk memenuhi kebutuhan Anda.</p>
                    <div class="flex gap-4">
                        <a href="catalog.php" class="bg-white text-gray-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition duration-300">
                            Lihat Katalog
                        </a>
                        <a href="#featured" class="bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-600 transition duration-300">
                            Produk Unggulan
                        </a>
                    </div>
                </div>
                <div class="hidden md:block">
                    <img src="https://png.pngtree.com/thumb_back/fh260/background/20230703/pngtree-d-illustration-of-a-lush-garden-inside-a-cafe-or-florist-image_3755613.jpg" alt="Hero Banner" class="rounded-lg shadow-xl">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Produk Berkualitas</h3>
                    <p class="text-gray-600">Kami menjamin kualitas setiap produk yang kami jual</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Pengiriman Cepat</h3>
                    <p class="text-gray-600">Pengiriman cepat ke seluruh wilayah Indonesia</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Pembayaran Aman</h3>
                    <p class="text-gray-600">Transaksi aman dengan berbagai metode pembayaran</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Banner -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-8">
                <div class="relative rounded-xl overflow-hidden group">
                    <img src="/api/placeholder/600/300" alt="Electronics" class="w-full h-64 object-cover transition duration-300 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end">
                        <div class="p-6 text-white">
                            <h3 class="text-2xl font-bold mb-2">Elektronik</h3>
                            <a href="catalog.php?category=electronics" class="text-white hover:text-blue-200 transition">Lihat Produk →</a>
                        </div>
                    </div>
                </div>
                <div class="relative rounded-xl overflow-hidden group">
                    <img src="/api/placeholder/600/300" alt="Fashion" class="w-full h-64 object-cover transition duration-300 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end">
                        <div class="p-6 text-white">
                            <h3 class="text-2xl font-bold mb-2">Fashion</h3>
                            <a href="catalog.php?category=fashion" class="text-white hover:text-blue-200 transition">Lihat Produk →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest Products Section -->
    <section class="py-16 bg-white" id="featured">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Produk Terbaru</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Temukan produk-produk terbaru kami dengan kualitas terbaik dan harga yang kompetitif.</p>
            </div>

             <!-- Product Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <?php while($product = mysqli_fetch_assoc($products_result)) { ?>
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
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-16 bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-8 md:p-12 text-center text-white">
                <h2 class="text-3xl font-bold mb-4">Dapatkan Info Produk Terbaru</h2>
                <p class="text-blue-100 mb-8 max-w-2xl mx-auto">Berlangganan newsletter kami untuk mendapatkan update tentang produk terbaru dan penawaran spesial.</p>
                <form class="max-w-md mx-auto flex gap-4">
                    <input type="email" placeholder="Masukkan email Anda" class="flex-1 px-4 py-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition duration-300">
                        Berlangganan
                    </button>
                </form>
            </div>
        </div>
    </section>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-semibold mb-4">Tentang Kami</h3>
                    <p class="text-gray-400">
                        Toko Online terpercaya dengan berbagai produk berkualitas untuk memenuhi kebutuhan Anda.
                    </p>
                </div>
                
                <div>
                    <h3 class="text-xl font-semibold mb-4">Kontak</h3>
                    <p class="text-gray-400">
                        Email: info@tokoonline.com<br>
                        Telepon: (021) 1234-5678<br>
                        Alamat: Jl. Contoh No. 123
                    </p>
                </div>
                
                <div>
                    <h3 class="text-xl font-semibold mb-4">Ikuti Kami</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            Facebook
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            Instagram
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            Twitter
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2024 Toko Online. Semua hak dilindungi.</p>
            </div>
        </div>
    </footer>
</body>
</html>
