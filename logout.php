<?php
/**
 * Bookshelf Management System - Logout
 * 
 * Logs out the current user and redirects to the login page
 */

// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Log out the user
logout_user();

// Redirect to login page with success message
$_SESSION['success_message'] = 'You have been successfully logged out.';
redirect('login.php');
?>
