<?php
include('db.php');

// Proses reset password
if (isset($_POST['reset'])) {
    $email = $_POST['email'];

    // Cek apakah email ada dalam database
    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Kirim email untuk mereset password (gunakan mail() atau library seperti PHPMailer)
        // Di sini, kita hanya akan menampilkan pesan sukses sementara
        $message = "Kami telah mengirimkan link reset password ke email Anda.";
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-sm bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold text-center text-gray-700 mb-6">Lupa Password</h1>

        <!-- Forgot Password Form -->
        <form method="POST" action="" class="space-y-4">

            <div>
                <label for="email" class="block text-gray-700">Masukkan Email</label>
                <input type="email" name="email" id="email" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <button type="submit" name="reset"
                    class="w-full py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition duration-300">
                    Kirim Link Reset
                </button>
            </div>
        </form>

        <?php if (isset($error)) { ?>
            <div class="mt-4 text-red-600 text-center">
                <p><?php echo $error; ?></p>
            </div>
        <?php } ?>

        <?php if (isset($message)) { ?>
            <div class="mt-4 text-green-600 text-center">
                <p><?php echo $message; ?></p>
            </div>
        <?php } ?>

        <div class="mt-4 text-center">
            <p class="text-gray-600">Sudah ingat password? <a href="login.php" class="text-blue-600 hover:underline">Login
                    di sini</a></p>
        </div>
    </div>

</body>

</html>
