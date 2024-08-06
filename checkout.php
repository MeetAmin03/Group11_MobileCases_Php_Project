<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'config/database.php';
include_once 'objects/product.php';
include_once 'objects/orders.php';
include_once 'objects/order_item.php';
include_once 'objects/addresses.php'; // Include the Addresses class
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
$order = new Order($db);
$orderItem = new OrderItem($db);
$addresses = new Addresses($db);

// Set the user ID from the session
$addresses->user_id = $_SESSION['user_id'];

// Fetch all addresses for the user
$addresses_stmt = $addresses->readAll();

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;

// POST request
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["selected_address"])) {
        $errors['address'] = "Address selection is required";
    }

    if (empty($errors)) {
        // Calculate the total
        foreach ($cart as $id => $quantity) {
            $product->product_id = $id;
            $product->readOne();
            $item_total = $product->price * $quantity;
            $total += $item_total;
        }

        // Create order
        $order->user_id = $_SESSION['user_id'];
        $order->total = $total;
        $order->address_id = $_POST["selected_address"]; // Set the selected address ID
        if ($order->create()) {
            // Retrieve the last inserted order id
            $order_id = $db->lastInsertId();

            // Save order details in session
            $_SESSION['total'] = $total;
            $_SESSION['order_number'] = $order->order_number;
            $_SESSION['order_id'] = $order_id;

            // Create order items
            foreach ($cart as $id => $quantity) {
                $product->product_id = $id;
                $product->readOne();
                $orderItem->order_id = $order_id;
                $orderItem->product_id = $id;
                $orderItem->quantity = $quantity;
                $orderItem->price = $product->price;
                $orderItem->create();
            }

            // Clear the cart
            unset($_SESSION['cart']);
            header("Location: thank_you.php?order_id=$order_id");
            exit();
        } else {
            $errors['order'] = "Failed to place the order. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Checkout</title>
    <link rel="stylesheet" href="src/css/cart.css">
    <link rel="stylesheet" href="src/css/styles.css">
    <script>
        function showAddressDetails() {
            let select = document.getElementById('selected_address');
            let addressDetails = document.getElementById('address_details');
            let errorElement = document.querySelector('.error');
            let addressId = select.value;

            if (addressId === 'new') {
                window.location.href = 'create_address.php?from_checkout=1';
            }

            if (addressId) {

                // Remove any existing error message
                if (errorElement) {
                    errorElement.innerHTML = '';
                }
                let xhr = new XMLHttpRequest();
                xhr.open('GET', 'fetch_address.php?address_id=' + encodeURIComponent(addressId), true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        let response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            addressDetails.innerHTML = `
                                <p><strong>Address:</strong></p>
                                <p>${response.address.first_name} ${response.address.last_name}</p>
                                <p>${response.address.mobile}</p>
                                <p>${response.address.email}</p>
                                <p>${response.address.street}, ${response.address.city}</p>
                                <p>${response.address.state}, ${response.address.postal_code}</p>
                                <p>${response.address.country}</p>
                            `;
                        } else {
                            addressDetails.innerHTML = '<p>Error fetching address details.</p>';
                        }
                    }
                };
                xhr.send();
            } else {
                addressDetails.innerHTML = '';
            }
        }
    </script>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <section class="cart-section">
        <div class="cart-card">
            <?php if (empty($cart)) : ?>
                <div class="cart-empty">
                    <h3>Your cart is empty</h3>
                    <a href="products.php">Continue Shopping</a>
                </div>
            <?php else : ?>
                <div class="cart-item-div">
                    <section class="cart-item-sec">
                        <?php foreach ($cart as $id => $quantity) : ?>
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
                                                <li class="price">$<?php echo htmlspecialchars($product->price, ENT_QUOTES, 'UTF-8'); ?></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <p>Qty: <?php echo htmlspecialchars($quantity, ENT_QUOTES, 'UTF-8'); ?></p>
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
            <?php endif; ?>

            <?php if (!empty($cart)) : ?>
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
                                <a href="cart.php">Edit Cart</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <?php if (!empty($cart)) : ?>
            <div class="form-section">
                <h3>Select Address</h3>
                <form action="checkout.php" method="POST">
                    <div>
                        <label for="selected_address">Choose Address:</label>
                        <select id="selected_address" name="selected_address" onchange="showAddressDetails()">
                            <option value="">-- Select Address --</option>
                            <?php while ($address = $addresses_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?php echo htmlspecialchars($address['address_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    - <?php echo htmlspecialchars($address['street'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php echo htmlspecialchars($address['city'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php echo htmlspecialchars($address['state'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php echo htmlspecialchars($address['postal_code'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php echo htmlspecialchars($address['country'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endwhile; ?>
                            <option value="new">Add New Address</option>
                        </select>
                        <?php if (isset($errors['address'])) : ?>
                            <div class="error"><?php echo $errors['address']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div id="address_details"></div>
                    <button type="submit">Place Order</button>
                </form>
            </div>
        <?php endif; ?>
    </section>
</body>

</html>
