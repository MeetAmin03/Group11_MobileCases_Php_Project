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

// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = trim($_POST['old_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (empty($old_password)) {
        $errors['old_password'] = 'Old Password is required.';
    }
    if (empty($new_password)) {
        $errors['new_password'] = 'New Password is required.';
    }
    if ($new_password !== $confirm_password) {
        $errors['confirm_password'] = 'New Password and Confirm Password do not match.';
    }

    if (empty($errors)) {
        $user->user_id = $user_id;

        if ($user->verifyPassword($old_password)) {
            if ($user->changePassword($new_password)) {
                // Log out user after successful password change
                session_unset();
                session_destroy();
                header("Location: signup.php");
                exit();
            } else {
                $errors['update'] = 'Failed to update password.';
            }
        } else {
            $errors['old_password'] = 'Old Password is incorrect.';
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="src/css/profile.css">
    <link rel="stylesheet" type="text/css" href="src/css/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <section class="profile-section">
        <div class="form-section">
            <h3>Change Password</h3>
            <form action="change_password.php" method="POST">
                <div>
                    <label for="old_password">Old Password:</label>
                    <input type="password" id="old_password" name="old_password">
                    <?php if (isset($errors['old_password'])) : ?>
                        <div class="error"><?php echo $errors['old_password']; ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password">
                    <?php if (isset($errors['new_password'])) : ?>
                        <div class="error"><?php echo $errors['new_password']; ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                    <?php if (isset($errors['confirm_password'])) : ?>
                        <div class="error"><?php echo $errors['confirm_password']; ?></div>
                    <?php endif; ?>
                </div>
                <button type="submit">Change Password</button>
                <?php if (isset($errors['update'])) : ?>
                    <div class="error"><?php echo $errors['update']; ?></div>
                <?php endif; ?>
            </form>
        </div>
    </section>
    <script>
        // Check if the URL contains the password_changed parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('password_changed')) {
            alert('Password changed successfully! Please log in again.');
        }
    </script>
</body>
</html>
