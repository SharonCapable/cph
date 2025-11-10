<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$user = Auth::requireRole('admin');

// Get property ID
$propertyId = $_GET['id'] ?? null;

if (!$propertyId) {
    setFlash('error', 'Property ID is required');
    redirect('/admin/properties.php');
}

// Get property details
$property = db()->fetchOne('SELECT * FROM properties WHERE id = ?', [$propertyId]);

if (!$property) {
    setFlash('error', 'Property not found');
    redirect('/admin/properties.php');
}

// Check if user has permission to delete this property
$isSuperAdmin = $user['role'] === 'super_admin';
if (!$isSuperAdmin && $property['manager_id'] !== $user['id']) {
    setFlash('error', 'You do not have permission to delete this property');
    redirect('/admin/properties.php');
}

// Check if property has active bookings
$activeBookings = db()->fetchAll(
    'SELECT * FROM bookings WHERE property_id = ? AND status IN ("pending", "confirmed") AND check_out >= CURDATE()',
    [$propertyId]
);

if (!empty($activeBookings)) {
    setFlash('error', 'Cannot delete property with active or pending bookings. Please cancel the bookings first.');
    redirect('/admin/properties.php');
}

// Handle confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Get all images
        $images = db()->fetchAll('SELECT * FROM property_images WHERE property_id = ?', [$propertyId]);

        // Delete images from filesystem
        foreach ($images as $image) {
            deleteImage($image['image_path']);
        }

        // Delete featured image if exists
        if ($property['featured_image']) {
            deleteImage($property['featured_image']);
        }

        // Delete property images from database
        db()->query('DELETE FROM property_images WHERE property_id = ?', [$propertyId]);

        // Delete associated bookings (only completed or cancelled ones)
        db()->query('DELETE FROM bookings WHERE property_id = ? AND status IN ("completed", "cancelled")', [$propertyId]);

        // Delete the property
        db()->query('DELETE FROM properties WHERE id = ?', [$propertyId]);

        // Log activity
        $activityId = Auth::generateId();
        db()->query(
            'INSERT INTO activity_log (id, user_id, action, entity_type, entity_id, details, created_at)
             VALUES (?, ?, "property_deleted", "property", ?, ?, NOW())',
            [
                $activityId,
                $user['id'],
                $propertyId,
                json_encode(['title' => $property['title']])
            ]
        );

        setFlash('success', 'Property deleted successfully');
        redirect('/admin/properties.php');

    } catch (Exception $e) {
        setFlash('error', 'Failed to delete property: ' . $e->getMessage());
        redirect('/admin/properties.php');
    }
}

$pageTitle = 'Delete Property';
include '../includes/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center mb-6">
        <a href="/admin/properties.php" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Delete Property</h1>
    </div>

    <div class="bg-white rounded-xl shadow-md p-8">
        <div class="text-center mb-8">
            <div class="bg-red-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Confirm Deletion</h2>
            <p class="text-gray-600">This action cannot be undone. All property data and images will be permanently deleted.</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-6 mb-8">
            <div class="flex items-start">
                <?php if ($property['featured_image']): ?>
                    <img src="<?php echo htmlspecialchars($property['featured_image']); ?>"
                         alt="<?php echo htmlspecialchars($property['title']); ?>"
                         class="w-24 h-24 rounded-lg object-cover mr-4">
                <?php else: ?>
                    <div class="w-24 h-24 bg-gray-200 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-home text-gray-400 text-2xl"></i>
                    </div>
                <?php endif; ?>

                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($property['title']); ?></h3>
                    <p class="text-gray-600 mb-2">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        <?php echo htmlspecialchars($property['city'] . ', ' . $property['country']); ?>
                    </p>
                    <p class="text-gray-600">
                        <i class="fas fa-bed mr-1"></i> <?php echo $property['bedrooms']; ?> Beds
                        <i class="fas fa-bath ml-3 mr-1"></i> <?php echo $property['bathrooms']; ?> Baths
                    </p>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="font-bold text-gray-900 mb-2">What will be deleted:</h3>
            <ul class="space-y-2 text-gray-600">
                <li><i class="fas fa-check text-red-600 mr-2"></i> Property information</li>
                <li><i class="fas fa-check text-red-600 mr-2"></i> All property images</li>
                <li><i class="fas fa-check text-red-600 mr-2"></i> Historical booking records (completed/cancelled)</li>
            </ul>
        </div>

        <form method="POST" class="space-y-4">
            <div class="flex items-start bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <i class="fas fa-info-circle text-yellow-600 mr-3 mt-1"></i>
                <p class="text-sm text-yellow-800">
                    To confirm deletion, please verify that you understand this action is permanent and cannot be reversed.
                </p>
            </div>

            <div class="flex justify-end space-x-4 pt-4">
                <a href="/admin/properties.php" class="px-6 py-3 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" name="confirm_delete" value="1"
                        class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-semibold transition-all">
                    <i class="fas fa-trash mr-2"></i>
                    Delete Property
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
