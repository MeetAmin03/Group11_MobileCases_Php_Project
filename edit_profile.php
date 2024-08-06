<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'config/database.php';
include_once 'objects/user.php'; // Include your User class

// Create a new User object
$database = new Database();
$db = $database->getConnection();

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

// Assuming the user is logged in and their user ID is stored in the session
$user_id = $_SESSION['user_id'] ?? 0;

// Fetch user data if the user ID is set
$errors = [];
if ($user_id) {
    $user->user_id = $user_id;
    $user->readOne(); // Read the user data

    // Populate form fields with existing user data
    $first_name = $user->first_name;
    $last_name = $user->last_name;
    $mobile = $user->mobile;
    $email = $user->email;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update user properties with form data
    $user->first_name = trim($_POST['first_name'] ?? '');
    $user->last_name = trim($_POST['last_name'] ?? '');
    $user->mobile = trim($_POST['mobile'] ?? '');
    $user->email = trim($_POST['email'] ?? '');

    // Basic validation
    if (empty($user->first_name)) {
        $errors['first_name'] = 'First Name is required.';
    }
    if (empty($user->last_name)) {
        $errors['last_name'] = 'Last Name is required.';
    }
    if (empty($user->mobile)) {
        $errors['mobile'] = 'Mobile Number is required.';
    }
    if (empty($user->email) || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid Email is required.';
    }

    // Update user data if there are no errors
    if (empty($errors)) {
        $user->user_id = $user_id; // Set user_id for update
        if ($user->update()) {
            // Redirect with success flag
            header("Location: edit_profile.php?success=true"); // Redirect to the same page with success query parameter
            exit();
        } else {
            $errors['update'] = 'Failed to update profile.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="src/css/profile.css">
    <link rel="stylesheet" type="text/css" href="src/css/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <section class="profile-section">
        <div class="form-section">
            <h3>Edit Profile Details</h3>
            <form action="edit_profile.php" method="POST">
                <div>
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user->first_name ?? '', ENT_QUOTES); ?>">
                    <?php if (isset($errors['first_name'])) : ?>
                        <div class="error"><?php echo $errors['first_name']; ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user->last_name ?? '', ENT_QUOTES); ?>">
                    <?php if (isset($errors['last_name'])) : ?>
                        <div class="error"><?php echo $errors['last_name']; ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="mobile">Mobile Number:</label>
                    <input type="tel" id="mobile" name="mobile" value="<?php echo htmlspecialchars($user->mobile ?? '', ENT_QUOTES); ?>">
                    <?php if (isset($errors['mobile'])) : ?>
                        <div class="error"><?php echo $errors['mobile']; ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user->email ?? '', ENT_QUOTES); ?>">
                    <?php if (isset($errors['email'])) : ?>
                        <div class="error"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>
                </div>
                <button type="submit">Save Changes</button>
            </form>
            <?php if (isset($errors['update'])) : ?>
                <div class="error"><?php echo $errors['update']; ?></div>
            <?php endif; ?>
        </div>
    </section>
    <script>
        // Check if the URL contains the success parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            alert('Profile updated successfully!');
        }
    </script>
</body>
</html>
