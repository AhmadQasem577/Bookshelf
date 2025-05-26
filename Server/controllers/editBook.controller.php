<?php
// Database connection configuration
require_once __DIR__ . '/../configuration/databaseConnection.php';

$database = new DatabaseConnection('localhost', 'root', '', 'bookshelf');
$connection = $database->connect();

function editBook($bookId, $userId, $title, $author, $description, $pdf_content = null, $image_content = null, $publish_date = null) {
    global $connection;

    // Check if user owns the book
    $checkStmt = $connection->prepare("SELECT * FROM userownbooks WHERE book_id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $bookId, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        return false; // no ownership
    }

    // Build dynamic update query depending on which fields are provided
    $fields = [];
    $types = '';
    $params = [];

    if ($title !== null) {
        $fields[] = "title = ?";
        $types .= 's';
        $params[] = $title;
    }
    if ($author !== null) {
        $fields[] = "author = ?";
        $types .= 's';
        $params[] = $author;
    }
    if ($description !== null) {
        $fields[] = "description = ?";
        $types .= 's';
        $params[] = $description;
    }
    if ($pdf_content !== null) {
        $fields[] = "pdf_content = ?";
        $types .= 'b'; // blob
        $params[] = $pdf_content;
    }
    if ($image_content !== null) {
        $fields[] = "image_content = ?";
        $types .= 'b'; // blob
        $params[] = $image_content;
    }
    if ($publish_date !== null) {
        $fields[] = "publish_date = ?";
        $types .= 's';
        $params[] = $publish_date;
    }

    if (empty($fields)) {
        return false; // nothing to update
    }

    $sql = "UPDATE books SET " . implode(", ", $fields) . " WHERE book_id = ?";
    $types .= 'i';
    $params[] = $bookId;

    $stmt = $connection->prepare($sql);
    if (!$stmt) {
        return false;
    }

    // Bind params dynamically
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

?>