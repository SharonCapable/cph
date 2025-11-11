<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$user = Auth::requireRole('admin');
$pageTitle = 'Edit Property';

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

// Check if user has permission to edit this property
$isSuperAdmin = $user['role'] === 'super_admin';
if (!$isSuperAdmin && $property['manager_id'] !== $user['id']) {
    setFlash('error', 'You do not have permission to edit this property');
    redirect('/admin/properties.php');
}

// Get existing images
$existingImages = db()->fetchAll(
    'SELECT * FROM property_images WHERE property_id = ? ORDER BY display_order ASC',
    [$propertyId]
);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle amenities
        $amenities = isset($_POST['amenities']) ? json_encode($_POST['amenities']) : '[]';

        // Generate slug from title
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['title'])));

        // Update property
        db()->query(
            'UPDATE properties SET
                title = ?, description = ?, property_type = ?, status = ?,
                address = ?, city = ?, state = ?, country = ?, zip_code = ?,
                bedrooms = ?, bathrooms = ?, square_feet = ?, furnished = ?, pets_allowed = ?, parking = ?,
                price_per_month = ?, security_deposit = ?, amenities = ?, slug = ?, updated_at = NOW()
             WHERE id = ?',
            [
                sanitize($_POST['title']),
                sanitize($_POST['description']),
                $_POST['property_type'],
                $_POST['status'],
                sanitize($_POST['address']),
                sanitize($_POST['city']),
                sanitize($_POST['state']),
                sanitize($_POST['country']),
                sanitize($_POST['zip_code']),
                (int)$_POST['bedrooms'],
                (int)$_POST['bathrooms'],
                isset($_POST['square_feet']) ? (int)$_POST['square_feet'] : null,
                isset($_POST['furnished']) ? 1 : 0,
                isset($_POST['pets_allowed']) ? 1 : 0,
                isset($_POST['parking']) ? 1 : 0,
                (float)$_POST['price_per_month'],
                isset($_POST['security_deposit']) ? (float)$_POST['security_deposit'] : null,
                $amenities,
                $slug,
                $propertyId
            ]
        );

        // Handle new image uploads
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $uploadedImages = [];
            $imageCount = count($_FILES['images']['name']);
            $currentImageCount = count($existingImages);

            for ($i = 0; $i < $imageCount; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['images']['name'][$i],
                        'type' => $_FILES['images']['type'][$i],
                        'tmp_name' => $_FILES['images']['tmp_name'][$i],
                        'error' => $_FILES['images']['error'][$i],
                        'size' => $_FILES['images']['size'][$i]
                    ];

                    $result = uploadImage($file, 'properties');

                    if ($result['success']) {
                        $imageId = Auth::generateId();
                        db()->query(
                            'INSERT INTO property_images (id, property_id, image_path, display_order, created_at)
                             VALUES (?, ?, ?, ?, NOW())',
                            [$imageId, $propertyId, $result['path'], $currentImageCount + $i]
                        );

                        $uploadedImages[] = $result['path'];

                        // Set first image as featured if no featured image exists
                        if ($currentImageCount === 0 && $i === 0) {
                            db()->query(
                                'UPDATE properties SET featured_image = ? WHERE id = ?',
                                [$result['path'], $propertyId]
                            );
                        }
                    }
                }
            }
        }

        // Handle image deletions
        if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $imageId) {
                // Get image path before deleting
                $image = db()->fetchOne('SELECT image_path FROM property_images WHERE id = ?', [$imageId]);
                if ($image) {
                    // Delete from filesystem
                    deleteImage($image['image_path']);
                    // Delete from database
                    db()->query('DELETE FROM property_images WHERE id = ?', [$imageId]);
                }
            }

            // Update featured image if it was deleted
            $remainingImages = db()->fetchAll(
                'SELECT * FROM property_images WHERE property_id = ? ORDER BY display_order ASC LIMIT 1',
                [$propertyId]
            );
            if (!empty($remainingImages)) {
                db()->query(
                    'UPDATE properties SET featured_image = ? WHERE id = ?',
                    [$remainingImages[0]['image_path'], $propertyId]
                );
            } else {
                db()->query('UPDATE properties SET featured_image = NULL WHERE id = ?', [$propertyId]);
            }
        }

        setFlash('success', 'Property updated successfully!');
        redirect('/admin/properties.php');

    } catch (Exception $e) {
        $error = 'Failed to update property: ' . $e->getMessage();
    }
}

// Parse amenities for display
$selectedAmenities = json_decode($property['amenities'] ?? '[]', true) ?: [];

include '../includes/header.php';
?>

