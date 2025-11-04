<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$user = Auth::requireRole('super_admin');
$pageTitle = 'Manage Users';

// Add archived_at column if it doesn't exist
try {
    db()->query('ALTER TABLE users ADD COLUMN archived_at TIMESTAMP NULL DEFAULT NULL');
} catch (Exception $e) {
    // Column already exists, ignore
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $userId = $_POST['user_id'];
        $action = $_POST['action'];

        // Prevent super admin from modifying themselves
        if ($userId === $user['id']) {
            setFlash('error', 'You cannot modify your own account from this page.');
            header('Location: /admin/users.php');
            exit;
        }

        switch ($action) {
            case 'verify':
                db()->query('UPDATE users SET email_verified = 1 WHERE id = ?', [$userId]);
                setFlash('success', 'User email verified successfully.');
                break;

            case 'unverify':
                db()->query('UPDATE users SET email_verified = 0 WHERE id = ?', [$userId]);
                setFlash('success', 'User email verification removed.');
                break;

            case 'archive':
                // Archive user
                db()->query('UPDATE users SET archived_at = NOW() WHERE id = ?', [$userId]);

                // Archive their properties
                db()->query('UPDATE properties SET status = "maintenance" WHERE manager_id = ?', [$userId]);

                // Log activity
                $activityId = Auth::generateId();
                db()->query(
                    'INSERT INTO activity_log (id, user_id, action, entity_type, entity_id, created_at)
                     VALUES (?, ?, "user_archived", "user", ?, NOW())',
                    [$activityId, $user['id'], $userId]
                );

                setFlash('success', 'User archived successfully. Their properties are now hidden from the platform.');
                break;

            case 'unarchive':
                // Unarchive user
                db()->query('UPDATE users SET archived_at = NULL WHERE id = ?', [$userId]);

                // Restore their properties to available
                db()->query('UPDATE properties SET status = "available" WHERE manager_id = ?', [$userId]);

                // Log activity
                $activityId = Auth::generateId();
                db()->query(
                    'INSERT INTO activity_log (id, user_id, action, entity_type, entity_id, created_at)
                     VALUES (?, ?, "user_unarchived", "user", ?, NOW())',
                    [$activityId, $user['id'], $userId]
                );

                setFlash('success', 'User restored successfully. Their properties are now visible on the platform.');
                break;
        }
    } catch (Exception $e) {
        setFlash('error', 'Failed to update user: ' . $e->getMessage());
    }

    header('Location: /admin/users.php');
    exit;
}

// Get filter
$filter = $_GET['filter'] ?? 'active';

// Get all users
$sql = 'SELECT u.*, p.role
        FROM users u
        JOIN user_profiles p ON u.id = p.user_id';

if ($filter === 'active') {
    $sql .= ' WHERE u.archived_at IS NULL';
} elseif ($filter === 'archived') {
    $sql .= ' WHERE u.archived_at IS NOT NULL';
}

$sql .= ' ORDER BY u.created_at DESC';

$users = db()->fetchAll($sql);

// Get stats
$stats = [
    'total' => db()->fetchOne('SELECT COUNT(*) as count FROM users WHERE archived_at IS NULL')['count'],
    'archived' => db()->fetchOne('SELECT COUNT(*) as count FROM users WHERE archived_at IS NOT NULL')['count'],
    'super_admin' => count(array_filter($users, fn($u) => $u['role'] === 'super_admin')),
    'admin' => count(array_filter($users, fn($u) => $u['role'] === 'admin')),
    'user' => count(array_filter($users, fn($u) => $u['role'] === 'user')),
];

include 'includes/header.php';
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Users</h1>
        <p class="text-gray-600 mt-2">
            <?php echo $stats['total']; ?> active users
            <?php if ($stats['archived'] > 0): ?>
                (<?php echo $stats['archived']; ?> archived)
            <?php endif; ?>
        </p>
    </div>
</div>

<!-- Filter Tabs -->
<div class="bg-white rounded-xl shadow-md mb-8">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <a href="?filter=active" class="<?php echo $filter === 'active' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                Active Users
                <span class="ml-2 text-xs text-gray-500">(<?php echo $stats['total']; ?>)</span>
            </a>
            <a href="?filter=archived" class="<?php echo $filter === 'archived' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                Archived Users
                <span class="ml-2 text-xs text-gray-500">(<?php echo $stats['archived']; ?>)</span>
            </a>
            <a href="?filter=all" class="<?php echo $filter === 'all' ? 'border-gray-500 text-gray-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                All Users
                <span class="ml-2 text-xs text-gray-500">(<?php echo $stats['total'] + $stats['archived']; ?>)</span>
            </a>
        </nav>
    </div>
</div>

