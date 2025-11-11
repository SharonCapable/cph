<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$user = Auth::requireRole('super_admin');
$pageTitle = 'Add New Property';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Generate property ID
        $propertyId = Auth::generateId();

        // Handle amenities
        $amenities = isset($_POST['amenities']) ? json_encode($_POST['amenities']) : '[]';

        // Generate slug from title
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['title'])));

        // Insert property
        db()->query(
            'INSERT INTO properties (
                id, manager_id, title, description, property_type, status,
                address, city, state, country, zip_code,
                bedrooms, bathrooms, square_feet, furnished, pets_allowed, parking,
                price_per_month, security_deposit, amenities, slug, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
            [
                $propertyId,
                $user['id'], // manager_id is the super admin
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
                $slug
            ]
        );

        // Handle image uploads
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $uploadedImages = [];
            $imageCount = count($_FILES['images']['name']);

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
                            [$imageId, $propertyId, $result['path'], $i]
                        );

                        $uploadedImages[] = $result['path'];

                        // Set first image as featured
                        if ($i === 0) {
                            db()->query(
                                'UPDATE properties SET featured_image = ? WHERE id = ?',
                                [$result['path'], $propertyId]
                            );
                        }
                    }
                }
            }
        }

        setFlash('success', 'Property created successfully!');
        redirect('/admin/properties.php');

    } catch (Exception $e) {
        $error = 'Failed to create property: ' . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="max-w-4xl">
    <div class="flex items-center mb-6">
        <a href="/admin/properties.php" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Add New Property</h1>
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
                    <input type="text" name="title" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., Modern 2BR Apartment in Downtown">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description<span class="text-red-600"> *</span></label>
                    <textarea name="description" required rows="5"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Describe your property in detail..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Property Type<span class="text-red-600"> *</span></label>
                    <select name="property_type" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="apartment">Apartment</option>
                        <option value="house">House</option>
                        <option value="condo">Condo</option>
                        <option value="villa">Villa</option>
                        <option value="studio">Studio</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status<span class="text-red-600"> *</span></label>
                    <select name="status" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="available">Available</option>
                        <option value="rented">Rented</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="pending">Pending</option>
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
                    <input type="text" name="address" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">City<span class="text-red-600"> *</span></label>
                    <input type="text" name="city" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">State/Province</label>
                    <input type="text" name="state"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Country<span class="text-red-600"> *</span></label>
                    <input type="text" name="country" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Zip/Postal Code</label>
                    <input type="text" name="zip_code"
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
                    <input type="number" name="bedrooms" required min="0"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bathrooms<span class="text-red-600"> *</span></label>
                    <input type="number" name="bathrooms" required min="0"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Square Feet</label>
                    <input type="number" name="square_feet" min="0"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <label class="flex items-center">
                    <input type="checkbox" name="furnished" value="1" class="w-5 h-5 text-blue-600 rounded">
                    <span class="ml-3 text-gray-700">Furnished</span>
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="pets_allowed" value="1" class="w-5 h-5 text-blue-600 rounded">
                    <span class="ml-3 text-gray-700">Pets Allowed</span>
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="parking" value="1" class="w-5 h-5 text-blue-600 rounded">
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
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="150.00">
                    <p class="text-xs text-gray-500 mt-1">Daily rental rate for this property</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cleaning Fee (Optional)</label>
                    <input type="number" name="security_deposit" min="0" step="0.01"
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
                               class="w-5 h-5 text-blue-600 rounded">
                        <span class="ml-3 text-gray-700"><?php echo $amenity; ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Images -->
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-images text-blue-600 mr-2"></i>
                Property Images
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
                <p class="text-sm text-gray-500 mt-4">Upload up to <?php echo MAX_IMAGES_PER_PROPERTY; ?> images (JPG, PNG, WebP)</p>
            </div>

            <div id="imagePreview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end space-x-4 pt-6">
            <a href="/admin/properties.php" class="px-6 py-3 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition-all">
                <i class="fas fa-check mr-2"></i>
                Create Property
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
                <div class="absolute top-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded">
                    ${i === 0 ? 'Featured' : `Image ${i + 1}`}
                </div>
            `;
            preview.appendChild(div);
        };

        reader.readAsDataURL(file);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
