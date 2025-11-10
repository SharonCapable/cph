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
    'SELECT * FROM property_images WHERE property_id = ? ORDER BY sort_order ASC',
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
            <?php endif; ?>
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

            <div class="space-y-3">
                <a href="mailto:<?php echo htmlspecialchars($booking['user_email']); ?>"
                   class="block w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-center font-medium">
                    <i class="fas fa-envelope mr-2"></i>
                    Send Email
                </a>

                <?php if ($booking['user_phone']): ?>
                    <a href="tel:<?php echo htmlspecialchars($booking['user_phone']); ?>"
                       class="block w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 text-center font-medium">
                        <i class="fas fa-phone mr-2"></i>
                        Call Guest
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
