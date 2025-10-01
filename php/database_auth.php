<?php
require_once 'config.php';

class MySQLUserManager {
    private $connection;
    
    public function __construct() {
        try {
            $this->connection = new PDO(
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
            throw new Exception("MySQL Connection failed: " . $e->getMessage());
        }
    }
    
    public function registerUser($username, $email, $password) {
        try {
            // Check if user already exists using prepared statement
            $stmt = $this->connection->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user into MySQL using prepared statement
            $stmt = $this->connection->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$username, $email, $hashedPassword]);
            
            $userId = $this->connection->lastInsertId();
            
            return ['success' => true, 'message' => 'Registration successful', 'user_id' => $userId];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }
    
    public function authenticateUser($username, $password) {
        try {
            // Get user from MySQL using prepared statement
            $stmt = $this->connection->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Authentication failed: ' . $e->getMessage()];
        }
    }
}

class RedisSessionManager {
    private $redis;
    
    public function __construct() {
        try {
            $this->redis = new Redis();
            $this->redis->connect(REDIS_HOST, REDIS_PORT);
        } catch (Exception $e) {
            throw new Exception("Redis Connection failed: " . $e->getMessage());
        }
    }
    
    public function createSession($userData) {
        try {
            // Generate session token
            $sessionToken = bin2hex(random_bytes(32));
            $sessionData = [
                'user_id' => $userData['id'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'login_time' => time()
            ];
            
            // Store session in Redis with expiration
            $this->redis->setex($sessionToken, SESSION_TIMEOUT, json_encode($sessionData));
            
            return ['success' => true, 'session_token' => $sessionToken];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Session creation failed: ' . $e->getMessage()];
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
    
    public function destroySession($sessionToken) {
        try {
            $this->redis->del($sessionToken);
            return ['success' => true, 'message' => 'Session destroyed'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Session destruction failed'];
        }
    }
}

class MongoProfileManager {
    private $manager;
    private $collection;
    
    public function __construct() {
        try {
            $this->manager = new MongoDB\Driver\Manager("mongodb://" . MONGO_HOST . ":" . MONGO_PORT);
            $this->collection = MONGO_DB . '.profiles';
        } catch (Exception $e) {
            throw new Exception("MongoDB Connection failed: " . $e->getMessage());
        }
    }
    
    public function getProfile($userId) {
        try {
            $filter = ['user_id' => (int)$userId];
            $options = [];
            
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->manager->executeQuery($this->collection, $query);
            
            $profile = null;
            foreach ($cursor as $document) {
                $profile = $document;
                break;
            }
            
            if (!$profile) {
                return ['success' => false, 'message' => 'Profile not found'];
            }
            
            return ['success' => true, 'profile' => $profile];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to fetch profile: ' . $e->getMessage()];
        }
    }
    
    public function updateProfile($userId, $profileData) {
        try {
            $filter = ['user_id' => (int)$userId];
            
            // Prepare update data
            $updateData = [
                'user_id' => (int)$userId,
                'first_name' => $profileData['first_name'] ?? '',
                'last_name' => $profileData['last_name'] ?? '',
                'age' => (int)($profileData['age'] ?? 0),
                'date_of_birth' => $profileData['date_of_birth'] ?? '',
                'contact' => $profileData['contact'] ?? '',
                'address' => $profileData['address'] ?? '',
                'city' => $profileData['city'] ?? '',
                'country' => $profileData['country'] ?? '',
                'bio' => $profileData['bio'] ?? '',
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ];
            
            // Check if profile exists
            $query = new MongoDB\Driver\Query($filter);
            $cursor = $this->manager->executeQuery($this->collection, $query);
            $exists = false;
            
            foreach ($cursor as $document) {
                $exists = true;
                break;
            }
            
            if ($exists) {
                // Update existing profile
                $update = ['$set' => $updateData];
                $updateCommand = new MongoDB\Driver\BulkWrite();
                $updateCommand->update($filter, $update);
                $result = $this->manager->executeBulkWrite($this->collection, $updateCommand);
            } else {
                // Insert new profile
                $updateData['created_at'] = new MongoDB\BSON\UTCDateTime();
                $insertCommand = new MongoDB\Driver\BulkWrite();
                $insertCommand->insert($updateData);
                $result = $this->manager->executeBulkWrite($this->collection, $insertCommand);
            }
            
            return ['success' => true, 'message' => 'Profile updated successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update profile: ' . $e->getMessage()];
        }
    }
}

// Main Authentication Class that coordinates all services
class DatabaseAuth {
    private $mysqlManager;
    private $redisManager;
    private $mongoManager;
    
    public function __construct() {
        $this->mysqlManager = new MySQLUserManager();
        $this->redisManager = new RedisSessionManager();
        $this->mongoManager = new MongoProfileManager();
    }
    
    public function register($username, $email, $password) {
        return $this->mysqlManager->registerUser($username, $email, $password);
    }
    
    public function login($username, $password) {
        $authResult = $this->mysqlManager->authenticateUser($username, $password);
        
        if (!$authResult['success']) {
            return $authResult;
        }
        
        $sessionResult = $this->redisManager->createSession($authResult['user']);
        
        if (!$sessionResult['success']) {
            return $sessionResult;
        }
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'session_token' => $sessionResult['session_token'],
            'user' => $authResult['user']
        ];
    }
    
    public function validateSession($sessionToken) {
        return $this->redisManager->validateSession($sessionToken);
    }
    
    public function logout($sessionToken) {
        return $this->redisManager->destroySession($sessionToken);
    }
    
    public function getProfile($userId) {
        return $this->mongoManager->getProfile($userId);
    }
    
    public function updateProfile($userId, $profileData) {
        return $this->mongoManager->updateProfile($userId, $profileData);
    }
}
?>