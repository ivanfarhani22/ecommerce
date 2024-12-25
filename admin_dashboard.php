<?php
session_start();

// Periksa apakah session admin ada
if (!isset($_SESSION['admin']['user_id']) || $_SESSION['admin']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

include('db.php');

// Query untuk total pesanan yang belum diterima
$totalOrdersQuery = "SELECT COUNT(*) AS total_orders FROM orders WHERE status NOT IN ('accepted', 'cancelled')";
$resultOrders = mysqli_query($conn, $totalOrdersQuery);
$totalOrders = mysqli_fetch_assoc($resultOrders)['total_orders'] ?? 0;

// Query untuk total pendapatan
$totalRevenueQuery = "SELECT SUM(amount) AS total_revenue FROM payments";
$resultRevenue = mysqli_query($conn, $totalRevenueQuery);
$totalRevenue = mysqli_fetch_assoc($resultRevenue)['total_revenue'] ?? 0;

// Query untuk total pengguna
$totalUsersQuery = "SELECT COUNT(*) AS total_users FROM users";
$resultUsers = mysqli_query($conn, $totalUsersQuery);
$totalUsers = mysqli_fetch_assoc($resultUsers)['total_users'] ?? 0;

// Query untuk total produk
$totalProductsQuery = "SELECT COUNT(*) AS total_products FROM products";
$resultProducts = mysqli_query($conn, $totalProductsQuery);
$totalProducts = mysqli_fetch_assoc($resultProducts)['total_products'] ?? 0;

// Query untuk mengambil data pesanan terbaru
$query = "
    SELECT 
        o.id,
        o.status,
        o.total_price,
        o.created_at,
        u.username,
        GROUP_CONCAT(p.name SEPARATOR ', ') as products,
        GROUP_CONCAT(od.quantity SEPARATOR ', ') as quantities
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN order_details od ON o.id = od.order_id
    LEFT JOIN products p ON od.product_id = p.id
    WHERE o.status NOT IN ('accepted', 'cancelled')
    GROUP BY o.id
    ORDER BY o.created_at DESC
";

// Menjalankan query
$orders = mysqli_query($conn, $query);

// Mengecek jika query gagal
if (!$orders) {
    die("Error in query: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<style>
    /* Sidebar styling */
    #sidebar {
        transform: translateX(-100%);
        transition: transform 0.4s ease, box-shadow 0.3s ease;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        background: linear-gradient(to bottom, #1f2937, #111827); /* Gradient background */
    }

    /* Show sidebar on toggle */
    #sidebar.show {
        transform: translateX(0);
        box-shadow: 4px 0 15px rgba(0, 0, 0, 0.5);
    }

    /* Responsive styles for mobile */
    @media (max-width: 768px) {
        #sidebar {
            width: 75%; /* Adjust sidebar width on mobile */
        }
    }

    /* Sidebar menu links */
    #sidebar nav a {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        border-radius: 0.375rem;
        font-size: 0.95rem;
        font-weight: 500;
        color: #d1d5db; /* Light gray */
        text-decoration: none;
        transition: background 0.3s ease, color 0.3s ease;
    }

    #sidebar nav a:hover {
        background: rgba(255, 255, 255, 0.1); /* Subtle highlight effect */
        color: #fff; /* Pure white text on hover */
    }

    /* Highlight active menu item */
    #sidebar nav a.active {
        background: rgba(255, 255, 255, 0.2); /* Slightly brighter highlight */
        color: #ffffff;
    }

    /* Button styling for mobile */
    #sidebarToggle {
        background: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    #sidebarToggle:hover {
        background: rgba(0, 0, 0, 0.7); /* Darker on hover */
    }

    /* Add a shadow to the sidebar toggle button */
    #sidebarToggle svg {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Animations */
    #sidebar nav a span {
        transition: transform 0.3s ease;
    }

    #sidebar nav a:hover span {
        transform: translateX(4px); /* Subtle move on hover */
    }
</style>

<body class="bg-gray-50 font-[Inter]">
    <div class="min-h-screen flex">
       <!-- Sidebar -->