<div class="max-w-4xl">
    <div class="flex items-center mb-6">
        <a href="/admin/properties.php" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Edit Property</h1>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-md p-8 space-y-8">
        <!-- Basic Information -->
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                Basic Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Property Title<span class="text-red-600"> *</span></label>
                    <input type="text" name="title" required value="<?php echo htmlspecialchars($property['title']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., Modern 2BR Apartment in Downtown">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description<span class="text-red-600"> *</span></label>
                    <textarea name="description" required rows="5"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Describe your property in detail..."><?php echo htmlspecialchars($property['description']); ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Property Type<span class="text-red-600"> *</span></label>
                    <select name="property_type" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="apartment" <?php echo $property['property_type'] === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                        <option value="house" <?php echo $property['property_type'] === 'house' ? 'selected' : ''; ?>>House</option>
                        <option value="condo" <?php echo $property['property_type'] === 'condo' ? 'selected' : ''; ?>>Condo</option>
                        <option value="villa" <?php echo $property['property_type'] === 'villa' ? 'selected' : ''; ?>>Villa</option>
                        <option value="studio" <?php echo $property['property_type'] === 'studio' ? 'selected' : ''; ?>>Studio</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status<span class="text-red-600"> *</span></label>
                    <select name="status" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="available" <?php echo $property['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="rented" <?php echo $property['status'] === 'rented' ? 'selected' : ''; ?>>Rented</option>
                        <option value="maintenance" <?php echo $property['status'] === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                        <option value="pending" <?php echo $property['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Location -->
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                Location
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Street Address<span class="text-red-600"> *</span></label>
                    <input type="text" name="address" required value="<?php echo htmlspecialchars($property['address']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">City<span class="text-red-600"> *</span></label>
                    <input type="text" name="city" required value="<?php echo htmlspecialchars($property['city']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">State/Province</label>
                    <input type="text" name="state" value="<?php echo htmlspecialchars($property['state']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Country<span class="text-red-600"> *</span></label>
                    <input type="text" name="country" required value="<?php echo htmlspecialchars($property['country']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Zip/Postal Code</label>
                    <input type="text" name="zip_code" value="<?php echo htmlspecialchars($property['zip_code']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Property Details -->
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-bed text-blue-600 mr-2"></i>
                Property Details
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bedrooms<span class="text-red-600"> *</span></label>
                    <input type="number" name="bedrooms" required min="0" value="<?php echo $property['bedrooms']; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bathrooms<span class="text-red-600"> *</span></label>
                    <input type="number" name="bathrooms" required min="0" value="<?php echo $property['bathrooms']; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Square Feet</label>
                    <input type="number" name="square_feet" min="0" value="<?php echo $property['square_feet']; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <label class="flex items-center">
                    <input type="checkbox" name="furnished" value="1" <?php echo $property['furnished'] ? 'checked' : ''; ?>
                           class="w-5 h-5 text-blue-600 rounded">
                    <span class="ml-3 text-gray-700">Furnished</span>
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="pets_allowed" value="1" <?php echo $property['pets_allowed'] ? 'checked' : ''; ?>
                           class="w-5 h-5 text-blue-600 rounded">
                    <span class="ml-3 text-gray-700">Pets Allowed</span>
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="parking" value="1" <?php echo $property['parking'] ? 'checked' : ''; ?>
                           class="w-5 h-5 text-blue-600 rounded">
                    <span class="ml-3 text-gray-700">Parking Available</span>
                </label>
            </div>
        </div>

        <!-- Pricing -->
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-dollar-sign text-blue-600 mr-2"></i>
                Pricing
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price per Night<span class="text-red-600"> *</span></label>
                    <input type="number" name="price_per_month" required min="0" step="0.01"
                           value="<?php echo $property['price_per_month']; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="150.00">
                    <p class="text-xs text-gray-500 mt-1">Daily rental rate for this property</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cleaning Fee (Optional)</label>
                    <input type="number" name="security_deposit" min="0" step="0.01"
                           value="<?php echo $property['security_deposit']; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="50.00">
                    <p class="text-xs text-gray-500 mt-1">One-time cleaning fee per stay</p>
                </div>
            </div>
        </div>

        <!-- Amenities -->
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-star text-blue-600 mr-2"></i>
                Amenities
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <?php
                $amenitiesList = [
                    'WiFi', 'Air Conditioning', 'Heating', 'Kitchen', 'Washer', 'Dryer',
                    'TV', 'Gym', 'Pool', 'Security', 'Elevator', 'Balcony'
                ];
                foreach ($amenitiesList as $amenity):
                ?>
                    <label class="flex items-center">
                        <input type="checkbox" name="amenities[]" value="<?php echo $amenity; ?>"
                               <?php echo in_array($amenity, $selectedAmenities) ? 'checked' : ''; ?>
                               class="w-5 h-5 text-blue-600 rounded">
                        <span class="ml-3 text-gray-700"><?php echo $amenity; ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Existing Images -->
        <?php if (!empty($existingImages)): ?>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-images text-blue-600 mr-2"></i>
                Existing Images
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($existingImages as $index => $image): ?>
                    <div class="relative">
                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>"
                             alt="Property image"
                             class="w-full h-32 object-cover rounded-lg">
                        <?php if ($index === 0): ?>
                            <div class="absolute top-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded">
                                Featured
                            </div>
                        <?php endif; ?>
                        <label class="absolute top-2 right-2 bg-red-600 text-white px-2 py-1 rounded cursor-pointer hover:bg-red-700 text-xs">
                            <input type="checkbox" name="delete_images[]" value="<?php echo $image['id']; ?>" class="mr-1">
                            Delete
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="text-sm text-gray-500 mt-2">Check "Delete" to remove images when saving.</p>
        </div>
        <?php endif; ?>

        <!-- Add New Images -->
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-plus-circle text-blue-600 mr-2"></i>
                Add New Images
            </h2>

            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                <label class="cursor-pointer">
                    <span class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 inline-block">
                        Choose Images
                    </span>
                    <input type="file" name="images[]" multiple accept="image/*" class="hidden" id="imageInput"
                           onchange="previewImages(event)">
                </label>
                <p class="text-sm text-gray-500 mt-4">Upload additional images (JPG, PNG, WebP)</p>
            </div>

            <div id="imagePreview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end space-x-4 pt-6">
            <a href="/admin/properties.php" class="px-6 py-3 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition-all">
                <i class="fas fa-save mr-2"></i>
                Update Property
            </button>
        </div>
    </form>
</div>

<script>
function previewImages(event) {
    const files = event.target.files;
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();

        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative';
            div.innerHTML = `
                <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg">
                <div class="absolute top-2 left-2 bg-green-600 text-white text-xs px-2 py-1 rounded">
                    New Image ${i + 1}
                </div>
            `;
            preview.appendChild(div);
        };

        reader.readAsDataURL(file);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
