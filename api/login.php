<?php
// API endpoint for admin login & session check
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/auth.php';

$action = $_GET['action'] ?? '';

// Check session
if ($action === 'check') {
    if (!empty($_SESSION['admin_user'])) {
        echo json_encode([
            'success' => true,
            'user' => $_SESSION['admin_user']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'authenticated' => false
        ]);
    }
    exit;
}

// Logout
if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    $username = trim($data['username'] ?? '');
    $password = trim($data['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Please enter username and password.'
        ]);
        exit;
    }
    
    $user = verifyAdminLogin($username, $password);
    
    if ($user) {
        $_SESSION['admin_user'] = [
            'id' => $user['id'] ?? 1,
            'username' => $user['username'],
            'role' => $user['role'] ?? 'admin',
            'login_time' => time()
        ];
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => $_SESSION['admin_user']
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid email or password.'
        ]);
    }
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Invalid request']);