<aside class="w-64 bg-gray-800 text-white fixed h-full lg:block hidden">
<div class="p-4">
            <a href="admin_dashboard.php"><h1 class="text-2xl font-bold text-white mb-8"> Admin Panel</h1></a>
                <nav class="space-y-2">
                    <a href="manage_products.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 text-gray-300 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span>Manajemen Produk</span>
                    </a>
                    
                    <a href="manage_orders.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 text-gray-300 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span>Manajemen Pesanan</span>
                    </a>
                    
                    <a href="manage_users.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 text-gray-300 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span>Manajemen Pengguna</span>
                    </a>
                    
                    <a href="manage_finances.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 text-gray-300 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Manajemen Keuangan</span>
                    </a>
                    
                    <a href="manage_content.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 text-gray-300 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H14"/>
                        </svg>
                        <span>Manajemen Konten</span>
                    </a>
                    
                    <a href="manage_security.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 text-gray-300 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>Manajemen Keamanan</span>
                    </a>
                    
                    <a href="analytics.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 text-gray-300 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span>Analisis</span>
                    </a>
                    
                    <a href="logout.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-red-600 text-gray-300 hover:text-white mt-8">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Logout</span>
                    </a>
                </nav>
            </div>
        </aside>
<!-- Button to toggle sidebar on mobile -->
<button id="sidebarToggle" class="text-white p-4 fixed top-4 left-4 md:hidden">
    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
</button>

<!-- Sidebar Mobile -->
<aside id="sidebar" class="w-64 bg-gray-800 text-white fixed h-full transform -translate-x-full md:translate-x-0 transition-transform duration-200">
    <div class="p-4">
        <a href="admin_dashboard.php">
            <h1 class="text-2xl font-bold text-white mb-8">Admin Panel</h1>
        </a>
        <nav class="space-y-2">
            <a href="manage_products.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 text-gray-300 hover:text-white">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>Manajemen Produk</span>
            </a>
            <a href="manage_orders.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 text-gray-300 hover:text-white">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Manajemen Pesanan</span>
            </a>
            <a href="manage_users.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 text-gray-300 hover:text-white">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197" />
                </svg>
                <span>Manajemen Pengguna</span>
            </a>
            <a href="manage_finances.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 text-gray-300 hover:text-white">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1" />
                </svg>
                <span>Manajemen Keuangan</span>
            </a>
            <a href="manage_content.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 text-gray-300 hover:text-white">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H14" />
                </svg>
                <span>Manajemen Konten</span>
            </a>
            <a href="manage_security.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 text-gray-300 hover:text-white">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>Manajemen Keamanan</span>
            </a>
            <a href="analytics.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 text-gray-300 hover:text-white">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Analisis</span>
            </a>
            <a href="logout.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-red-600 text-gray-300 hover:text-white mt-8">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3v-4a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Logout</span>
            </a>
        </nav>
    </div>
</aside>

        <!-- Main Content -->
        <div class="ml-64 flex-1">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">
                            <?php echo "Selamat datang, " . htmlspecialchars($_SESSION['admin']['username']) . "!"; ?> (Admin)
                        </span>
                    </div>
                </div>
            </header>

            
<!-- Main Content Area -->
<main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-500 bg-opacity-10">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Pesanan</h3>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $totalOrders; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-500 bg-opacity-10">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Pendapatan</h3>
                    <p class="text-2xl font-semibold text-gray-900">Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-500 bg-opacity-10">
                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Pengguna</h3>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $totalUsers; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-500 bg-opacity-10">
                    <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Produk</h3>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $totalProducts; ?></p>
                </div>
            </div>
        </div>
    </div>

                <!-- Welcome Message -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    <?php echo "Selamat datang, " . htmlspecialchars($_SESSION['admin']['username']) . "!"; ?> (Admin)
                    </h2>
                    <p class="text-gray-600">
                        Gunakan menu di sidebar untuk mengelola toko online Anda. Dashboard ini memberikan gambaran umum tentang performa toko Anda.
                    </p>
                </div>
                <!-- Recent Orders Section -->
                <!-- Recent Orders Section -->
