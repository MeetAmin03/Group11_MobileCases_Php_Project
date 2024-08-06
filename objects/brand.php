<?php
class Brand {
    private $conn;
    private $table_name = "Brands";

    public $brand_id;
    public $brand_name;
    public $description;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE brand_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->brand_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->brand_name = $row['brand_name'];
        $this->description = $row['description'];
        $this->is_active = $row['is_active'];

    }

    // Make Brand in_active
    public function delete() {
        $query = "UPDATE " . $this->table_name . " SET is_active = 0 WHERE brand_id = :brand_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':brand_id', $this->brand_id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
