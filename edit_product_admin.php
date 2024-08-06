<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'config/database.php';
include_once 'objects/product.php';
include_once 'objects/brand.php'; // Include the Brand class
include_once 'objects/user.php'; // Include the User class

// Create a new Product and Brand object
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
$brand = new Brand($db);

// Check if the product ID is provided
if (!isset($_GET['product_id'])) {
    header("Location: view_products_list_admin.php");
    exit();
}

$product_id = $_GET['product_id'];


// Fetch the product details
$product->product_id = $product_id;
$product->readOne();

if (!$product->product_name) {
    // Redirect to error page later
    header("Location: view_products_list_admin.php");
    exit();
}

// Fetch all brands for the dropdown
$brand_stmt = $brand->readAll();
$brands = $brand_stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize variables and error array
$product_name = $product->product_name;
$short_description = $product->short_description;
$description = $product->description;
$price = $product->price;
$brand_id = $product->brand_id;
$image = $product->image;
$formated_description = $product->formatDescription($description);
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update product properties with form data
    $product_name = trim($_POST['product_name'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $brand_id = trim($_POST['brand_id'] ?? '');
    $image = $_FILES['image']['name'] ?? $image;

    // Basic validation
    if (empty($product_name)) {
        $errors['product_name'] = 'Product Name is required.';
    }
    if (empty($short_description)) {
        $errors['short_description'] = 'Short Description is required.';
    }

    if (empty($price)) {
        $errors['price'] = 'Price is required.';
    }
    if (empty($brand_id)) {
        $errors['brand_id'] = 'Brand is required.';
    }

    // Image upload
    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "src/images/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);

        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Image uploaded successfully
        } else {
            $errors['image'] = 'Failed to upload image.';
        }
    }

    // Update product if there are no errors
    if (empty($errors)) {
        $product->product_id = $product_id;
        $product->product_name = $product_name;
        $product->short_description = $short_description;
        $product->price = $price;
        $product->brand_id = $brand_id;
        $product->image = $image;

       

        if ($product->update()) {
            header("Location: view_products_list_admin.php");
            exit();
        } else {
            $errors['update'] = 'Failed to update product.';
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <link rel="stylesheet" type="text/css" href="src/css/profile.css">
    <link rel="stylesheet" type="text/css" href="src/css/styles.css">
</head>

<body>
    <?php include 'navbar.php'; ?>
    <section class="form-section">
        <h3>Edit Product</h3>
        <form action="edit_product_admin.php?product_id=<?php echo htmlspecialchars($product_id, ENT_QUOTES); ?>" method="POST" enctype="multipart/form-data">
            <div>
                <label for="product_name">Product Name:</label>
                <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product_name, ENT_QUOTES); ?>">
                <?php if (isset($errors['product_name'])) : ?>
                    <div class="error"><?php echo $errors['product_name']; ?></div>
                <?php endif; ?>
            </div>
            <div>
                <label for="short_description">Short Description:</label>
                <input type="text" id="short_description" name="short_description" value="<?php echo htmlspecialchars($short_description, ENT_QUOTES); ?>">
                <?php if (isset($errors['short_description'])) : ?>
                    <div class="error"><?php echo $errors['short_description']; ?></div>
                <?php endif; ?>
            </div>
            <!-- <div>
                <label for="description">Description:</label>
                <textarea id="description" name="description"><?php echo $formated_description; ?></textarea>

                <?php if (isset($errors['description'])) : ?>
                    <div class="error"><?php echo $errors['description']; ?></div>
                <?php endif; ?>
            </div> -->


            <div>
                <label for="price">Price:</label>
                <input type="text" id="price" name="price" value="<?php echo htmlspecialchars($price, ENT_QUOTES); ?>">
                <?php if (isset($errors['price'])) : ?>
                    <div class="error"><?php echo $errors['price']; ?></div>
                <?php endif; ?>
            </div>
            <div>
                <label for="brand_id">Brand:</label>
                <select id="brand_id" name="brand_id">
                    <option value="">Select a Brand</option>
                    <?php foreach ($brands as $b) : ?>
                        <option value="<?php echo htmlspecialchars($b['brand_id'], ENT_QUOTES); ?>" <?php echo ($brand_id == $b['brand_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($b['name'], ENT_QUOTES); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['brand_id'])) : ?>
                    <div class="error"><?php echo $errors['brand_id']; ?></div>
                <?php endif; ?>
            </div>
            <div>
                <label for="image">Product Image:</label>
                <input type="file" id="image" name="image">
                <?php if ($image) : ?>
                    <img src="src/images/<?php echo htmlspecialchars($image, ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?>" style="width:100px;">
                <?php endif; ?>
                <?php if (isset($errors['image'])) : ?>
                    <div class="error"><?php echo $errors['image']; ?></div>
                <?php endif; ?>
            </div>
            <button type="submit">Update Product</button>
            <?php if (isset($errors['update'])) : ?>
                <div class="error"><?php echo $errors['update']; ?></div>
            <?php endif; ?>
        </form>
        <?php if (isset($_GET['success']) && $_GET['success'] === 'true') : ?>
            <div class="success">Product updated successfully!</div>
        <?php endif; ?>
    </section>
</body>

</html>