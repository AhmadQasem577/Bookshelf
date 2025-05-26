<?php
// Database connection configuration
require_once __DIR__ . '/../configuration/databaseConnection.php';
require_once __DIR__ . '/../models/user.model.php';

$database = new DatabaseConnection('localhost', 'root', '', 'bookshelf');
$connection = $database->connect();

function signup($name, $email, $password) {
    global $connection;
    // Check if user already exists
    $stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    echo 'sql query executed';
    if ($result->num_rows > 0) {
        return null; // User already exists
    }
    // Insert new user
    echo 'inserting new user';
    $stmt = $connection->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $name, $email, $password);
    if ($stmt->execute()) {
        $newUser = new User($name, $email, $password, date('Y-m-d H:i:s'));
        return $newUser; // Return the newly created user object
    } else {
        return null; // Signup failed
    }
}
?>