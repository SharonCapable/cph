<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/mail.php';

// Check if user is logged in (optional for application)
$user = Auth::user();

$pageTitle = 'Apply to List Properties';

// Check if user already has an application
$existingApp = null;
if ($user) {
    $existingApp = db()->fetchOne(
        'SELECT * FROM property_manager_applications WHERE user_id = ?',
        [$user['id']]
    );
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$existingApp) {
    try {
        $email = sanitize($_POST['email']);

        // Check if user with this email exists
        $existingUser = db()->fetchOne('SELECT * FROM users WHERE email = ?', [$email]);

        if ($existingUser) {
            // Check if they already have an application
            $existingUserApp = db()->fetchOne(
                'SELECT * FROM property_manager_applications WHERE user_id = ?',
                [$existingUser['id']]
            );

            if ($existingUserApp) {
                $error = 'An application already exists for this email address.';
            } else {
                $userId = $existingUser['id'];
            }
        } else {
            // Create new user account
            $userId = Auth::generateId();
            $tempPassword = bin2hex(random_bytes(8)); // Generate temporary password
            $passwordHash = Auth::hashPassword($tempPassword);

            db()->query(
                'INSERT INTO users (id, email, password_hash, email_verified, created_at)
                 VALUES (?, ?, ?, 0, NOW())',
                [$userId, $email, $passwordHash]
            );

            // Create user profile with 'user' role
            $profileId = Auth::generateId();
            db()->query(
                'INSERT INTO user_profiles (id, user_id, role, created_at)
                 VALUES (?, ?, "user", NOW())',
                [$profileId, $userId]
            );

            // Store temp password for later email
            $tempPasswordMessage = "A temporary password has been created: $tempPassword";
        }

        if (!isset($error)) {
            $applicationId = Auth::generateId();

            db()->query(
                'INSERT INTO property_manager_applications (
                    id, user_id, company_name, business_type, phone,
                    address, city, state, country, zip_code,
                    years_experience, number_of_properties, description, website,
                    status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "pending", NOW())',
                [
                    $applicationId,
                    $userId,
                    sanitize($_POST['company_name']) ?: null,
                    $_POST['business_type'],
                    sanitize($_POST['phone']),
                    sanitize($_POST['address']),
                    sanitize($_POST['city']),
                    sanitize($_POST['state']) ?: null,
                    sanitize($_POST['country']),
                    sanitize($_POST['zip_code']) ?: null,
                    isset($_POST['years_experience']) ? (int)$_POST['years_experience'] : null,
                    isset($_POST['number_of_properties']) ? (int)$_POST['number_of_properties'] : null,
                    sanitize($_POST['description']) ?: null,
                    sanitize($_POST['website']) ?: null
                ]
            );

            // Send email with credentials
            $firstName = $existingUser['first_name'] ?? 'there';
            $emailPassword = isset($tempPassword) ? $tempPassword : null;

            try {
                sendApplicationReceivedEmail($email, $firstName, $emailPassword);
            } catch (Exception $e) {
                error_log('Failed to send application email: ' . $e->getMessage());
                // Continue anyway - credentials will be shown on screen
            }

            $successMessage = 'Application submitted successfully! We will review it and contact you at ' . $email . '.';
            if (isset($tempPasswordMessage)) {
                $successMessage .= '<br><br><strong>IMPORTANT - Save this information:</strong><br>';
                $successMessage .= 'Temporary Password: <strong>' . htmlspecialchars($tempPassword) . '</strong><br>';
                $successMessage .= 'Email: <strong>' . htmlspecialchars($email) . '</strong><br><br>';
                $successMessage .= 'Once approved, use these credentials to login at <a href="/public/login.php" class="underline text-blue-600">/public/login.php</a>';
            }

            // Store temp password in session for display (since flash doesn't support HTML well)
            if (isset($tempPassword)) {
                $_SESSION['application_credentials'] = [
                    'email' => $email,
                    'password' => $tempPassword
                ];
            }

            setFlash('success', 'Application submitted successfully! Check below for your login credentials.');
            header('Location: /public/list-property.php');
            exit;
        }

    } catch (Exception $e) {
        $error = 'Failed to submit application: ' . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Become a Property Manager</h1>
        <p class="text-xl text-gray-600">Join our network and start listing your properties today</p>
    </div>

    <?php if (isset($_SESSION['application_credentials']) && !isset($_GET['dismiss'])): ?>
        <div id="credentialsBox" class="bg-green-50 border-2 border-green-500 rounded-xl p-6 mb-8 relative">
            <button onclick="dismissCredentials()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-2xl font-bold leading-none" title="I've saved my credentials">
                ×
            </button>
            <h2 class="text-2xl font-bold text-green-900 mb-4">
                <i class="fas fa-check-circle mr-2"></i>
                Application Submitted! Save Your Login Details
            </h2>

            <div class="bg-red-50 border-2 border-red-400 rounded-lg p-4 mb-4">
                <p class="text-red-900 font-bold text-sm flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    IMPORTANT: Copy these credentials NOW! This message will disappear once closed.
                </p>
            </div>

            <div class="bg-white rounded-lg p-4 mb-4">
                <p class="text-gray-900 font-semibold mb-2">Your Login Credentials:</p>
                <div class="grid grid-cols-1 gap-3 font-mono text-base">
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <div class="flex-1">
                            <span class="text-gray-600 text-sm block">Email:</span>
                            <span class="font-bold text-gray-900" id="credEmail"><?php echo htmlspecialchars($_SESSION['application_credentials']['email']); ?></span>
                        </div>
                        <button onclick="copyToClipboard('credEmail')" class="ml-2 px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                            <i class="fas fa-copy mr-1"></i> Copy
                        </button>
                    </div>
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <div class="flex-1">
                            <span class="text-gray-600 text-sm block">Password:</span>
                            <span class="font-bold text-green-600" id="credPassword"><?php echo htmlspecialchars($_SESSION['application_credentials']['password']); ?></span>
                        </div>
                        <button onclick="copyToClipboard('credPassword')" class="ml-2 px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                            <i class="fas fa-copy mr-1"></i> Copy
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <p class="text-blue-900 font-semibold mb-2"><i class="fas fa-info-circle mr-1"></i> What's Next?</p>
                <ol class="text-blue-800 space-y-1 ml-4 list-decimal text-sm">
                    <li><strong>SAVE these credentials</strong> - You'll need them to login</li>
                    <li>Check your email for a copy of these credentials</li>
                    <li>We'll review your application (usually within 24 hours)</li>
                    <li>Once approved, <a href="/public/login.php" class="underline font-semibold">login here</a> with the credentials above</li>
                    <li>Start adding your properties!</li>
                </ol>
            </div>

            <div class="text-center">
                <button onclick="dismissCredentials()" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                    <i class="fas fa-check mr-2"></i>
                    I've Saved My Credentials
                </button>
            </div>
        </div>

        <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent;
            navigator.clipboard.writeText(text).then(() => {
                // Show success feedback
                const btn = event.target.closest('button');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check mr-1"></i> Copied!';
                btn.classList.add('bg-green-600');
                btn.classList.remove('bg-blue-600');
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('bg-green-600');
                    btn.classList.add('bg-blue-600');
                }, 2000);
            });
        }

        function dismissCredentials() {
            if (confirm('Have you saved your login credentials? You will not be able to see them again on this page.')) {
                window.location.href = '?dismiss=1';
            }
        }
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['dismiss'])): ?>
        <?php unset($_SESSION['application_credentials']); ?>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
            <h2 class="text-xl font-bold text-blue-900 mb-2">
                <i class="fas fa-info-circle mr-2"></i>
                Application Received
            </h2>
            <p class="text-blue-800">
                Your application has been submitted successfully! We'll review it and notify you via email within 24-48 hours.
            </p>
        </div>
    <?php endif; ?>

    <?php if ($existingApp): ?>
        <!-- Existing Application Status -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center">
                <?php if ($existingApp['status'] === 'pending'): ?>
                    <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-clock text-orange-600 text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Application Under Review</h2>
                    <p class="text-gray-600 mb-6">
                        We received your application on <?php echo formatDate($existingApp['created_at']); ?>.
                        Our team is currently reviewing it and will get back to you soon.
                    </p>
                <?php elseif ($existingApp['status'] === 'approved'): ?>
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check-circle text-green-600 text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Application Approved!</h2>
                    <p class="text-gray-600 mb-6">
                        Congratulations! You're now a Property Manager. You can add and manage your properties through your dashboard.
                    </p>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
                        <p class="text-blue-900 font-semibold mb-2">Getting Started:</p>
                        <ol class="text-blue-800 space-y-1 ml-4 list-decimal text-sm">
                            <li>Click "Go to Dashboard" below</li>
                            <li>Click "Add New Property" to list your first property</li>
                            <li>Upload photos, set pricing, and add details</li>
                            <li>Your property will be live immediately!</li>
                        </ol>
                    </div>
                    <a href="/admin/index.php" class="inline-block bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-4 rounded-lg font-bold text-lg hover:shadow-lg transition-all">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Go to Dashboard
                    </a>
                <?php elseif ($existingApp['status'] === 'rejected'): ?>
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-times-circle text-red-600 text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Application Not Approved</h2>
                    <p class="text-gray-600 mb-6">
                        Unfortunately, we couldn't approve your application at this time.
                    </p>
                    <?php if ($existingApp['rejection_reason']): ?>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-left">
                            <p class="text-sm text-red-700 font-semibold mb-2">Reason:</p>
                            <p class="text-red-800"><?php echo htmlspecialchars($existingApp['rejection_reason']); ?></p>
                        </div>
                    <?php endif; ?>
                    <p class="text-sm text-gray-500">
                        If you have questions, please <a href="<?php echo getWhatsAppLink('Property Manager Application', 'inquiry'); ?>" class="text-blue-600 underline">contact us</a>.
                    </p>
                <?php endif; ?>
            </div>

            <div class="mt-8 pt-8 border-t">
                <h3 class="font-bold text-gray-900 mb-4">Your Application Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <?php if ($existingApp['company_name']): ?>
                        <div>
                            <span class="text-gray-500">Company Name:</span>
                            <span class="ml-2 text-gray-900 font-medium"><?php echo htmlspecialchars($existingApp['company_name']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div>
                        <span class="text-gray-500">Business Type:</span>
                        <span class="ml-2 text-gray-900 font-medium"><?php echo ucfirst($existingApp['business_type']); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Location:</span>
                        <span class="ml-2 text-gray-900 font-medium"><?php echo htmlspecialchars($existingApp['city'] . ', ' . $existingApp['country']); ?></span>
                    </div>
                    <?php if ($existingApp['years_experience']): ?>
                        <div>
                            <span class="text-gray-500">Experience:</span>
                            <span class="ml-2 text-gray-900 font-medium"><?php echo $existingApp['years_experience']; ?> years</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Application Form -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-8">
                <!-- Business Information -->
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-briefcase text-blue-600 mr-2"></i>
                        Business Information
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address<span class="text-red-600"> *</span></label>
                            <input type="email" name="email" required
                                   value="<?php echo $user ? htmlspecialchars($user['email']) : ''; ?>"
                                   <?php echo $user ? 'readonly' : ''; ?>
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg <?php echo $user ? 'bg-gray-50 text-gray-600' : 'focus:ring-2 focus:ring-blue-500'; ?>">
                            <p class="text-xs text-gray-500 mt-1">
                                <?php echo $user ? 'Using your account email' : 'We will create an account for you with this email'; ?>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Business Type<span class="text-red-600"> *</span></label>
                            <select name="business_type" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="individual">Individual</option>
                                <option value="company">Company</option>
                                <option value="agency">Agency</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Company Name (Optional)</label>
                            <input type="text" name="company_name"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="Your business name">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number<span class="text-red-600"> *</span></label>
                            <input type="tel" name="phone" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="+1234567890">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Website (Optional)</label>
                            <input type="url" name="website"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="https://yourwebsite.com">
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                        Location
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Street Address<span class="text-red-600"> *</span></label>
                            <input type="text" name="address" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City<span class="text-red-600"> *</span></label>
                            <input type="text" name="city" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">State/Province</label>
                            <input type="text" name="state"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Country<span class="text-red-600"> *</span></label>
                            <input type="text" name="country" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Zip/Postal Code</label>
                            <input type="text" name="zip_code"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Experience -->
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                        Experience
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Years of Experience</label>
                            <input type="number" name="years_experience" min="0"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Number of Properties</label>
                            <input type="number" name="number_of_properties" min="0"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="How many properties do you manage?">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tell us about yourself</label>
                            <textarea name="description" rows="4"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                      placeholder="Describe your experience in property management..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex justify-end space-x-4 pt-6">
                    <a href="/public/index.php" class="px-6 py-3 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition-all">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Submit Application
                    </button>
                </div>
            </form>
        </div>

        <!-- Benefits Section -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-md p-6 text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-dollar-sign text-blue-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Earn More</h3>
                <p class="text-gray-600 text-sm">Reach more travelers and maximize your rental income</p>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 text-center">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-purple-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Secure Platform</h3>
                <p class="text-gray-600 text-sm">Safe and reliable booking management system</p>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-headset text-green-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">24/7 Support</h3>
                <p class="text-gray-600 text-sm">We're here to help you succeed</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
