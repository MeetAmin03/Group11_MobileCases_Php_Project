<?php
session_start();

include_once 'config/database.php';
include_once 'objects/orders.php';
include_once 'objects/order_item.php';
include_once 'objects/product.php';
include_once 'objects/addresses.php'; // Ensure this class file is included
include_once 'objects/user.php';

$database = new Database();
$db = $database->getConnection();
$database = new Database();
$db = $database->getConnection();

// Verify user is logged in
$user = User::getInstance($db);
$res = $user->verifyUserSession(['user', 'admin']);

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

$order = new Order($db);
$orderItem = new OrderItem($db);
$product = new Product($db);
$addresses = new Addresses($db); // Create instance of Addresses class

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;

// Fetch the order
$order->order_id = $order_id;
$order->readOne();

if (!$order->order_number) {
    $error_message = urlencode("Order Not Found");
    header("Location: error_page.php?message=$error_message");
    exit();
}

// Fetch order items
$orderItem->order_id = $order->order_id;
$items_stmt = $orderItem->readAll();

// Fetch the address
$addresses->address_id = $order->address_id;
$address_stmt = $addresses->readOne();

$total = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Order Details</title>
    <link rel="stylesheet" href="src/css/cart.css">
    <link rel="stylesheet" href="src/css/styles.css">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <section class="cart-section">
        <div class="cart-card" style="width: 50%;">
            <div class="cart-item-div" style="width: 100%;">
                <section class="cart-item-sec">
                    <div class="order-details-header">
                        <div class="order-summary">
                            <h3>Order Number: <?php echo htmlspecialchars($order->order_number, ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p>Order Date: <?php echo date('F j, Y', strtotime($order->created_at)); ?></p>
                            <p>Total: $<?php echo number_format($order->total, 2); ?></p>
                        </div>
                        <div class="address-details">
                            <h3>Shipping Address</h3>
                            <?php if ($address_stmt): ?>
                                <p><strong><?php echo htmlspecialchars($address_stmt['first_name'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($address_stmt['last_name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                                <p><?php echo htmlspecialchars($address_stmt['street'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p><?php echo htmlspecialchars($address_stmt['city'], ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($address_stmt['state'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($address_stmt['postal_code'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p><?php echo htmlspecialchars($address_stmt['country'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p>Phone: <?php echo htmlspecialchars($address_stmt['mobile'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p>Email: <?php echo htmlspecialchars($address_stmt['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php else: ?>
                                <p>Address not found.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($items_stmt->rowCount() > 0): ?>
                        <?php while ($item = $items_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <?php
                            $product->product_id = $item['product_id'];
                            $product->readOne();
                            $item_total = $product->price * $item['quantity'];
                            $total += $item_total;
                            ?>
                            <div class="cart-item view-order" style="justify-content: space-between;">
                                <div class="cart-item-img">
                                    <img src="src/images/<?php echo htmlspecialchars($product->image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product->product_name, ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="cart-title">
                                            <h3><?php echo htmlspecialchars($product->product_name, ENT_QUOTES, 'UTF-8'); ?></h3>
                                        </div>
                                </div>
                                <div class="cart-item-info">
                                    <div class="order-title-sec">
                                        
                                        <div class="cart-price">
                                            <ul>
                                                <li>Quantity: <?php echo htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8'); ?></li>
                                                <li class="price">Price: $<?php echo number_format($product->price, 2); ?></li>
                                                <li class="price">Total: $<?php echo number_format($item_total, 2); ?></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <div class="product-total">
                            <h3>Order Total</h3>
                            <h3>$<?php echo number_format($total, 2); ?></h3>
                        </div>
                    <?php else: ?>
                        <p>No items found for this order.</p>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </section>
</body>

</html>
