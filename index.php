<?php
/**
 * Bookshelf Management System - Homepage
 * 
 * Displays all books in the system with pagination
 */

// Set page title
$page_title = 'All Books';

// Include header
include 'components/header.php';

// Set up pagination
$books_per_page = 12;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); // Ensure page is at least 1
$offset = ($current_page - 1) * $books_per_page;

// Get total book count
try {
    $count_stmt = $pdo->query('SELECT COUNT(*) FROM bookshelf_books');
    $total_books = $count_stmt->fetchColumn();
    $total_pages = ceil($total_books / $books_per_page);
    
    // Fetch books for current page
    $stmt = $pdo->prepare('
        SELECT * FROM bookshelf_books 
        ORDER BY official_publish_date DESC 
        LIMIT :limit OFFSET :offset
    ');
    $stmt->bindParam(':limit', $books_per_page, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $books = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching books: " . $e->getMessage());
    $_SESSION['error_message'] = 'An error occurred while retrieving books. Please try again later.';
    $books = [];
    $total_pages = 0;
}
?>

<h1 class="page-title">All Books</h1>

<?php if (empty($books)): ?>
    <div class="no-books-message">
        <p>No books found in the library.</p>
        <?php if (is_logged_in()): ?>
            <p><a href="create-book.php" class="btn btn-primary">Add Your First Book</a></p>
        <?php else: ?>
            <p><a href="login.php" class="btn btn-primary">Login to Add Books</a></p>
        <?php endif; ?>
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
