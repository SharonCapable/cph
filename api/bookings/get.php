<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_ENV['APP_URL'] ?? 'http://localhost:3000'));
header('Access-Control-Allow-Credentials: true');

require_once '../../includes/config.php';
require_once '../../includes/auth.php';

try {
    // Require authentication
    $user = Auth::requireAuth();

    $bookingId = $_GET['id'] ?? null;

    if (!$bookingId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Booking ID is required'
        ]);
        exit;
    }

    // Get booking (ensure it belongs to the user)
    $booking = db()->fetchOne(
        'SELECT b.*, p.title as property_title, p.location as property_location
         FROM bookings b
         LEFT JOIN properties p ON b.property_id = p.id
         WHERE b.id = ? AND b.user_id = ?',
        [$bookingId, $user['id']]
    );

    if (!$booking) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Booking not found'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => $booking
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Authentication required'
    ]);
}
