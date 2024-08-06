<?php
class OrderItem {
    private $conn;
    private $table_name = "OrderItems";

    public $order_item_id;
    public $order_id;
    public $product_id;
    public $quantity;
    public $price;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE order_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->order_id);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (order_id,product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->order_id = htmlspecialchars(strip_tags($this->order_id));
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->price = htmlspecialchars(strip_tags($this->price));

        // Bind values
        $stmt->bindParam(":order_id", $this->order_id);
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":price", $this->price);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
