<?php
/**
 * Bookshelf Management System - Database Setup
 * 
 * This script creates the necessary database and tables for the Bookshelf Management System.
 * It should be run once during initial setup.
 */

// Set page title
$page_title = 'Database Setup';

// Start output buffering to prevent header issues
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookshelf - Database Setup</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #212529;
            background-color: #f5f7fa;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #4a6fa5;
            margin-bottom: 20px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.5rem 1rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 4px;
            transition: all 0.3s ease;
            cursor: pointer;
            color: white;
            background-color: #4a6fa5;
            border-color: #4a6fa5;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #166088;
            border-color: #166088;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bookshelf Management System - Database Setup</h1>
        
        <?php
        // Database credentials - same as in config.php
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'bookshelf');
        define('DB_USER', 'root');
        define('DB_PASS', '');
        define('DB_CHARSET', 'utf8mb4');

        // Function to display error message
        function display_error($message) {
            return '<div class="error-message">' . $message . '</div>';
        }

        // Function to display success message
        function display_success($message) {
            return '<div class="success-message">' . $message . '</div>';
        }

        // Check if setup has already been run
        $setup_complete = false;
        try {
            $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if database exists
            $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
            if ($stmt->rowCount() > 0) {
                // Check if tables exist
                $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
                $stmt = $pdo->query("SHOW TABLES LIKE 'bookshelf_users'");
                if ($stmt->rowCount() > 0) {
                    $setup_complete = true;
                }
            }
        } catch (PDOException $e) {
            echo display_error("Connection failed: " . $e->getMessage());
        }

        // Process setup if not already completed
        if (!$setup_complete && isset($_POST['setup'])) {
            try {
                // Create connection without database selected
                $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
                $pdo = new PDO($dsn, DB_USER, DB_PASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Create database if not exists
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                echo display_success("Database created successfully");
                
                // Select database
                $pdo->exec("USE `" . DB_NAME . "`");
                
                // Create users table
                $pdo->exec("CREATE TABLE IF NOT EXISTS `bookshelf_users` (
                    `U_ID` int(11) NOT NULL AUTO_INCREMENT,
                    `email` varchar(255) NOT NULL,
                    `name` varchar(255) NOT NULL,
                    `password` varchar(255) NOT NULL,
                    PRIMARY KEY (`U_ID`),
                    UNIQUE KEY `email` (`email`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                echo display_success("Users table created successfully");
                
                // Create books table
                $pdo->exec("CREATE TABLE IF NOT EXISTS `bookshelf_books` (
                    `Book_ID` int(11) NOT NULL AUTO_INCREMENT,
                    `Title` varchar(255) NOT NULL,
                    `author` varchar(255) NOT NULL,
                    `description` text NOT NULL,
                    `image_cover` longblob DEFAULT NULL,
                    `PDF_cover` longblob DEFAULT NULL,
                    `official_publish_date` date NOT NULL,
                    `posted_by` int(11) NOT NULL,
                    PRIMARY KEY (`Book_ID`),
                    KEY `posted_by` (`posted_by`),
                    CONSTRAINT `fk_books_users` FOREIGN KEY (`posted_by`) REFERENCES `bookshelf_users` (`U_ID`) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                echo display_success("Books table created successfully");
                
                // Create favorites table
                $pdo->exec("CREATE TABLE IF NOT EXISTS `bookshelf_favorites` (
                    `U_ID` int(11) NOT NULL,
                    `Book_ID` int(11) NOT NULL,
                    PRIMARY KEY (`U_ID`,`Book_ID`),
                    KEY `Book_ID` (`Book_ID`),
                    CONSTRAINT `fk_favorites_users` FOREIGN KEY (`U_ID`) REFERENCES `bookshelf_users` (`U_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
                    CONSTRAINT `fk_favorites_books` FOREIGN KEY (`Book_ID`) REFERENCES `bookshelf_books` (`Book_ID`) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                echo display_success("Favorites table created successfully");
                
                // Insert sample user (password: password123)
                $password_hash = password_hash('password123', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO `bookshelf_users` (`email`, `name`, `password`) VALUES (?, ?, ?)");
                $stmt->execute(['user@example.com', 'John Doe', $password_hash]);
                echo display_success("Sample user created successfully");
                
                // Insert sample books
                $stmt = $pdo->prepare("
                    INSERT INTO `bookshelf_books` (`Title`, `author`, `description`, `official_publish_date`, `posted_by`) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    'The Great Gatsby', 
                    'F. Scott Fitzgerald', 
                    'The Great Gatsby is a 1925 novel by American writer F. Scott Fitzgerald. Set in the Jazz Age on Long Island, near New York City, the novel depicts first-person narrator Nick Carraway\'s interactions with mysterious millionaire Jay Gatsby and Gatsby\'s obsession to reunite with his former lover, Daisy Buchanan.', 
                    '1925-04-10', 
                    1
                ]);
                
                $stmt->execute([
                    'To Kill a Mockingbird', 
                    'Harper Lee', 
                    'To Kill a Mockingbird is a novel by the American author Harper Lee. It was published in 1960 and was instantly successful. In the United States, it is widely read in high schools and middle schools. To Kill a Mockingbird has become a classic of modern American literature, winning the Pulitzer Prize.', 
                    '1960-07-11', 
                    1
                ]);
                
                $stmt->execute([
                    '1984', 
                    'George Orwell', 
                    '1984 is a dystopian novel by English novelist George Orwell. It was published on 8 June 1949 by Secker & Warburg as Orwell\'s ninth and final book completed in his lifetime. Thematically, 1984 centres on the consequences of totalitarianism, mass surveillance, and repressive regimentation of persons and behaviours within society.', 
                    '1949-06-08', 
                    1
                ]);
                echo display_success("Sample books created successfully");
                
                // Add sample favorites
                $stmt = $pdo->prepare("INSERT INTO `bookshelf_favorites` (`U_ID`, `Book_ID`) VALUES (?, ?)");
                $stmt->execute([1, 1]);
                $stmt->execute([1, 2]);
                echo display_success("Sample favorites created successfully");
                
                $setup_complete = true;
                
                // Clear any existing sessions to prevent foreign key constraint violations
                // when users with old session IDs try to add books
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION = [];
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
                    session_destroy();
                    echo display_success("Existing sessions cleared to prevent foreign key constraint issues.");
                }
                
                echo display_success("<strong>Setup completed successfully!</strong> You can now <a href='index.php'>visit the homepage</a> and log in with the sample user account.");
            } catch (PDOException $e) {
                echo display_error("Setup error: " . $e->getMessage());
            }
        }
        ?>
        
        <?php if (!$setup_complete): ?>
            <p>This script will set up the database for the Bookshelf Management System. It will:</p>
            <ol>
                <li>Create a database named <code>bookshelf_db</code></li>
                <li>Create the necessary tables (users, books, favorites)</li>
                <li>Add a sample user and books for testing</li>
            </ol>
            
            <p><strong>Database Configuration:</strong></p>
            <pre>
Host: <?php echo DB_HOST; ?>
Database Name: <?php echo DB_NAME; ?>
Username: <?php echo DB_USER; ?>
Password: <?php echo DB_PASS ? '********' : '[empty]'; ?>
            </pre>
            
            <p>If you need to change these settings, please edit the <code>includes/config.php</code> file before running this setup.</p>
            
            <form method="POST" action="setup.php">
                <p>
                    <button type="submit" name="setup" class="btn">Run Database Setup</button>
                </p>
            </form>
        <?php else: ?>
            <p>The database setup has already been completed.</p>
            <p><a href="index.php" class="btn">Go to Homepage</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
ob_end_flush();
?>
