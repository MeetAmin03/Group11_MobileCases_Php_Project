<?php
session_start();

include_once 'config/database.php';
include_once 'objects/addresses.php';
include_once 'objects/user.php';


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

header('Content-Type: application/json'); // Ensure response is JSON

$database = new Database();
$db = $database->getConnection();
$addresses = new Addresses($db);

$response = array('success' => false, 'address' => array());

if (isset($_GET['address_id'])) {
    $addresses->address_id = $_GET['address_id'];
    $address = $addresses->readOne(); // Fetch the address based on address_id

    if ($address) {
        $response['success'] = true;
        $response['address'] = $address;
    } else {
        $response['error'] = 'Address not found.';
    }
} else {
    $response['error'] = 'Address ID not provided.';
}

echo json_encode($response);
?>
