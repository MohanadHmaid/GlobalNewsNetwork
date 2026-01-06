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

if (!isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$user->username = $input['username'];
$user->password = $input['password'];

if ($user->login()) {
    $_SESSION['user_id'] = $user->user_id;
    $_SESSION['username'] = $user->username;
    $_SESSION['role'] = $user->role;
    $_SESSION['logged_in'] = true;

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'user_id' => $user->user_id,
            'username' => $user->username,
            'role' => $user->role
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
}
?>

