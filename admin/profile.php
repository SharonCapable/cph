<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require login (any authenticated user can access their profile)
$user = Auth::requireAuth();
$pageTitle = 'My Profile';

// Get user details
$userDetails = db()->fetchOne(
    'SELECT u.*, p.role FROM users u
     JOIN user_profiles p ON u.id = p.user_id
     WHERE u.id = ?',
    [$user['id']]
);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'update_profile') {
            $firstName = sanitize($_POST['first_name']);
            $lastName = sanitize($_POST['last_name']);
            $phone = sanitize($_POST['phone']);

            db()->query(
                'UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?',
                [$firstName, $lastName, $phone, $user['id']]
            );

            setFlash('success', 'Profile updated successfully!');
            header('Location: /admin/profile.php');
            exit;

        } elseif ($_POST['action'] === 'change_password') {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];

            // Verify current password
            if (!Auth::verifyPassword($currentPassword, $userDetails['password_hash'])) {
                throw new Exception('Current password is incorrect.');
            }

            // Validate new password
            if (strlen($newPassword) < 8) {
                throw new Exception('New password must be at least 8 characters.');
            }

            if ($newPassword !== $confirmPassword) {
                throw new Exception('New passwords do not match.');
            }

            // Update password
            $newPasswordHash = Auth::hashPassword($newPassword);
            db()->query('UPDATE users SET password_hash = ? WHERE id = ?', [$newPasswordHash, $user['id']]);

            // Log activity
            $activityId = Auth::generateId();
            db()->query(
                'INSERT INTO activity_log (id, user_id, action, entity_type, entity_id, created_at)
                 VALUES (?, ?, "password_changed", "user", ?, NOW())',
                [$activityId, $user['id'], $user['id']]
            );

            setFlash('success', 'Password changed successfully!');
            header('Location: /admin/profile.php');
            exit;
        }
    } catch (Exception $e) {
        setFlash('error', $e->getMessage());
        header('Location: /admin/profile.php');
        exit;
    }
}

include 'includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">My Profile</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Profile Summary Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="text-center">
                    <div class="w-24 h-24 bg-gradient-to-br from-blue-600 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-3xl mx-auto mb-4">
                        <?php echo getInitials($userDetails['first_name'], $userDetails['last_name'], $userDetails['email']); ?>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">
                        <?php echo htmlspecialchars(($userDetails['first_name'] ?: '') . ' ' . ($userDetails['last_name'] ?: '') ?: 'No name set'); ?>
                    </h2>
                    <p class="text-gray-600 text-sm mt-1"><?php echo htmlspecialchars($userDetails['email']); ?></p>

                    <div class="mt-4">
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php
                            echo $userDetails['role'] === 'super_admin' ? 'bg-purple-100 text-purple-800' :
                                ($userDetails['role'] === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800');
                        ?>">
                            <?php
                            echo $userDetails['role'] === 'super_admin' ? 'Super Admin' :
                                ($userDetails['role'] === 'admin' ? 'Property Manager' : 'User');
                            ?>
                        </span>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200 text-left space-y-3">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-calendar-alt w-5"></i>
                            <span class="ml-2">Joined <?php echo formatDate($userDetails['created_at']); ?></span>
                        </div>
                        <?php if ($userDetails['phone']): ?>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-phone w-5"></i>
                                <span class="ml-2"><?php echo htmlspecialchars($userDetails['phone']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-<?php echo $userDetails['email_verified'] ? 'check-circle text-green-600' : 'exclamation-circle text-yellow-600'; ?> w-5"></i>
                            <span class="ml-2"><?php echo $userDetails['email_verified'] ? 'Email Verified' : 'Email Not Verified'; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Forms -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Update Profile Form -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-user-edit mr-2 text-blue-600"></i>
                    Personal Information
                </h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                First Name
                            </label>
                            <input type="text" name="first_name"
                                   value="<?php echo htmlspecialchars($userDetails['first_name'] ?: ''); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Enter your first name">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Last Name
                            </label>
                            <input type="text" name="last_name"
                                   value="<?php echo htmlspecialchars($userDetails['last_name'] ?: ''); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Enter your last name">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <input type="email"
                               value="<?php echo htmlspecialchars($userDetails['email']); ?>"
                               disabled
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                               title="Email cannot be changed">
                        <p class="text-xs text-gray-500 mt-1">Email address cannot be changed</p>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number
                        </label>
                        <input type="tel" name="phone"
                               value="<?php echo htmlspecialchars($userDetails['phone'] ?: ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="+1 (555) 123-4567">
                    </div>

                    <div class="mt-6">
                        <button type="submit"
                                class="w-full md:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors">
                            <i class="fas fa-save mr-2"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password Form -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-lock mr-2 text-purple-600"></i>
                    Change Password
                </h3>
                <form method="POST" onsubmit="return validatePasswordForm()">
                    <input type="hidden" name="action" value="change_password">

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Current Password *
                            </label>
                            <input type="password" name="current_password" id="currentPassword" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="Enter your current password">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                New Password *
                            </label>
                            <input type="password" name="new_password" id="newPassword" required minlength="8"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="Enter new password (min 8 characters)">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Confirm New Password *
                            </label>
                            <input type="password" name="confirm_password" id="confirmPassword" required minlength="8"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="Confirm new password">
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <p class="text-yellow-800 text-sm">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Password Requirements:</strong>
                            </p>
                            <ul class="text-yellow-700 text-sm mt-2 ml-5 list-disc">
                                <li>Minimum 8 characters</li>
                                <li>Use a strong, unique password</li>
                            </ul>
                        </div>

                        <div>
                            <button type="submit"
                                    class="w-full md:w-auto px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors">
                                <i class="fas fa-key mr-2"></i>
                                Change Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function validatePasswordForm() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (newPassword !== confirmPassword) {
        alert('New passwords do not match!');
        return false;
    }

    if (newPassword.length < 8) {
        alert('Password must be at least 8 characters long!');
        return false;
    }

    return true;
}
</script>

<?php include 'includes/footer.php'; ?>
