<?php
// Ensure the admin is logged in
session_start();

// Include necessary files and initialize database connection
include_once 'config/database.php';
include_once 'objects/orders.php';
include_once 'objects/order_item.php';
include_once 'objects/user.php';

$database = new Database();
$db = $database->getConnection();

// Verify user is logged in
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

$order = new Order($db);
$orderItem = new OrderItem($db);

// Fetch all orders
$orders = $order->readAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="src/css/order_history.css">
    <link rel="stylesheet" href="src/css/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="order-history-section">
        <h2>All Orders</h2>
        <table class="order-history-table">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>User Email</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($orders)) {
                    echo '<tr><td colspan="5">No orders found.</td></tr>';
                } else {
                    foreach ($orders as $row) {
                        // Fetch order details
                        $order->order_id = $row['order_id'];
                        $order->readOne();

                        // Fetch user details
                        $user->user_id = $order->user_id;
                        $user->readOne();

                        // Calculate total
                        

                        $order_number = htmlspecialchars($order->order_number, ENT_QUOTES, 'UTF-8');
                        $user_email = htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8');
                        $total = htmlspecialchars($order->total, ENT_QUOTES, 'UTF-8');
                        $order_status = htmlspecialchars($order->status, ENT_QUOTES, 'UTF-8');
                ?>
                <tr>
                    <td><?php echo $order_number; ?></td>
                    <td><?php echo $user_email; ?></td>
                    <td>$<?php echo $total; ?></td>
                    <td><?php echo $order_status; ?></td>
                    <td>
                        <a href="view_order.php?order_id=<?php echo htmlspecialchars($order->order_id, ENT_QUOTES, 'UTF-8'); ?>" class="view-order-btn">View</a>
                    </td>
                </tr>
                <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </section>
</body>
</html>
