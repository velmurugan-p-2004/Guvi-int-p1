<?php
require_once 'simple_auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$auth = new SimpleAuth();

// Validate required fields
$required_fields = ['username', 'email', 'password'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Basic validation
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if (strlen($input['password']) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
    exit;
}

if (strlen($input['username']) < 3) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username must be at least 3 characters long']);
    exit;
}

$result = $auth->register($input['username'], $input['email'], $input['password']);

if (!$result['success']) {
    http_response_code(400);
}

echo json_encode($result);
?>