<?php
session_start();
require_once "../config/db.php";
require_once "../models/User.php";

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php?error=Invalid request");
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    
    $user->email = trim($_POST['email']);
    $user->password = $_POST['password'];
    
    if($user->login()) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->name;
                $_SESSION['user_role'] = $user->role;
        
        header("Location: ../dashboard.php");
        exit;
    } else {
        header("Location: login.php?error=Invalid email or password");
        exit;
    }
    
} catch(Exception $e) {
    error_log("Login error: " . $e->getMessage());
    header("Location: login.php?error=An error occurred. Please try again.");
    exit;
}
?>
