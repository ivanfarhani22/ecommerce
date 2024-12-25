<?php
// Implementasikan rekomendasi produk berdasarkan riwayat pembelian (sederhana)
include('db.php');
$recommended_query = "SELECT * FROM products WHERE id != 1 LIMIT 5";
$recommended_result = mysqli_query($conn, $recommended_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekomendasi Produk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Rekomendasi Produk</h1>
    </header>

    <section class="recommended-products">
        <div class="product-list">
            <?php while ($product = mysqli_fetch_assoc($recommended_result)) { ?>
                <div class="product-item">
                    <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                    <h3><?php echo $product['name']; ?></h3>
                    <p>Rp <?php echo number_format($product['price'], 2, ',', '.'); ?></p>
                    <a href="add_to_cart.php?id=<?php echo $product['id']; ?>">Tambahkan ke Keranjang</a>
                </div>
            <?php } ?>
        </div>
    </section>
</body>
</html>
