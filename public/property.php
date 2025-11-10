<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Get property ID
$propertyId = $_GET['id'] ?? null;

if (!$propertyId) {
    header('Location: /public/index.php');
    exit;
}

// Get property details
$property = db()->fetchOne(
    'SELECT p.*, u.email as manager_email, u.first_name, u.last_name, u.phone as manager_phone
     FROM properties p
     JOIN users u ON p.manager_id = u.id
     WHERE p.id = ?',
    [$propertyId]
);

if (!$property) {
    header('Location: /public/index.php');
    exit;
}

// Get property images
$images = db()->fetchAll(
    'SELECT * FROM property_images
     WHERE property_id = ?
     ORDER BY display_order ASC',
    [$propertyId]
);

// Increment view count
db()->query('UPDATE properties SET views = views + 1 WHERE id = ?', [$propertyId]);

// Parse amenities
$amenities = json_decode($property['amenities'] ?? '[]', true) ?: [];

$pageTitle = $property['title'];

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <a href="/public/index.php" class="text-blue-600 hover:underline">Home</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-600"><?php echo htmlspecialchars($property['title']); ?></span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Image Gallery -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
                <?php if (!empty($images)): ?>
                    <!-- Main Image - Clickable -->
                    <div class="relative h-96 bg-gray-200 cursor-pointer" onclick="openGallery(0)">
                        <img src="<?php echo htmlspecialchars($images[0]['image_path']); ?>"
                             alt="<?php echo htmlspecialchars($property['title']); ?>"
                             class="w-full h-full object-cover hover:opacity-95 transition-opacity"
                             id="mainImage">

                        <!-- Click to expand hint -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-20 transition-all flex items-center justify-center">
                            <div class="bg-white bg-opacity-90 px-4 py-2 rounded-lg opacity-0 hover:opacity-100 transition-opacity">
                                <i class="fas fa-search-plus mr-2"></i>
                                Click to view full gallery
                            </div>
                        </div>

                        <!-- Image Counter -->
                        <div class="absolute bottom-4 right-4 bg-black bg-opacity-50 text-white px-3 py-1 rounded-lg text-sm">
                            <i class="fas fa-images mr-1"></i>
                            <span id="currentImageIndex">1</span> / <?php echo count($images); ?>
                        </div>
                    </div>

                    <!-- Thumbnail Gallery -->
                    <?php if (count($images) > 1): ?>
                        <div class="p-4 bg-gray-50 flex gap-2 overflow-x-auto">
                            <?php foreach ($images as $index => $image): ?>
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>"
                                     alt="<?php echo htmlspecialchars($image['caption'] ?: 'Property image'); ?>"
                                     class="w-24 h-24 object-cover rounded-lg cursor-pointer hover:opacity-75 transition-opacity <?php echo $index === 0 ? 'ring-2 ring-blue-600' : ''; ?>"
                                     onclick="openGallery(<?php echo $index; ?>)">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php elseif ($property['featured_image']): ?>
                    <div class="h-96 bg-gray-200 cursor-pointer" onclick="openGallery(0)">
                        <img src="<?php echo htmlspecialchars($property['featured_image']); ?>"
                             alt="<?php echo htmlspecialchars($property['title']); ?>"
                             class="w-full h-full object-cover">
                    </div>
                <?php else: ?>
                    <div class="h-96 bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                        <i class="fas fa-home text-8xl text-gray-400"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Property Details -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($property['title']); ?></h1>
                        <p class="text-xl text-gray-600">
                            <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                            <?php echo htmlspecialchars($property['address'] . ', ' . $property['city'] . ', ' . $property['country']); ?>
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-4xl font-bold text-blue-600"><?php echo formatPrice($property['price_per_month']); ?></div>
                        <div class="text-gray-500">per night</div>
                    </div>
                </div>

                <!-- Property Features -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 pb-8 border-b">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <i class="fas fa-bed text-blue-600 text-2xl mb-2"></i>
                        <div class="font-bold text-gray-900"><?php echo $property['bedrooms']; ?></div>
                        <div class="text-sm text-gray-600">Bedrooms</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <i class="fas fa-bath text-blue-600 text-2xl mb-2"></i>
                        <div class="font-bold text-gray-900"><?php echo $property['bathrooms']; ?></div>
                        <div class="text-sm text-gray-600">Bathrooms</div>
                    </div>
                    <?php if ($property['square_feet']): ?>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-ruler-combined text-blue-600 text-2xl mb-2"></i>
                            <div class="font-bold text-gray-900"><?php echo number_format($property['square_feet']); ?></div>
                            <div class="text-sm text-gray-600">Sq Ft</div>
                        </div>
                    <?php endif; ?>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <i class="fas fa-home text-blue-600 text-2xl mb-2"></i>
                        <div class="font-bold text-gray-900"><?php echo ucfirst($property['property_type']); ?></div>
                        <div class="text-sm text-gray-600">Type</div>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">About This Property</h2>
                    <p class="text-gray-700 leading-relaxed whitespace-pre-line"><?php echo htmlspecialchars($property['description']); ?></p>
                </div>

                <!-- Additional Features -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Features</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <?php if ($property['furnished']): ?>
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-couch text-blue-600 mr-3"></i>
                                <span>Furnished</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($property['pets_allowed']): ?>
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-paw text-blue-600 mr-3"></i>
                                <span>Pets Allowed</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($property['parking']): ?>
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-parking text-blue-600 mr-3"></i>
                                <span>Parking Available</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Amenities -->
                <?php if (!empty($amenities)): ?>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Amenities</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php foreach ($amenities as $amenity): ?>
                                <div class="flex items-center text-gray-700">
                                    <i class="fas fa-check-circle text-green-600 mr-3"></i>
                                    <span><?php echo htmlspecialchars($amenity); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Booking Request Form -->
            <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-24">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Book This Property</h3>

                <?php if (!Auth::check()): ?>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            <a href="/public/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="font-semibold underline">Sign in</a>
                            or
                            <a href="/public/signup.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="font-semibold underline">create an account</a>
                            to book this property.
                        </p>
                    </div>
                <?php else: ?>
                    <form action="/api/booking.php" method="POST" class="space-y-6" id="bookingForm">
                        <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">

                        <!-- Basic Booking Info -->
                        <div class="space-y-4">
                            <h4 class="font-bold text-gray-900 flex items-center border-b pb-2">
                                <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                                Booking Dates
                            </h4>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Date *</label>
                                <input type="date" name="check_in" id="checkInDate" required
                                       min="<?php echo date('Y-m-d'); ?>"
                                       onchange="updateCheckoutMin()"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Date *</label>
                                <input type="date" name="check_out" id="checkOutDate" required
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Number of Guests *</label>
                                <input type="number" name="guests" required min="1" value="1"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>
                        </div>

                        <!-- Guest Information -->
                        <div class="space-y-4">
                            <h4 class="font-bold text-gray-900 flex items-center border-b pb-2">
                                <i class="fas fa-user text-blue-600 mr-2"></i>
                                Guest Information
                            </h4>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                <input type="text" name="guest_full_name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                <input type="email" name="guest_email" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                                <input type="tel" name="phone" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                                <input type="date" name="guest_date_of_birth" required
                                       max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                                <select name="guest_gender" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nationality *</label>
                                <input type="text" name="guest_nationality" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Passport/ID Number *</label>
                                <input type="text" name="guest_passport_number" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Home Address *</label>
                                <textarea name="guest_address" required rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                            </div>
                        </div>

                        <!-- Travel Information -->
                        <div class="space-y-4">
                            <h4 class="font-bold text-gray-900 flex items-center border-b pb-2">
                                <i class="fas fa-plane text-blue-600 mr-2"></i>
                                Travel Information
                            </h4>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Purpose of Visit *</label>
                                <textarea name="purpose_of_visit" required rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                                          placeholder="e.g., Tourism, Business, Family Visit"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Arrival Date *</label>
                                <input type="date" name="arrival_date" required
                                       min="<?php echo date('Y-m-d'); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Arrival Flight (Optional)</label>
                                <input type="text" name="arrival_flight"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                                       placeholder="e.g., BA123">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Departure Date *</label>
                                <input type="date" name="departure_date" required
                                       min="<?php echo date('Y-m-d'); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Departure Flight (Optional)</label>
                                <input type="text" name="departure_flight"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                                       placeholder="e.g., BA456">
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="space-y-4">
                            <h4 class="font-bold text-gray-900 flex items-center border-b pb-2">
                                <i class="fas fa-phone-alt text-blue-600 mr-2"></i>
                                Emergency Contact
                            </h4>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Name *</label>
                                <input type="text" name="emergency_contact_name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Relationship *</label>
                                <input type="text" name="emergency_contact_relationship" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                                       placeholder="e.g., Parent, Spouse, Sibling">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone *</label>
                                <input type="tel" name="emergency_contact_phone" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                                <input type="email" name="emergency_contact_email"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>
                        </div>

                        <!-- Visa Requirements -->
                        <div class="space-y-4">
                            <h4 class="font-bold text-gray-900 flex items-center border-b pb-2">
                                <i class="fas fa-passport text-blue-600 mr-2"></i>
                                Visa Requirements
                            </h4>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" name="is_foreigner" value="1" id="isForeignerCheck"
                                           onchange="toggleVisaLetter()"
                                           class="mt-1 w-5 h-5 text-blue-600 rounded">
                                    <span class="ml-3 text-sm text-gray-700">
                                        I am a foreigner and may need visa support documents
                                        <span class="block text-xs text-gray-500 mt-1">Check this if you need an invitation letter for visa purposes</span>
                                    </span>
                                </label>
                            </div>

                            <div id="visaLetterOption" class="hidden bg-blue-50 rounded-lg p-4">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" name="requires_visa_letter" value="1"
                                           class="mt-1 w-5 h-5 text-blue-600 rounded">
                                    <span class="ml-3 text-sm text-gray-700">
                                        I need a visa invitation letter
                                        <span class="block text-xs text-gray-500 mt-1">An official invitation letter will be generated for your visa application</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Declaration -->
                        <div class="space-y-4">
                            <h4 class="font-bold text-gray-900 flex items-center border-b pb-2">
                                <i class="fas fa-file-signature text-blue-600 mr-2"></i>
                                Declaration
                            </h4>

                            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 space-y-2">
                                <p><strong>I hereby declare that:</strong></p>
                                <ul class="list-disc list-inside space-y-1 ml-2">
                                    <li>All information provided above is true and accurate</li>
                                    <li>I agree to abide by the property rules and regulations</li>
                                    <li>I understand the payment terms and cancellation policy</li>
                                    <li>I will be responsible for any damages during my stay</li>
                                </ul>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Digital Signature (Type Your Full Name) *</label>
                                <input type="text" name="signature_data" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm font-serif"
                                       placeholder="Type your full name as signature">
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" name="terms_accepted" value="1" required
                                           class="mt-1 w-5 h-5 text-blue-600 rounded">
                                    <span class="ml-3 text-sm text-gray-700">
                                        I accept the terms and conditions and confirm all information is accurate *
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Additional Message -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Additional Comments (Optional)</label>
                            <textarea name="message" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                                      placeholder="Any special requests or questions?"></textarea>
                        </div>

                        <button type="submit"
                                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 rounded-lg font-bold text-lg hover:shadow-lg transition-all">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Submit Booking Request
                        </button>
                    </form>
                <?php endif; ?>

                <!-- Contact via WhatsApp -->
                <div class="mt-6 pt-6 border-t">
                    <p class="text-sm font-medium text-gray-700 mb-2">Need more information?</p>
                    <a href="<?php echo getWhatsAppLink($property['title'], $property['id']); ?>"
                       target="_blank"
                       class="block w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-lg font-semibold text-center transition-all">
                        <i class="fab fa-whatsapp mr-2"></i>
                        Message Property Owner
                    </a>
                    <p class="text-xs text-gray-500 text-center mt-2">Get instant answers about availability, amenities, and more</p>
                </div>

                <!-- Property Info -->
                <div class="mt-6 pt-6 border-t text-sm text-gray-600 space-y-2">
                    <div class="flex items-center">
                        <i class="fas fa-eye mr-2"></i>
                        <span><?php echo number_format($property['views']); ?> views</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-calendar mr-2"></i>
                        <span>Listed <?php echo formatDate($property['created_at']); ?></span>
                    </div>
                    <?php if ($property['security_deposit']): ?>
                        <div class="flex items-center">
                            <i class="fas fa-broom mr-2"></i>
                            <span>Cleaning Fee: <?php echo formatPrice($property['security_deposit']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Full-Screen Image Gallery Modal -->
<div id="galleryModal" class="hidden fixed inset-0 bg-black bg-opacity-95 z-50 flex items-center justify-center">
    <div class="relative w-full h-full flex items-center justify-center">
        <!-- Close Button -->
        <button onclick="closeGallery()" class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300 z-10">
            <i class="fas fa-times"></i>
        </button>

        <!-- View Details Button -->
        <a href="#details" onclick="closeGalleryAndStay()" class="absolute top-4 left-4 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold z-10">
            <i class="fas fa-info-circle mr-2"></i>
            View Details
        </a>

        <!-- Previous Button -->
        <button onclick="previousImage()" class="absolute left-4 text-white text-5xl hover:text-gray-300 z-10">
            <i class="fas fa-chevron-left"></i>
        </button>

        <!-- Image -->
        <div class="w-full h-full flex items-center justify-center p-16">
            <img id="galleryImage" src="" alt="Property image" class="max-w-full max-h-full object-contain">
        </div>

        <!-- Next Button -->
        <button onclick="nextImage()" class="absolute right-4 text-white text-5xl hover:text-gray-300 z-10">
            <i class="fas fa-chevron-right"></i>
        </button>

        <!-- Image Counter -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-70 text-white px-6 py-3 rounded-lg text-lg">
            <span id="galleryCounter">1</span> / <span id="galleryTotal"><?php echo count($images); ?></span>
        </div>

        <!-- Thumbnail Strip -->
        <?php if (count($images) > 1): ?>
            <div class="absolute bottom-24 left-1/2 transform -translate-x-1/2 flex gap-2 max-w-screen-lg overflow-x-auto px-4">
                <?php foreach ($images as $index => $image): ?>
                    <img src="<?php echo htmlspecialchars($image['image_path']); ?>"
                         class="w-16 h-16 object-cover rounded cursor-pointer opacity-60 hover:opacity-100 transition-opacity gallery-thumb"
                         onclick="goToImage(<?php echo $index; ?>)"
                         data-index="<?php echo $index; ?>">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Gallery images
const galleryImages = <?php echo json_encode(array_map(fn($img) => $img['image_path'], $images)); ?>;
let currentGalleryIndex = 0;

function openGallery(index) {
    currentGalleryIndex = index;
    document.getElementById('galleryModal').classList.remove('hidden');
    updateGalleryImage();
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeGallery() {
    document.getElementById('galleryModal').classList.add('hidden');
    document.body.style.overflow = 'auto';

    // If we came from homepage (gallery=1), go back
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('gallery') === '1') {
        window.history.back();
    }
}

function closeGalleryAndStay() {
    // Close gallery but stay on this page (for View Details button)
    document.getElementById('galleryModal').classList.add('hidden');
    document.body.style.overflow = 'auto';

    // Remove gallery parameter from URL without reload
    const url = new URL(window.location);
    url.searchParams.delete('gallery');
    window.history.replaceState({}, '', url);
}

function updateGalleryImage() {
    document.getElementById('galleryImage').src = galleryImages[currentGalleryIndex];
    document.getElementById('galleryCounter').textContent = currentGalleryIndex + 1;

    // Update thumbnail highlights
    document.querySelectorAll('.gallery-thumb').forEach((thumb, i) => {
        if (i === currentGalleryIndex) {
            thumb.classList.add('ring-2', 'ring-blue-500', 'opacity-100');
            thumb.classList.remove('opacity-60');
        } else {
            thumb.classList.remove('ring-2', 'ring-blue-500', 'opacity-100');
            thumb.classList.add('opacity-60');
        }
    });
}

function nextImage() {
    currentGalleryIndex = (currentGalleryIndex + 1) % galleryImages.length;
    updateGalleryImage();
}

function previousImage() {
    currentGalleryIndex = (currentGalleryIndex - 1 + galleryImages.length) % galleryImages.length;
    updateGalleryImage();
}

function goToImage(index) {
    currentGalleryIndex = index;
    updateGalleryImage();
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('galleryModal');
    if (!modal.classList.contains('hidden')) {
        if (e.key === 'ArrowRight') nextImage();
        if (e.key === 'ArrowLeft') previousImage();
        if (e.key === 'Escape') closeGallery();
    }
});

