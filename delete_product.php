<?php
session_start();

include_once 'config/database.php';
include_once 'objects/product.php';
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

$product = new Product($db);

// Get the product_id from the URL
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : 0;

if (empty($product_id)) {
    echo "Invalid product ID.";
    exit();
}

// Set the product_id for the Product object
$product->product_id = $product_id;

// Attempt to delete the product
if ($product->delete()) {
    header("Location: view_products_list_admin.php");
    exit();
} else {
    echo "Unable to delete the product. Please try again.";
}
?>
