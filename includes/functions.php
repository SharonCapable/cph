<?php
/**
 * CirclePoint Homes - Utility Functions
 * Helper functions used throughout the application
 */

/**
 * Sanitize user input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Format price
 */
function formatPrice($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

/**
 * Get user initials
 */
function getInitials($firstName, $lastName, $email) {
    if ($firstName && $lastName) {
        return strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
    }
    return strtoupper(substr($email, 0, 2));
}

/**
 * Generate WhatsApp link
 * Context-aware: Shows property-specific message if property details provided,
 * otherwise shows generic interest message
 */
function getWhatsAppLink($propertyTitle = null, $propertyId = null) {
    // If property details are provided, use property-specific message with link
    if (!empty($propertyTitle) && !empty($propertyId)) {
        $propertyUrl = APP_URL . "/public/property.php?id=" . $propertyId;
        $message = "Hi, I'm interested in $propertyTitle.\n\nHere's the property link: $propertyUrl";
    } else {
        // Generic message for non-property contexts
        $message = "Hi, I was just on your site and I'm interested in learning more about your properties. Could you help me?";
    }
    return "https://wa.me/" . WHATSAPP_NUMBER . "?text=" . urlencode($message);
}

/**
 * Get Instagram link
 */
function getInstagramLink() {
    return "https://instagram.com/" . INSTAGRAM_HANDLE;
}

/**
 * Get LinkedIn link
 */
function getLinkedInLink() {
    // Check if LINKEDIN_COMPANY is empty or not set
    if (empty(LINKEDIN_COMPANY)) {
        return "#";
    }
    // If it already starts with http or https, return as is
    if (strpos(LINKEDIN_COMPANY, 'http') === 0) {
        return LINKEDIN_COMPANY;
    }
    // Otherwise, prepend the LinkedIn base URL
    return "https://linkedin.com/" . LINKEDIN_COMPANY;
}

/**
 * Upload image
 */
function uploadImage($file, $directory = 'properties') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'No file uploaded or upload error'];
    }

    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds limit'];
    }

    // Validate file type
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }

    // Generate unique filename
    $filename = bin2hex(random_bytes(16)) . '.' . $fileExtension;
    $uploadDir = UPLOAD_PATH . '/' . $directory;

    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destination = $uploadDir . '/' . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => '/uploads/' . $directory . '/' . $filename
        ];
    }

    return ['success' => false, 'error' => 'Failed to move uploaded file'];
}

/**
 * Delete image
 */
function deleteImage($path) {
    $fullPath = ROOT_PATH . $path;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Redirect
 */
function redirect($path) {
    header('Location: ' . $path);
    exit;
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Get and clear flash message
 */
function getFlash($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

/**
 * Check if flash message exists
 */
function hasFlash($type) {
    return isset($_SESSION['flash'][$type]);
}
