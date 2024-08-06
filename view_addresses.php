<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'config/database.php';
include_once 'objects/addresses.php'; // Include the Addresses class
include_once 'objects/user.php'; // Include the User class

$database = new Database();
$db = $database->getConnection();

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

$addresses = new Addresses($db);

// Set the user ID from the session
$addresses->user_id = $_SESSION['user_id'];

// Fetch all addresses for the user
$addresses_stmt = $addresses->readAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>My Addresses</title>
    <link rel="stylesheet" href="src/css/order_history.css">
    <link rel="stylesheet" href="src/css/styles.css">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <section class="order-history-section">
        <div class="order-history-header">
            <h1>My Addresses</h1>
            <!-- <button class=""><a href="create_address.php" class="btn btn-primary">Add New Address</a></button> -->
            <form action="create_address.php" method="get">   
                <button class="btn btn-primary">
                    <span>Add New Address</span>
                </button>
            </form>
        </div>
        
        <table class="order-history-table">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Street</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Postal Code</th>
                    <th>Country</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($addresses_stmt->rowCount() > 0): ?>
                    <?php while ($address = $addresses_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($address['first_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($address['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($address['street'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($address['city'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($address['state'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($address['postal_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($address['country'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($address['mobile'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($address['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <div class="address_buttons">
                                    <a href="edit_address.php?address_id=<?php echo htmlspecialchars($address['address_id'], ENT_QUOTES, 'UTF-8'); ?>">Edit</a> /
                                    <a href="delete_address.php?address_id=<?php echo htmlspecialchars($address['address_id'], ENT_QUOTES, 'UTF-8'); ?>" onclick="return confirm('Are you sure you want to delete this address?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center;">No addresses found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</body>

</html>
