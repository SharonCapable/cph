<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$user = Auth::requireRole('admin');
$isSuperAdmin = $user['role'] === 'super_admin';

// Get booking ID
$bookingId = $_GET['id'] ?? null;
if (!$bookingId) {
    redirect('/admin/bookings.php');
}

// Get booking details
$booking = db()->fetchOne(
    'SELECT b.*,
     p.title as property_title, p.description, p.address, p.city, p.state, p.country,
     p.bedrooms, p.bathrooms, p.price_per_month as price_per_night, p.featured_image,
     u.email as user_email, u.first_name, u.last_name, u.phone as user_phone
     FROM bookings b
     JOIN properties p ON b.property_id = p.id
     JOIN users u ON b.user_id = u.id
     WHERE b.id = ?',
    [$bookingId]
);

if (!$booking) {
    setFlash('error', 'Booking not found.');
    redirect('/admin/bookings.php');
}

// Check permissions - property managers can only view their own bookings
if (!$isSuperAdmin) {
    $propertyManager = db()->fetchOne('SELECT manager_id FROM properties WHERE id = ?', [$booking['property_id']]);
    if ($propertyManager['manager_id'] !== $user['id']) {
        setFlash('error', 'You do not have permission to view this booking.');
        redirect('/admin/bookings.php');
    }
}

// Get property images
$images = db()->fetchAll(
    'SELECT * FROM property_images WHERE property_id = ? ORDER BY display_order ASC',
    [$booking['property_id']]
);

// Calculate nights
$checkInDate = new DateTime($booking['check_in']);
$checkOutDate = new DateTime($booking['check_out']);
$interval = $checkInDate->diff($checkOutDate);
$nights = $interval->days;

$pageTitle = 'Booking Details';
include 'includes/header.php';
?>

