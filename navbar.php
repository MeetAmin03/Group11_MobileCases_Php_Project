<nav>
    <div class="logo">
        <a href="index.html">
            <!-- Logo is Created by myself using Figma interface design tool -->
            <a href="index.php"><img src="src/images/logo_black.png" alt="PhoneFashion LOGO"></a>
            <img >
        </a>
    </div>
    <div class="menubar">
        <ul>
        <?php if(isset($_SESSION['userType']) && $_SESSION['userType'] === 'admin'): ?>
                <li><a href="view_products_list_admin.php">Products</a></li>
                <li><a href="view_orders_admin.php">Customer Orders</a></li>
        <?php endif; ?>
        <?php if(isset($_SESSION['userType']) && $_SESSION['userType'] === 'user'): ?>
                <li><a href="index.php">Home</a></li>
                <li><a href="order_history.php">Orders</a></li>
                <li><a href="cart.php"><i class="fa fa-shopping-cart"></i></a></li>
        <?php endif; ?>
                <div class="dropdown">
                    <li><a href="#"><i class="fa fa-user"></i></a></li>
                    <div class="dropdown-content">
                        <ul>
                            <li>
                                <a href="edit_profile.php">Edit Profile</a>
                            </li>
                            <li>
                                <a href="change_password.php">Change Password</a>
                            </li>
                            <?php if(isset($_SESSION['userType']) && $_SESSION['userType'] === 'user'): ?>
                            <li>
                                <a href="view_addresses.php">Address</a>
                            </li>
                            <?php endif; ?>
                            <li>
                                <a href="logout.php">Logout</a>
                            </li>
                        </ul>
                    </div>
                </div>
        </ul>
    </div>
</nav>

