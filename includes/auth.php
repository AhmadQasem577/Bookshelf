<?php
/**
 * Authentication Functions
 * 
 * This file contains functions for user authentication, registration,
 * and session management.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Register a new user
 * 
 * @param PDO $pdo Database connection
 * @param string $email User email
 * @param string $name User's full name
 * @param string $password User password (plaintext, will be hashed)
 * @return array [success, message]
 */
function register_user($pdo, $email, $name, $password) {
    // Validate inputs
    if (empty($email) || empty($name) || empty($password)) {
        return [false, 'All fields are required'];
    }
    
    if (!is_valid_email($email)) {
        return [false, 'Invalid email format'];
    }
    
    if (strlen($password) < 8) {
        return [false, 'Password must be at least 8 characters long'];
    }
    
    try {
        // Check if email already exists
        $stmt = $pdo->prepare('SELECT U_ID FROM bookshelf_users WHERE email = ?');
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            return [false, 'Email already registered'];
        }
        
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $pdo->prepare('INSERT INTO bookshelf_users (email, name, password) VALUES (?, ?, ?)');
        $result = $stmt->execute([$email, $name, $password_hash]);
        
        if ($result) {
            return [true, 'Registration successful! You can now log in.'];
        } else {
            return [false, 'Registration failed. Please try again.'];
        }
    } catch (PDOException $e) {
        $error_message = "Registration error: " . $e->getMessage();
        error_log($error_message);
        // Return the actual error message for debugging
        return [false, 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Authenticate a user
 * 
 * @param PDO $pdo Database connection
 * @param string $email User email
 * @param string $password User password (plaintext)
 * @param bool $remember Whether to set a remember-me cookie
 * @return array [success, message, user_id (if successful)]
 */
function login_user($pdo, $email, $password, $remember = false) {
    // Validate inputs
    if (empty($email) || empty($password)) {
        return [false, 'Email and password are required', null];
    }
    
    try {
        // Get user by email
        $stmt = $pdo->prepare('SELECT U_ID, email, name, password FROM bookshelf_users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return [false, 'Invalid email or password', null];
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            return [false, 'Invalid email or password', null];
        }
        
        // Set session variables
        $_SESSION['user_id'] = $user['U_ID'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        
        // Set remember-me cookie if requested
        if ($remember) {
            $token = generate_random_string(32);
            $expiry = time() + (30 * 24 * 60 * 60); // 30 days
            
            // Store token in cookie
            setcookie('remember_token', $token, $expiry, '/', '', false, true);
            
            // Store token in database (you would need to add a remember_token column to your users table)
            // This is a simplified example - in production, you'd want to store tokens in a separate table
            // with expiry dates and user IDs
            // $stmt = $pdo->prepare('UPDATE bookshelf_users SET remember_token = ? WHERE U_ID = ?');
            // $stmt->execute([$token, $user['U_ID']]);
        }
        
        return [true, 'Login successful', $user['U_ID']];
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return [false, 'An error occurred during login. Please try again later.', null];
    }
}

/**
 * Log out the current user
 * 
 * @return void
 */
function logout_user() {
    // Unset all session variables
    $_SESSION = [];
    
    // Delete the session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Remove remember-me cookie
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

/**
 * Check if a user is logged in via session or remember-me cookie
 * 
 * @param PDO $pdo Database connection
 * @return bool True if user is logged in, false otherwise
 */
function check_login($pdo) {
    // Already logged in via session
    if (isset($_SESSION['user_id'])) {
        return true;
    }
    
    // Check for remember-me cookie
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        
        // In a production environment, you would validate this token against your database
        // This is a simplified example - you'd need to implement token storage and validation
        // $stmt = $pdo->prepare('SELECT U_ID, email, name FROM bookshelf_users WHERE remember_token = ?');
        // $stmt->execute([$token]);
        // $user = $stmt->fetch();
        
        // if ($user) {
        //     $_SESSION['user_id'] = $user['U_ID'];
        //     $_SESSION['user_email'] = $user['email'];
        //     $_SESSION['user_name'] = $user['name'];
        //     return true;
        // }
    }
    
    return false;
}

/**
 * Require user to be logged in to access a page
 * 
 * @param string $redirect_url URL to redirect to if not logged in
 * @return void
 */
function require_login($redirect_url = 'login.php') {
    if (!is_logged_in()) {
        $_SESSION['error_message'] = 'You must be logged in to access that page';
        redirect($redirect_url);
    }
}

/**
 * Check if current user is the owner of a book
 * 
 * @param PDO $pdo Database connection
 * @param int $book_id Book ID
 * @return bool True if user is owner, false otherwise
 */
function is_book_owner($pdo, $book_id) {
    if (!is_logged_in()) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare('SELECT posted_by FROM bookshelf_books WHERE Book_ID = ?');
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();
        
        if (!$book) {
            return false;
        }
        
        return (int)$book['posted_by'] === (int)$_SESSION['user_id'];
    } catch (PDOException $e) {
        error_log("Book ownership check error: " . $e->getMessage());
        return false;
    }
}
