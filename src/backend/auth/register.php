<?php
session_start();
require_once '../config/database.php';
require_once '../classes/User.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username, email, and password are required']);
    exit;
}

// Validate input
if (strlen($input['username']) < 3) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username must be at least 3 characters long']);
    exit;
}

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

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$user->username = $input['username'];
$user->email = $input['email'];
$user->password = $input['password'];
$user->role = 'user'; // Default role

// Check if username already exists
if ($user->usernameExists()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Username already exists']);
    exit;
}

// Check if email already exists
if ($user->emailExists()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Email already exists']);
    exit;
}

if ($user->register()) {
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration failed']);
}
?>

