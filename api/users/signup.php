<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_ENV['APP_URL'] ?? 'http://localhost:3000'));
header('Access-Control-Allow-Credentials: true');

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    exit;
}

try {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullName = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $role = sanitize($_POST['role'] ?? 'guest');

    // Validate required fields
    if (empty($email) || empty($password) || empty($fullName) || empty($phone)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'All fields are required'
        ]);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid email format'
        ]);
        exit;
    }

    // Check if email already exists
    $existingUser = db()->fetchOne('SELECT id FROM users WHERE email = ?', [$email]);
    if ($existingUser) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'error' => 'Email already registered'
        ]);
        exit;
    }

    // Create user
    $userId = Auth::generateId();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    db()->query(
        'INSERT INTO users (id, email, password, full_name, phone, role, status, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, "active", NOW(), NOW())',
        [$userId, $email, $hashedPassword, $fullName, $phone, $role]
    );

    // Fetch the created user
    $user = db()->fetchOne('SELECT id, email, full_name, phone, role, status, created_at FROM users WHERE id = ?', [$userId]);

    // Log them in
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_role'] = $role;

    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully',
        'data' => [
            'user' => $user
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Signup failed: ' . $e->getMessage()
    ]);
}
