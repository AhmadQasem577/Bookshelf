<?php
// Database connection configuration
require_once __DIR__ . '/../configuration/databaseConnection.php';

$database = new DatabaseConnection('localhost', 'root', '', 'bookshelf');
$connection = $database->connect();

function deleteBook($bookId, $userId) {
    global $connection;

    // First, check if the book is owned by the user
    $checkStmt = $connection->prepare("SELECT * FROM userownbooks WHERE book_id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $bookId, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        return false; // User doesn't own the book
    }

    // Delete from books (will cascade delete userownbooks & favorites via FK)
    $deleteStmt = $connection->prepare("DELETE FROM books WHERE book_id = ?");
    $deleteStmt->bind_param("i", $bookId);
    return $deleteStmt->execute();
}

?>