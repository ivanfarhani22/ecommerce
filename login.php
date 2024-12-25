<?php
session_start();
include('db.php'); // Koneksi database

// Proses login
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = md5($_POST['password']); // Enkripsi password

    // Query untuk mencari pengguna
    $query = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Pisahkan session berdasarkan role
        if ($user['role'] == 'admin') {
            $_SESSION['admin'] = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => 'admin',
            ];
            header('Location: admin_dashboard.php');
        } elseif ($user['role'] == 'customer') {
            $_SESSION['customer'] = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => 'customer',
            ];
            header('Location: index.php');
        }
        exit();
    } else {
        $error = "Email atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Your Brand</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-100 via-white to-purple-100">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-md" data-aos="fade-up">
            <!-- Card Container -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Header Section -->
                <div class="bg-gradient-to-r from-gray-500 to-gray-700 px-8 py-6">
                    <div class="text-center">
                        <h2 class="text-3xl font-bold text-white mb-2">Selamat Datang</h2>
                        <p class="text-blue-100">Silakan masuk ke akun Anda</p>
                    </div>
                </div>

                <!-- Form Section -->
                <div class="p-8">
                    <form method="POST" action="" class="space-y-6">
                        <!-- Email Field -->
                        <div class="group">
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="email">Email</label>
                            <div class="relative">
                                <input type="email" name="email" id="email" required
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50 hover:bg-white"
                                    placeholder="nama@email.com">
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="group">
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="password">Password</label>
                            <div class="relative">
                                <input type="password" name="password" id="password" required
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50 hover:bg-white"
                                    placeholder="Masukkan password">
                            </div>
                        </div>

                        <!-- Error Message -->
                        <?php if (isset($error)) { ?>
                            <div class="bg-red-50 text-red-500 px-4 py-3 rounded-lg text-sm text-center animate-bounce">
                                <?php echo $error; ?>
                            </div>
                        <?php } ?>

                        <!-- Login Button -->
                        <button type="submit" name="login"
                            class="w-full bg-gradient-to-r from-gray-500 to-gray-700 text-white font-bold py-3 px-4 rounded-lg hover:opacity-90 transform hover:-translate-y-0.5 transition duration-200">
                            Masuk
                        </button>
                    </form>

                    <!-- Links -->
                    <div class="mt-6 text-center space-y-2">
                        <p class="text-gray-600">
                            Belum punya akun? 
                            <a href="signup.php" class="text-blue-600 hover:text-blue-700 font-semibold hover:underline transition duration-200">
                                Daftar sekarang
                            </a>
                        </p>
                        <a href="forgot_password.php" class="block text-blue-600 hover:text-blue-700 font-semibold hover:underline transition duration-200">
                            Lupa password?
                        </a>
                    </div>
                </div>
            </div>

            <!-- Brand Footer -->
            <div class="text-center mt-6 text-gray-500">
                <p>&copy; 2024 Your Brand. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
</body>
</html>