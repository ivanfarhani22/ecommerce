<?php
// Mulai sesi untuk memeriksa status login
session_start();

// Koneksi ke database
include('db.php');

// Ambil produk terbaru
$products_query = "SELECT * FROM products ORDER BY id DESC LIMIT 10";
$products_result = mysqli_query($conn, $products_query);
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk - Toko Online</title>
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
        <!-- Products Section -->
        <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Katalog Produk</h2>
            
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