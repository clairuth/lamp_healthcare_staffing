<?php
// core/Database.php

// This file will handle the database connection.
// It uses the constants defined in app/config.php

class Database {
    private static $instance = null;
    private $connection;

    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;

    private function __construct() {
        $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // In a real application, log this error and show a user-friendly message
            // For now, just rethrow or die with error.
            error_log("Database Connection Error: " . $e->getMessage(), 0);
            die("Database connection failed. Please check logs or contact support.");
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Prevent cloning and unserialization
    private function __clone() { }
    public function __wakeup() { }
}

// Example usage (you would call this from your models or controllers):
/*
require_once __DIR__ . "/../app/config.php"; // Make sure config is loaded
$db = Database::getInstance();
$conn = $db->getConnection();
// Now use $conn for your queries, e.g., $stmt = $conn->prepare("SELECT * FROM users");
*/
?>
