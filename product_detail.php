<?php
session_start();

include_once 'config/database.php';
include_once 'objects/product.php';
include_once 'objects/user.php';

$database = new Database();
$db = $database->getConnection();

// Verify user is logged in
$user = User::getInstance($db);
$res = $user->verifyUserSession(['user','admin']);

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

$product_id = isset($_GET['id']) ? $_GET['id'] : 0;

if (!$product_id) {
    $error_message = urlencode("Please provide a Product ID");
    header("Location: error_page.php?message=$error_message");
    exit();
}

$product->product_id = $product_id;
$product->readOne();


if (!$product->product_name) {
    $error_message = urlencode("Please provide a valid product ID");
    header("Location: error_page.php?message=$error_message");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product->product_name, ENT_QUOTES, 'UTF-8'); ?> - Product Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="src/css/product_detail.css">
    <link rel="stylesheet" href="src/css/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="product-detail">
            <div class="product-image">
                <img src="src/images/<?php echo htmlspecialchars($product->image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product->product_name, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="product-info">
                <h2 class="product-title"><?php echo htmlspecialchars($product->product_name, ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="product-description">
                    <?php echo $product->description; ?>
                </p>
                <div class="product-price">
                    <span class="label">Price:</span>
                    <span class="value">$<?php echo htmlspecialchars($product->price, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="product-brand">
                    <span class="label">Brand:</span>
                    <span class="value"><?php echo htmlspecialchars($product->brand_name, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <?php if(isset($_SESSION['userType']) && $_SESSION['userType'] === 'user'): ?>
                    <div class="quantity-control">
                        <button class="quantity-button" onclick="changeQuantity(-1)">-</button>
                        <input type="text" class="quantity-input" id="quantity-input" value="1" readonly>
                        <button class="quantity-button" onclick="changeQuantity(1)">+</button>
                    </div>
                
                    <form action="add_to_cart.php" method="get">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($product->product_id, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="quantity" value="1" id="quantity">
                        <button type="submit" class="add-to-cart-button">Add to Cart</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        function changeQuantity(amount) {
            const quantityInputEle = document.getElementById('quantity-input');
            const quantityEle = document.getElementById('quantity');
            const currentQuantity = parseInt(quantityInputEle.value);
            let newQuantity = currentQuantity + amount;
            if (newQuantity < 1) newQuantity = 1;
            quantityInputEle.value = newQuantity;
            quantityEle.value = newQuantity;
        }
    </script>
</body>
</html>
