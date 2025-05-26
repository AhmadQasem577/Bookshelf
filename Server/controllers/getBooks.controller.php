<?php
// Database connection configuration
require_once __DIR__ . '/../configuration/databaseConnection.php';
require_once __DIR__ . '/../models/book.model.php';

$database = new DatabaseConnection('localhost', 'root', '', 'bookshelf');
$connection = $database->connect();

function ListBooks() {
    global $connection;

    $stmt = $connection->prepare("SELECT * FROM books");
    if (!$stmt) die("Prepare failed: " . $connection->error);
    
    $stmt->execute();
    $result = $stmt->get_result();

    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = new Book(
            $row['title'],
            $row['author'],
            $row['description'],
            $row['publisher'], // publisher is actually user_id
            $row['publish_date'],
            $row['post_date'],
            $row['image_content'],
            $row['pdf_content']
        );
    }
    $stmt->close();
    return $books;
}

function searchBook($title) {
    global $connection;

    $stmt = $connection->prepare("SELECT * FROM books WHERE title LIKE ?");
    if (!$stmt) die("Prepare failed: " . $connection->error);

    $searchTerm = '%' . $title . '%';
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = new Book(
            $row['title'],
            $row['author'],
            $row['description'],
            $row['publisher'],
            $row['publish_date'],
            $row['post_date'],
            $row['image_content'],
            $row['pdf_content']
        );
    }
    $stmt->close();
    return $books;
}

function ListBooksByUser($userId) {
    global $connection;

    $sql = "
        SELECT 
            b.book_id,
            b.title,
            b.author,
            b.description,
            b.publisher,
            b.pdf_content,
            b.image_content,
            b.publish_date,
            b.post_date,
            ub.created_at AS added_to_library
        FROM userownbooks ub
        JOIN books b ON ub.book_id = b.book_id
        WHERE ub.user_id = ?
    ";
    $stmt = $connection->prepare($sql);
    if (!$stmt) die("Prepare failed: " . $connection->error);

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = new Book(
            $row['title'],
            $row['author'],
            $row['description'],
            $row['publisher'],
            $row['publish_date'],
            $row['post_date'],
            $row['image_content'],
            $row['pdf_content']
        );
    }
    $stmt->close();
    return $books;
}

function ListFavoriteBooks($userId) {
    global $connection;

    $sql = "
        SELECT 
            b.title,
            b.author,
            b.description,
            b.publisher,
            b.pdf_content,
            b.image_content,
            b.publish_date,
            b.post_date
        FROM userfavoritebooks uf
        JOIN books b ON uf.book_id = b.book_id
        WHERE uf.user_id = ?
    ";
    $stmt = $connection->prepare($sql);
    if (!$stmt) die("Prepare failed: " . $connection->error);

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = new Book(
            $row['title'],
            $row['author'],
            $row['description'],
            $row['publisher'],
            $row['publish_date'],
            $row['post_date'],
            $row['image_content'],
            $row['pdf_content']
        );
    }
    $stmt->close();
    return $books;
}

?>
