<?php
// Define database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'edu_nova_db');
define('DB_USER', 'root'); // Change for production
define('DB_PASS', '9889');     // Change for production

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // Charset utf8mb4 ensures compatibility with all regional characters
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            // Set timezone to Erbil (Kurdistan Region)
            $this->conn->exec("SET time_zone = '+03:00'");
            
        } catch(PDOException $exception) {
            // In a real environment, log this error instead of outputting it
            echo json_encode([
                "status" => "error", 
                "message" => "Database Connection Error: " . $exception->getMessage()
            ]);
            exit();
        }

        return $this->conn;
    }
}
?>