<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class Product {
    private $conn;
    private $table_name = "Products";

    public $product_id;
    public $product_name; // This should match the database column name
    public $short_description;
    public $description;
    public $price;
    public $brand_id;
    public $brand_name;
    public $image;
    public $created_at;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT p.*, b.name AS brand_name FROM " . $this->table_name . " p JOIN Brands b ON p.brand_id = b.brand_id WHERE p.is_active = 1 ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT p.*, b.name AS brand_name FROM " . $this->table_name . " p JOIN Brands b ON p.brand_id = b.brand_id WHERE p.product_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->product_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->product_name = $row['name']; // Ensure this matches your DB column
        $this->short_description = $row['short_description'];
        $this->description = $row['description'];
        $this->price = $row['price'];
        $this->brand_id = $row['brand_id'];
        $this->brand_name = $row['brand_name'];
        $this->image = $row['image'];
        $this->created_at = $row['created_at'];
        $this->is_active = $row['is_active'];

    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
            (name, short_description, description, price, brand_id, image) 
            VALUES (:name, :short_description, :description, :price, :brand_id, :image)";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->product_name = htmlspecialchars(strip_tags($this->product_name));
        $this->short_description = htmlspecialchars(strip_tags($this->short_description));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->brand_id = htmlspecialchars(strip_tags($this->brand_id));
        $this->image = htmlspecialchars(strip_tags($this->image));

        // Bind values
        $stmt->bindParam(':name', $this->product_name);
        $stmt->bindParam(':short_description', $this->short_description);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':brand_id', $this->brand_id);
        $stmt->bindParam(':image', $this->image);

        // Execute the query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function update() {
        // Prepare the update query
        $query = "UPDATE " . $this->table_name . " 
                  SET 
                      name = :name,
                      short_description = :short_description,
                      description = :description,
                      price = :price,
                      brand_id = :brand_id,
                      image = :image
                  WHERE 
                      product_id = :product_id";

        $stmt = $this->conn->prepare($query);

        // Bind the parameters
        $stmt->bindParam(':name', $this->product_name);
        $stmt->bindParam(':short_description', $this->short_description);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':brand_id', $this->brand_id);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':product_id', $this->product_id);

        // Execute the query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function formatDescription($description) {
        // Remove HTML tags
        $clean_description = strip_tags($description);

        // Optionally format the description
        // Example: Convert new lines to <br> tags
        // $formatted_description = nl2br($clean_description);

        return $clean_description;
    }

    // Make a product inactive
    public function delete() {
        $query = "UPDATE " . $this->table_name . " SET is_active = 0 WHERE product_id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $this->product_id);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    public function get_products_by_brand($brand_id = '') {
        $query = "SELECT p.*, b.name AS brand_name FROM " . $this->table_name . " p JOIN Brands b ON p.brand_id = b.brand_id WHERE p.is_active = 1 AND b.is_active = 1";
        
        if (!empty($brand_id)) {
            $query .= " AND b.brand_id = :brand_id";
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($brand_id)) {
            $stmt->bindParam(':brand_id', $brand_id);
        }

        $stmt->execute();
        return $stmt;
    }
}
?>
