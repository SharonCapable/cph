<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/mail.php';

$user = Auth::requireRole('super_admin');
$pageTitle = 'Property Manager Applications';

// Handle application review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'])) {
    try {
        $applicationId = $_POST['application_id'];
        $action = $_POST['action']; // 'approve' or 'reject'
        $rejectionReason = isset($_POST['rejection_reason']) ? sanitize($_POST['rejection_reason']) : null;

        $application = db()->fetchOne('SELECT * FROM property_manager_applications WHERE id = ?', [$applicationId]);

        if ($application) {
            if ($action === 'approve') {
                // Update application status
                db()->query(
                    'UPDATE property_manager_applications
                     SET status = "approved", reviewed_by = ?, reviewed_at = NOW()
                     WHERE id = ?',
                    [$user['id'], $applicationId]
                );

                // Get current user role
                $currentRole = db()->fetchOne('SELECT role FROM user_profiles WHERE user_id = ?', [$application['user_id']]);

                // Only update role to admin if they're currently a regular user (don't downgrade super_admin!)
                if ($currentRole && $currentRole['role'] === 'user') {
                    db()->query(
                        'UPDATE user_profiles SET role = "admin" WHERE user_id = ?',
                        [$application['user_id']]
                    );
                }

                // Log activity
                $activityId = Auth::generateId();
                db()->query(
                    'INSERT INTO activity_log (id, user_id, action, entity_type, entity_id, details, created_at)
                     VALUES (?, ?, "application_approved", "application", ?, ?, NOW())',
                    [$activityId, $user['id'], $applicationId, json_encode(['applicant_id' => $application['user_id']])]
                );

                // Get applicant details for notification
                $applicant = db()->fetchOne('SELECT email, first_name, last_name FROM users WHERE id = ?', [$application['user_id']]);

                // Send approval email with login link
                try {
                    $firstName = $applicant['first_name'] ?: 'there';
                    sendApplicationApprovedEmail($applicant['email'], $firstName);
                } catch (Exception $e) {
                    error_log('Failed to send approval email: ' . $e->getMessage());
                    // Continue anyway
                }

                setFlash('success', 'Application approved! ' . htmlspecialchars($applicant['email']) . ' is now a property manager. They have been notified via email and can login to start adding properties.');

            } elseif ($action === 'reject') {
                // Update application status
                db()->query(
                    'UPDATE property_manager_applications
                     SET status = "rejected", reviewed_by = ?, reviewed_at = NOW(), rejection_reason = ?
                     WHERE id = ?',
                    [$user['id'], $rejectionReason, $applicationId]
                );

                // Log activity
                $activityId = Auth::generateId();
                db()->query(
                    'INSERT INTO activity_log (id, user_id, action, entity_type, entity_id, details, created_at)
                     VALUES (?, ?, "application_rejected", "application", ?, ?, NOW())',
                    [$activityId, $user['id'], $applicationId, json_encode(['applicant_id' => $application['user_id'], 'reason' => $rejectionReason])]
                );

                setFlash('success', 'Application rejected.');
            }
        }
    } catch (Exception $e) {
        setFlash('error', 'Failed to process application: ' . $e->getMessage());
    }

    header('Location: /admin/applications.php');
    exit;
}

// Get filter
$filter = $_GET['status'] ?? 'pending';
$validFilters = ['pending', 'approved', 'rejected', 'all'];
if (!in_array($filter, $validFilters)) {
    $filter = 'pending';
}

// Get applications
$sql = 'SELECT a.*, u.email, u.first_name, u.last_name, u.phone as user_phone
        FROM property_manager_applications a
        JOIN users u ON a.user_id = u.id';

if ($filter !== 'all') {
    $sql .= ' WHERE a.status = ?';
    $applications = db()->fetchAll($sql . ' ORDER BY a.created_at DESC', [$filter]);
} else {
    $applications = db()->fetchAll($sql . ' ORDER BY a.created_at DESC');
}

// Get counts for tabs
$counts = [
    'pending' => db()->fetchOne('SELECT COUNT(*) as count FROM property_manager_applications WHERE status = "pending"')['count'],
    'approved' => db()->fetchOne('SELECT COUNT(*) as count FROM property_manager_applications WHERE status = "approved"')['count'],
    'rejected' => db()->fetchOne('SELECT COUNT(*) as count FROM property_manager_applications WHERE status = "rejected"')['count'],
    'all' => db()->fetchOne('SELECT COUNT(*) as count FROM property_manager_applications')['count'],
];

include 'includes/header.php';
?>

<div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Property Manager Applications</h1>
</div>

