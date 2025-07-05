<?php
session_start();
require_once "../config/db.php";
require_once "../models/Activity.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../activities.php?error=Invalid request method");
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $activity = new Activity($db);
    
    $activity->id = $_POST['activity_id'];
    
    if($activity->delete()) {
        header("Location: ../activities.php?success=Activity deleted successfully");
    } else {
        header("Location: ../activities.php?error=Failed to delete activity");
    }
    
} catch(Exception $e) {
    error_log("Delete activity error: " . $e->getMessage());
    header("Location: ../activities.php?error=An error occurred while deleting the activity");
}
?>
