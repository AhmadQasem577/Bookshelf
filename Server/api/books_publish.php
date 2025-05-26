<?php
   header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: https://localhost:5173');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400');
    header('Access-Control-Expose-Headers: Content-Length, X-JSON');

    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit();
    }

    require_once __DIR__ . '/../controllers/createBook.controller.php';
    
    $input  = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? '';


    switch($action)
    {
          ///////////////// CREATE BOOK \\\\\\\\\\\\\\\\\\\\\

        case 'createBook':
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Not authenticated']);
                exit();
            }

            $required = ['title','author','description','publish_date'];
            foreach ($required as $f) {
                if (empty($_POST[$f])) {
                    http_response_code(400);
                    echo json_encode(['error'=>"Missing field: $f"]);
                    exit();
                }
            }

            $title        = $_POST['title'];
            $author       = $_POST['author'];
            $description  = $_POST['description'];
            $publish_date = $_POST['publish_date'];
            $userId       = $_SESSION['user_id'];

            if (!isset($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['error'=>'PDF file is required and must upload successfully']);
                exit();
            }
            $pdf_content   = file_get_contents($_FILES['pdf']['tmp_name']);
            $image_content = (isset($_FILES['image']) && $_FILES['image']['error']===UPLOAD_ERR_OK)
                            ? file_get_contents($_FILES['image']['tmp_name'])
                            : null;

            $book = createBook(
                $title, $author, $description,
                $pdf_content, $image_content,
                $publish_date, $userId
            );

            if ($book) {
                echo json_encode(['status'=>'success','message'=>'Book created']);
            } else {
                http_response_code(500);
                echo json_encode(['error'=>'Failed to create book']);
            }
            break;

            ///////////////// EDIT BOOKS \\\\\\\\\\\\\\\\\\\\\

        case 'editBook':
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Not authenticated']);
                exit();
            }

            // Accept book_id from GET or JSON input
            $bookId = $_GET['book_id'] ?? ($input['book_id'] ?? null);
            if (!$bookId) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing book_id']);
                exit();
            }

            // Get other fields to update (optional)
            $title = $_POST['title'] ?? null;
            $author = $_POST['author'] ?? null;
            $description = $_POST['description'] ?? null;
            $publish_date = $_POST['publish_date'] ?? null;

            // Handle optional files (pdf and image)
            $pdf_content = null;
            if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
                $pdf_content = file_get_contents($_FILES['pdf']['tmp_name']);
            }

            $image_content = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_content = file_get_contents($_FILES['image']['tmp_name']);
            }

            require_once __DIR__ . '/../controllers/editBook.controller.php';

            $updated = editBook($bookId, $_SESSION['user_id'], $title, $author, $description, $pdf_content, $image_content, $publish_date);

            if ($updated) {
                echo json_encode(['status' => 'success', 'message' => 'Book updated successfully']);
            } else {
                http_response_code(403);
                echo json_encode(['error' => 'Failed to update book. Check ownership or input']);
            }
            break;


            ///////////////// DELETE BOOKS \\\\\\\\\\\\\\\\\\\\\

        case 'deleteBook':
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Not authenticated']);
                exit();
            }

            // Accept ID from GET or JSON input
            $bookId = $_GET['book_id'] ?? ($input['book_id'] ?? null);
            if (!$bookId) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing book_id']);
                exit();
            }

            require_once __DIR__ . '/../controllers/deleteBook.controller.php';
            $deleted = deleteBook($bookId, $_SESSION['user_id']);

            if ($deleted) {
                echo json_encode(['status' => 'success', 'message' => 'Book deleted']);
            } else {
                http_response_code(403);
                echo json_encode(['error' => 'You do not have permission to delete this book or it does not exist']);
            }
            break;


            ////////////////// DEFAULT \\\\\\\\\\\\\\\\\\\\\
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;  
    }
?>