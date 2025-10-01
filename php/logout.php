<?php
require_once 'hybrid_auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['session_token'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Session token required']);
    exit;
}

$auth = new HybridAuth();
$result = $auth->logout($input['session_token']);

if (!$result['success']) {
    http_response_code(400);
}

echo json_encode($result);
?>