<?php
/**
 * Bookshelf Management System - User Login
 * 
 * Allows existing users to log in to their account
 */

// Set page title
$page_title = 'Login';

// Include header
include 'components/header.php';

// Process form submission
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate form data
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    // If no errors, attempt login
    if (empty($errors)) {
        list($success, $message, $user_id) = login_user($pdo, $email, $password, $remember);
        
        if ($success) {
            $_SESSION['success_message'] = $message;
            redirect('index.php');
        } else {
            $errors[] = $message;
        }
    }
}
?>

<div class="form-container">
    <h1 class="page-title">Log In</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="login.php" class="needs-validation">
        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <div class="form-check">
                <input type="checkbox" id="remember" name="remember" class="form-check-input">
                <label for="remember" class="form-check-label">Remember me</label>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">Log In</button>
        </div>
    </form>
    
    <div class="form-footer">
        <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
    </div>
</div>

<?php
// Include footer
include 'components/footer.php';
?>
