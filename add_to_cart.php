<?php
session_start();

// Verify user is logged in
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

$id = isset($_GET['id']) ? $_GET['id'] : '';
$quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;

if ($id) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (!isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = 0;
    }

    $_SESSION['cart'][$id] += $quantity;
}

header('Location: cart.php');
exit;
?>
