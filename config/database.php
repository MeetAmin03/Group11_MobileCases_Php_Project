<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'mobile_case_store';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection() {

        if ($this->conn != null) {
            return $this->conn;
        }
        
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
