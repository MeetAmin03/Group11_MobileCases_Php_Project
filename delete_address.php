<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'config/database.php';
include_once 'objects/addresses.php'; // Include the Addresses class
include_once 'objects/user.php'; // Include the User class

// Create a new Addresses object
$database = new Database();
$db = $database->getConnection();
$addresses = new Addresses($db);

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

// Check if the address ID is provided
if (!isset($_GET['address_id'])) {
    header("Location: view_addresses.php");
    exit();
}

$address_id = $_GET['address_id'];

// Set the address ID in the Addresses object
$addresses->address_id = $address_id;

// Attempt to delete the address
if ($addresses->delete()) {
    header("Location: view_addresses.php?success=true");
    exit();
} else {
    header("Location: view_addresses.php?error=delete_failed");
    exit();
}
?>