<div class="mt-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Pesanan Terbaru</h3>
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pesanan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (mysqli_num_rows($orders) > 0): ?>
                        <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    #<?php echo htmlspecialchars($order['id']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($order['username'] ?? 'Tidak tersedia'); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?php
                                    if (!empty($order['products']) && !empty($order['quantities'])) {
                                        $products = explode(', ', $order['products']);
                                        $quantities = explode(', ', $order['quantities']);

                                        if (count($products) === count($quantities)) {
                                            foreach ($products as $index => $product) {
                                                $quantity = htmlspecialchars($quantities[$index]);
                                                echo htmlspecialchars($product) . " (x" . $quantity . ")<br>";
                                            }
                                        } else {
                                            echo "Data produk/kuantitas tidak sesuai.";
                                        }
                                    } else {
                                        echo "Data produk tidak tersedia.";
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp <?php echo number_format($order['total_price'] ?? 0, 0, ',', '.'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo isset($order['created_at']) ? date('d/m/Y H:i', strtotime($order['created_at'])) : 'Tidak tersedia'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php
                                        $status = $order['status'] ?? '';
                                        switch ($status) {
                                            case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'confirmed': echo 'bg-blue-100 text-blue-800'; break;
                                            case 'shipped': echo 'bg-green-100 text-green-800'; break;
                                            case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                            case 'accepted': echo 'bg-gray-100 text-gray-800'; break;
                                            default: echo 'bg-gray-200 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($status ?: 'Tidak diketahui'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <?php if ($status == 'pending'): ?>
                                        <a href="?confirm_order=<?php echo htmlspecialchars($order['id']); ?>" class="text-blue-600 hover:text-blue-900 mr-2">Konfirmasi</a>
                                    <?php endif; ?>
                                    
                                    <?php if ($status == 'confirmed'): ?>
                                        <a href="?ship_order=<?php echo htmlspecialchars($order['id']); ?>" class="text-green-600 hover:text-green-900 mr-2">Kirim</a>
                                    <?php endif; ?>
                                    
                                    <?php if (in_array($status, ['pending', 'confirmed'])): ?>
                                        <a href="?cancel_order=<?php echo htmlspecialchars($order['id']); ?>" class="text-red-600 hover:text-red-900">Batal</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada pesanan yang tersedia.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
                <!-- Activity & Notifications Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                    <!-- Recent Activity -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Aktivitas Terbaru</h3>
                        </div>
                        <div class="p-6">
                            <ul class="space-y-4">
                                <li class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <span class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100">
                                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Produk baru ditambahkan: <span class="font-medium">Smartphone X Pro</span></p>
                                        <p class="text-xs text-gray-400">2 jam yang lalu</p>
                                    </div>
                                </li>
                                <li class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <span class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100">
                                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Pesanan #ORD-2024-001 selesai diproses</p>
                                        <p class="text-xs text-gray-400">4 jam yang lalu</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Notifikasi</h3>
                        </div>
                        <div class="p-6">
                            <ul class="space-y-4">
                                <li class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <span class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100">
                                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Stok produk "Smartphone X Pro" hampir habis</p>
                                        <p class="text-xs text-gray-400">1 jam yang lalu</p>
                                    </div>
                                </li>
                                <li class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <span class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100">
                                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">5 pesanan baru memerlukan perhatian</p>
                                        <p class="text-xs text-gray-400">3 jam yang lalu</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-8">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">
                &copy; 2024 Toko Online. Semua hak dilindungi.
            </p>
        </div>
    </footer>
    <script>
    const sidebarToggle = document.getElementById("sidebarToggle");
const sidebar = document.getElementById("sidebar");

// Toggle sidebar visibility
sidebarToggle.addEventListener("click", (event) => {
    event.stopPropagation(); // Mencegah klik pada tombol menutup sidebar
    sidebar.classList.toggle("show");
});

// Close sidebar when clicking outside of it
document.addEventListener("click", (event) => {
    if (sidebar.classList.contains("show") && !sidebar.contains(event.target) && event.target !== sidebarToggle) {
        sidebar.classList.remove("show");
    }
});

    </script>
</body>
</html>