<?php
/**
 * Bookshelf Management System - My Books
 * 
 * Displays books uploaded by the current user
 */

// Set page title
$page_title = 'My Books';

// Include header
include 'components/header.php';

// Require user to be logged in
require_login();

// Set up pagination
$books_per_page = 12;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); // Ensure page is at least 1
$offset = ($current_page - 1) * $books_per_page;

// Debug information
error_log("My Books page accessed by user ID: {$_SESSION['user_id']}");
error_log("Pagination: page $current_page, offset $offset, books per page $books_per_page");

// First verify the user exists to prevent foreign key constraint violation
try {
    // Check if user exists in database
    $check_user = $pdo->prepare('SELECT U_ID FROM bookshelf_users WHERE U_ID = ?');
    $check_user->execute([$_SESSION['user_id']]);
    $user_exists = $check_user->fetch();
    
    if (!$user_exists) {
        error_log("Error: User ID {$_SESSION['user_id']} does not exist in the database");
        throw new Exception("Your user account appears to be invalid. Please log out and log in again.");
    }
    
    error_log("User verification successful. User ID {$_SESSION['user_id']} exists.");
    
    // Get total book count for current user
    $count_stmt = $pdo->prepare('SELECT COUNT(*) FROM bookshelf_books WHERE posted_by = ?');
    $count_stmt->execute([$_SESSION['user_id']]);
    $total_books = $count_stmt->fetchColumn();
    $total_pages = ceil($total_books / $books_per_page);
    
    error_log("Total books for user: $total_books, Total pages: $total_pages");
    
    // Fetch books for current page
    $stmt = $pdo->prepare('
        SELECT * FROM bookshelf_books 
        WHERE posted_by = :user_id
        ORDER BY official_publish_date DESC 
        LIMIT :limit OFFSET :offset
    ');
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(':limit', $books_per_page, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $books = $stmt->fetchAll();
    error_log("Retrieved " . count($books) . " books for page $current_page");
} catch (PDOException $e) {
    $error_message = "Error fetching user books: " . $e->getMessage() . " in file " . $e->getFile() . " on line " . $e->getLine();
    error_log($error_message);
    error_log("Stack trace: " . $e->getTraceAsString());
    
    $_SESSION['error_message'] = 'An error occurred while retrieving your books. Please try again later.';
    $books = [];
    $total_pages = 0;
    
    // Display detailed error for debugging
    echo "<div class='error-message' style='border: 2px solid red; padding: 10px; margin: 10px 0;'>";
    echo "<h3>Debug Information (PDO Exception):</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "</div>";
} catch (Exception $e) {
    $error_message = "General error fetching user books: " . $e->getMessage() . " in file " . $e->getFile() . " on line " . $e->getLine();
    error_log($error_message);
    error_log("Stack trace: " . $e->getTraceAsString());
    
    $_SESSION['error_message'] = $e->getMessage();
    $books = [];
    $total_pages = 0;
    
    // Display detailed error for debugging
    echo "<div class='error-message' style='border: 2px solid red; padding: 10px; margin: 10px 0;'>";
    echo "<h3>Debug Information (General Exception):</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "</div>";
}
?>

<h1 class="page-title">My Books</h1>

<?php if (empty($books)): ?>
    <div class="no-books-message">
        <p>You haven't added any books yet.</p>
        <p><a href="create-book.php" class="btn btn-primary">Add Your First Book</a></p>
    </div>
<?php else: ?>
    <div class="page-actions">
        <a href="create-book.php" class="btn btn-primary">Add New Book</a>
    </div>
    
    <div class="books-grid">
        <?php foreach ($books as $book): ?>
            <?php include 'components/book-card.php'; ?>
        <?php endforeach; ?>
    </div>
    
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($current_page > 1): ?>
                <a href="?page=<?php echo $current_page - 1; ?>" class="pagination-link">&laquo; Previous</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $current_page): ?>
                    <span class="pagination-link active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?php echo $current_page + 1; ?>" class="pagination-link">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
// Include footer
include 'components/footer.php';
?>
