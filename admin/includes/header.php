<?php
if (!isset($user)) {
    $user = Auth::user();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Dashboard'; ?> - CirclePoint Homes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Top Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/admin/index.php" class="flex items-center">
                        <i class="fas fa-home text-blue-600 text-2xl mr-3"></i>
                        <span class="text-xl font-bold text-gray-900">CirclePoint Admin</span>
                    </a>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="/" class="text-gray-600 hover:text-gray-900" title="View Site" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <span class="ml-2 hidden md:inline">View Site</span>
                    </a>

                    <div class="relative group">
                        <button class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                <?php echo getInitials($user['firstName'], $user['lastName'], $user['email']); ?>
                            </div>
                            <span class="hidden md:block font-medium"><?php echo htmlspecialchars($user['firstName'] ?: $user['email']); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>

                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block">
                            <a href="/admin/profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i> Profile
                            </a>
                            <a href="/api/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Admin Navigation Tabs -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex space-x-8 overflow-x-auto">
                <a href="/admin/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-chart-line mr-2"></i>Dashboard
                </a>
                <a href="/admin/properties.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'properties') !== false ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-home mr-2"></i>Properties
                </a>
                <a href="/admin/bookings.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'bookings.php' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-calendar-check mr-2"></i>Bookings
                </a>
                <?php if (Auth::hasRole('super_admin')): ?>
                    <a href="/admin/applications.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'applications.php' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        <i class="fas fa-clipboard-list mr-2"></i>Applications
                        <?php
                        $pendingCount = db()->fetchOne('SELECT COUNT(*) as count FROM property_manager_applications WHERE status = "pending"')['count'];
                        if ($pendingCount > 0):
                        ?>
                            <span class="ml-2 bg-orange-500 text-white text-xs font-bold rounded-full px-2 py-0.5"><?php echo $pendingCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="/admin/users.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        <i class="fas fa-users mr-2"></i>Users
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (hasFlash('success')): ?>
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-6" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo getFlash('success'); ?>
            </div>
        <?php endif; ?>

        <?php if (hasFlash('error')): ?>
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-6" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo getFlash('error'); ?>
            </div>
        <?php endif; ?>
