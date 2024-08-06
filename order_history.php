<?php
// Ensure the user is logged in
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

$order = new Order($db);
$orderItem = new OrderItem($db);

$user_id = $_SESSION['user_id'];

// Fetch orders for the user
$query = "SELECT * FROM Orders WHERE user_id = ? ORDER BY created_at DESC" ;
$stmt = $db->prepare($query);
$stmt->bindParam(1, $user_id);
$stmt->execute();

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="src/css/order_history.css">
    <link rel="stylesheet" href="src/css/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="order-history-section">
        <h2>Order History</h2>
        <table class="order-history-table">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Order Date</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($orders)) {
                    echo '<tr><td colspan="5">No orders found.</td></tr>';
                } else {
                    foreach ($orders as $row) {
                        $order->order_id = $row['order_id'];
                        $order->readOne();

                        // Fetch order items
                        $orderItem->order_id = $order->order_id;
                        $items_stmt = $orderItem->readAll();

                      
                        $total = number_format($order->total, 2);
                        $order_date = date('F j, Y', strtotime($row['created_at']));
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['order_number'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($order_date, ENT_QUOTES, 'UTF-8'); ?></td>
                   
                    <td>$<?php echo $total; ?></td>
                    <td><a href="view_order.php?order_id=<?php echo htmlspecialchars($row['order_id'], ENT_QUOTES, 'UTF-8'); ?>" class="view-order-btn">View Order</a> / 
                    <a href="invoice.php?order_id=<?php echo htmlspecialchars($row['order_id'], ENT_QUOTES, 'UTF-8'); ?>" class="view-order-btn">Download Invoice</a></td>
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
