<?php
session_start();
include('db.php'); // Koneksi database

// Proses signup
if (isset($_POST['signup'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = md5($_POST['password']); // Enkripsi password

    // Cek apakah email sudah digunakan
    $check_query = "SELECT * FROM users WHERE email='$email'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $error = "Email sudah terdaftar!";
    } else {
        // Tambahkan pengguna baru ke database
        $query = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', 'user')";
        if (mysqli_query($conn, $query)) {
            $success = "Akun berhasil dibuat! Anda akan diarahkan ke halaman login.";
            header("Location: login.php"); // Redirect ke login.php setelah sukses
            exit();
        } else {
            $error = "Terjadi kesalahan saat mendaftar. Coba lagi!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold text-center text-gray-700 mb-6">Create an Account</h1>

        <!-- Signup Form -->
        <form method="POST" action="" class="space-y-4">
            <div>
                <label for="username" class="block text-gray-700">Username</label>
                <input type="text" name="username" id="username" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="email" class="block text-gray-700">Email</label>
                <input type="email" name="email" id="email" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="password" class="block text-gray-700">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <button type="submit" name="signup"
                    class="w-full py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition duration-300">
                    Sign Up
                </button>
            </div>
        </form>

        <?php if (isset($error)) { ?>
            <div class="mt-4 text-red-600 text-center">
                <p><?php echo $error; ?></p>
            </div>
        <?php } ?>

        <?php if (isset($success)) { ?>
            <div class="mt-4 text-green-600 text-center">
                <p><?php echo $success; ?></p>
            </div>
        <?php } ?>

        <div class="mt-4 text-center">
            <p class="text-gray-600">Already have an account? <a href="login.php" class="text-blue-600 hover:underline">Login
                here</a></p>
        </div>
    </div>
</body>

</html>
