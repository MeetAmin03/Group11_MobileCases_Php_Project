<?php
class Addresses {
    private $conn;
    private $table_name = "Addresses";

    public $address_id;
    public $user_id;
    public $first_name;
    public $last_name;
    public $street;
    public $city;
    public $state;
    public $postal_code;
    public $country;
    public $mobile;
    public $email;
    public $created_at;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE address_id = :address_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':address_id', $this->address_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Debugging statement
        error_log("Query result: " . print_r($row, true));

        return $row ? $row : null;
    }


    

    public function readAll() {
        // Create the query
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id AND is_active = 1";
        
        // Prepare the statement
        $stmt = $this->conn->prepare($query);
        
        // Bind the user_id parameter
        $stmt->bindParam(':user_id', $this->user_id);
        
        // Execute the query
        $stmt->execute();
        
        return $stmt; // Return the PDOStatement object
    }

    // Create a new address
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
            (user_id, first_name, last_name, street, city, state, postal_code, country, mobile, email) 
            VALUES (:user_id, :first_name, :last_name, :street, :city, :state, :postal_code, :country, :mobile, :email)";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->street = htmlspecialchars(strip_tags($this->street));
        $this->city = htmlspecialchars(strip_tags($this->city));
        $this->state = htmlspecialchars(strip_tags($this->state));
        $this->postal_code = htmlspecialchars(strip_tags($this->postal_code));
        $this->country = htmlspecialchars(strip_tags($this->country));
        $this->mobile = htmlspecialchars(strip_tags($this->mobile));
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":street", $this->street);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":state", $this->state);
        $stmt->bindParam(":postal_code", $this->postal_code);
        $stmt->bindParam(":country", $this->country);
        $stmt->bindParam(":mobile", $this->mobile);
        $stmt->bindParam(":email", $this->email);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update an existing address
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
            SET first_name = :first_name, last_name = :last_name, street = :street, city = :city, 
            state = :state, postal_code = :postal_code, country = :country, mobile = :mobile, email = :email 
            WHERE address_id = :address_id";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->address_id = htmlspecialchars(strip_tags($this->address_id));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->street = htmlspecialchars(strip_tags($this->street));
        $this->city = htmlspecialchars(strip_tags($this->city));
        $this->state = htmlspecialchars(strip_tags($this->state));
        $this->postal_code = htmlspecialchars(strip_tags($this->postal_code));
        $this->country = htmlspecialchars(strip_tags($this->country));
        $this->mobile = htmlspecialchars(strip_tags($this->mobile));
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind values
        $stmt->bindParam(":address_id", $this->address_id);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":street", $this->street);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":state", $this->state);
        $stmt->bindParam(":postal_code", $this->postal_code);
        $stmt->bindParam(":country", $this->country);
        $stmt->bindParam(":mobile", $this->mobile);
        $stmt->bindParam(":email", $this->email);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }


    // Make an address inactive
    public function delete() {
        $query = "UPDATE " . $this->table_name . " SET is_active = 0 WHERE address_id = :address_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':address_id', $this->address_id);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>
