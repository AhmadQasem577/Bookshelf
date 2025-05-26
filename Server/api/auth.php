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

require_once __DIR__ . '/../controllers/login.controller.php';
require_once __DIR__ . '/../controllers/signup.controller.php';




$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

switch ($action) {
    ///////////////////login/////////////////////////////////////////////////////////////////////
    case 'login':
        if (!isset($input['email'], $input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password required']);
            exit();
        }
        $user = login($input['email'], $input['password']);
        if ($user) {
            $_SESSION['user_email'] = $user->getEmail();
            $_SESSION['user_name'] = $user->getName();
            echo json_encode(['status' => 'success', 'message' => 'Logged in', 'user' => [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'createdAt' => $user->getCreatedAt(),
            ]]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
        break;

    ///////////////////signup/////////////////////////////////////////////////////////////////////
    case 'signup':
        if (!isset($input['name'], $input['email'], $input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Name, email, and password required']);
            exit();
        }
        // Call your signup function from controller (you gotta implement this)
        $newUser = signup($input['name'], $input['email'], $input['password']);
        if ($newUser) {
            echo json_encode(['status' => 'success', 'message' => 'User registered']);
        } else {
            http_response_code(409);
            echo json_encode(['error' => 'User already exists or signup failed']);
        }
        break;

    ///////////////////logout/////////////////////////////////////////////////////////////////////
    case 'logout':
        session_unset();
        session_destroy();
        echo json_encode(['status' => 'success', 'message' => 'Logged out']);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>