<div class="mb-6">
    <a href="/admin/bookings.php" class="text-blue-600 hover:text-blue-700">
        <i class="fas fa-arrow-left mr-2"></i>Back to Bookings
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Column: Property Info -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Property Images & Info -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <!-- Featured Image -->
            <?php if ($booking['featured_image']): ?>
                <div class="h-64 bg-gray-200">
                    <img src="<?php echo htmlspecialchars($booking['featured_image']); ?>"
                         alt="<?php echo htmlspecialchars($booking['property_title']); ?>"
                         class="w-full h-full object-cover">
                </div>
            <?php endif; ?>

            <!-- Property Gallery -->
            <?php if (!empty($images)): ?>
                <div class="p-4 bg-gray-50 border-t">
                    <div class="grid grid-cols-4 gap-2">
                        <?php foreach (array_slice($images, 0, 4) as $img): ?>
                            <img src="<?php echo htmlspecialchars($img['image_path']); ?>"
                                 alt="Property image"
                                 class="w-full h-20 object-cover rounded cursor-pointer hover:opacity-75"
                                 onclick="window.open('/public/property.php?id=<?php echo $booking['property_id']; ?>&gallery=1', '_blank')">
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($images) > 4): ?>
                        <a href="/public/property.php?id=<?php echo $booking['property_id']; ?>&gallery=1"
                           target="_blank"
                           class="text-blue-600 hover:text-blue-700 text-sm mt-2 inline-block">
                            <i class="fas fa-images mr-1"></i>
                            View all <?php echo count($images); ?> photos
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    <?php echo htmlspecialchars($booking['property_title']); ?>
                </h1>
                <p class="text-gray-600 mb-4">
                    <i class="fas fa-map-marker-alt mr-1"></i>
                    <?php echo htmlspecialchars($booking['address'] . ', ' . $booking['city'] . ', ' . $booking['country']); ?>
                </p>

                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <i class="fas fa-bed text-blue-600 text-xl mb-1"></i>
                        <div class="text-sm font-semibold"><?php echo $booking['bedrooms']; ?> Bedrooms</div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <i class="fas fa-bath text-blue-600 text-xl mb-1"></i>
                        <div class="text-sm font-semibold"><?php echo $booking['bathrooms']; ?> Bathrooms</div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <i class="fas fa-dollar-sign text-blue-600 text-xl mb-1"></i>
                        <div class="text-sm font-semibold"><?php echo formatPrice($booking['price_per_night']); ?>/night</div>
                    </div>
                </div>

                <a href="/public/property.php?id=<?php echo $booking['property_id']; ?>"
                   target="_blank"
                   class="text-blue-600 hover:text-blue-700 text-sm">
                    <i class="fas fa-external-link-alt mr-1"></i>
                    View full property details
                </a>
            </div>
        </div>

        <!-- Guest Message -->
        <?php if ($booking['message']): ?>
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-3">
                    <i class="fas fa-comment-alt mr-2 text-blue-600"></i>
                    Guest Message
                </h2>
                <div class="bg-gray-50 rounded-lg p-4 text-gray-700">
                    <?php echo nl2br(htmlspecialchars($booking['message'])); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Booking Info -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Booking Status -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Booking Status</h2>

            <div class="mb-4">
                <span class="px-4 py-2 inline-flex text-sm font-semibold rounded-full <?php
                    echo $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-800' :
                        ($booking['status'] === 'pending' ? 'bg-orange-100 text-orange-800' :
                        ($booking['status'] === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'));
                ?>">
                    <?php echo ucfirst($booking['status']); ?>
                </span>
            </div>

            <div class="text-sm text-gray-600">
                <div class="mb-2">
                    <strong>Booking ID:</strong> <?php echo htmlspecialchars($booking['id']); ?>
                </div>
                <div>
                    <strong>Requested:</strong> <?php echo formatDate($booking['created_at']); ?>
                </div>
            </div>

            <?php if ($booking['status'] === 'pending'): ?>
                <div class="mt-4 p-3 bg-orange-50 border border-orange-200 rounded-lg text-sm text-orange-800">
                    <i class="fas fa-clock mr-1"></i>
                    This booking request is awaiting your response.
                </div>

                <!-- Action Buttons for Pending Bookings -->
                <div class="mt-4 space-y-2">
                    <form method="POST" action="/api/booking-action.php" onsubmit="return confirm('Approve this booking?');">
                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                            <i class="fas fa-check mr-2"></i>
                            Approve Booking
                        </button>
                    </form>

                    <form method="POST" action="/api/booking-action.php" onsubmit="return confirm('Reject this booking? This cannot be undone.');">
                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                            <i class="fas fa-times mr-2"></i>
                            Reject Booking
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Delete Button (for all statuses) -->
            <div class="mt-4 pt-4 border-t">
                <form method="POST" action="/api/booking-action.php" onsubmit="return confirm('Delete this booking permanently? This cannot be undone!');">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="w-full px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-medium text-sm">
                        <i class="fas fa-trash mr-2"></i>
                        Delete Booking
                    </button>
                </form>
            </div>
        </div>

        <!-- Guest Information -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Guest Information</h2>

            <div class="space-y-3">
                <div>
                    <div class="text-sm text-gray-500">Name</div>
                    <div class="font-semibold text-gray-900">
                        <?php echo htmlspecialchars(($booking['first_name'] ?: '') . ' ' . ($booking['last_name'] ?: '') ?: 'No name provided'); ?>
                    </div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Email</div>
                    <div class="text-gray-900">
                        <a href="mailto:<?php echo htmlspecialchars($booking['user_email']); ?>"
                           class="text-blue-600 hover:text-blue-700">
                            <?php echo htmlspecialchars($booking['user_email']); ?>
                        </a>
                    </div>
                </div>

                <?php if ($booking['user_phone']): ?>
                    <div>
                        <div class="text-sm text-gray-500">Phone</div>
                        <div class="text-gray-900">
                            <a href="tel:<?php echo htmlspecialchars($booking['user_phone']); ?>"
                               class="text-blue-600 hover:text-blue-700">
                                <?php echo htmlspecialchars($booking['user_phone']); ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <div>
                    <div class="text-sm text-gray-500">Number of Guests</div>
                    <div class="font-semibold text-gray-900">
                        <?php echo $booking['guests']; ?> guest<?php echo $booking['guests'] > 1 ? 's' : ''; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stay Details -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Stay Details</h2>

            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <div class="text-xs text-gray-500">Check-in</div>
                        <div class="font-semibold text-gray-900">
                            <?php echo date('F j, Y', strtotime($booking['check_in'])); ?>
                        </div>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400"></i>
                    <div>
                        <div class="text-xs text-gray-500">Check-out</div>
                        <div class="font-semibold text-gray-900">
                            <?php echo date('F j, Y', strtotime($booking['check_out'])); ?>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-blue-50 rounded-lg text-center">
                    <div class="text-2xl font-bold text-blue-600"><?php echo $nights; ?></div>
                    <div class="text-sm text-blue-800">Night<?php echo $nights > 1 ? 's' : ''; ?></div>
                </div>

                <div class="border-t pt-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600"><?php echo formatPrice($booking['price_per_night']); ?> × <?php echo $nights; ?> nights</span>
                        <span class="text-gray-900"><?php echo formatPrice($booking['price_per_night'] * $nights); ?></span>
                    </div>
                    <div class="flex justify-between font-bold text-lg border-t pt-2">
                        <span>Total</span>
                        <span class="text-blue-600"><?php echo formatPrice($booking['total_price']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Actions -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Contact Guest</h2>

            <p class="text-xs text-gray-600 mb-3">
                <i class="fas fa-info-circle mr-1"></i>
                These buttons will open your email client or phone app
            </p>

            <div class="space-y-3">
                <a href="mailto:<?php echo htmlspecialchars($booking['user_email']); ?>"
                   class="block w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-center font-medium">
                    <i class="fas fa-envelope mr-2"></i>
                    Open Email Client
                </a>

                <?php if ($booking['user_phone']): ?>
                    <a href="tel:<?php echo htmlspecialchars($booking['user_phone']); ?>"
                       class="block w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 text-center font-medium">
                        <i class="fas fa-phone mr-2"></i>
                        Open Phone
                    </a>
                <?php endif; ?>

                <a href="<?php echo getWhatsAppLink($booking['property_title'], $booking['property_id']); ?>"
                   target="_blank"
                   class="flex items-center justify-center w-full px-4 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 font-medium">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    Message on WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
