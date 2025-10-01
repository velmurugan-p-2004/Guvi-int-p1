<?php
require_once 'database.php';

class UserAuth {
    private $db;
    private $mysql;
    private $mongodb;
    private $redis;
    
    public function __construct() {
        $this->db = new DatabaseManager();
        $this->mysql = $this->db->getMySQLConnection();
        $this->mongodb = $this->db->getMongoDBConnection();
        $this->redis = $this->db->getRedisConnection();
    }
    
    public function register($username, $email, $password) {
        try {
            // Check if user already exists
            $stmt = $this->mysql->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user into MySQL
            $stmt = $this->mysql->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$username, $email, $hashedPassword]);
            
            $userId = $this->mysql->lastInsertId();
            
            return ['success' => true, 'message' => 'Registration successful', 'user_id' => $userId];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }
    
    public function login($username, $password) {
        try {
            // Get user from MySQL
            $stmt = $this->mysql->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            // Generate session token
            $sessionToken = bin2hex(random_bytes(32));
            $sessionData = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'login_time' => time()
            ];
            
            // Store session in Redis
            $this->redis->setex($sessionToken, SESSION_TIMEOUT, json_encode($sessionData));
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'session_token' => $sessionToken,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }
    
    public function validateSession($sessionToken) {
        try {
            $sessionData = $this->redis->get($sessionToken);
            if (!$sessionData) {
                return ['success' => false, 'message' => 'Invalid or expired session'];
            }
            
            $sessionData = json_decode($sessionData, true);
            
            // Extend session
            $this->redis->expire($sessionToken, SESSION_TIMEOUT);
            
            return ['success' => true, 'data' => $sessionData];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Session validation failed'];
        }
    }
    
    public function logout($sessionToken) {
        try {
            $this->redis->del($sessionToken);
            return ['success' => true, 'message' => 'Logout successful'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Logout failed'];
        }
    }
}
?>