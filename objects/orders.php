<?php
class Order {
    private $conn;
    private $table_name = "Orders";

    public $order_id;
    public $order_number;
    public $user_id;
    public $total;
    public $address_id;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all orders
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE order_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->order_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->user_id = $row['user_id'];
            $this->total = $row['total'];
            $this->created_at = $row['created_at'];
            $this->order_number = $row['order_number'];
            $this->address_id = $row['address_id'];
            $this->status = $row['status'];
        }
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (user_id, order_number, total, address_id) VALUES (:user_id, :order_number, :total,:address_id)";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->total = htmlspecialchars(strip_tags($this->total));
        $this->order_number = $this->generateOrderNumber();
        $this->address_id = htmlspecialchars(strip_tags($this->address_id));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":total", $this->total);
        $stmt->bindParam(":order_number", $this->order_number);
        $stmt->bindParam(":address_id", $this->address_id);

        
        // print msg
        echo "Order created successfully";


        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readMaxID() {
        $query = "SELECT MAX(order_id) AS max_id FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['max_id'];
        }
        return null;
    }

   
    public function generateOrderNumber() {
            $timestamp = microtime(true);
            $randomNumber = mt_rand(1000, 9999);
            $orderNumber = strtoupper(substr(md5($timestamp . $randomNumber), 0, 8));
            return $orderNumber;
        }
        

}
?>
