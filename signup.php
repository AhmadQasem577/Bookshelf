<?php
/**
 * Bookshelf Management System - User Registration
 * 
 * Allows new users to create an account
 */

// Set page title
$page_title = 'Sign Up';

// Include header
include 'components/header.php';

// Process form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = sanitize_input($_POST['email'] ?? '');
    $name = sanitize_input($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate form data
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!is_valid_email($email)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    // If no errors, register user
    if (empty($errors)) {
        list($success, $message) = register_user($pdo, $email, $name, $password);
        
        if ($success) {
            $_SESSION['success_message'] = $message;
            redirect('login.php');
        } else {
            $errors[] = $message;
        }
    }
}
?>

<div class="form-container">
    <h1 class="page-title">Create an Account</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="signup.php" class="needs-validation">
        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" required minlength="8">
            <small class="form-text">Password must be at least 8 characters long</small>
        </div>
        
        <div class="form-group">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="8">
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
        </div>
    </form>
    
    <div class="form-footer">
        <p>Already have an account? <a href="login.php">Log In</a></p>
    </div>
</div>

<?php
// Include footer
include 'components/footer.php';
?>
