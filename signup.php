<?php
session_start();

include_once 'config/database.php';
include_once 'objects/user.php';

$database = new Database();
$db = $database->getConnection();
$user = User::getInstance($db);


// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $user->username = $_POST['username'];
    $user->password = $_POST['password'];
    $isValidUser = $user->login();

    if ($isValidUser) {
        $_SESSION['user_id'] = $user->user_id;
        $_SESSION['userType'] = $user->userType;

        if ($user->userType === 'admin') {
            header("Location: view_products_list_admin.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $loginError = "Invalid login credentials.";
    }
}

// Handle signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $user->userType = 'user'; // Default user type
    $user->username = $_POST['username'];
    $user->password = $_POST['password'];
    $user->email = $_POST['email'];

    if ($user->create()) {
        header("Location: index.php");
        exit();
    } else {
        $signupError = "Unable to register user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phone Fashion | LogIn SignUp</title>
    <link rel="icon" type="image/x-icon" href="src/images/logo_black.png">
    <link rel="stylesheet" href="src/css/signup.css">
</head>
<body>
    <section>
        <div class="container">
            <!-- Login Form -->
            <div class="user signinBox">
                <div class="imgBox">
                    <img src="src/images/signup_1.png" alt="">
                </div>
                <div class="formBox">
                    <form action="" method="post">
                        <div class="sinup-logo"><a href="index.php"><img src="src/images/logo_white.png" alt=""></a></div>
                        <h2>Login</h2>
                        <?php if (isset($loginError)) echo "<div class='auth-error'>$loginError</div>"; ?>
                        <input type="text" name="username" placeholder="Enter Username" required>
                        <input type="password" name="password" placeholder="Enter Password" required>
                        <input type="submit" name="login" value="Login">
                        <p class="signup">Don't have an Account? <a href="#" onclick="javascript:doToggle();">Sign Up</a></p>
                    </form>
                </div>
            </div>
            <!-- Signup Form -->
            <div class="user signupBox">
                <div class="formBox">
                    <form action="" method="post">
                    <div class="sinup-logo"><a href="index.php"><img src="src/images/logo_white.png" alt=""></a></div>
                        <h2>Create An Account</h2>
                        <?php if (isset($signupError)) echo "<div class='alert alert-danger'>$signupError</div>"; ?>
                        <input type="text" name="username" placeholder="Enter Username" required>
                        <input type="email" name="email" placeholder="Enter Email" required>
                        <input type="password" name="password" placeholder="Enter Password" required>
                        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                        <input type="submit" name="signup" value="Sign Up">
                        <p class="signup">Already have an Account? <a href="#" onclick="javascript:doToggle();">Login</a></p>
                    </form>
                </div>
                <div class="imgBox">
                    <img src="src/images/signup_2.jpg" alt="">
                </div>
            </div>
        </div>
    </section>
    <script type="text/javascript">
        function doToggle(){
            var container = document.querySelector('.container');
            container.classList.toggle('active');
        }
    </script>
</body>
</html>
