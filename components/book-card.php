<?php
/**
 * Book Card Component
 * 
 * This component displays a single book as a card with all relevant information
 * and action buttons based on user authentication and ownership.
 * 
 * @param array $book Book data from database
 * @param PDO $pdo Database connection for additional queries
 */

// Ensure required variables are available
if (!isset($book) || !isset($pdo)) {
    return;
}

// Check if book is in user's favorites
$is_favorite = false;
if (is_logged_in()) {
    try {
        error_log("Checking favorite status for Book ID: {$book['Book_ID']}, User ID: {$_SESSION['user_id']}");
        $stmt = $pdo->prepare('SELECT * FROM bookshelf_favorites WHERE U_ID = ? AND Book_ID = ?');
        $stmt->execute([$_SESSION['user_id'], $book['Book_ID']]);
        $favorite_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $is_favorite = ($favorite_result !== false);
        
        error_log("Favorite status for Book ID: {$book['Book_ID']}, User ID: {$_SESSION['user_id']} is: " . ($is_favorite ? 'Yes' : 'No'));
        if ($is_favorite) {
            error_log("Favorite record: " . json_encode($favorite_result));
        }
    } catch (PDOException $e) {
        error_log("Error checking favorite status: " . $e->getMessage());
        $is_favorite = false;
    }
}

// Check if user is the book owner
$is_owner = is_logged_in() && (int)$book['posted_by'] === (int)$_SESSION['user_id'];

// Get uploader name
$stmt = $pdo->prepare('SELECT name FROM bookshelf_users WHERE U_ID = ?');
$stmt->execute([$book['posted_by']]);
$uploader = $stmt->fetch();
$uploader_name = $uploader ? htmlspecialchars($uploader['name']) : 'Unknown';
?>

<div class="book-card" data-book-id="<?php echo $book['Book_ID']; ?>">
    <div class="book-cover">
        <?php if ($book['image_cover']): ?>
            <img src="data:image/jpeg;base64,<?php echo base64_encode($book['image_cover']); ?>" alt="<?php echo htmlspecialchars($book['Title']); ?> cover">
        <?php else: ?>
            <div class="no-cover">No Cover Available</div>
        <?php endif; ?>
    </div>
    
    <div class="book-details">
        <h3 class="book-title"><?php echo htmlspecialchars($book['Title']); ?></h3>
        <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
        
        <div class="book-meta">
            <span class="book-date">Published: <?php echo format_date($book['official_publish_date']); ?></span>
            <span class="book-uploader">Added by: <?php echo $uploader_name; ?></span>
        </div>
        
        <div class="book-description">
            <p><?php echo truncate_text(htmlspecialchars($book['description']), 150); ?></p>
            <?php if (strlen($book['description']) > 150): ?>
                <button class="read-more-btn" data-book-id="<?php echo $book['Book_ID']; ?>">Read More</button>
                <div class="full-description" id="full-desc-<?php echo $book['Book_ID']; ?>" style="display: none;">
                    <?php echo htmlspecialchars($book['description']); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="book-actions">
            <?php if ($book['PDF_cover']): ?>
                <a href="download-pdf.php?id=<?php echo $book['Book_ID']; ?>" class="btn btn-primary">Download PDF</a>
            <?php endif; ?>
            
            <?php if (is_logged_in()): ?>
                <?php if ($is_owner): ?>
                    <a href="edit-book.php?id=<?php echo $book['Book_ID']; ?>" class="btn btn-secondary">Edit</a>
                    <button class="btn btn-danger delete-book-btn" data-book-id="<?php echo $book['Book_ID']; ?>">Delete</button>
                <?php endif; ?>
                
                <button class="btn <?php echo $is_favorite ? 'btn-favorite active' : 'btn-favorite'; ?> toggle-favorite" 
                        data-book-id="<?php echo $book['Book_ID']; ?>"
                        data-action="<?php echo $is_favorite ? 'remove' : 'add'; ?>">
                    <?php echo $is_favorite ? 'Remove from Favorites' : 'Add to Favorites'; ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal for full description (will be shown/hidden via JavaScript) -->
<div class="modal" id="description-modal-<?php echo $book['Book_ID']; ?>">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3><?php echo htmlspecialchars($book['Title']); ?></h3>
        <p><?php echo htmlspecialchars($book['description']); ?></p>
    </div>
</div>