<?php if (empty($users)): ?>
    <div class="bg-white rounded-xl shadow-md p-12 text-center">
        <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">No Users</h2>
        <p class="text-gray-600">No users found.</p>
    </div>
<?php else: ?>
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-gray-50 <?php echo $u['archived_at'] ? 'bg-gray-100' : ''; ?>">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-full flex items-center justify-center text-white font-bold mr-4">
                                    <?php echo getInitials($u['first_name'], $u['last_name'], $u['email']); ?>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">
                                        <?php echo htmlspecialchars(($u['first_name'] ?: '') . ' ' . ($u['last_name'] ?: '') ?: 'No name'); ?>
                                        <?php if ($u['id'] === $user['id']): ?>
                                            <span class="ml-2 text-xs text-blue-600 font-bold">(You)</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($u['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($u['phone']): ?>
                                <div class="text-sm text-gray-900">
                                    <i class="fas fa-phone mr-1"></i>
                                    <?php echo htmlspecialchars($u['phone']); ?>
                                </div>
                            <?php else: ?>
                                <span class="text-sm text-gray-400">No phone</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php
                                echo $u['role'] === 'super_admin' ? 'bg-purple-100 text-purple-800' :
                                    ($u['role'] === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800');
                            ?>">
                                <?php
                                echo $u['role'] === 'super_admin' ? 'Super Admin' :
                                    ($u['role'] === 'admin' ? 'Property Manager' : 'User');
                                ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?php echo formatDate($u['created_at']); ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($u['archived_at']): ?>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Archived
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php
                                    echo $u['email_verified'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                                ?>">
                                    <?php echo $u['email_verified'] ? 'Verified' : 'Not Verified'; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <?php if ($u['id'] !== $user['id']): ?>
                                <div class="flex justify-end gap-2">
                                    <?php if (!$u['archived_at']): ?>
                                        <!-- Verify/Unverify Button -->
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <input type="hidden" name="action" value="<?php echo $u['email_verified'] ? 'unverify' : 'verify'; ?>">
                                            <button type="submit"
                                                    class="text-blue-600 hover:text-blue-900 px-2 py-1"
                                                    title="<?php echo $u['email_verified'] ? 'Remove verification' : 'Verify email'; ?>">
                                                <i class="fas fa-<?php echo $u['email_verified'] ? 'times' : 'check'; ?>-circle"></i>
                                                <?php echo $u['email_verified'] ? 'Unverify' : 'Verify'; ?>
                                            </button>
                                        </form>

                                        <!-- Archive Button -->
                                        <button onclick="showArchiveModal('<?php echo $u['id']; ?>', '<?php echo htmlspecialchars($u['email']); ?>', '<?php echo $u['role']; ?>')"
                                                class="text-red-600 hover:text-red-900 px-2 py-1">
                                            <i class="fas fa-archive"></i>
                                            Archive
                                        </button>
                                    <?php else: ?>
                                        <!-- Unarchive Button -->
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <input type="hidden" name="action" value="unarchive">
                                            <button type="submit"
                                                    onclick="return confirm('Restore this user and their properties?')"
                                                    class="text-green-600 hover:text-green-900 px-2 py-1">
                                                <i class="fas fa-undo"></i>
                                                Restore
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400 text-xs">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Archive Modal -->
<div id="archiveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">
            <i class="fas fa-archive text-red-600 mr-2"></i>
            Archive User
        </h3>
        <form method="POST" id="archiveForm">
            <input type="hidden" name="user_id" id="archiveUserId">
            <input type="hidden" name="action" value="archive">

            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-yellow-900 text-sm">
                    <strong>⚠️ Warning:</strong> Archiving this user will:
                </p>
                <ul class="text-yellow-800 text-sm mt-2 ml-4 list-disc">
                    <li>Prevent them from logging in</li>
                    <li id="propertiesWarning" class="hidden">Hide all their properties from the platform</li>
                    <li>Can be restored later if needed</li>
                </ul>
            </div>

            <p class="text-gray-700 mb-4">
                Are you sure you want to archive: <strong id="archiveUserEmail"></strong>?
            </p>

            <div class="flex gap-3">
                <button type="button" onclick="closeArchiveModal()"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                    Archive User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showArchiveModal(userId, userEmail, userRole) {
    document.getElementById('archiveUserId').value = userId;
    document.getElementById('archiveUserEmail').textContent = userEmail;

    // Show properties warning only for property managers
    if (userRole === 'admin') {
        document.getElementById('propertiesWarning').classList.remove('hidden');
    } else {
        document.getElementById('propertiesWarning').classList.add('hidden');
    }

    document.getElementById('archiveModal').classList.remove('hidden');
}

function closeArchiveModal() {
    document.getElementById('archiveModal').classList.add('hidden');
    document.getElementById('archiveForm').reset();
}
</script>

<?php include 'includes/footer.php'; ?>
