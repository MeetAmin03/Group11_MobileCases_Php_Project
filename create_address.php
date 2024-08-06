<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'config/database.php';
include_once 'objects/addresses.php';
include_once 'objects/user.php';


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

// Assuming the user is logged in and their user ID is stored in the session
$user_id = $_SESSION['user_id'] ?? 0;

// Initialize variables and error array
$first_name = '';
$last_name = '';
$street = '';
$city = '';
$state = '';
$postal_code = '';
$country = '';
$mobile = '';
$email = '';
$errors = [];

// Check if the URL contains the from_checkout parameter
$from_checkout_GET = isset($_GET['from_checkout']) && $_GET['from_checkout'] == 1;

echo '<pre>';
print_r($from_checkout_GET);
echo '</pre>';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update address properties with form data
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $from_checkout_POST = isset($_POST['from_checkout']) && $_POST['from_checkout'] == 1;

    // Basic validation
    if (empty($first_name)) {
        $errors['first_name'] = 'First Name is required.';
    }
    if (empty($last_name)) {
        $errors['last_name'] = 'Last Name is required.';
    }
    if (empty($street)) {
        $errors['street'] = 'Street is required.';
    }
    if (empty($city)) {
        $errors['city'] = 'City is required.';
    }
    if (empty($state)) {
        $errors['state'] = 'State is required.';
    }
    if (empty($postal_code)) {
        $errors['postal_code'] = 'Postal Code is required.';
    }
    if (empty($country)) {
        $errors['country'] = 'Country is required.';
    }
    if (empty($mobile)) {
        $errors['mobile'] = 'Mobile Number is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid Email is required.';
    }

    // Create address if there are no errors
    if (empty($errors)) {
        $addresses->user_id = $user_id;
        $addresses->first_name = $first_name;
        $addresses->last_name = $last_name;
        $addresses->street = $street;
        $addresses->city = $city;
        $addresses->state = $state;
        $addresses->postal_code = $postal_code;
        $addresses->country = $country;
        $addresses->mobile = $mobile;
        $addresses->email = $email;

        if ($addresses->create()) {
            // Redirect with success flag

            
           
            if ($from_checkout_POST) {
                header("Location: checkout.php");
                exit();
            }
            header("Location: view_addresses.php?success=true"); // Redirect to addresses view page
            exit();
        } else {
            $errors['create'] = 'Failed to create address.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Address</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="src/css/profile.css">
    <link rel="stylesheet" type="text/css" href="src/css/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <section class="profile-section">
        <div class="form-section">
            <h3>Create New Address</h3>
            <form action="create_address.php" method="POST">
                <input type="hidden" name="from_checkout" value="<?php echo $from_checkout_GET == 1 ? 1 : 0; ?>">
                <div>
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name, ENT_QUOTES); ?>">
                    <?php if (isset($errors['first_name'])) : ?>
                        <div class="error"><?php echo $errors['first_name']; ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name, ENT_QUOTES); ?>">
                    <?php if (isset($errors['last_name'])) : ?>
                        <div class="error"><?php echo $errors['last_name']; ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="street">Street:</label>
                    <input type="text" id="street" name="street" value="<?php echo htmlspecialchars($street, ENT_QUOTES); ?>">
                    <?php if (isset($errors['street'])) : ?>
                        <div class="error"><?php echo $errors['street']; ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="city">City:</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($city, ENT_QUOTES); ?>">
                    <?php if (isset($errors['city'])) : ?>
                        <div class="error"><?php echo $errors['city']; ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="state">State:</label>
                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($state, ENT_QUOTES); ?>">
                    <?php if (isset($errors['state'])) : ?>
                        <div class="error"><?php echo $errors['state']; ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="postal_code">Postal Code:</label>
                    <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($postal_code, ENT_QUOTES); ?>">
                    <?php if (isset($errors['postal_code'])) : ?>
                        <div class="error"><?php echo $errors['postal_code']; ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="country">Country:</label>
                    <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($country, ENT_QUOTES); ?>">
                    <?php if (isset($errors['country'])) : ?>
                        <div class="error"><?php echo $errors['country']; ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="mobile">Mobile Number:</label>
                    <input type="tel" id="mobile" name="mobile" value="<?php echo htmlspecialchars($mobile, ENT_QUOTES); ?>">
                    <?php if (isset($errors['mobile'])) : ?>
                        <div class="error"><?php echo $errors['mobile']; ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES); ?>">
                    <?php if (isset($errors['email'])) : ?>
                        <div class="error"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>
                </div>
                <button type="submit">Create Address</button>
            </form>
            <?php if (isset($errors['create'])) : ?>
                <div class="error"><?php echo $errors['create']; ?></div>
            <?php endif; ?>
        </div>
    </section>
    <script>
        // Check if the URL contains the success parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            alert('Address created successfully!');
        }
    </script>
</body>
</html>
