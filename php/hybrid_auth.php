<?php
require_once 'config.php';

// Fallback implementation that simulates the database structure
class FallbackMySQLUser {
    private $users_file = 'data/mysql_users.json';
    
    public function __construct() {
        if (!file_exists('data')) {
            mkdir('data', 0777, true);
        }
        if (!file_exists($this->users_file)) {
            file_put_contents($this->users_file, json_encode([]));
        }
    }
    
    public function registerUser($username, $email, $password) {
        try {
            $users = json_decode(file_get_contents($this->users_file), true) ?: [];
            
            // Check if user already exists (simulating prepared statement)
            foreach ($users as $user) {
                if ($user['username'] === $username || $user['email'] === $email) {
                    return ['success' => false, 'message' => 'Username or email already exists'];
                }
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user (simulating prepared statement)
            $newUser = [
                'id' => count($users) + 1,
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $users[] = $newUser;
            file_put_contents($this->users_file, json_encode($users, JSON_PRETTY_PRINT));
            
            return ['success' => true, 'message' => 'Registration successful', 'user_id' => $newUser['id']];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }
    
    public function authenticateUser($username, $password) {
        try {
            $users = json_decode(file_get_contents($this->users_file), true) ?: [];
            
            // Find user (simulating prepared statement)
            foreach ($users as $user) {
                if (($user['username'] === $username || $user['email'] === $username) 
                    && password_verify($password, $user['password'])) {
                    
                    return [
                        'success' => true,
                        'user' => [
                            'id' => $user['id'],
                            'username' => $user['username'],
                            'email' => $user['email']
                        ]
                    ];
                }
            }
            
            return ['success' => false, 'message' => 'Invalid credentials'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Authentication failed: ' . $e->getMessage()];
        }
    }
}

class FallbackRedisSession {
    private $sessions_file = 'data/redis_sessions.json';
    
    public function __construct() {
        if (!file_exists('data')) {
            mkdir('data', 0777, true);
        }
        if (!file_exists($this->sessions_file)) {
            file_put_contents($this->sessions_file, json_encode([]));
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
                'login_time' => time(),
                'expires' => time() + SESSION_TIMEOUT
            ];
            
            // Store session (simulating Redis)
            $sessions = json_decode(file_get_contents($this->sessions_file), true) ?: [];
            $sessions[$sessionToken] = $sessionData;
            file_put_contents($this->sessions_file, json_encode($sessions, JSON_PRETTY_PRINT));
            
            return ['success' => true, 'session_token' => $sessionToken];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Session creation failed: ' . $e->getMessage()];
        }
    }
    
    public function validateSession($sessionToken) {
        try {
            $sessions = json_decode(file_get_contents($this->sessions_file), true) ?: [];
            
            if (!isset($sessions[$sessionToken])) {
                return ['success' => false, 'message' => 'Invalid session'];
            }
            
            $sessionData = $sessions[$sessionToken];
            
            if ($sessionData['expires'] < time()) {
                unset($sessions[$sessionToken]);
                file_put_contents($this->sessions_file, json_encode($sessions, JSON_PRETTY_PRINT));
                return ['success' => false, 'message' => 'Session expired'];
            }
            
            // Extend session (simulating Redis expire)
            $sessions[$sessionToken]['expires'] = time() + SESSION_TIMEOUT;
            file_put_contents($this->sessions_file, json_encode($sessions, JSON_PRETTY_PRINT));
            
            return ['success' => true, 'data' => $sessionData];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Session validation failed'];
        }
    }
    
    public function destroySession($sessionToken) {
        try {
            $sessions = json_decode(file_get_contents($this->sessions_file), true) ?: [];
            unset($sessions[$sessionToken]);
            file_put_contents($this->sessions_file, json_encode($sessions, JSON_PRETTY_PRINT));
            
            return ['success' => true, 'message' => 'Session destroyed'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Session destruction failed'];
        }
    }
}

class FallbackMongoProfile {
    private $profiles_file = 'data/mongo_profiles.json';
    
    public function __construct() {
        if (!file_exists('data')) {
            mkdir('data', 0777, true);
        }
        if (!file_exists($this->profiles_file)) {
            file_put_contents($this->profiles_file, json_encode([]));
        }
    }
    
    public function getProfile($userId) {
        try {
            $profiles = json_decode(file_get_contents($this->profiles_file), true) ?: [];
            
            // Find profile (simulating MongoDB query)
            foreach ($profiles as $profile) {
                if ($profile['user_id'] == $userId) {
                    return ['success' => true, 'profile' => $profile];
                }
            }
            
            return ['success' => false, 'message' => 'Profile not found'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to fetch profile: ' . $e->getMessage()];
        }
    }
    
    public function updateProfile($userId, $profileData) {
        try {
            $profiles = json_decode(file_get_contents($this->profiles_file), true) ?: [];
            
            // Prepare update data (simulating MongoDB document)
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
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Check if profile exists (simulating MongoDB find)
            $found = false;
            foreach ($profiles as &$profile) {
                if ($profile['user_id'] == $userId) {
                    $profile = array_merge($profile, $updateData);
                    $found = true;
                    break;
                }
            }
            
            // If not found, create new profile (simulating MongoDB upsert)
            if (!$found) {
                $updateData['created_at'] = date('Y-m-d H:i:s');
                $profiles[] = $updateData;
            }
            
            file_put_contents($this->profiles_file, json_encode($profiles, JSON_PRETTY_PRINT));
            
            return ['success' => true, 'message' => 'Profile updated successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update profile: ' . $e->getMessage()];
        }
    }
}

// Main Authentication Class that follows the specified architecture
class HybridAuth {
    private $mysqlManager;
    private $redisManager;
    private $mongoManager;
    
    public function __construct() {
        // Try to use real databases first, fallback to file-based simulation
        try {
            // Attempt to connect to real MySQL
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->mysqlManager = new MySQLUserManager();
        } catch (Exception $e) {
            // Fallback to file-based MySQL simulation
            $this->mysqlManager = new FallbackMySQLUser();
        }
        
        // Check if Redis extension is available
        if (class_exists('Redis')) {
            try {
                $redis = new Redis();
                $redis->connect(REDIS_HOST, REDIS_PORT);
                $this->redisManager = new RedisSessionManager();
            } catch (Exception $e) {
                $this->redisManager = new FallbackRedisSession();
            }
        } else {
            // Fallback to file-based Redis simulation
            $this->redisManager = new FallbackRedisSession();
        }
        
        // Check if MongoDB extension is available
        if (class_exists('MongoDB\Driver\Manager')) {
            try {
                $mongo = new MongoDB\Driver\Manager("mongodb://" . MONGO_HOST . ":" . MONGO_PORT);
                $this->mongoManager = new MongoProfileManager();
            } catch (Exception $e) {
                $this->mongoManager = new FallbackMongoProfile();
            }
        } else {
            // Fallback to file-based MongoDB simulation
            $this->mongoManager = new FallbackMongoProfile();
        }
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