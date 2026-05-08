<?php
// auth/process_register.php
session_start();
require_once '../config/config.php';

$pdo = getDB();

// If someone tries to load this page directly without submitting the form, send them back
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit;
}

$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$password  = $_POST['password'] ?? '';

// Basic Validation
if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
    $_SESSION['error_msg'] = "Please fill in all required fields.";
    header("Location: register.php");
    exit;
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_msg'] = "Please enter a valid email address.";
    header("Location: register.php");
    exit;
} else {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        $_SESSION['error_msg'] = "An account with this email already exists.";
        header("Location: register.php");
        exit;
    } else {
        // Secure Password Hashing
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert into Database
        $insertStmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)");
        
        try {
            $insertStmt->execute([$firstName, $lastName, $email, $phone, $passwordHash]);
            
            // Success! Redirect to login with success message
            $_SESSION['success_msg'] = "Welcome to JBeauty! Your account has been created. Please sign in.";
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error_msg'] = "Registration failed. Please try again later.";
            header("Location: register.php");
            exit;
        }
    }
}