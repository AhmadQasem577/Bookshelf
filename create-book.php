<?php
/**
 * Bookshelf Management System - Create Book
 * 
 * Allows authenticated users to add a new book to the system
 */

// Set page title
$page_title = 'Add New Book';

// Include header
include 'components/header.php';

// Require user to be logged in
require_login();

// Process form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = sanitize_input($_POST['title'] ?? '');
    $author = sanitize_input($_POST['author'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $publish_date = sanitize_input($_POST['publish_date'] ?? '');
    
    // Validate form data
    if (empty($title)) {
        $errors[] = 'Book title is required';
    }
    
    if (empty($author)) {
        $errors[] = 'Author name is required';
    }
    
    if (empty($description)) {
        $errors[] = 'Book description is required';
    }
    
    if (empty($publish_date)) {
        $errors[] = 'Publication date is required';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $publish_date)) {
        $errors[] = 'Invalid date format. Use YYYY-MM-DD';
    }
    
    // Validate cover image
    $image_data = null;
    if (isset($_FILES['image_cover']) && $_FILES['image_cover']['size'] > 0) {
        list($is_valid, $message) = validate_image_upload($_FILES['image_cover']);
        
        if (!$is_valid) {
            $errors[] = $message;
        } else {
            // Read image data
            $image_data = file_get_contents($_FILES['image_cover']['tmp_name']);
        }
    }
    
    // Validate PDF file
    $pdf_data = null;
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['size'] > 0) {
        list($is_valid, $message) = validate_pdf_upload($_FILES['pdf_file']);
        
        if (!$is_valid) {
            $errors[] = $message;
        } else {
            // Read PDF data
            $pdf_data = file_get_contents($_FILES['pdf_file']['tmp_name']);
        }
    }
    
    // If no errors, add book to database
    if (empty($errors)) {
        try {
            // Debug information
            error_log("Attempting to add book with title: " . $title);
            error_log("User ID: " . $_SESSION['user_id']);
            error_log("Image data size: " . ($image_data ? strlen($image_data) : 0) . " bytes");
            error_log("PDF data size: " . ($pdf_data ? strlen($pdf_data) : 0) . " bytes");
            
            // First verify the user exists to prevent foreign key constraint violation
            $check_user = $pdo->prepare('SELECT U_ID FROM bookshelf_users WHERE U_ID = ?');
            $check_user->execute([$_SESSION['user_id']]);
            $user_exists = $check_user->fetch();
            
            if (!$user_exists) {
                error_log("Error: User ID {$_SESSION['user_id']} does not exist in the database");
                throw new Exception("Your user account appears to be invalid. Please log out and log in again.");
            }
            
            error_log("User verification successful. User ID {$_SESSION['user_id']} exists.");
            
            $stmt = $pdo->prepare('
                INSERT INTO bookshelf_books 
                (Title, author, description, image_cover, PDF_cover, official_publish_date, posted_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');
            
            $result = $stmt->execute([
                $title,
                $author,
                $description,
                $image_data,
                $pdf_data,
                $publish_date,
                $_SESSION['user_id']
            ]);
            
            if ($result) {
                $_SESSION['success_message'] = 'Book added successfully!';
                redirect('index.php');
            } else {
                $error_info = $stmt->errorInfo();
                $errors[] = 'Failed to add book. Database error: ' . ($error_info[2] ?? 'Unknown error');
                error_log("Database error when adding book: " . json_encode($error_info));
            }
        } catch (PDOException $e) {
            $error_message = "Error adding book: " . $e->getMessage() . " in file " . $e->getFile() . " on line " . $e->getLine();
            error_log($error_message);
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Display detailed error for debugging
            $errors[] = 'Database error: ' . $e->getMessage();
            
            // Add detailed error information to the page for debugging
            echo "<div class='error-message' style='border: 2px solid red; padding: 10px; margin: 10px 0;'>";
            echo "<h3>Debug Information (PDO Exception):</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "<p>File: " . $e->getFile() . ":" . $e->getLine() . "</p>";
            echo "</div>";
        } catch (Exception $e) {
            $error_message = "General error adding book: " . $e->getMessage() . " in file " . $e->getFile() . " on line " . $e->getLine();
            error_log($error_message);
            error_log("Stack trace: " . $e->getTraceAsString());
            
            $errors[] = $e->getMessage();
            
            // Display the error on the page for debugging
            echo "<div class='error-message' style='border: 2px solid red; padding: 10px; margin: 10px 0;'>";
            echo "<h3>Debug Information (General Exception):</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "<p>File: " . $e->getFile() . ":" . $e->getLine() . "</p>";
            echo "</div>";
        }
    }
}
?>

<div class="form-container">
    <h1 class="page-title">Add New Book</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="create-book.php" enctype="multipart/form-data" class="needs-validation">
        <div class="form-group">
            <label for="title" class="form-label">Book Title</label>
            <input type="text" id="title" name="title" class="form-control" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="author" class="form-label">Author</label>
            <input type="text" id="author" name="author" class="form-control" value="<?php echo isset($author) ? htmlspecialchars($author) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="publish_date" class="form-label">Publication Date</label>
            <input type="date" id="publish_date" name="publish_date" class="form-control" value="<?php echo isset($publish_date) ? htmlspecialchars($publish_date) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="image_cover" class="form-label">Cover Image</label>
            <input type="file" id="image_cover" name="image_cover" class="form-control" accept="image/jpeg,image/png,image/gif">
            <small class="form-text">Accepted formats: JPG, PNG, GIF. Max size: 2MB.</small>
            <div id="image-preview" class="mt-2" style="display: none;"></div>
        </div>
        
        <div class="form-group">
            <label for="pdf_file" class="form-label">PDF File</label>
            <input type="file" id="pdf_file" name="pdf_file" class="form-control" accept="application/pdf">
            <small class="form-text">Only PDF format accepted. Max size: 10MB.</small>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">Add Book</button>
        </div>
    </form>
</div>

<?php
// Include footer
include 'components/footer.php';
?>
