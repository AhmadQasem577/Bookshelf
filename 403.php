<?php
/**
 * Bookshelf Management System - 403 Error Page
 * 
 * Displayed when a user attempts to access a forbidden resource
 */

// Set page title
$page_title = 'Access Denied';

// Include header
include 'components/header.php';
?>

<div class="error-container">
    <h1 class="error-title">403</h1>
    <h2>Access Denied</h2>
    <p>You don't have permission to access this resource.</p>
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
        color: var(--danger-color);
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
