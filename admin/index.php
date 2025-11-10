<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin access (super_admin or admin)
$user = Auth::requireRole('admin');

$pageTitle = 'Dashboard';
$isSuperAdmin = $user['role'] === 'super_admin';

// Get statistics based on role
if ($isSuperAdmin) {
    $stats = [
        'total_properties' => db()->fetchOne('SELECT COUNT(*) as count FROM properties')['count'] ?? 0,
        'active_properties' => db()->fetchOne('SELECT COUNT(*) as count FROM properties WHERE status = "available"')['count'] ?? 0,
        'total_bookings' => db()->fetchOne('SELECT COUNT(*) as count FROM bookings')['count'] ?? 0,
        'pending_applications' => db()->fetchOne('SELECT COUNT(*) as count FROM property_manager_applications WHERE status = "pending"')['count'] ?? 0,
        'total_users' => db()->fetchOne('SELECT COUNT(*) as count FROM users')['count'] ?? 0,
    ];
} else {
    // Property manager sees only their own stats
    $stats = [
        'total_properties' => db()->fetchOne('SELECT COUNT(*) as count FROM properties WHERE manager_id = ?', [$user['id']])['count'] ?? 0,
        'active_properties' => db()->fetchOne('SELECT COUNT(*) as count FROM properties WHERE manager_id = ? AND status = "available"', [$user['id']])['count'] ?? 0,
        'total_bookings' => db()->fetchOne('SELECT COUNT(*) as count FROM bookings b JOIN properties p ON b.property_id = p.id WHERE p.manager_id = ?', [$user['id']])['count'] ?? 0,
        'pending_applications' => 0, // Property managers don't see applications
        'total_users' => 0, // Property managers don't see user count
    ];
}

// Recent bookings (filtered by role)
if ($isSuperAdmin) {
    $recentBookings = db()->fetchAll(
        'SELECT b.*, p.title as property_title, u.email as user_email
         FROM bookings b
         JOIN properties p ON b.property_id = p.id
         JOIN users u ON b.user_id = u.id
         ORDER BY b.created_at DESC
         LIMIT 5'
    );

    // Recent applications (only super admin sees these)
    $recentApplications = db()->fetchAll(
        'SELECT a.*, u.email, u.first_name, u.last_name
         FROM property_manager_applications a
         JOIN users u ON a.user_id = u.id
         WHERE a.status = "pending"
         ORDER BY a.created_at DESC
         LIMIT 5'
    );
} else {
    // Property manager sees only bookings for their properties
    $recentBookings = db()->fetchAll(
        'SELECT b.*, p.title as property_title, u.email as user_email
         FROM bookings b
         JOIN properties p ON b.property_id = p.id
         JOIN users u ON b.user_id = u.id
         WHERE p.manager_id = ?
         ORDER BY b.created_at DESC
         LIMIT 5',
        [$user['id']]
    );

    $recentApplications = []; // Property managers don't see applications
}

