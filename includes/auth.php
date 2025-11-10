<?php
/**
 * CirclePoint Homes - Authentication System
 * Session management, login, signup, user verification
 */

require_once __DIR__ . '/config.php';

class Auth {
    /**
     * Hash password using bcrypt
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify password against hash
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Generate unique ID
     */
    public static function generateId() {
        return bin2hex(random_bytes(16));
    }

    /**
     * Register new user
     */
    public static function register($email, $password, $firstName = null, $lastName = null) {
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid email address'];
        }

        // Validate password length
        if (strlen($password) < 6) {
            return ['success' => false, 'error' => 'Password must be at least 6 characters'];
        }

        // Check if user already exists
        $existing = db()->fetchOne('SELECT id FROM users WHERE email = ?', [$email]);
        if ($existing) {
            return ['success' => false, 'error' => 'User already exists with this email'];
        }

        try {
            // Create user
            $userId = self::generateId();
            $passwordHash = self::hashPassword($password);

            db()->query(
                'INSERT INTO users (id, email, password_hash, first_name, last_name, email_verified, created_at)
                 VALUES (?, ?, ?, ?, ?, 0, NOW())',
                [$userId, $email, $passwordHash, $firstName, $lastName]
            );

            // Create user profile with 'user' role
            $profileId = self::generateId();
            db()->query(
                'INSERT INTO user_profiles (id, user_id, role, created_at)
                 VALUES (?, ?, "user", NOW())',
                [$profileId, $userId]
            );

            return ['success' => true, 'userId' => $userId];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to create user account'];
        }
    }

    /**
     * Login user
     */
    public static function login($email, $password) {
        // Get user from database
        $user = db()->fetchOne('SELECT * FROM users WHERE email = ?', [$email]);

        if (!$user) {
            return ['success' => false, 'error' => 'Invalid email or password'];
        }

        // Verify password
        if (!self::verifyPassword($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Invalid email or password'];
        }

        // Get user profile for role
        $profile = db()->fetchOne('SELECT * FROM user_profiles WHERE user_id = ?', [$user['id']]);

        if (!$profile) {
            return ['success' => false, 'error' => 'User profile not found'];
        }

        // Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $profile['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();

        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'firstName' => $user['first_name'],
                'lastName' => $user['last_name'],
                'role' => $profile['role']
            ]
        ];
    }

    /**
     * Logout user
     */
    public static function logout() {
        session_unset();
        session_destroy();
        return ['success' => true];
    }

    /**
     * Check if user is logged in
     */
    public static function check() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Get current user
     */
    public static function user() {
        if (!self::check()) {
            return null;
        }

        $user = db()->fetchOne('SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']]);
        $profile = db()->fetchOne('SELECT * FROM user_profiles WHERE user_id = ?', [$_SESSION['user_id']]);

        if (!$user || !$profile) {
            return null;
        }

        return [
            'id' => $user['id'],
            'email' => $user['email'],
            'firstName' => $user['first_name'],
            'lastName' => $user['last_name'],
            'phone' => $user['phone'],
            'role' => $profile['role']
        ];
    }

    /**
     * Require authentication (redirect to login if not authenticated)
     */
    public static function requireAuth() {
        if (!self::check()) {
            header('Location: /public/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        return self::user();
    }

    /**
     * Check if user has specific role
     */
    public static function hasRole($role) {
        $user = self::user();
        if (!$user) return false;

        $roleHierarchy = ['user' => 1, 'admin' => 2, 'super_admin' => 3];
        $userLevel = $roleHierarchy[$user['role']] ?? 0;
        $requiredLevel = $roleHierarchy[$role] ?? 0;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Require specific role (redirect if not authorized)
     */
    public static function requireRole($role) {
        $user = self::requireAuth();

        if (!self::hasRole($role)) {
            header('Location: /public/index.php');
            exit;
        }

        return $user;
    }
}
