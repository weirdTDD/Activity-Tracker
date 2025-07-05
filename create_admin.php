<?php
require_once "config/db.php";

$database = new Database();
$db = $database->getConnection();

$password_hash = password_hash("admin123", PASSWORD_DEFAULT);

$query = "INSERT INTO users (name, email, password) VALUES 
          ('Admin User', 'admin@example.com', :password)";

$stmt = $db->prepare($query);
$stmt->bindParam(":password", $password_hash);

if($stmt->execute()) {
    echo "Admin user created successfully!<br>";
    echo "Email: admin@example.com<br>";
    echo "Password: admin123";
} else {
    echo "Error creating admin user.";
}
