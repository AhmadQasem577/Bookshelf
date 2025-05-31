<?php
/**
 * Utility Functions
 * 
 * This file contains helper functions used throughout the application.
 */

/**
 * Sanitize user input to prevent XSS attacks
 * 
 * @param string $data The input to sanitize
 * @return string Sanitized input
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 * 
 * @param string $email The email to validate
 * @return bool True if valid, false otherwise
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate a random string for tokens
 * 
 * @param int $length Length of the random string
 * @return string Random string
 */
function generate_random_string($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Redirect to a specified page
 * 
 * @param string $location The URL to redirect to
 * @return void
 */
function redirect($location) {
    header("Location: $location");
    exit;
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 * 
 * @return int|null User ID if logged in, null otherwise
 */
function get_current_user_id() {
    return is_logged_in() ? $_SESSION['user_id'] : null;
}

/**
 * Display error message
 * 
 * @param string $message Error message to display
 * @return string HTML for error message
 */
function display_error($message) {
    return '<div class="error-message">' . $message . '</div>';
}

/**
 * Display success message
 * 
 * @param string $message Success message to display
 * @return string HTML for success message
 */
function display_success($message) {
    return '<div class="success-message">' . $message . '</div>';
}

/**
 * Validate file upload for images
 * 
 * @param array $file The $_FILES array element
 * @param int $max_size Maximum file size in bytes
 * @return array [is_valid, message]
 */
function validate_image_upload($file, $max_size = 2097152) { // 2MB default
    // Debug information
    error_log("Validating image upload: " . json_encode($file));
    
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        $error_message = $upload_errors[$file['error']] ?? 'Unknown upload error';
        error_log("Image upload error: " . $error_message);
        return [false, $error_message];
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        $error_message = 'File is too large (maximum ' . ($max_size / 1024 / 1024) . 'MB). Current size: ' . round($file['size'] / 1024 / 1024, 2) . 'MB';
        error_log($error_message);
        return [false, $error_message];
    }
    
    // Check file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    error_log("Detected MIME type: " . $mime_type);
    
    if (!in_array($mime_type, $allowed_types)) {
        $error_message = 'Invalid file type: ' . $mime_type . '. Only JPG, PNG, and GIF images are allowed.';
        error_log($error_message);
        return [false, $error_message];
    }
    
    error_log("Image validation successful");
    return [true, 'File is valid'];
}

/**
 * Validate file upload for PDFs
 * 
 * @param array $file The $_FILES array element
 * @param int $max_size Maximum file size in bytes
 * @return array [is_valid, message]
 */
function validate_pdf_upload($file, $max_size = 80485760) { // 80MB default
    // Debug information
    error_log("Validating PDF upload: " . json_encode($file));
    
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        $error_message = $upload_errors[$file['error']] ?? 'Unknown upload error';
        error_log("PDF upload error: " . $error_message);
        return [false, $error_message];
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        $error_message = 'File is too large (maximum ' . ($max_size / 1024 / 1024) . 'MB). Current size: ' . round($file['size'] / 1024 / 1024, 2) . 'MB';
        error_log($error_message);
        return [false, $error_message];
    }
    
    // Check file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    error_log("Detected PDF MIME type: " . $mime_type);
    
    if ($mime_type !== 'application/pdf') {
        $error_message = 'Invalid file type: ' . $mime_type . '. Only PDF files are allowed.';
        error_log($error_message);
        return [false, $error_message];
    }
    
    error_log("PDF validation successful");
    return [true, 'File is valid'];
}

/**
 * Format date in a user-friendly way
 * 
 * @param string $date Date string in MySQL format
 * @return string Formatted date
 */
function format_date($date) {
    $timestamp = strtotime($date);
    return date('F j, Y', $timestamp);
}

/**
 * Truncate text to a specified length
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $append String to append if truncated
 * @return string Truncated text
 */
function truncate_text($text, $length = 100, $append = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    return $text . $append;
}
