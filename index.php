<?php
session_start();

// $owner = exec('whoami');

// echo '</pre>';
// print_r($owner);
// echo '</pre>';


if (isset($_SESSION['user_id']) && isset($_SESSION['userType']) && $_SESSION['userType'] === 'admin') {
    header("Location: view_products_list_admin.php");
    exit();
}

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'config/database.php';
include_once 'objects/product.php';
include_once 'objects/brand.php';
include_once 'objects/user.php';

$database = new Database();
$db = $database->getConnection();

// Verify user is logged in
$user = User::getInstance($db);
$res = $user->verifyUserSession(['user']);

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


$product = new Product($db);

// Get selected brand from form submission
$selected_brand = isset($_GET['brand']) ? $_GET['brand'] : '';

// Prepare the SQL query
$query = "SELECT p.*, b.name AS brand_name FROM Products p JOIN Brands b ON p.brand_id = b.brand_id";
if ($selected_brand) {
    $query .= " WHERE b.name = :selected_brand";
}
$stmt = $db->prepare($query);

if ($selected_brand) {
    $stmt->bindParam(':selected_brand', $selected_brand);
}

if ($stmt->execute()) {
    $brands = $db->query("SELECT DISTINCT name FROM Brands")->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "Error executing query: " . $stmt->errorInfo()[2];
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <title>PhoneFashion | Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Cabin&family=Playfair+Display&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="Imgs/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="src/css/styles.css">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
</head>

<body>
    <!-- Navbar/Menubar -->
    <?php include 'navbar.php'; ?>

    <header>
    </header>

    <main>
        <div class="shop-container w-85">
            <h1>Shop Page</h1>

            <form method="GET" action="" class="filter-form">
                <label for="brand" class="filter-label">Filter by Brand:</label>
                <select name="brand" id="brand" class="filter-select">
                    <option value="">All Brands</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?php echo htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8'); ?>"
                            <?php echo (isset($_GET['brand']) && $_GET['brand'] === $brand['name']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary filter">Filter</button>
            </form>

            <div id="products" class="product-grid">
                <!-- Products will be dynamically added here -->

                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="product">
                        <a href="product_detail.php?id=<?php echo htmlspecialchars($row['product_id'], ENT_QUOTES, 'UTF-8'); ?>" class="product-link" style="color: black;">
                            <img src="src/images/<?php echo htmlspecialchars($row['image'] ?? 'default.jpg', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($row['name'] ?? 'Product', ENT_QUOTES, 'UTF-8'); ?>">
                            <h3><?php echo htmlspecialchars($row['name'] ?? 'No Name', ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p><?php echo htmlspecialchars($row['short_description'] ?? 'No Description', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="card-text">Brand: <?php echo htmlspecialchars($row['brand_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="card-text">Price: $ <?php echo htmlspecialchars($row['price'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></p>
                        </a>
                        <div class="product-btn">
                        <form action="add_to_cart.php" method="get">
                            <input type="hidden" name="id" value="<?php echo $row['product_id']; ?>">   
                            <button class="btn btn-primary">
                                <span>Add to Cart</span>
                            </button>
                        </form>
                        </div>

                        
                    </div>

                <?php endwhile; ?>

            </div>
        </div>
    </main>

    <!-- Jquery CDN -->
    <script src="https://code.jquery.com/jquery-3.4.1.js"></script>

    <!-- Slick Slider CDN JS file -->
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <!-- <script src="src/js/script.js"></script> -->
    <!-- <script>
        jQuery(document).ready(function ($) {
            $(".art-sec-slider").slick({
                slidesToShow: 4,
                infinite: true,
                slidesToScroll: 1,
                prevArrow: '<button type="button" class="slick-custom-arrow slick-prev"> < </button>',
                nextArrow: '<button type="button" class="slick-custom-arrow slick-next"> > </button>'
            });
        });
    </script> -->
</body>

</html>
