<?php
/**
 * Database Configuration
 * 
 * This file contains the database connection parameters and establishes
 * a PDO connection to the MySQL database.
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'bookshelf');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Error reporting - Verbose for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// For debugging - log all errors to browser
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    echo "<div style='color:red; border:1px solid red; padding:5px; margin:5px;'>"; 
    echo "Error [$errno]: $errstr<br>";
    echo "File: $errfile:$errline<br>";
    echo "</div>";
    return true;
}
set_error_handler('custom_error_handler');

// Establish database connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Log error and display user-friendly message
    error_log("Connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
