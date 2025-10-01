<?php
require_once 'config.php';

class SimpleAuth {
    private $users_file = 'data/users.json';
    private $profiles_file = 'data/profiles.json';
    private $sessions_file = 'data/sessions.json';
    
    public function __construct() {
        // Create data directory if it doesn't exist
        if (!file_exists('data')) {
            mkdir('data', 0777, true);
        }
        
        // Initialize files if they don't exist
        if (!file_exists($this->users_file)) {
            file_put_contents($this->users_file, json_encode([]));
        }
        if (!file_exists($this->profiles_file)) {
            file_put_contents($this->profiles_file, json_encode([]));
        }
        if (!file_exists($this->sessions_file)) {
            file_put_contents($this->sessions_file, json_encode([]));
        }
    }
    
    private function loadUsers() {
        return json_decode(file_get_contents($this->users_file), true) ?: [];
    }
    
    private function saveUsers($users) {
        file_put_contents($this->users_file, json_encode($users, JSON_PRETTY_PRINT));
    }
    
    private function loadSessions() {
        return json_decode(file_get_contents($this->sessions_file), true) ?: [];
    }
    
    private function saveSessions($sessions) {
        file_put_contents($this->sessions_file, json_encode($sessions, JSON_PRETTY_PRINT));
    }
    
    public function register($username, $email, $password) {
        $users = $this->loadUsers();
        
        // Check if user exists
        foreach ($users as $user) {
            if ($user['username'] === $username || $user['email'] === $email) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
        }
        
        // Add new user
        $users[] = [
            'id' => count($users) + 1,
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->saveUsers($users);
        
        return ['success' => true, 'message' => 'Registration successful'];
    }
    
    public function login($username, $password) {
        $users = $this->loadUsers();
        
        foreach ($users as $user) {
            if (($user['username'] === $username || $user['email'] === $username) 
                && password_verify($password, $user['password'])) {
                
                // Create session
                $sessionToken = bin2hex(random_bytes(32));
                $sessions = $this->loadSessions();
                
                $sessions[$sessionToken] = [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'login_time' => time(),
                    'expires' => time() + SESSION_TIMEOUT
                ];
                
                $this->saveSessions($sessions);
                
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
            }
        }
        
        return ['success' => false, 'message' => 'Invalid credentials'];
    }
    
    public function validateSession($sessionToken) {
        $sessions = $this->loadSessions();
        
        if (!isset($sessions[$sessionToken])) {
            return ['success' => false, 'message' => 'Invalid session'];
        }
        
        $session = $sessions[$sessionToken];
        
        if ($session['expires'] < time()) {
            unset($sessions[$sessionToken]);
            $this->saveSessions($sessions);
            return ['success' => false, 'message' => 'Session expired'];
        }
        
        // Extend session
        $sessions[$sessionToken]['expires'] = time() + SESSION_TIMEOUT;
        $this->saveSessions($sessions);
        
        return ['success' => true, 'data' => $session];
    }
    
    public function logout($sessionToken) {
        $sessions = $this->loadSessions();
        unset($sessions[$sessionToken]);
        $this->saveSessions($sessions);
        
        return ['success' => true, 'message' => 'Logout successful'];
    }
}

class SimpleProfile {
    private $profiles_file = 'data/profiles.json';
    
    private function loadProfiles() {
        return json_decode(file_get_contents($this->profiles_file), true) ?: [];
    }
    
    private function saveProfiles($profiles) {
        file_put_contents($this->profiles_file, json_encode($profiles, JSON_PRETTY_PRINT));
    }
    
    public function getProfile($userId) {
        $profiles = $this->loadProfiles();
        
        foreach ($profiles as $profile) {
            if ($profile['user_id'] == $userId) {
                return ['success' => true, 'profile' => $profile];
            }
        }
        
        return ['success' => false, 'message' => 'Profile not found'];
    }
    
    public function updateProfile($userId, $profileData) {
        $profiles = $this->loadProfiles();
        
        $profileData['user_id'] = (int)$userId;
        $profileData['updated_at'] = date('Y-m-d H:i:s');
        
        // Check if profile exists
        $found = false;
        foreach ($profiles as &$profile) {
            if ($profile['user_id'] == $userId) {
                $profile = array_merge($profile, $profileData);
                $found = true;
                break;
            }
        }
        
        // If not found, create new profile
        if (!$found) {
            $profileData['created_at'] = date('Y-m-d H:i:s');
            $profiles[] = $profileData;
        }
        
        $this->saveProfiles($profiles);
        
        return ['success' => true, 'message' => 'Profile updated successfully'];
    }
    
    public function deleteProfile($userId) {
        $profiles = $this->loadProfiles();
        
        foreach ($profiles as $key => $profile) {
            if ($profile['user_id'] == $userId) {
                unset($profiles[$key]);
                $this->saveProfiles(array_values($profiles));
                return ['success' => true, 'message' => 'Profile deleted successfully'];
            }
        }
        
        return ['success' => false, 'message' => 'Profile not found'];
    }
}
?>