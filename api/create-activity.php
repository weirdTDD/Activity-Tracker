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
    
    $required = ['title', 'priority', 'description', 'assigned_to', 'due_date'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit;
        }
    }
    
    $database = new Database();
    $db = $database->getConnection();
    $activity = new Activity($db);
    $activity->title = $input['title'];
    $activity->description = $input['description'];
    $activity->priority = $input['priority'];
    $activity->created_by = $_SESSION['user_id'];
    $activity->assigned_to = $input['assigned_to'];
    $activity->due_date = $input['due_date'];

    if($activity->create()) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Activity created successfully']);

    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create activity']);

    }
} catch(Exception $e) {
    error_log("Create activity error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);

}
?>