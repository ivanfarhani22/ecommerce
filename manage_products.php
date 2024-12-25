<?php
include('db.php');

// Konfigurasi upload
define('UPLOAD_DIR', 'uploads/products/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Buat direktori upload jika belum ada
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

function handleFileUpload($file) {
    // Validasi file
    if ($file['error'] !== UPLOAD_ERR_OK) {  // Menggunakan UPLOAD_ERR_OK yang benar
        throw new Exception("Error dalam upload file: " . getUploadErrorMessage($file['error']));
    }
    
    if (!in_array($file['type'], ALLOWED_TYPES)) {
        throw new Exception("Tipe file tidak diizinkan");
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception("Ukuran file terlalu besar (max 5MB)");
    }
    
    // Generate nama file unik
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $destination = UPLOAD_DIR . $filename;
    
    // Pindahkan file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("Gagal memindahkan file");
    }
    
    return $destination;
}

// Fungsi untuk mendapatkan pesan error upload yang lebih deskriptif
function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'File melebihi batas upload_max_filesize di php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'File melebihi batas MAX_FILE_SIZE yang ditentukan dalam form HTML';
        case UPLOAD_ERR_PARTIAL:
            return 'File hanya terupload sebagian';
        case UPLOAD_ERR_NO_FILE:
            return 'Tidak ada file yang diupload';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Folder temporary tidak ditemukan';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Gagal menulis file ke disk';
        case UPLOAD_ERR_EXTENSION:
            return 'File upload dihentikan oleh ekstensi PHP';
        default:
            return 'Unknown upload error';
    }
}

// Menambahkan produk
if (isset($_POST['add_product'])) {
    try {
        $name = $_POST['name'];
        $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : null;
        $brand_id = isset($_POST['brand_id']) ? $_POST['brand_id'] : null;
        $price = $_POST['price'];
        $stock = isset($_POST['stock']) ? $_POST['stock'] : 0;
        $description = $_POST['description'];
        
        // Handle file upload
        $image_path = '';
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {  // Menggunakan UPLOAD_ERR_NO_FILE yang benar
            $image_path = handleFileUpload($_FILES['product_image']);
        }
        
        // Tambahkan produk ke database
        $query = "INSERT INTO products (name, description, price, stock, category_id, brand_id, image_url) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssdiiss', $name, $description, $price, $stock, $category_id, $brand_id, $image_path);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal menyimpan produk ke database");
        }
        
        mysqli_stmt_close($stmt);
        $_SESSION['success'] = "Produk berhasil ditambahkan";
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Mengedit produk
if (isset($_POST['edit_product'])) {
    try {
        $product_id = $_POST['product_id'];
        $name = $_POST['name'];
        $category_id = $_POST['category_id'];
        $brand_id = $_POST['brand_id'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $description = $_POST['description'];
        
        // Ambil image_url lama
        $old_image = '';
        $result = mysqli_query($conn, "SELECT image_url FROM products WHERE id = $product_id");
        if ($row = mysqli_fetch_assoc($result)) {
            $old_image = $row['image_url'];
        }
        
        // Handle file upload jika ada file baru
        $image_path = $old_image;
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $image_path = handleFileUpload($_FILES['product_image']);
            // Hapus file lama jika ada
            if (!empty($old_image) && file_exists($old_image)) {
                unlink($old_image);
            }
        }
        
        // Update database
        $query = "UPDATE products 
                  SET name=?, category_id=?, brand_id=?, price=?, stock=?, description=?, image_url=? 
                  WHERE id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssdiissi', $name, $category_id, $brand_id, $price, $stock, $description, $image_path, $product_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal mengupdate produk");
        }
        
        mysqli_stmt_close($stmt);
        $_SESSION['success'] = "Produk berhasil diupdate";
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Menghapus produk
if (isset($_GET['delete_product'])) {
    try {
        $product_id = $_GET['delete_product'];
        
        // Ambil info file gambar
        $result = mysqli_query($conn, "SELECT image_url FROM products WHERE id = $product_id");
        if ($row = mysqli_fetch_assoc($result)) {
            $image_path = $row['image_url'];
            // Hapus file jika ada
            if (!empty($image_path) && file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Hapus dari database
        $query = "DELETE FROM products WHERE id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $product_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal menghapus produk");
        }
        
        mysqli_stmt_close($stmt);
        $_SESSION['success'] = "Produk berhasil dihapus";
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Query untuk menampilkan produk
$query = "SELECT p.*, c.name AS category_name, b.name AS brand_name 
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          LEFT JOIN brands b ON p.brand_id = b.id";
$products = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Add this in the <head> section -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
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
            <!-- Header -->
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <h1 class="text-3xl font-bold text-gray-900">Manajemen Produk</h1>
                </div>
            </header>

            <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <!-- Add Product Form -->
                <div class="bg-white rounded-lg shadow mb-8">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium leading-6 text-gray-900 mb-4">Tambah Produk Baru</h2>
                    <form method="POST" action="manage_products.php" enctype="multipart/form-data" class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Produk</label>
                                <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kategori</label>
                                <select name="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="">Pilih Kategori</option>
                                    <?php
                                    $categories = mysqli_query($conn, "SELECT * FROM categories");
                                    while ($category = mysqli_fetch_assoc($categories)) {
                                        echo "<option value='{$category['id']}'>{$category['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Merek</label>
                                <select name="brand_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="">Pilih Merek</option>
                                    <?php
                                    $brands = mysqli_query($conn, "SELECT * FROM brands");
                                    while ($brand = mysqli_fetch_assoc($brands)) {
                                        echo "<option value='{$brand['id']}'>{$brand['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Harga</label>
                                <input type="number" name="price" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Stock</label>
                                <input type="number" name="stock" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                                <textarea name="description" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                            </div>

                            <div>
                            <label class="block text-sm font-medium text-gray-700">Gambar Produk</label>
                            <input type="file" name="product_image" accept="image/*" required 
                                   class="mt-1 block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-full file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-blue-50 file:text-blue-700
                                          hover:file:bg-blue-100">
                            <p class="mt-1 text-sm text-gray-500">Format yang didukung: JPG, PNG, GIF, WEBP (Max. 5MB)</p>
                        </div>

                        <div class="col-span-2 text-right">
                            <button type="submit" name="add_product" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                Tambah Produk
                            </button>
                        </div>
                    </form>
                </div>
            </div>

                <!-- Product Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gambar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Merek</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($product = mysqli_fetch_assoc($products)): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" 
                                         class="h-16 w-16 object-cover rounded">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $product['name']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $product['category_name']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $product['brand_name']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $product['stock']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                       <button onclick="confirmDelete(<?php echo $product['id']; ?>)" 
                                       class="text-red-600 hover:text-red-900"> 
                                       <i class="fas fa-trash mr-2"></i>Hapus</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <!-- Add this JavaScript function -->
<script>
function confirmDelete(productId) {
    Swal.fire({
        title: 'Konfirmasi Penghapusan',
        text: "Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-lg',
            title: 'text-lg font-bold',
            confirmButton: 'px-4 py-2 rounded-md',
            cancelButton: 'px-4 py-2 rounded-md'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?delete_product=${productId}`;
        }
    });
    return false;
}
</script>

<!-- Optional: Add this CSS for additional styling -->
<style>
.swal2-popup {
    font-family: 'Arial', sans-serif;
    border-radius: 15px;
}

.swal2-title {
    font-size: 1.5rem !important;
    color: #2d3748 !important;
}

.swal2-text {
    color: #4a5568 !important;
}

.swal2-confirm {
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
}

.swal2-cancel {
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
}
</style>
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