<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_ENV['APP_URL'] ?? 'http://localhost:3000'));
header('Access-Control-Allow-Credentials: true');

require_once '../../includes/config.php';
require_once '../../includes/functions.php';

try {
    // Get all available properties with images
    $properties = db()->fetchAll(
        'SELECT p.*,
                GROUP_CONCAT(
                    JSON_OBJECT(
                        "id", pi.id,
                        "image_path", pi.image_path,
                        "is_primary", pi.is_primary,
                        "display_order", pi.display_order
                    )
                    ORDER BY pi.is_primary DESC, pi.display_order ASC
                ) as images_json
         FROM properties p
         LEFT JOIN property_images pi ON p.id = pi.property_id
         WHERE p.status = "available"
         GROUP BY p.id
         ORDER BY p.is_featured DESC, p.created_at DESC'
    );

    // Process images JSON for each property
    foreach ($properties as &$property) {
        if ($property['images_json']) {
            $property['images'] = json_decode('[' . $property['images_json'] . ']', true);
        } else {
            $property['images'] = [];
        }
        unset($property['images_json']);

        // Parse amenities if stored as JSON
        if ($property['amenities'] && is_string($property['amenities'])) {
            $property['amenities'] = json_decode($property['amenities'], true) ?: [];
        } else {
            $property['amenities'] = [];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $properties
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch properties: ' . $e->getMessage()
    ]);
}
