<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_ENV['APP_URL'] ?? 'http://localhost:3000'));
header('Access-Control-Allow-Credentials: true');

require_once '../../includes/config.php';
require_once '../../includes/auth.php';

try {
    // Check if user is logged in
    $user = Auth::user();

    if ($user) {
        // Remove sensitive data
        unset($user['password']);

        echo json_encode([
            'success' => true,
            'data' => $user
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Not authenticated'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch user: ' . $e->getMessage()
    ]);
}
