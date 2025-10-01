<?php
require_once 'database.php';

class UserProfile {
    private $db;
    private $mongodb;
    private $redis;
    
    public function __construct() {
        $this->db = new DatabaseManager();
        $this->mongodb = $this->db->getMongoDBConnection();
        $this->redis = $this->db->getRedisConnection();
    }
    
    public function getProfile($userId) {
        try {
            $filter = ['user_id' => (int)$userId];
            $options = [];
            
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->mongodb->executeQuery(MONGO_DB . '.profiles', $query);
            
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
            $cursor = $this->mongodb->executeQuery(MONGO_DB . '.profiles', $query);
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
                $result = $this->mongodb->executeBulkWrite(MONGO_DB . '.profiles', $updateCommand);
            } else {
                // Insert new profile
                $updateData['created_at'] = new MongoDB\BSON\UTCDateTime();
                $insertCommand = new MongoDB\Driver\BulkWrite();
                $insertCommand->insert($updateData);
                $result = $this->mongodb->executeBulkWrite(MONGO_DB . '.profiles', $insertCommand);
            }
            
            return ['success' => true, 'message' => 'Profile updated successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update profile: ' . $e->getMessage()];
        }
    }
    
    public function deleteProfile($userId) {
        try {
            $filter = ['user_id' => (int)$userId];
            $deleteCommand = new MongoDB\Driver\BulkWrite();
            $deleteCommand->delete($filter);
            
            $result = $this->mongodb->executeBulkWrite(MONGO_DB . '.profiles', $deleteCommand);
            
            return ['success' => true, 'message' => 'Profile deleted successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to delete profile: ' . $e->getMessage()];
        }
    }
}
?>