<?php
/**
 * Bookshelf Management System - Favorites
 * 
 * Displays books marked as favorites by the current user
 */

// Set page title
$page_title = 'My Favorites';

// Include header
include 'components/header.php';

// Require user to be logged in
require_login();

// Set up pagination
$items_per_page = 12; // Consistent variable naming
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); // Ensure page is at least 1
$offset = ($current_page - 1) * $items_per_page;

// Get total favorite book count for current user
try {
    // Debug - Display session information
    error_log("SESSION data in favorites.php: " . json_encode($_SESSION));
    
    if (!isset($_SESSION['user_id'])) {
        error_log("ERROR: user_id not set in session");
        echo "<div class='error-message'>Session error: User ID not found. Please log in again.</div>";
    } else {
        error_log("Processing favorites for user ID: " . $_SESSION['user_id']);
    }
    
    // Count total favorites for this user
    $count_stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM bookshelf_favorites f 
        JOIN bookshelf_books b ON f.Book_ID = b.Book_ID
        WHERE f.U_ID = ?
    ");
    $count_stmt->execute([$_SESSION['user_id']]);
    $total_books = $count_stmt->fetch()['total'];
    
    // Debug
    error_log("User ID: " . $_SESSION['user_id'] . ", Total favorites: " . $total_books);
    
    // Calculate pagination
    $total_pages = ceil($total_books / $items_per_page);
    $page = max(1, min($current_page, $total_pages));
    $offset = ($page - 1) * $items_per_page;
    
    // Get favorites for current page
    $stmt = $pdo->prepare("
        SELECT b.* 
        FROM bookshelf_favorites f 
        JOIN bookshelf_books b ON f.Book_ID = b.Book_ID 
        WHERE f.U_ID = ? 
        ORDER BY b.official_publish_date DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$_SESSION['user_id'], $items_per_page, $offset]);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug - detailed information about fetched favorites
    error_log("Fetched favorites count: " . count($books));
    if (count($books) > 0) {
        foreach ($books as $index => $book) {
            error_log("Favorite book #{$index}: ID={$book['Book_ID']}, Title={$book['Title']}");
        }
    } else {
        // Check if there are any favorites at all without pagination
        $check_stmt = $pdo->prepare("SELECT Book_ID FROM bookshelf_favorites WHERE U_ID = ?");
        $check_stmt->execute([$_SESSION['user_id']]);
        $all_favorites = $check_stmt->fetchAll();
        error_log("Total favorites without pagination: " . count($all_favorites));
        
        // Check if the books actually exist
        if (count($all_favorites) > 0) {
            $book_ids = array_column($all_favorites, 'Book_ID');
            $placeholders = implode(',', array_fill(0, count($book_ids), '?'));
            $check_books_stmt = $pdo->prepare("SELECT Book_ID, Title FROM bookshelf_books WHERE Book_ID IN ($placeholders)");
            $check_books_stmt->execute($book_ids);
            $existing_books = $check_books_stmt->fetchAll();
            error_log("Found books from favorites: " . json_encode($existing_books));
        }
    }
    
    // Debug SQL query
    $debug_query = "SELECT b.* FROM bookshelf_favorites f JOIN bookshelf_books b ON f.Book_ID = b.Book_ID WHERE f.U_ID = {$_SESSION['user_id']} ORDER BY b.official_publish_date DESC LIMIT $items_per_page OFFSET $offset";
    error_log("SQL Query: " . $debug_query);
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
    error_log("Error fetching favorite books: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Display detailed error for debugging
    echo "<div class='error-message'>";
    echo "<h3>Database Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "</div>";
    $books = [];
    $total_pages = 0;
}
?>

<h1 class="page-title">My Favorite Books</h1>

<?php if (empty($books)): ?>
    <div class="no-books-message">
        <p>You don't have any favorite books yet.</p>
        <p><a href="index.php" class="btn btn-primary">Browse All Books</a></p>
    </div>
<?php else: ?>
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
