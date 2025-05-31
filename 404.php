<?php
/**
 * Bookshelf Management System - 404 Error Page
 * 
 * Displayed when a requested page is not found
 */

// Set page title
$page_title = 'Page Not Found';

// Include header
include 'components/header.php';
?>

<div class="error-container">
    <h1 class="error-title">404</h1>
    <h2>Page Not Found</h2>
    <p>The page you are looking for does not exist or has been moved.</p>
    <p><a href="index.php" class="btn btn-primary">Return to Homepage</a></p>
</div>

<style>
    .error-container {
        text-align: center;
        padding: 50px 20px;
        max-width: 600px;
        margin: 0 auto;
    }
    
    .error-title {
        font-size: 6rem;
        color: var(--primary-color);
        margin-bottom: 0;
    }
    
    .error-container h2 {
        margin-bottom: 20px;
    }
    
    .error-container p {
        margin-bottom: 30px;
    }
</style>

<?php
// Include footer
include 'components/footer.php';
?>
