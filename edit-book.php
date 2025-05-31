<?php
/**
 * Bookshelf Management System - Edit Book
 * 
 * Allows authenticated users to edit their own books
 */

// Set page title
$page_title = 'Edit Book';

// Include header
include 'components/header.php';

// Require user to be logged in
require_login();

// Check if book ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'No book specified for editing';
    redirect('index.php');
}

$book_id = (int)$_GET['id'];

// Check if user is the book owner
try {
    $stmt = $pdo->prepare('SELECT * FROM bookshelf_books WHERE Book_ID = ?');
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();
    
    if (!$book) {
        $_SESSION['error_message'] = 'Book not found';
        redirect('index.php');
    }
    
    if ((int)$book['posted_by'] !== (int)$_SESSION['user_id']) {
        $_SESSION['error_message'] = 'You can only edit your own books';
        redirect('index.php');
    }
} catch (PDOException $e) {
    error_log("Error checking book ownership: " . $e->getMessage());
    $_SESSION['error_message'] = 'An error occurred. Please try again later.';
    redirect('index.php');
}

// Process form submission
$errors = [];

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
    
    // Prepare update query and parameters
    $query = 'UPDATE bookshelf_books SET Title = ?, author = ?, description = ?, official_publish_date = ?';
    $params = [$title, $author, $description, $publish_date];
    
    // Check if new cover image is uploaded
    if (isset($_FILES['image_cover']) && $_FILES['image_cover']['size'] > 0) {
        list($is_valid, $message) = validate_image_upload($_FILES['image_cover']);
        
        if (!$is_valid) {
            $errors[] = $message;
        } else {
            // Read image data and add to query
            $image_data = file_get_contents($_FILES['image_cover']['tmp_name']);
            $query .= ', image_cover = ?';
            $params[] = $image_data;
        }
    }
    
    // Check if new PDF file is uploaded
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['size'] > 0) {
        list($is_valid, $message) = validate_pdf_upload($_FILES['pdf_file']);
        
        if (!$is_valid) {
            $errors[] = $message;
        } else {
            // Read PDF data and add to query
            $pdf_data = file_get_contents($_FILES['pdf_file']['tmp_name']);
            $query .= ', PDF_cover = ?';
            $params[] = $pdf_data;
        }
    }
    
    // Complete query
    $query .= ' WHERE Book_ID = ?';
    $params[] = $book_id;
    
    // If no errors, update book in database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare($query);
            $result = $stmt->execute($params);
            
            if ($result) {
                $_SESSION['success_message'] = 'Book updated successfully!';
                redirect('my-books.php');
            } else {
                $errors[] = 'Failed to update book. Please try again.';
            }
        } catch (PDOException $e) {
            error_log("Error updating book: " . $e->getMessage());
            $errors[] = 'An error occurred while updating the book. Please try again later.';
        }
    }
}
?>

<div class="form-container">
    <h1 class="page-title">Edit Book</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="edit-book.php?id=<?php echo $book_id; ?>" enctype="multipart/form-data" class="needs-validation">
        <div class="form-group">
            <label for="title" class="form-label">Book Title</label>
            <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($book['Title']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="author" class="form-label">Author</label>
            <input type="text" id="author" name="author" class="form-control" value="<?php echo htmlspecialchars($book['author']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($book['description']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="publish_date" class="form-label">Publication Date</label>
            <input type="date" id="publish_date" name="publish_date" class="form-control" value="<?php echo htmlspecialchars($book['official_publish_date']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="image_cover" class="form-label">Cover Image</label>
            <?php if ($book['image_cover']): ?>
                <div class="current-image">
                    <p>Current cover image:</p>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($book['image_cover']); ?>" alt="Current cover" style="max-width: 200px;">
                </div>
            <?php endif; ?>
            <input type="file" id="image_cover" name="image_cover" class="form-control" accept="image/jpeg,image/png,image/gif">
            <small class="form-text">Leave empty to keep current image. Accepted formats: JPG, PNG, GIF. Max size: 2MB.</small>
            <div id="image-preview" class="mt-2" style="display: none;"></div>
        </div>
        
        <div class="form-group">
            <label for="pdf_file" class="form-label">PDF File</label>
            <?php if ($book['PDF_cover']): ?>
                <div class="current-pdf">
                    <p>Current PDF file is available.</p>
                </div>
            <?php endif; ?>
            <input type="file" id="pdf_file" name="pdf_file" class="form-control" accept="application/pdf">
            <small class="form-text">Leave empty to keep current PDF. Only PDF format accepted. Max size: 10MB.</small>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update Book</button>
            <a href="my-books.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
// Include footer
include 'components/footer.php';
?>
