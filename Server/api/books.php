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


    require_once __DIR__ . '/../controllers/getBooks.controller.php';

    $input  = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? '';

    switch ($action) {
      

        ///////////////// LIST ALL BOOKS \\\\\\\\\\\\\\\\\\\\\
        case 'listBooks':
            $books = ListBooks();
            $out = array_map(fn($b)=>[
                'title'       => $b->getTitle(),
                'author'      => $b->getAuthor(),
                'description' => $b->getDescription(),
                'publisher'   => $b->getPublisher(),
                'publishDate' => $b->getPublishDate(),
                'postDate'    => $b->getPostDate()
            ], $books);

            echo json_encode(['status'=>'success','books'=>$out]);
            break;

        ///////////////// SEARCH BOOKS \\\\\\\\\\\\\\\\\\\\\
        case 'searchBook':
            $q = $_GET['title'] ?? $input['title'] ?? '';
            if ($q === '') {
                http_response_code(400);
                echo json_encode(['error'=>'Search title required']);
                exit();
            }
            $books = searchBook($q);
            $out = array_map(fn($b)=>[
                'title'=>$b->getTitle(),
                'author'=>$b->getAuthor(),
                'description'=>$b->getDescription(),
                'publisher'=>$b->getPublisher(),
                'publishDate'=>$b->getPublishDate(),
                'postDate'=>$b->getPostDate()
            ], $books);

            echo json_encode(['status'=>'success','books'=>$out]);
            break;

        ///////////////// LIST BOOKS BY USER \\\\\\\\\\\\\\\\\\\\\
        case 'listBooksByUser':
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error'=>'Not authenticated']);
                exit();
            }
            $books = ListBooksByUser($_SESSION['user_id']);
            $out = array_map(fn($b)=>[
                'title'=>$b->getTitle(),
                'author'=>$b->getAuthor(),
                'description'=>$b->getDescription(),
                'publisher'=>$b->getPublisher(),
                'publishDate'=>$b->getPublishDate(),
                'postDate'=>$b->getPostDate()
            ], $books);

            echo json_encode(['status'=>'success','books'=>$out]);
            break;

        ///////////////// LIST FAVORITES \\\\\\\\\\\\\\\\\\\\\
        case 'listFavoriteBooks':
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error'=>'Not authenticated']);
                exit();
            }
            $books = ListFavoriteBooks($_SESSION['user_id']);
            $out = array_map(fn($b)=>[
                'title'=>$b->getTitle(),
                'author'=>$b->getAuthor(),
                'description'=>$b->getDescription(),
                'publisher'=>$b->getPublisher(),
                'publishDate'=>$b->getPublishDate(),
                'postDate'=>$b->getPostDate()
            ], $books);

            echo json_encode(['status'=>'success','books'=>$out]);
            break;

        ///////////////// INVALID ACTION \\\\\\\\\\\\\\\\\\\\\
        default:
            http_response_code(400);
            echo json_encode(['error'=>'Invalid action']);
            break;
    }
?>