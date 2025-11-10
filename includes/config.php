<?php
/**
 * CirclePoint Homes - Configuration
 * Database connection and app settings
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables
function loadEnv($path) {
    if (!file_exists($path)) {
        die('Environment file not found');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }
    }
}

loadEnv(__DIR__ . '/../.env');

// Database Configuration
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASSWORD', $_ENV['DB_PASSWORD']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_PORT', $_ENV['DB_PORT']);

// App Configuration
define('APP_NAME', $_ENV['APP_NAME']);
define('APP_URL', $_ENV['APP_URL']);
define('APP_ENV', $_ENV['APP_ENV']);

// Super Admin
define('SUPER_ADMIN_EMAIL', $_ENV['SUPER_ADMIN_EMAIL']);

// Session Configuration
define('SESSION_LIFETIME', (int)$_ENV['SESSION_LIFETIME']);
define('SESSION_NAME', $_ENV['SESSION_NAME']);

// File Upload
define('MAX_FILE_SIZE', (int)$_ENV['MAX_FILE_SIZE']);
define('ALLOWED_IMAGE_TYPES', explode(',', $_ENV['ALLOWED_IMAGE_TYPES']));
define('MAX_IMAGES_PER_PROPERTY', (int)$_ENV['MAX_IMAGES_PER_PROPERTY']);

// Contact Info
define('WHATSAPP_NUMBER', $_ENV['WHATSAPP_NUMBER']);
define('INSTAGRAM_HANDLE', $_ENV['INSTAGRAM_HANDLE']);
define('LINKEDIN_COMPANY', $_ENV['LINKEDIN_COMPANY']);

// Email Configuration
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS']);
define('MAIL_FROM_NAME', trim($_ENV['MAIL_FROM_NAME'], '"'));
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? null);
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? null);
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? null);
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? null);
define('SMTP_ENCRYPTION', $_ENV['SMTP_ENCRYPTION'] ?? null);

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');

// Create database connection
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $this->connection = new mysqli(
                DB_HOST,
                DB_USER,
                DB_PASSWORD,
                DB_NAME,
                DB_PORT
            );

            if ($this->connection->connect_error) {
                throw new Exception('Database connection failed: ' . $this->connection->connect_error);
            }

            $this->connection->set_charset('utf8mb4');
        } catch (Exception $e) {
            die('Database error: ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $this->connection->error);
        }

        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }

    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function insert($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $this->connection->insert_id;
    }

    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }
}

// Helper function to get database instance
function db() {
    return Database::getInstance();
}

// Error handling
function handleError($message, $statusCode = 500) {
    http_response_code($statusCode);
    if (APP_ENV === 'development') {
        echo json_encode(['error' => $message]);
    } else {
        echo json_encode(['error' => 'An error occurred']);
    }
    exit;
}

// CORS headers for API
function setCorsHeaders() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

// JSON response helper
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Test database connection on config load
try {
    db()->getConnection();
} catch (Exception $e) {
    if (APP_ENV === 'development') {
        die('Database connection failed: ' . $e->getMessage());
    } else {
        die('Database connection failed. Please check configuration.');
    }
}
