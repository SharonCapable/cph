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
    $fullName = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $companyName = sanitize($_POST['company_name'] ?? null);
    $propertiesCount = (int)($_POST['properties_count'] ?? 0);
    $experienceYears = (int)($_POST['experience_years'] ?? 0);
    $message = sanitize($_POST['message'] ?? '');

    // Validate required fields
    if (empty($fullName) || empty($email) || empty($phone) || empty($message)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'All required fields must be filled'
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

    // Check if application already exists
    $existing = db()->fetchOne('SELECT id FROM applications WHERE email = ?', [$email]);
    if ($existing) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'error' => 'An application with this email already exists'
        ]);
        exit;
    }

    // Create application
    $applicationId = Auth::generateId();

    db()->query(
        'INSERT INTO applications (id, full_name, email, phone, company_name, properties_count, experience_years, message, status, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, "pending", NOW(), NOW())',
        [$applicationId, $fullName, $email, $phone, $companyName, $propertiesCount, $experienceYears, $message]
    );

    echo json_encode([
        'success' => true,
        'message' => 'Application submitted successfully. We will review your application and contact you soon.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to submit application: ' . $e->getMessage()
    ]);
}
