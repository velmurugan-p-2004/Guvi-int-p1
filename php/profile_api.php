<?php
require_once 'simple_auth.php';

header('Content-Type: application/json');

$auth = new SimpleAuth();
$profileManager = new SimpleProfile();

// Get session token from headers
$headers = getallheaders();
$sessionToken = $headers['Authorization'] ?? $_GET['session_token'] ?? null;

if (!$sessionToken) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session token required']);
    exit;
}

// Remove "Bearer " prefix if present
$sessionToken = str_replace('Bearer ', '', $sessionToken);

// Validate session
$sessionResult = $auth->validateSession($sessionToken);
if (!$sessionResult['success']) {
    http_response_code(401);
    echo json_encode($sessionResult);
    exit;
}

$userId = $sessionResult['data']['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get profile
    $result = $profileManager->getProfile($userId);
    if (!$result['success']) {
        http_response_code(404);
    }
    echo json_encode($result);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Update profile
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    $result = $profileManager->updateProfile($userId, $input);
    if (!$result['success']) {
        http_response_code(400);
    }
    echo json_encode($result);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Delete profile
    $result = $profileManager->deleteProfile($userId);
    if (!$result['success']) {
        http_response_code(400);
    }
    echo json_encode($result);
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>