<?php
// Database connection settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'password_manager');

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->connection->connect_error) {
            die('Database connection failed: ' . $this->connection->connect_error);
        }
        $this->connection->set_charset('utf8mb4');
    }

    // Singleton - one connection shared across the app
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): mysqli {
        return $this->connection;
    }
}
