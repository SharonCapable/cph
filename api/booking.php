<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require authentication
$user = Auth::requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /public/index.php');
    exit;
}

try {
    $propertyId = sanitize($_POST['property_id']);
    $checkIn = $_POST['check_in'];
    $checkOut = $_POST['check_out'];
    $guests = (int)$_POST['guests'];
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : null;
    $message = isset($_POST['message']) ? sanitize($_POST['message']) : null;

    // Validate dates
    $checkInDate = new DateTime($checkIn);
    $checkOutDate = new DateTime($checkOut);
    $now = new DateTime();

    if ($checkInDate < $now) {
        setFlash('error', 'Check-in date must be in the future');
        header('Location: /public/property.php?id=' . $propertyId);
        exit;
    }

    if ($checkOutDate <= $checkInDate) {
        setFlash('error', 'Check-out date must be after check-in date');
        header('Location: /public/property.php?id=' . $propertyId);
        exit;
    }

    // Get property details
    $property = db()->fetchOne('SELECT * FROM properties WHERE id = ?', [$propertyId]);

    if (!$property) {
        setFlash('error', 'Property not found');
        header('Location: /public/index.php');
        exit;
    }

    // Calculate total price (number of nights)
    $interval = $checkInDate->diff($checkOutDate);
    $nights = $interval->days;
    if ($nights < 1) $nights = 1; // Minimum 1 night

    $pricePerNight = $property['price_per_month']; // Using same column but it's now price per night
    $cleaningFee = $property['security_deposit'] ?? 0; // Using same column but it's now cleaning fee

    $totalPrice = ($pricePerNight * $nights) + $cleaningFee;

    // Create booking
    $bookingId = Auth::generateId();
    db()->query(
        'INSERT INTO bookings (
            id, property_id, user_id, check_in, check_out, guests,
            total_price, phone, message, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "pending", NOW())',
        [
            $bookingId,
            $propertyId,
            $user['id'],
            $checkIn,
            $checkOut,
            $guests,
            $totalPrice,
            $phone,
            $message
        ]
    );

    // Log activity
    $activityId = Auth::generateId();
    db()->query(
        'INSERT INTO activity_log (id, user_id, action, entity_type, entity_id, details, created_at)
         VALUES (?, ?, "booking_created", "booking", ?, ?, NOW())',
        [
            $activityId,
            $user['id'],
            $bookingId,
            json_encode(['property_id' => $propertyId, 'check_in' => $checkIn, 'check_out' => $checkOut])
        ]
    );

    setFlash('success', 'Booking request submitted successfully! The property manager will contact you soon.');
    header('Location: /public/property.php?id=' . $propertyId);

} catch (Exception $e) {
    setFlash('error', 'Failed to create booking: ' . $e->getMessage());
    header('Location: /public/property.php?id=' . ($propertyId ?? ''));
}
exit;