<!-- Filter Tabs -->
<div class="bg-white rounded-xl shadow-md mb-8">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <a href="?status=pending" class="<?php echo $filter === 'pending' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                Pending
                <?php if ($counts['pending'] > 0): ?>
                    <span class="ml-2 bg-orange-100 text-orange-800 text-xs font-bold rounded-full px-2 py-0.5"><?php echo $counts['pending']; ?></span>
                <?php endif; ?>
            </a>
            <a href="?status=approved" class="<?php echo $filter === 'approved' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                Approved
                <span class="ml-2 text-xs text-gray-500">(<?php echo $counts['approved']; ?>)</span>
            </a>
            <a href="?status=rejected" class="<?php echo $filter === 'rejected' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                Rejected
                <span class="ml-2 text-xs text-gray-500">(<?php echo $counts['rejected']; ?>)</span>
            </a>
            <a href="?status=all" class="<?php echo $filter === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                All
                <span class="ml-2 text-xs text-gray-500">(<?php echo $counts['all']; ?>)</span>
            </a>
        </nav>
    </div>
</div>

<!-- Applications List -->
<?php if (empty($applications)): ?>
    <div class="bg-white rounded-xl shadow-md p-12 text-center">
        <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">No Applications</h2>
        <p class="text-gray-600">No <?php echo $filter !== 'all' ? $filter : ''; ?> applications at this time.</p>
    </div>
<?php else: ?>
    <div class="space-y-6">
        <?php foreach ($applications as $app): ?>
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">
                            <?php echo htmlspecialchars($app['company_name'] ?: ($app['first_name'] . ' ' . $app['last_name'])); ?>
                        </h3>
                        <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                            <div>
                                <i class="fas fa-envelope mr-1"></i>
                                <?php echo htmlspecialchars($app['email']); ?>
                            </div>
                            <div>
                                <i class="fas fa-phone mr-1"></i>
                                <?php echo htmlspecialchars($app['phone']); ?>
                            </div>
                            <div>
                                <i class="fas fa-briefcase mr-1"></i>
                                <?php echo ucfirst($app['business_type']); ?>
                            </div>
                        </div>
                    </div>

                    <span class="px-4 py-2 rounded-full text-sm font-semibold <?php
                        echo $app['status'] === 'approved' ? 'bg-green-100 text-green-800' :
                            ($app['status'] === 'pending' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800');
                    ?>">
                        <?php echo ucfirst($app['status']); ?>
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <div class="text-sm text-gray-500 mb-1">Location</div>
                        <div class="text-gray-900">
                            <?php echo htmlspecialchars($app['city'] . ', ' . $app['country']); ?>
                        </div>
                    </div>

                    <?php if ($app['years_experience']): ?>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Experience</div>
                            <div class="text-gray-900"><?php echo $app['years_experience']; ?> years</div>
                        </div>
                    <?php endif; ?>

                    <?php if ($app['number_of_properties']): ?>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Current Properties</div>
                            <div class="text-gray-900"><?php echo $app['number_of_properties']; ?> properties</div>
                        </div>
                    <?php endif; ?>

                    <?php if ($app['website']): ?>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Website</div>
                            <a href="<?php echo htmlspecialchars($app['website']); ?>" target="_blank" class="text-blue-600 hover:underline">
                                <?php echo htmlspecialchars($app['website']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($app['description']): ?>
                    <div class="mb-4">
                        <div class="text-sm text-gray-500 mb-1">Description</div>
                        <div class="text-gray-900"><?php echo nl2br(htmlspecialchars($app['description'])); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($app['rejection_reason']): ?>
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="text-sm text-red-700 font-semibold mb-1">Rejection Reason:</div>
                        <div class="text-red-800"><?php echo htmlspecialchars($app['rejection_reason']); ?></div>
                    </div>
                <?php endif; ?>

                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="text-sm text-gray-500">
                        Applied <?php echo formatDate($app['created_at']); ?>
                        <?php if ($app['reviewed_at']): ?>
                            • Reviewed <?php echo formatDate($app['reviewed_at']); ?>
                        <?php endif; ?>
                    </div>

                    <?php if ($app['status'] === 'pending'): ?>
                        <div class="flex gap-2">
                            <button onclick="showRejectModal('<?php echo $app['id']; ?>', '<?php echo htmlspecialchars($app['company_name'] ?: $app['email']); ?>')"
                                    class="px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 font-medium">
                                <i class="fas fa-times mr-1"></i>
                                Reject
                            </button>
                            <form method="POST" class="inline">
                                <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit"
                                        onclick="return confirm('Approve this application and grant property manager access?')"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                                    <i class="fas fa-check mr-1"></i>
                                    Approve
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Reject Application</h3>
        <form method="POST" id="rejectForm">
            <input type="hidden" name="application_id" id="rejectApplicationId">
            <input type="hidden" name="action" value="reject">

            <p class="text-gray-700 mb-4">
                Rejecting application from: <strong id="rejectApplicationName"></strong>
            </p>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection</label>
                <textarea name="rejection_reason" required rows="4"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                          placeholder="Provide a reason for rejection..."></textarea>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeRejectModal()"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                    Reject Application
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal(applicationId, applicationName) {
    document.getElementById('rejectApplicationId').value = applicationId;
    document.getElementById('rejectApplicationName').textContent = applicationName;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectForm').reset();
}
</script>

<?php include 'includes/footer.php'; ?>
