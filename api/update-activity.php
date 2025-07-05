<?php
session_start();
require_once "../config/db.php";
require_once "../models/Activity.php";

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if(!isset($input['activity_id']) || !isset($input['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Update activity status
    $query = "UPDATE activities SET status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if($stmt->execute([$input['status'], $input['activity_id']])) {
        echo json_encode(['success' => true, 'message' => 'Activity updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update activity']);
    }
    
} catch(Exception $e) {
    error_log("Update activity error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
