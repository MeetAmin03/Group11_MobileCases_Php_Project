<?php
class User {
    private $conn;
    private $table_name = "Users";

    public $user_id;
    public $userType;
    public $username;
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $mobile;
    public $is_active;

    private static $instance = null;

    private function __construct($db) {
        $this->conn = $db;
    }

    public static function getInstance($db) {
        if (self::$instance == null) {
            self::$instance = new User($db);
        }
        return self::$instance;
    }

    public function getFullName() {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->user_id = $row['user_id'];
            $this->userType = $row['userType'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->mobile = $row['mobile'];
            $this->is_active = $row['is_active'];
        }
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (userType, username, email, password) VALUES (:userType, :username, :email, :password)";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->userType = htmlspecialchars(strip_tags($this->userType));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));

        // Bind values
        $stmt->bindParam(":userType", $this->userType);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", password_hash($this->password, PASSWORD_BCRYPT));

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login() {
        $query = "SELECT user_id, password FROM " . $this->table_name . " WHERE username = :username LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($this->password, $row['password'])) {
            $this->user_id = $row['user_id'];
            $this->readOne();
            return true;
        }
        return false;
    }

    // Update user data
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET first_name = :first_name, last_name = :last_name, mobile = :mobile, email = :email WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->mobile = htmlspecialchars(strip_tags($this->mobile));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Bind values
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":mobile", $this->mobile);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":user_id", $this->user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Verify user password
    public function verifyPassword($oldPassword) {
        if (empty($oldPassword)) {
            return false; // or throw an exception if you prefer
        }
    
        $query = "SELECT password FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Ensure $row['password'] is not null
        if ($row && isset($row['password']) && !empty($row['password']) && password_verify($oldPassword, $row['password'])) {
            return true;
        }
    
        return false;
    }

    // Change user password
    public function changePassword($newPassword) {
        if (empty($newPassword)) {
            return false; // or throw an exception if you prefer
        }
    
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $newPassword = htmlspecialchars(strip_tags($newPassword));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
    
        // Bind values
        $stmt->bindParam(":password", password_hash($newPassword, PASSWORD_BCRYPT));
        $stmt->bindParam(":user_id", $this->user_id);
    
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Make user in_active
    public function delete() {
        $query = "UPDATE " . $this->table_name . " SET is_active = 0 WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Verify user_id and userType set in session and return true // Parameter: allowed userTypes : array
    // Return array of two boolean values if user_id and userType are not set in session or userType is not in allowedUserTypes
    public function verifyUserSession($allowedUserTypes = []) {
        $user_id = $_SESSION['user_id'] ?? null;
        $userType = $_SESSION['userType'] ?? null;

        $res = [true, 'Success', true]; // Pramas [status, message, authorized status of userType]

        if (!$user_id || !$userType) {
            $res = [false, 'User not logged in', true];
        } elseif ($allowedUserTypes && !in_array($userType, $allowedUserTypes)) {
            $res = [false, 'User not authorized', false];
        }


        return $res;

    
    }
}
?>
