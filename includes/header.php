<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="/public/index.php" class="flex items-center group">
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-2 rounded-xl mr-3 group-hover:shadow-lg transition-shadow">
                        <i class="fas fa-home text-white text-xl"></i>
                    </div>
                    <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        <?php echo APP_NAME; ?>
                    </span>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/public/index.php" class="text-gray-700 hover:text-blue-600 transition-colors">
                        Properties
                    </a>
                    <a href="/public/list-property.php" class="text-gray-700 hover:text-blue-600 transition-colors">
                        Become a Manager
                    </a>
                    <?php if (Auth::check()): ?>
                        <?php $user = Auth::user(); ?>
                        <?php if ($user['role'] === 'admin' || $user['role'] === 'super_admin'): ?>
                            <a href="/admin/index.php" class="text-gray-700 hover:text-blue-600 transition-colors">
                                <i class="fas fa-cog mr-1"></i> Dashboard
                            </a>
                        <?php endif; ?>
                        <a href="/api/logout.php" class="text-gray-700 hover:text-blue-600 transition-colors">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="/public/login.php" class="text-gray-700 hover:text-blue-600 transition-colors">
                            Login
                        </a>
                        <a href="/public/signup.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Sign Up
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <button id="mobile-menu-button" class="md:hidden text-gray-700">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div id="mobile-menu" class="hidden md:hidden border-t">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="/public/index.php" class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-md">
                    Properties
                </a>
                <a href="/public/list-property.php" class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-md">
                    Become a Manager
                </a>
                <?php if (Auth::check()): ?>
                    <?php $user = Auth::user(); ?>
                    <?php if ($user['role'] === 'admin' || $user['role'] === 'super_admin'): ?>
                        <a href="/admin/index.php" class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-md">
                            <i class="fas fa-cog mr-1"></i> Dashboard
                        </a>
                    <?php endif; ?>
                    <a href="/api/logout.php" class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-md">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="/public/login.php" class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-md">
                        Login
                    </a>
                    <a href="/public/signup.php" class="block px-3 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-md text-center">
                        Sign Up
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (hasFlash('success')): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span><?php echo getFlash('success'); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if (hasFlash('error')): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span><?php echo getFlash('error'); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main>
