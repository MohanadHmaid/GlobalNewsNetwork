<?php
session_start();

// Destroy all session data
session_destroy();

// Redirect back to the referring page or index
$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header('Location: ' . $redirect);
exit;
?>
