<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// If already logged in, redirect
if (Auth::check()) {
    $user = Auth::user();
    if ($user['role'] === 'admin' || $user['role'] === 'super_admin') {
        redirect('/admin/index.php');
    } else {
        redirect('/public/index.php');
    }
}

$pageTitle = 'Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $result = Auth::login($email, $password);

        if ($result['success']) {
            $redirect = $_GET['redirect'] ?? '';
            if ($redirect) {
                redirect($redirect);
            } else {
                if ($result['user']['role'] === 'admin' || $result['user']['role'] === 'super_admin') {
                    redirect('/admin/index.php');
                } else {
                    redirect('/public/index.php');
                }
            }
        } else {
            $error = $result['error'];
        }
    }
}

include '../includes/header.php';
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="mx-auto w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-sign-in-alt text-white text-2xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Welcome Back</h2>
                <p class="text-gray-600 mt-2">Sign in to your account</p>
            </div>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="you@example.com"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="••••••••"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 rounded-lg font-semibold hover:shadow-lg transition-all duration-200"
                >
                    Sign In
                </button>
            </form>

            <!-- Divider -->
            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Don't have an account?
                    <a href="/public/signup.php" class="text-blue-600 hover:text-blue-700 font-semibold">
                        Sign Up
                    </a>
                </p>
                <p class="text-gray-600 mt-2 text-sm">
                    Want to list properties?
                    <a href="/public/list-property.php" class="text-blue-600 hover:text-blue-700 font-semibold">
                        Become a Manager
                    </a>
                </p>
            </div>

            <!-- Test Credentials (Development Only) -->
            <?php if (APP_ENV === 'development'): ?>
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800 font-semibold mb-2">Test Credentials (Dev Only):</p>
                    <p class="text-sm text-yellow-700">
                        Email: admin@circlepointhomes.com<br>
                        Password: admin123
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
