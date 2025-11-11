<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/mail.php';

// Require admin authentication
$user = Auth::requireRole('admin');
$isSuperAdmin = $user['role'] === 'super_admin';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/bookings.php');
    exit;
}

try {
    $bookingId = sanitize($_POST['booking_id'] ?? '');
    $action = sanitize($_POST['action'] ?? '');

    if (!$bookingId || !$action) {
        setFlash('error', 'Invalid request');
        header('Location: /admin/bookings.php');
        exit;
    }

    // Get booking details
    $booking = db()->fetchOne(
        'SELECT b.*, p.title as property_title, p.manager_id, u.email as guest_email
         FROM bookings b
         JOIN properties p ON b.property_id = p.id
         JOIN users u ON b.user_id = u.id
         WHERE b.id = ?',
        [$bookingId]
    );

    if (!$booking) {
        setFlash('error', 'Booking not found');
        header('Location: /admin/bookings.php');
        exit;
    }

    // Check permissions - property managers can only manage their own properties
    if (!$isSuperAdmin && $booking['manager_id'] !== $user['id']) {
        setFlash('error', 'You do not have permission to manage this booking');
        header('Location: /admin/bookings.php');
        exit;
    }

    // Handle different actions
    switch ($action) {
        case 'approve':
            // Update status to confirmed
            db()->query(
                'UPDATE bookings SET status = "confirmed", updated_at = NOW() WHERE id = ?',
                [$bookingId]
            );

            // Log activity
            $activityId = Auth::generateId();
            db()->query(
                'INSERT INTO activity_log (id, user_id, action, entity_type, entity_id, details, created_at)
                 VALUES (?, ?, "booking_approved", "booking", ?, ?, NOW())',
                [
                    $activityId,
                    $user['id'],
                    $bookingId,
                    json_encode(['property_id' => $booking['property_id']])
                ]
            );

            // TODO: Send confirmation email to guest
            // sendBookingApprovedEmail($booking);

            setFlash('success', 'Booking approved successfully! Guest will be notified.');
            break;

        case 'reject':
            // Update status to cancelled
            db()->query(
                'UPDATE bookings SET status = "cancelled", updated_at = NOW() WHERE id = ?',
                [$bookingId]
            );

            // Log activity
            $activityId = Auth::generateId();
            db()->query(
                'INSERT INTO activity_log (id, user_id, action, entity_type, entity_id, details, created_at)
                 VALUES (?, ?, "booking_rejected", "booking", ?, ?, NOW())',
                [
                    $activityId,
                    $user['id'],
                    $bookingId,
                    json_encode(['property_id' => $booking['property_id']])
                ]
            );

            // TODO: Send rejection email to guest
            // sendBookingRejectedEmail($booking);

            setFlash('success', 'Booking rejected. Guest will be notified.');
            break;

        case 'delete':
            // Delete the booking permanently
            db()->query('DELETE FROM bookings WHERE id = ?', [$bookingId]);

            // Log activity
            $activityId = Auth::generateId();
            db()->query(
                'INSERT INTO activity_log (id, user_id, action, entity_type, entity_id, details, created_at)
                 VALUES (?, ?, "booking_deleted", "booking", ?, ?, NOW())',
                [
                    $activityId,
                    $user['id'],
                    $bookingId,
                    json_encode([
                        'property_id' => $booking['property_id'],
                        'property_title' => $booking['property_title'],
                        'guest_email' => $booking['guest_email']
                    ])
                ]
            );

            setFlash('success', 'Booking deleted successfully.');
            header('Location: /admin/bookings.php');
            exit;

        default:
            setFlash('error', 'Invalid action');
            break;
    }

    // Redirect back to booking detail page (except for delete)
    header('Location: /admin/booking-detail.php?id=' . $bookingId);

} catch (Exception $e) {
    setFlash('error', 'Failed to process action: ' . $e->getMessage());
    header('Location: /admin/bookings.php');
}
exit;
