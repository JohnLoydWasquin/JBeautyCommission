<?php
// auth/process_login.php
session_start();
require_once '../config/config.php';

$pdo = getDB();

// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['error_msg'] = "Please enter both email and password.";
    header("Location: login.php");
    exit;
} else {
    // Fetch user by email
    $stmt = $pdo->prepare("SELECT id, password_hash, first_name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify password hash
    if ($user && password_verify($password, $user['password_hash'])) {
        // Success! Start secure session
        session_regenerate_id(true); // Prevents Session Fixation Attacks
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'];
        
        // Redirect to protected dashboard
        header("Location: ../index.php");
        exit;
    } else {
        // Keep error message generic to prevent email enumeration
        $_SESSION['error_msg'] = "Invalid email address or password.";
        header("Location: login.php");
        exit;
    }
}
?>