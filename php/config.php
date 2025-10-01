<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Update this with your MySQL root password
define('DB_NAME', 'user_system');

// MongoDB configuration
define('MONGO_HOST', 'localhost');
define('MONGO_PORT', 27017);
define('MONGO_DB', 'user_profiles');

// Redis configuration
define('REDIS_HOST', 'localhost');
define('REDIS_PORT', 6379);

// Session configuration
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers for AJAX requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}
?>