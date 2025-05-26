<?php
// Database connection configuration
require_once __DIR__ . '/../configuration/databaseConnection.php';
require_once __DIR__ . '/../models/book.model.php';
require_once __DIR__ . '/../models/user.model.php';

$database = new DatabaseConnection('localhost', 'root', '', 'bookshelf');
$connection = $database->connect();

function createBook($title, $author, $description, $pdf_content, $image_content, $publish_date, $userId) {
    global $connection;

    // Step 1: Insert book into books table
    $stmt = $connection->prepare("INSERT INTO books (title, author, description, pdf_content, image_content, publish_date) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $connection->error);
    }

    $stmt->bind_param("ssssss", $title, $author, $description, $pdf_content, $image_content, $publish_date);

    if (!$stmt->execute()) {
        echo "Error inserting book: " . $stmt->error;
        return null;
    }

    // Step 2: Get the ID of the inserted book
    $bookId = $stmt->insert_id;
    $stmt->close();

    // Step 3: Link the book to the user in userownbooks
    $linkStmt = $connection->prepare("INSERT INTO userownbooks (user_id, book_id) VALUES (?, ?)");
    if (!$linkStmt) {
        die("Prepare failed (userownbooks): " . $connection->error);
    }

    $linkStmt->bind_param("ii", $userId, $bookId);

    if (!$linkStmt->execute()) {
        echo "Error linking book to user: " . $linkStmt->error;
        return null;
    }

    $linkStmt->close();

    // Done: return book object
    $book = new Book($title, $author, $description, $userId, $publish_date, date('Y-m-d H:i:s'), $image_content, $pdf_content);
    return $book;
}

?>