<?php
session_start();
require_once 'backend/config/database.php';
require_once 'backend/classes/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($username) || empty($email) || empty($password)) {
    $_SESSION['error'] = 'All fields are required';
    header('Location: index.php');
    exit;
}

if (strlen($username) < 3) {
    $_SESSION['error'] = 'Username must be at least 3 characters long';
    header('Location: index.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email format';
    header('Location: index.php');
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error'] = 'Password must be at least 6 characters long';
    header('Location: index.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$user->username = $username;
$user->email = $email;
$user->password = $password;
$user->role = 'user'; // Default role

// Check if username already exists
if ($user->usernameExists()) {
    $_SESSION['error'] = 'Username already exists';
    header('Location: index.php');
    exit;
}

// Check if email already exists
if ($user->emailExists()) {
    $_SESSION['error'] = 'Email already exists';
    header('Location: index.php');
    exit;
}

if ($user->register()) {
    $_SESSION['success'] = 'Registration successful! You can now login.';
} else {
    $_SESSION['error'] = 'Registration failed. Please try again.';
}

// Redirect back to the referring page or index
$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header('Location: ' . $redirect);
exit;
?>
