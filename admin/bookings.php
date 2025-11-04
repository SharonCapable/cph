<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$user = Auth::requireRole('admin');
$pageTitle = 'Manage Bookings';
$isSuperAdmin = $user['role'] === 'super_admin';

// Get filter
$filter = $_GET['status'] ?? 'all';
$validFilters = ['pending', 'confirmed', 'cancelled', 'completed', 'all'];
if (!in_array($filter, $validFilters)) {
    $filter = 'all';
}

// Build query based on role
$sql = 'SELECT b.*, p.title as property_title, p.city, p.country,
        u.email as user_email, u.first_name, u.last_name, u.phone as user_phone
        FROM bookings b
        JOIN properties p ON b.property_id = p.id
        JOIN users u ON b.user_id = u.id';

$params = [];

// Add role filter
if (!$isSuperAdmin) {
    $sql .= ' WHERE p.manager_id = ?';
    $params[] = $user['id'];
}

// Add status filter
if ($filter !== 'all') {
    $sql .= ($isSuperAdmin ? ' WHERE' : ' AND') . ' b.status = ?';
    $params[] = $filter;
}

$bookings = db()->fetchAll($sql . ' ORDER BY b.created_at DESC', $params);

// Get counts
$counts = [
    'pending' => db()->fetchOne('SELECT COUNT(*) as count FROM bookings WHERE status = "pending"')['count'],
    'confirmed' => db()->fetchOne('SELECT COUNT(*) as count FROM bookings WHERE status = "confirmed"')['count'],
    'cancelled' => db()->fetchOne('SELECT COUNT(*) as count FROM bookings WHERE status = "cancelled"')['count'],
    'completed' => db()->fetchOne('SELECT COUNT(*) as count FROM bookings WHERE status = "completed"')['count'],
    'all' => db()->fetchOne('SELECT COUNT(*) as count FROM bookings')['count'],
];

include 'includes/header.php';
?>

<div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Bookings</h1>
</div>

<!-- Filter Tabs -->
<div class="bg-white rounded-xl shadow-md mb-8">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <a href="?status=all" class="<?php echo $filter === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                All Bookings
                <span class="ml-2 text-xs text-gray-500">(<?php echo $counts['all']; ?>)</span>
            </a>
            <a href="?status=pending" class="<?php echo $filter === 'pending' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                Pending
                <?php if ($counts['pending'] > 0): ?>
                    <span class="ml-2 bg-orange-100 text-orange-800 text-xs font-bold rounded-full px-2 py-0.5"><?php echo $counts['pending']; ?></span>
                <?php endif; ?>
            </a>
            <a href="?status=confirmed" class="<?php echo $filter === 'confirmed' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                Confirmed
                <span class="ml-2 text-xs text-gray-500">(<?php echo $counts['confirmed']; ?>)</span>
            </a>
            <a href="?status=completed" class="<?php echo $filter === 'completed' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                Completed
                <span class="ml-2 text-xs text-gray-500">(<?php echo $counts['completed']; ?>)</span>
            </a>
            <a href="?status=cancelled" class="<?php echo $filter === 'cancelled' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                Cancelled
                <span class="ml-2 text-xs text-gray-500">(<?php echo $counts['cancelled']; ?>)</span>
            </a>
        </nav>
    </div>
</div>

<!-- Bookings List -->
<?php if (empty($bookings)): ?>
    <div class="bg-white rounded-xl shadow-md p-12 text-center">
        <i class="fas fa-calendar-check text-6xl text-gray-300 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">No Bookings</h2>
        <p class="text-gray-600">No <?php echo $filter !== 'all' ? $filter : ''; ?> bookings at this time.</p>
    </div>
<?php else: ?>
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guests</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($bookings as $booking): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($booking['property_title']); ?></div>
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                <?php echo htmlspecialchars($booking['city'] . ', ' . $booking['country']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <?php echo htmlspecialchars(($booking['first_name'] ?: '') . ' ' . ($booking['last_name'] ?: '')); ?>
                            </div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($booking['user_email']); ?></div>
                            <?php if ($booking['phone']): ?>
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-phone mr-1"></i>
                                    <?php echo htmlspecialchars($booking['phone']); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <?php echo date('M d, Y', strtotime($booking['check_in'])); ?>
                            </div>
                            <div class="text-sm text-gray-500">to</div>
                            <div class="text-sm text-gray-900">
                                <?php echo date('M d, Y', strtotime($booking['check_out'])); ?>
                            </div>
                            <?php
                            $nights = (strtotime($booking['check_out']) - strtotime($booking['check_in'])) / 86400;
                            ?>
                            <div class="text-xs text-gray-500 mt-1">
                                <?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <?php echo $booking['guests']; ?> guest<?php echo $booking['guests'] > 1 ? 's' : ''; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900">
                                <?php echo formatPrice($booking['total_price']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php
                                echo $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-800' :
                                    ($booking['status'] === 'pending' ? 'bg-orange-100 text-orange-800' :
                                    ($booking['status'] === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'));
                            ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="/admin/booking-detail.php?id=<?php echo $booking['id']; ?>"
                               class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </td>
                    </tr>
                    <?php if ($booking['message']): ?>
                        <tr class="bg-gray-50">
                            <td colspan="7" class="px-6 py-3">
                                <div class="text-sm">
                                    <span class="font-medium text-gray-700">Message:</span>
                                    <span class="text-gray-600 ml-2"><?php echo nl2br(htmlspecialchars($booking['message'])); ?></span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
