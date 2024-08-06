<?php
session_start();


include_once 'config/database.php';
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

// Get Order number and total amount from the session
$order_number = $_SESSION['order_number'];
$total = $_SESSION['total'];


// Get order_id from the URL
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
    <link rel="stylesheet" href="src/css/thankyou.css">
    <link rel="stylesheet" href="src/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <section class="thank-you-section">
        <div class="thank-you-card">
            <h1>Thank You!</h1>
            <p>Your order has been placed successfully.</p>
            <p>Order Number: <?php echo htmlspecialchars($order_number, ENT_QUOTES, 'UTF-8'); ?></p>
            <p>Total Amount: $<?php echo number_format($total, 2); ?></p>
            <p>We will send you an email confirmation shortly.</p>
            <div class="thank-you-actions">
                <a href="index.php" class="btn">Back to Home</a>
                <a href="order_history.php" class="btn">View Order History</a>
                <a href="invoice.php?order_id=<?php echo htmlspecialchars($order_id, ENT_QUOTES, 'UTF-8'); ?>" class="btn">Download Invoice</a>
            </div>
        </div>
    </section>
</body>

</html>
