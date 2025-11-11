<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$pageTitle = 'Find Your Perfect Home';

// Get available properties (handle case where table doesn't exist yet)
$properties = [];
try {
    $properties = db()->fetchAll(
        'SELECT * FROM properties
         WHERE status = "available"
         ORDER BY is_featured DESC, created_at DESC
         LIMIT 12'
    );
} catch (Exception $e) {
    // Properties table doesn't exist yet - that's okay
    $properties = [];
}

include '../includes/header.php';
?>

<!-- Hero Section -->
<div class="bg-gradient-to-br from-blue-600 via-purple-600 to-blue-800 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-5xl md:text-6xl font-bold mb-6">
            Find Your Perfect Home
        </h1>
        <p class="text-xl md:text-2xl mb-8 text-blue-100">
            Quality properties for travelers worldwide
        </p>

        <!-- Search Bar (Coming Soon) -->
        <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-2xl p-6 text-left">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                    <input type="text" placeholder="Where do you want to stay?" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" disabled>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                    <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" disabled>
                        <option>Any price</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bedrooms</label>
                    <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" disabled>
                        <option>Any</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 rounded-lg font-semibold hover:shadow-lg transition-all" disabled>
                    <i class="fas fa-search mr-2"></i> Search Properties (Coming Soon)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Property Listings Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Featured Properties</h2>
        <p class="text-gray-600">Discover amazing places to stay in Ghana</p>
    </div>

    <?php if (empty($properties)): ?>
        <!-- No Properties Yet -->
        <div class="text-center py-16">
            <i class="fas fa-home text-6xl text-gray-300 mb-6"></i>
            <h3 class="text-2xl font-bold text-gray-900 mb-4">No Properties Available Yet</h3>
            <p class="text-gray-600 mb-8">Check back soon for amazing properties!</p>
            <?php if (Auth::check() && Auth::hasRole('super_admin')): ?>
                <a href="/admin/properties/create.php" class="inline-block bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition-all">
                    <i class="fas fa-plus mr-2"></i>
                    Add Your First Property
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Property Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($properties as $property): ?>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all property-card group">
                    <!-- Property Image - Clickable (opens gallery) -->
                    <a href="/public/property.php?id=<?php echo $property['id']; ?>&gallery=1" class="block h-64 relative overflow-hidden">
                        <?php if ($property['featured_image']): ?>
                            <img src="<?php echo htmlspecialchars($property['featured_image']); ?>"
                                 alt="<?php echo htmlspecialchars($property['title']); ?>"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                        <?php else: ?>
                            <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                <i class="fas fa-home text-6xl text-gray-400"></i>
                            </div>
                        <?php endif; ?>

                        <!-- Featured Badge -->
                        <?php if ($property['is_featured']): ?>
                            <div class="absolute top-4 left-4 bg-yellow-400 text-gray-900 px-3 py-1 rounded-full text-xs font-bold">
                                <i class="fas fa-star mr-1"></i> Featured
                            </div>
                        <?php endif; ?>

                        <!-- Status Badge -->
                        <?php if ($property['status'] === 'rented'): ?>
                            <div class="absolute top-4 right-4 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                                Rented
                            </div>
                        <?php endif; ?>
                    </a>

                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                            <?php echo htmlspecialchars($property['title']); ?>
                        </h3>

                        <p class="text-gray-600 mb-4">
                            <i class="fas fa-map-marker-alt text-blue-600 mr-1"></i>
                            <?php echo htmlspecialchars($property['city'] . ', ' . $property['country']); ?>
                        </p>

                        <!-- Property Features -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-sm text-gray-600">
                                <i class="fas fa-bed mr-1"></i> <?php echo $property['bedrooms']; ?> Beds
                                <i class="fas fa-bath ml-3 mr-1"></i> <?php echo $property['bathrooms']; ?> Baths
                            </div>
                            <div class="text-2xl font-bold text-blue-600">
                                <?php echo formatPrice($property['price_per_month']); ?><span class="text-sm text-gray-500">/night</span>
                            </div>
                        </div>

                        <!-- View Details Button -->
                        <a href="/public/property.php?id=<?php echo $property['id']; ?>"
                           class="block w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-2 rounded-lg font-semibold hover:shadow-lg transition-all text-center">
                            View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($properties) >= 12): ?>
            <div class="text-center mt-12">
                <a href="/public/properties.php" class="inline-block bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition-all">
                    View All Properties
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Call to Action - List Your Property -->
<div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="max-w-3xl mx-auto">
            <i class="fas fa-building text-6xl mb-6"></i>
            <h2 class="text-4xl font-bold mb-4">Have a Property to List?</h2>
            <p class="text-xl text-blue-100 mb-8">
                Join our network of property managers and start earning today
            </p>
            <a href="/public/list-property.php" class="inline-block bg-white text-purple-600 px-8 py-4 rounded-xl font-bold text-lg hover:shadow-2xl transition-all">
                <i class="fas fa-plus-circle mr-2"></i>
                Apply to List Your Property
            </a>
            <p class="mt-4 text-sm text-blue-100">
                Simple application process • Fast approval • Start listing in 24 hours
            </p>
        </div>
    </div>
</div>

<!-- About Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center max-w-3xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">About CirclePoint Homes</h2>
        <p class="text-lg text-gray-600 mb-8">
            We connect travelers with quality homes around the world. Whether you're looking for a short-term rental or a long-term stay, we have the perfect property for you.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
            <div class="text-center">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-blue-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Verified Properties</h3>
                <p class="text-gray-600 text-sm">All properties are verified by our team</p>
            </div>
            <div class="text-center">
                <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-headphones-alt text-purple-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">24/7 Support</h3>
                <p class="text-gray-600 text-sm">We're here to help anytime you need</p>
            </div>
            <div class="text-center">
                <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-calendar-check text-green-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Easy Booking</h3>
                <p class="text-gray-600 text-sm">Simple and secure booking process</p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