// Auto-open gallery if ?gallery=1 in URL
window.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('gallery') === '1' && galleryImages.length > 0) {
        openGallery(0);
    }
});

// Update checkout minimum date when check-in changes
function updateCheckoutMin() {
    const checkInInput = document.getElementById('checkInDate');
    const checkOutInput = document.getElementById('checkOutDate');

    if (checkInInput.value) {
        // Set checkout minimum to 1 day after check-in
        const checkInDate = new Date(checkInInput.value);
        checkInDate.setDate(checkInDate.getDate() + 1);
        const minCheckOut = checkInDate.toISOString().split('T')[0];
        checkOutInput.min = minCheckOut;

        // If current checkout is before the new minimum, update it
        if (checkOutInput.value && checkOutInput.value <= checkInInput.value) {
            checkOutInput.value = minCheckOut;
        }
    }
}

// Toggle visa letter option
function toggleVisaLetter() {
    const isForeignerCheck = document.getElementById('isForeignerCheck');
    const visaLetterOption = document.getElementById('visaLetterOption');

    if (isForeignerCheck && visaLetterOption) {
        if (isForeignerCheck.checked) {
            visaLetterOption.classList.remove('hidden');
        } else {
            visaLetterOption.classList.add('hidden');
        }
    }
}
</script>

<?php include '../includes/footer.php'; ?>
