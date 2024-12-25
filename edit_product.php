<?php
include('db.php');

// Cek jika ada ID produk yang diterima dari URL
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$product) {
        header('Location: manage_products.php');
        exit;
    }
} else {
    header('Location: manage_products.php');
    exit;
}

// Proses form untuk mengupdate produk
if (isset($_POST['edit_product'])) {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $brand_id = $_POST['brand_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];
    
    // Handle file upload
    $image_path = $product['image_url']; // Default to existing image
    
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "uploads/products/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Check if image file is actual image
        $check = getimagesize($_FILES["product_image"]["tmp_name"]);
        if ($check !== false) {
            $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($file_extension, $allowed_types)) {
                if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                    // Delete old image if exists and different
                    if (!empty($product['image_url']) && file_exists($product['image_url']) && $product['image_url'] != $target_file) {
                        unlink($product['image_url']);
                    }
                    $image_path = $target_file;
                }
            }
        }
    }

    // Update data produk di database
    $query = "UPDATE products SET name=?, category_id=?, brand_id=?, price=?, stock=?, description=?, image_url=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ssdiissi', $name, $category_id, $brand_id, $price, $stock, $description, $image_path, $product_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header('Location: manage_products.php');
    exit;
}

// Ambil kategori dan brand untuk dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories");
$brands = mysqli_query($conn, "SELECT * FROM brands");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-6 bg-gray-100 flex items-center justify-center">
        <div class="container max-w-screen-lg mx-auto">
            <div class="bg-white rounded shadow-lg p-4 px-4 md:p-8 mb-6">
                <div class="grid gap-4 gap-y-2 text-sm grid-cols-1 lg:grid-cols-3">
                    <div class="text-gray-600">
                        <p class="font-medium text-lg">Edit Product</p>
                        <p>Please fill out all the fields.</p>
                        
                        <?php if (!empty($product['image_url'])): ?>
                        <div class="mt-4">
                            <p class="mb-2">Current Image:</p>
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                 alt="Current product image" 
                                 class="w-full max-w-xs rounded-lg shadow-sm">
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="lg:col-span-2">
                        <form action="edit_product.php?id=<?php echo $product_id; ?>" 
                              method="POST" 
                              enctype="multipart/form-data" 
                              class="grid gap-4 gap-y-2 text-sm grid-cols-1 md:grid-cols-5">
                            
                            <div class="md:col-span-5">
                                <label for="name">Product Name</label>
                                <input type="text" name="name" id="name" 
                                       class="h-10 border mt-1 rounded px-4 w-full bg-gray-50" 
                                       value="<?php echo htmlspecialchars($product['name']); ?>" required>
                            </div>

                            <div class="md:col-span-3">
                                <label for="category_id">Category</label>
                                <select name="category_id" id="category_id" 
                                        class="h-10 border mt-1 rounded px-4 w-full bg-gray-50" required>
                                    <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo ($category['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label for="brand_id">Brand</label>
                                <select name="brand_id" id="brand_id" 
                                        class="h-10 border mt-1 rounded px-4 w-full bg-gray-50" required>
                                    <?php while ($brand = mysqli_fetch_assoc($brands)): ?>
                                        <option value="<?php echo $brand['id']; ?>" 
                                                <?php echo ($brand['id'] == $product['brand_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($brand['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="md:col-span-3">
                                <label for="price">Price</label>
                                <input type="number" step="0.01" name="price" id="price" 
                                       class="h-10 border mt-1 rounded px-4 w-full bg-gray-50" 
                                       value="<?php echo htmlspecialchars($product['price']); ?>" required>
                            </div>

                            <div class="md:col-span-2">
                                <label for="stock">Stock</label>
                                <input type="number" name="stock" id="stock" 
                                       class="h-10 border mt-1 rounded px-4 w-full bg-gray-50" 
                                       value="<?php echo htmlspecialchars($product['stock']); ?>" required>
                            </div>

                            <div class="md:col-span-5">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" 
                                          class="h-32 border mt-1 rounded px-4 w-full bg-gray-50" 
                                          required><?php echo htmlspecialchars($product['description']); ?></textarea>
                            </div>

                            <div class="md:col-span-5">
                                <label for="product_image">Product Image</label>
                                <input type="file" name="product_image" id="product_image" 
                                       class="mt-1 block w-full text-sm text-gray-500
                                              file:mr-4 file:py-2 file:px-4
                                              file:rounded-full file:border-0
                                              file:text-sm file:font-semibold
                                              file:bg-blue-50 file:text-blue-700
                                              hover:file:bg-blue-100"
                                       accept="image/*">
                                <p class="text-xs text-gray-500 mt-1">
                                    Accepted formats: JPG, JPEG, PNG, GIF. Leave empty to keep current image.
                                </p>
                            </div>

                            <div class="md:col-span-5 text-right">
                            <div class="inline-flex items-center space-x-4">
                                    <!-- Tombol Kembali -->
                                    <a href="manage_products.php" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded ml-2">
                                        <i class="fas fa-times mr-2"></i> Cancel
                                    </a>
                                    <button type="submit" name="edit_product" 
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        <i class="fas fa-save mr-2"></i> Update Product
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Preview image before upload
        document.getElementById('product_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('img');
                    if (preview) {
                        preview.src = e.target.result;
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>