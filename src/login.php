<?php
session_start();
require_once 'backend/config/database.php';
require_once 'backend/classes/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['error'] = 'Username and password are required';
    header('Location: index.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$user->username = $username;
$user->password = $password;

if ($user->login()) {
    $_SESSION['user_id'] = $user->user_id;
    $_SESSION['username'] = $user->username;
    $_SESSION['role'] = $user->role;
    $_SESSION['logged_in'] = true;
    $_SESSION['success'] = 'Login successful!';
} else {
    $_SESSION['error'] = 'Invalid username or password';
}

// Redirect back to the referring page or index
$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header('Location: ' . $redirect);
exit;
?>