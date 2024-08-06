<?php
session_start();

include_once 'config/database.php';
include_once 'objects/product.php';

$database = new Database();
$db = $database->getConnection();

include_once 'objects/user.php';
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

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="src/css/cart.css">
    <link rel="stylesheet" href="src/css/thankyou.css">
    <link rel="stylesheet" href="src/css/styles.css">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <?php if (empty($cart)): ?>
        <section class="thank-you-section">
            <div class="thank-you-card">
                <h1 style="color: red;">Your Cart is Empty</h1>
                <p>It looks like you don't have any items in your cart.</p>
                <div class="thank-you-actions">
                    <a href="index.php" class="btn">Browse Products</a>
                </div>
            </div>
        </section>
    <?php else: ?>



    <section class="cart-section">
        <div class="cart-card">
           
            <div class="cart-item-div">
                <section class="cart-item-sec">
                    
                        <?php foreach ($cart as $id => $quantity): ?>
                            <?php
                            $product->product_id = $id;
                            $product->readOne();
                            $item_total = $product->price * $quantity;
                            $total += $item_total;
                            ?>
                            <div class="cart-item">
                                <div class="cart-item-img">
                                    <img src="src/images/<?php echo htmlspecialchars($product->image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product->product_name, ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="cart-item-info">
                                    <div class="cart-title-sec">
                                        <div class="cart-title">
                                            <h3><?php echo htmlspecialchars($product->product_name, ENT_QUOTES, 'UTF-8'); ?></h3>
                                        </div>
                                        <div class="cart-price">
                                            <ul>
                                                <li></li>
                                                <li class="price">$<?php echo htmlspecialchars($product->price, ENT_QUOTES, 'UTF-8'); ?></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <p><span>&#10003;</span> Available to ship</p>
                                    </div>
                                    <div class="quantity-sec">
                                        <div class="qty" onclick="updateQuantity('<?php echo $id; ?>', -1)">-</div>
                                        <div class="qty-number" id="qty-<?php echo $id; ?>"><?php echo $quantity; ?></div>
                                        <div class="qty" onclick="updateQuantity('<?php echo $id; ?>', 1)">+</div>
                                    </div>
                                    <div class="remove-product-div">
                                        <button class="remove-button" onclick="removeItem('<?php echo $id; ?>')">Remove</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="product-total">
                            <h3>Product Total</h3>
                            <h3>$<?php echo number_format($total, 2); ?></h3>
                        </div>
                    
                </section>
            </div>
            

            <?php if (!empty($cart)): ?>
            <div class="cart-total-sec">
                <div class="cart-total-div">
                    <div class="order-summary">
                        <h3>Order Summary</h3>
                        <div class="subtotal">
                            <p>Product Subtotal</p>
                            <p>$<?php echo number_format($total, 2); ?></p>
                        </div>
                        <div class="product-discount">
                            <p>Order Discount</p>
                            <p class="discount">-$0.00</p> <!-- Update discount as needed -->
                        </div>
                        <div class="shipping">
                            <p>Estimated Shipping</p>
                            <p>$10.00</p> <!-- Update shipping cost as needed -->
                        </div>
                        <div class="taxes">
                            <p>Estimated Taxes</p>
                            <p>$0.00</p> <!-- Update taxes as needed -->
                        </div>
                        <hr class="hr-line">
                        <div class="final-total">
                            <h3>Estimated Total</h3>
                            <h3>$<?php echo number_format($total + 10, 2); ?></h3> <!-- Update total calculation as needed -->
                        </div>
                        <hr class="hr-line">
                        <div class="checkout-btn">
                            <a href="checkout.php">Continue to Checkout</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>


    <?php endif; ?>
    <script>
        function updateQuantity(productId, change) {
            var qtyElement = document.getElementById('qty-' + productId);
            var currentQty = parseInt(qtyElement.textContent);
            var newQty = currentQty + change;
            if (newQty < 1) newQty = 1; // Minimum quantity is 1

            qtyElement.textContent = newQty;

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('id=' + productId + '&quantity=' + newQty);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    location.reload(); // Reload to update totals
                }
            };
        }

        function removeItem(productId) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'remove_from_cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('id=' + productId);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    location.reload(); // Reload to update cart
                }
            };
        }
    </script>
</body>

</html>
