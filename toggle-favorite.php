<?php
/**
 * Bookshelf Management System - Toggle Favorite
 * 
 * AJAX endpoint for adding/removing books from favorites
 */

// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Set content type to JSON
header('Content-Type: application/json');

// Debug information
error_log("Toggle favorite request received");
error_log("SESSION data: " . json_encode($_SESSION));
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in - no user_id in session");
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to manage favorites'
    ]);
    exit;
}

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get book ID from request
$raw_input = file_get_contents('php://input');
error_log("Raw input: " . $raw_input);

// Check if input is JSON or form data
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $data = json_decode($raw_input, true);
    error_log("Decoded JSON data: " . json_encode($data));
} else {
    // Handle form-encoded data as fallback
    $data = $_POST;
    error_log("Using POST data: " . json_encode($data));
}

if (!isset($data['book_id']) || !is_numeric($data['book_id']) || !isset($data['action'])) {
    error_log("Invalid book ID or action in request: " . json_encode($data));
    echo json_encode([
        'success' => false,
        'message' => 'Invalid book ID or action'
    ]);
    exit;
}

$book_id = (int) $data['book_id'];
$action = $data['action'];
$user_id = $_SESSION['user_id'];

error_log("Processing toggle favorite - Book ID: $book_id, User ID: $user_id, Action: $action");

// Validate action
if ($action !== 'add' && $action !== 'remove') {
    error_log("Invalid action: $action");
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
    exit;
}

try {
    // Check if book exists
    $stmt = $pdo->prepare('SELECT Book_ID FROM bookshelf_books WHERE Book_ID = ?');
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();
    
    if (!$book) {
        error_log("Book not found with ID: $book_id");
        echo json_encode([
            'success' => false,
            'message' => 'Book not found'
        ]);
        exit;
    }
    
    error_log("Book found with ID: $book_id");

    if ($action === 'add') {
        // Check if already in favorites
        $stmt = $pdo->prepare('SELECT * FROM bookshelf_favorites WHERE U_ID = ? AND Book_ID = ?');
        $stmt->execute([$user_id, $book_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Book is already in your favorites'
            ]);
            exit;
        }
        
        // Add to favorites
        $stmt = $pdo->prepare('INSERT INTO bookshelf_favorites (U_ID, Book_ID) VALUES (?, ?)');
        $result = $stmt->execute([$user_id, $book_id]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Book added to favorites'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add book to favorites'
            ]);
        }
    } else {
        // Remove from favorites
        $stmt = $pdo->prepare('DELETE FROM bookshelf_favorites WHERE U_ID = ? AND Book_ID = ?');
        $result = $stmt->execute([$user_id, $book_id]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Book removed from favorites'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to remove book from favorites'
            ]);
        }
    }
} catch (PDOException $e) {
    $error_message = "Error managing favorites: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
    error_log($error_message);
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // In development environment, return detailed error
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'debug_info' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $e->getCode()
        ]
    ]);
}
?>
