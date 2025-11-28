<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_ENV['APP_URL'] ?? 'http://localhost:3000'));
header('Access-Control-Allow-Credentials: true');

require_once '../../includes/config.php';
require_once '../../includes/functions.php';

try {
    $propertyId = $_GET['id'] ?? null;

    if (!$propertyId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Property ID is required'
        ]);
        exit;
    }

    // Get property with images
    $property = db()->fetchOne(
        'SELECT p.*
         FROM properties p
         WHERE p.id = ?',
        [$propertyId]
    );

    if (!$property) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Property not found'
        ]);
        exit;
    }

    // Get property images
    $images = db()->fetchAll(
        'SELECT id, image_path, is_primary, display_order
         FROM property_images
         WHERE property_id = ?
         ORDER BY is_primary DESC, display_order ASC',
        [$propertyId]
    );

    $property['images'] = $images;

    // Parse amenities if stored as JSON
    if ($property['amenities'] && is_string($property['amenities'])) {
        $property['amenities'] = json_decode($property['amenities'], true) ?: [];
    } else {
        $property['amenities'] = [];
    }

    echo json_encode([
        'success' => true,
        'data' => $property
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch property: ' . $e->getMessage()
    ]);
}
