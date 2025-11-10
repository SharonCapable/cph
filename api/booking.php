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
    // Basic booking information
    $propertyId = sanitize($_POST['property_id']);
    $checkIn = $_POST['check_in'];
    $checkOut = $_POST['check_out'];
    $guests = (int)$_POST['guests'];
    $phone = sanitize($_POST['phone']);
    $message = isset($_POST['message']) ? sanitize($_POST['message']) : null;

    // Guest information
    $guestFullName = sanitize($_POST['guest_full_name']);
    $guestEmail = sanitize($_POST['guest_email']);
    $guestNationality = sanitize($_POST['guest_nationality']);
    $guestDateOfBirth = $_POST['guest_date_of_birth'];
    $guestGender = $_POST['guest_gender'];
    $guestPassportNumber = sanitize($_POST['guest_passport_number']);
    $guestAddress = sanitize($_POST['guest_address']);

    // Travel information
    $purposeOfVisit = sanitize($_POST['purpose_of_visit']);
    $arrivalDate = $_POST['arrival_date'];
    $arrivalFlight = isset($_POST['arrival_flight']) ? sanitize($_POST['arrival_flight']) : null;
    $departureDate = $_POST['departure_date'];
    $departureFlight = isset($_POST['departure_flight']) ? sanitize($_POST['departure_flight']) : null;

    // Emergency contact
    $emergencyContactName = sanitize($_POST['emergency_contact_name']);
    $emergencyContactRelationship = sanitize($_POST['emergency_contact_relationship']);
    $emergencyContactPhone = sanitize($_POST['emergency_contact_phone']);
    $emergencyContactEmail = isset($_POST['emergency_contact_email']) ? sanitize($_POST['emergency_contact_email']) : null;

    // Declaration and signature
    $termsAccepted = isset($_POST['terms_accepted']) ? 1 : 0;
    $signatureData = sanitize($_POST['signature_data']);

    // Visa requirements
    $isForeigner = isset($_POST['is_foreigner']) ? 1 : 0;
    $requiresVisaLetter = isset($_POST['requires_visa_letter']) ? 1 : 0;

    // Validate required fields
    if (!$termsAccepted) {
        setFlash('error', 'You must accept the terms and conditions');
        header('Location: /public/property.php?id=' . $propertyId);
        exit;
    }

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

    // Create booking with all expanded fields
    $bookingId = Auth::generateId();
    db()->query(
        'INSERT INTO bookings (
            id, property_id, user_id, check_in, check_out, guests,
            total_price, phone, message,
            guest_full_name, guest_email, guest_nationality, guest_date_of_birth,
            guest_gender, guest_passport_number, guest_address,
            purpose_of_visit, arrival_date, arrival_flight, departure_date, departure_flight,
            emergency_contact_name, emergency_contact_relationship,
            emergency_contact_phone, emergency_contact_email,
            terms_accepted, signature_data, signature_date,
            is_foreigner, requires_visa_letter,
            status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, "pending", NOW())',
        [
            $bookingId,
            $propertyId,
            $user['id'],
            $checkIn,
            $checkOut,
            $guests,
            $totalPrice,
            $phone,
            $message,
            $guestFullName,
            $guestEmail,
            $guestNationality,
            $guestDateOfBirth,
            $guestGender,
            $guestPassportNumber,
            $guestAddress,
            $purposeOfVisit,
            $arrivalDate,
            $arrivalFlight,
            $departureDate,
            $departureFlight,
            $emergencyContactName,
            $emergencyContactRelationship,
            $emergencyContactPhone,
            $emergencyContactEmail,
            $termsAccepted,
            $signatureData,
            $isForeigner,
            $requiresVisaLetter
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
            json_encode([
                'property_id' => $propertyId,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'is_foreigner' => $isForeigner,
                'requires_visa_letter' => $requiresVisaLetter
            ])
        ]
    );

    // TODO: Generate PDF letters here (will be implemented next)
    // - Booking request letter (always)
    // - Visa invitation letter (if $requiresVisaLetter is true)

    $successMessage = 'Booking request submitted successfully! ';
    if ($requiresVisaLetter) {
        $successMessage .= 'Your visa invitation letter will be generated and sent to you via email.';
    }
    $successMessage .= ' The property manager will contact you soon.';

    setFlash('success', $successMessage);
    header('Location: /public/property.php?id=' . $propertyId);

} catch (Exception $e) {
    setFlash('error', 'Failed to create booking: ' . $e->getMessage());
    header('Location: /public/property.php?id=' . ($propertyId ?? ''));
}
exit;
