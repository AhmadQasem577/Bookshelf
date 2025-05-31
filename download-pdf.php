<?php
/**
 * Bookshelf Management System - Download PDF
 * 
 * Allows users to download PDF files for books
 */

// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if book ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    error_log("PDF download error: No book ID specified in request");
    $_SESSION['error_message'] = 'No book specified for download';
    redirect('index.php');
}

$book_id = (int)$_GET['id'];
error_log("Attempting to download PDF for Book ID: $book_id");

try {
    // Get book details
    $stmt = $pdo->prepare('SELECT Title, PDF_cover FROM bookshelf_books WHERE Book_ID = ?');
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Book query result: " . ($book ? "Found" : "Not found"));
    if ($book) {
        error_log("Book title: " . $book['Title']);
        error_log("PDF data size: " . ($book['PDF_cover'] ? strlen($book['PDF_cover']) . " bytes" : "No PDF data"));
    }
    
    if (!$book) {
        error_log("Book not found with ID: $book_id");
        $_SESSION['error_message'] = 'Book not found';
        redirect('index.php');
    }
    
    if (!$book['PDF_cover']) {
        error_log("No PDF data available for book ID: $book_id, Title: {$book['Title']}");
        $_SESSION['error_message'] = 'No PDF file available for this book';
        redirect('index.php');
    }
    
    // Generate safe filename
    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $book['Title']) . '.pdf';
    
    // Set headers for file download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($book['PDF_cover']));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Output PDF data
    echo $book['PDF_cover'];
    exit;
} catch (PDOException $e) {
    $error_message = "Error downloading PDF for book ID $book_id: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
    error_log($error_message);
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Store detailed error in session for debugging
    $_SESSION['error_message'] = 'An error occurred while downloading the PDF. Please try again later.';
    $_SESSION['debug_error'] = $error_message;
    
    redirect('index.php');
}
