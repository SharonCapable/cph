<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$user = Auth::requireRole('admin');
$pageTitle = 'Manage Properties';
$isSuperAdmin = $user['role'] === 'super_admin';

// Get properties based on role
if ($isSuperAdmin) {
    // Super admin sees all properties
    $properties = db()->fetchAll(
        'SELECT p.*, u.email as manager_email,
         (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as image_count
         FROM properties p
         JOIN users u ON p.manager_id = u.id
         ORDER BY p.created_at DESC'
    );
} else {
    // Property manager sees only their own properties
    $properties = db()->fetchAll(
        'SELECT p.*, u.email as manager_email,
         (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as image_count
         FROM properties p
         JOIN users u ON p.manager_id = u.id
         WHERE p.manager_id = ?
         ORDER BY p.created_at DESC',
        [$user['id']]
    );
}

include 'includes/header.php';
?>

<div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Properties</h1>
    <a href="/admin/properties/create.php" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition-all">
        <i class="fas fa-plus mr-2"></i>
        Add New Property
    </a>
</div>

<?php if (empty($properties)): ?>
    <div class="bg-white rounded-xl shadow-md p-12 text-center">
        <i class="fas fa-home text-6xl text-gray-300 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">No Properties Yet</h2>
        <p class="text-gray-600 mb-6">Get started by adding your first property listing</p>
        <a href="/admin/properties/create.php" class="inline-block bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition-all">
            <i class="fas fa-plus mr-2"></i>
            Add Your First Property
        </a>
    </div>
<?php else: ?>
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Images</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($properties as $property): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <?php if ($property['featured_image']): ?>
                                    <img src="<?php echo htmlspecialchars($property['featured_image']); ?>"
                                         alt="<?php echo htmlspecialchars($property['title']); ?>"
                                         class="w-16 h-16 rounded-lg object-cover mr-4">
                                <?php else: ?>
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-home text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($property['title']); ?></div>
                                    <div class="text-sm text-gray-500">
                                        <i class="fas fa-bed mr-1"></i><?php echo $property['bedrooms']; ?> beds
                                        <i class="fas fa-bath ml-2 mr-1"></i><?php echo $property['bathrooms']; ?> baths
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($property['city']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($property['country']); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900"><?php echo formatPrice($property['price_per_month']); ?>/night</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php
                                echo $property['status'] === 'available' ? 'bg-green-100 text-green-800' :
                                    ($property['status'] === 'rented' ? 'bg-blue-100 text-blue-800' :
                                    ($property['status'] === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'));
                            ?>">
                                <?php echo ucfirst($property['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <i class="fas fa-images mr-1"></i><?php echo $property['image_count']; ?>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium space-x-2">
                            <a href="/admin/properties/edit.php?id=<?php echo $property['id']; ?>"
                               class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="/public/property.php?id=<?php echo $property['id']; ?>"
                               class="text-green-600 hover:text-green-900"
                               target="_blank">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="/admin/properties/delete.php?id=<?php echo $property['id']; ?>"
                               class="text-red-600 hover:text-red-900"
                               onclick="return confirm('Are you sure you want to delete this property?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
