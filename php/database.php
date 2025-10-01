<?php
require_once 'config.php';

class DatabaseManager {
    private $mysql_connection;
    private $mongodb_connection;
    private $redis_connection;
    
    public function __construct() {
        $this->connectMySQL();
        $this->connectMongoDB();
        $this->connectRedis();
    }
    
    private function connectMySQL() {
        try {
            $this->mysql_connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die("MySQL Connection failed: " . $e->getMessage());
        }
    }
    
    private function connectMongoDB() {
        try {
            $this->mongodb_connection = new MongoDB\Driver\Manager("mongodb://" . MONGO_HOST . ":" . MONGO_PORT);
        } catch (Exception $e) {
            die("MongoDB Connection failed: " . $e->getMessage());
        }
    }
    
    private function connectRedis() {
        try {
            $this->redis_connection = new Redis();
            $this->redis_connection->connect(REDIS_HOST, REDIS_PORT);
        } catch (Exception $e) {
            die("Redis Connection failed: " . $e->getMessage());
        }
    }
    
    public function getMySQLConnection() {
        return $this->mysql_connection;
    }
    
    public function getMongoDBConnection() {
        return $this->mongodb_connection;
    }
    
    public function getRedisConnection() {
        return $this->redis_connection;
    }
    
    public function __destruct() {
        if ($this->mysql_connection) {
            $this->mysql_connection = null;
        }
        if ($this->redis_connection) {
            $this->redis_connection->close();
        }
    }
}
?>