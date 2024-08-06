<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('fpdf/fpdf.php');
include_once 'config/database.php';
include_once 'objects/orders.php';
include_once 'objects/order_item.php';
include_once 'objects/product.php';
include_once 'objects/addresses.php'; // Added Addresses class
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
$order_item = new OrderItem($db);
$product = new Product($db);
$addresses = new Addresses($db); // Initialize Addresses

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;

if ($order_id == 0) {
    echo "Order not found.";
    exit();
}

$order->order_id = $order_id;
$order->readOne();

// Fetch the address
$addresses->address_id = $order->address_id;
$address_stmt = $addresses->readOne();

// Calculate total order amount
$total_amount = 0;
$order_item->order_id = $order_id;
$stmt = $order_item->readAll();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $total_amount += $row['quantity'] * $row['price'];
}

class PDF extends FPDF {
    function Header() {
        // Center the logo
        $this->Image('src/images/logo_white.png', 85, 10, 30); // Adjust the X position to center
        $this->SetFont('Arial', 'B', 14);
        $this->Ln(30); // Adjust the line break to make space for the header
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        $this->SetY(-10);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, '© 2024 PhoneFashion. All rights reserved.', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Order Details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(95, 10, 'Order Details', 0, 0, 'L');
$pdf->Cell(35, 10, 'Shipping Details', 0, 1, 'R');

// Order Details content
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(95, 10, 'Order Number: ' . $order->order_number, 0, 1, 'L');
$pdf->Cell(95, 10, 'Order Date: ' . date('F j, Y', strtotime($order->created_at)), 0, 1, 'L');
$pdf->Cell(95, 10, 'Total: $' . number_format($total_amount, 2), 0, 1, 'L');

// Shipping Details content
$pdf->SetXY(105, $pdf->GetY() - 30); // Adjust Y position to align with Order Details
if ($address_stmt) {
    $shipping_details = "Name: " . $address_stmt['first_name'] . " " . $address_stmt['last_name'] . "\n"
                      . "Address: " . $address_stmt['street'] . "\n"
                      . "City: " . $address_stmt['city'] . "\n"
                      . "State: " . $address_stmt['state'] . "\n"
                      . "ZIP Code: " . $address_stmt['postal_code'] . "\n"
                      . "Country: " . $address_stmt['country'] . "\n"
                      . "Phone: " . $address_stmt['mobile'] . "\n"
                      . "Email: " . $address_stmt['email'];
    $pdf->MultiCell(95, 5, $shipping_details); // Adjusted height to 5
} else {
    $pdf->MultiCell(95, 5, 'Address not found.'); // Adjusted height to 5
}


// Move to next line for table
$pdf->Ln(10);

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(80, 10, 'Product Name', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Quantity', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Price', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Total', 1, 1, 'C', true);

// Table Body
$pdf->SetFont('Arial', '', 12);
$stmt->execute(); // Re-execute the query to fetch the data again
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $product->product_id = $row['product_id'];
    $product->readOne();
    $pdf->Cell(80, 10, $product->product_name, 1);
    $pdf->Cell(30, 10, $row['quantity'], 1);
    $pdf->Cell(40, 10, '$' . number_format($row['price'], 2), 1);
    $pdf->Cell(40, 10, '$' . number_format($row['quantity'] * $row['price'], 2), 1);
    $pdf->Ln();
}

// Total
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(150, 10, 'Order Total', 1);
$pdf->Cell(40, 10, '$' . number_format($total_amount, 2), 1, 1, 'C');

$pdf->Output('D', 'invoice_' . $order->order_number . '.pdf');
?>
