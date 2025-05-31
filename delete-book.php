<?php
/**
 * Bookshelf Management System - Delete Book
 * 
 * Allows authenticated users to delete their own books
 */

// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require user to be logged in
require_login();

// Check if book ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'No book specified for deletion';
    redirect('index.php');
}

$book_id = (int)$_GET['id'];

// Check if user is the book owner
try {
    $stmt = $pdo->prepare('SELECT posted_by FROM bookshelf_books WHERE Book_ID = ?');
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();
    
    if (!$book) {
        $_SESSION['error_message'] = 'Book not found';
        redirect('index.php');
    }
    
    if ((int)$book['posted_by'] !== (int)$_SESSION['user_id']) {
        $_SESSION['error_message'] = 'You can only delete your own books';
        redirect('index.php');
    }
    
    // Delete book from favorites first (to maintain referential integrity)
    $stmt = $pdo->prepare('DELETE FROM bookshelf_favorites WHERE Book_ID = ?');
    $stmt->execute([$book_id]);
    
    // Delete the book
    $stmt = $pdo->prepare('DELETE FROM bookshelf_books WHERE Book_ID = ?');
    $result = $stmt->execute([$book_id]);
    
    if ($result) {
        $_SESSION['success_message'] = 'Book deleted successfully!';
    } else {
        $_SESSION['error_message'] = 'Failed to delete book. Please try again.';
    }
} catch (PDOException $e) {
    error_log("Error deleting book: " . $e->getMessage());
    $_SESSION['error_message'] = 'An error occurred while deleting the book. Please try again later.';
}

// Redirect back to my books page
redirect('my-books.php');
?>
