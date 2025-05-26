<?php
// Database connection configuration
require_once __DIR__ . '/../configuration/databaseConnection.php';
require_once __DIR__ . '/../models/user.model.php';

$database = new DatabaseConnection('localhost', 'root', '', 'bookshelf');
$connection = $database->connect();

function login($email, $password) {
    global $connection;
    
    $stmt = $connection->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "User found";
        $userData = $result->fetch_assoc();
        $user = new User($userData['name'], $userData['email'], $userData['password'], $userData['createdAt']);
        return $user;
    } else {
        return null; // Invalid credentials
    }
}

?>