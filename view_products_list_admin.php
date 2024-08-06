<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);


include_once 'config/database.php';
include_once 'objects/product.php';
include_once 'objects/brand.php';
include_once 'objects/user.php';

$database = new Database();
$db = $database->getConnection();

// Check if the user is logged in
$user = User::getInstance($db);
$res = $user->verifyUserSession(['admin']);

if (!$res[0]) {
    if (!$res[2]){
        $error_message = urlencode("You are not authorized to access this page");
        header("Location: error_page.php?message=$error_message");
        exit();
    }
    session_destroy();
    header("Location: signup.php");
    exit();
}

// Create Product and Brand objects
$product = new Product($db);
$brand = new Brand($db); // Assume you have a Brand class

// Get selected brand from form submission
$selected_brand = isset($_GET['brand']) ? $_GET['brand'] : '';

// Fetch products by selected brand
$stmt = $product->get_products_by_brand($selected_brand);

// Fetch distinct brands for filter dropdown
$brand_stmt = $db->query("SELECT brand_id, name FROM Brands WHERE is_active = 1");
$brands = $brand_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Admin - Products</title>
    <link rel="stylesheet" href="src/css/order_history.css">
    <link rel="stylesheet" href="src/css/styles.css">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <section class="order-history-section">
        <div class="order-history-header">
            <h1>Manage Products</h1>
            <form method="GET" action="">
                <label for="brand" class="filter-label">Filter by Brand:</label>
                <select name="brand" id="brand" class="filter-select">
                    <option value="">All Brands</option>
                    <?php foreach ($brands as $brand_item) : ?>
                        <option value="<?php echo htmlspecialchars($brand_item['brand_id'], ENT_QUOTES, 'UTF-8'); ?>"
                            <?php echo (isset($_GET['brand']) && $_GET['brand'] === $brand_item['brand_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($brand_item['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
            <form action="create_product_admin.php" method="get">
                <button class="btn btn-primary">
                    <span>Create New Product</span>
                </button>
            </form>
        </div>

        <table class="order-history-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Short Description</th>
                    <th>Price</th>
                    <th>Brand Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                    <tr>
                        <td><img src="src/images/<?php echo htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($row['product_name'] ?? 'Product', ENT_QUOTES, 'UTF-8'); ?>" style="width:35%;"></td>
                        <td><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['short_description'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['price'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['brand_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                        <div class="address_buttons">
                            <a href="edit_product_admin.php?product_id=<?php echo htmlspecialchars($row['product_id'], ENT_QUOTES, 'UTF-8'); ?>">Edit</a> /
                            <a href="delete_product.php?product_id=<?php echo htmlspecialchars($row['product_id'], ENT_QUOTES, 'UTF-8'); ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                        </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</body>

</html>
