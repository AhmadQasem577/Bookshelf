<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookshelf - <?php echo $page_title ?? 'Book Management System'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="index.php">Bookshelf</a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">All Books</a></li>
                    <?php if (is_logged_in()): ?>
                        <li><a href="my-books.php">My Books</a></li>
                        <li><a href="favorites.php">Favorites</a></li>
                        <li><a href="create-book.php">Add Book</a></li>
                        <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="signup.php">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <main class="site-content">
        <div class="container">
            <?php
            // Display error messages
            if (isset($_SESSION['error_message'])) {
                echo display_error($_SESSION['error_message']);
                unset($_SESSION['error_message']);
            }
            
            // Display success messages
            if (isset($_SESSION['success_message'])) {
                echo display_success($_SESSION['success_message']);
                unset($_SESSION['success_message']);
            }
            ?>
