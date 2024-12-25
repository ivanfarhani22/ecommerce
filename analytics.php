<?php
include('db.php');

// Menampilkan laporan penjualan
$query_sales = "SELECT SUM(total_price) AS total_sales FROM orders WHERE status='confirmed'";
$result_sales = mysqli_query($conn, $query_sales);
$sales = mysqli_fetch_assoc($result_sales);

// Menampilkan analisis produk terlaris
$query_products = "SELECT product_name, COUNT(*) AS times_sold 
                   FROM orders 
                   GROUP BY product_name 
                   ORDER BY times_sold DESC LIMIT 5";
$top_products = mysqli_query($conn, $query_products);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis</title>
</head>
<body>
    <header>
        <h1>Analisis Toko</h1>
    </header>
    <main>
        <h2>Laporan Penjualan</h2>
        <p>Total Penjualan: Rp <?php echo number_format($sales['total_sales'], 2, ',', '.'); ?></p>

        <h2>Produk Terlaris</h2>
        <table>
            <tr>
                <th>Produk</th>
                <th>Jumlah Terjual</th>
            </tr>
            <?php while ($product = mysqli_fetch_assoc($top_products)) { ?>
                <tr>
                    <td><?php echo $product['product_name']; ?></td>
                    <td><?php echo $product['times_sold']; ?></td>
                </tr>
            <?php } ?>
        </table>
    </main>
</body>
</html>
