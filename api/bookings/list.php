<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_ENV['APP_URL'] ?? 'http://localhost:3000'));
header('Access-Control-Allow-Credentials: true');

require_once '../../includes/config.php';
require_once '../../includes/auth.php';

try {
    // Require authentication
    $user = Auth::requireAuth();

    // Get bookings for the current user
    $bookings = db()->fetchAll(
        'SELECT b.*, p.title as property_title, p.location as property_location
         FROM bookings b
         LEFT JOIN properties p ON b.property_id = p.id
         WHERE b.user_id = ?
         ORDER BY b.created_at DESC',
        [$user['id']]
    );

    echo json_encode([
        'success' => true,
        'data' => $bookings
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Authentication required'
    ]);
}