include 'includes/header.php';
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?php echo $isSuperAdmin ? '5' : '4'; ?> gap-6 mb-8">
    <!-- Total Properties -->
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Properties</p>
                <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $stats['total_properties']; ?></p>
                <p class="text-xs text-gray-400 mt-1">All statuses</p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-home text-blue-600 text-2xl"></i>
            </div>
        </div>
        <a href="/admin/properties.php" class="text-blue-600 text-sm font-medium mt-4 inline-block hover:underline">
            View all →
        </a>
    </div>

    <!-- Available Properties -->
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Available to Rent</p>
                <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $stats['active_properties']; ?></p>
                <p class="text-xs text-gray-400 mt-1">Ready for booking</p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Bookings -->
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Bookings</p>
                <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $stats['total_bookings']; ?></p>
                <p class="text-xs text-gray-400 mt-1">All time</p>
            </div>
            <div class="bg-purple-100 rounded-full p-3">
                <i class="fas fa-calendar-check text-purple-600 text-2xl"></i>
            </div>
        </div>
        <a href="/admin/bookings.php" class="text-purple-600 text-sm font-medium mt-4 inline-block hover:underline">
            View all →
        </a>
    </div>

    <?php if ($isSuperAdmin): ?>
        <!-- Total Users (Super Admin Only) -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-indigo-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $stats['total_users']; ?></p>
                    <p class="text-xs text-gray-400 mt-1">All accounts</p>
                </div>
                <div class="bg-indigo-100 rounded-full p-3">
                    <i class="fas fa-users text-indigo-600 text-2xl"></i>
                </div>
            </div>
            <a href="/admin/users.php" class="text-indigo-600 text-sm font-medium mt-4 inline-block hover:underline">
                Manage →
            </a>
        </div>

        <!-- Pending Applications (Super Admin Only) -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Pending Applications</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $stats['pending_applications']; ?></p>
                    <p class="text-xs text-gray-400 mt-1">Needs review</p>
                </div>
                <div class="bg-orange-100 rounded-full p-3">
                    <i class="fas fa-user-clock text-orange-600 text-2xl"></i>
                </div>
            </div>
            <a href="/admin/applications.php" class="text-orange-600 text-sm font-medium mt-4 inline-block hover:underline">
                Review →
            </a>
        </div>
    <?php else: ?>
        <!-- Property Views (Property Manager) -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-indigo-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Views</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">
                        <?php echo db()->fetchOne('SELECT SUM(views) as total FROM properties WHERE manager_id = ?', [$user['id']])['total'] ?? 0; ?>
                    </p>
                </div>
                <div class="bg-indigo-100 rounded-full p-3">
                    <i class="fas fa-eye text-indigo-600 text-2xl"></i>
                </div>
            </div>
            <p class="text-indigo-600 text-sm font-medium mt-4">
                Across all your properties
            </p>
        </div>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Bookings -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">Recent Bookings</h2>
            <a href="/admin/bookings.php" class="text-blue-600 text-sm font-medium hover:underline">View all</a>
        </div>

        <?php if (empty($recentBookings)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-4xl mb-3"></i>
                <p>No bookings yet</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($recentBookings as $booking): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($booking['property_title']); ?></h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-user mr-1"></i>
                                    <?php echo htmlspecialchars($booking['user_email']); ?>
                                </p>
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-calendar mr-1"></i>
                                    <?php echo date('M d', strtotime($booking['check_in'])); ?> - <?php echo date('M d, Y', strtotime($booking['check_out'])); ?>
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium <?php
                                echo $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-800' :
                                    ($booking['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800');
                            ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($isSuperAdmin): ?>
        <!-- Pending Applications (Super Admin Only) -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Pending Applications</h2>
                <a href="/admin/applications.php" class="text-blue-600 text-sm font-medium hover:underline">View all</a>
            </div>

            <?php if (empty($recentApplications)): ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-3"></i>
                    <p>No pending applications</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentApplications as $app): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($app['company_name'] ?: ($app['first_name'] . ' ' . $app['last_name'])); ?>
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <i class="fas fa-envelope mr-1"></i>
                                        <?php echo htmlspecialchars($app['email']); ?>
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <i class="fas fa-briefcase mr-1"></i>
                                        <?php echo ucfirst($app['business_type']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-2">
                                        Applied <?php echo formatDate($app['created_at']); ?>
                                    </p>
                                </div>
                                <a href="/admin/applications.php?id=<?php echo $app['id']; ?>"
                                   class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                    Review →
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Quick Tips for Property Managers -->
        <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl shadow-md p-6 border border-blue-100">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                Property Manager Tips
            </h2>
            <ul class="space-y-3 text-gray-700">
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-600 mr-2 mt-1"></i>
                    <span>Add high-quality photos to attract more bookings</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-600 mr-2 mt-1"></i>
                    <span>Respond quickly to booking requests for better reviews</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-600 mr-2 mt-1"></i>
                    <span>Keep your property details up-to-date</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-600 mr-2 mt-1"></i>
                    <span>Mark properties as "Rented" when unavailable</span>
                </li>
            </ul>
        </div>
    <?php endif; ?>
</div>

<!-- Quick Actions -->
<div class="mt-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-lg p-6 text-white">
    <h2 class="text-2xl font-bold mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="/admin/properties/create.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-4 transition-all">
            <i class="fas fa-plus-circle text-2xl mb-2"></i>
            <p class="font-semibold">Add New Property</p>
        </a>
        <a href="/admin/users.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-4 transition-all">
            <i class="fas fa-users text-2xl mb-2"></i>
            <p class="font-semibold">Manage Users</p>
        </a>
        <a href="/admin/applications.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-4 transition-all">
            <i class="fas fa-clipboard-list text-2xl mb-2"></i>
            <p class="font-semibold">Review Applications</p>
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
