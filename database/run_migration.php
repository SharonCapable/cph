<?php
/**
 * Database Migration Runner
 * Run this script once to apply the expanded booking fields migration
 * Access via: http://yourdomain.com/database/run_migration.php
 *
 * IMPORTANT: Delete this file after running the migration for security!
 */

require_once '../includes/config.php';

// Security check - only run in development or with proper authentication
if (APP_ENV !== 'development') {
    die('Migration can only be run in development mode or by a super admin. Please update APP_ENV in .env file temporarily.');
}

// Read the migration file
$migrationFile = __DIR__ . '/migrations/add_expanded_booking_fields.sql';

if (!file_exists($migrationFile)) {
    die('Migration file not found: ' . $migrationFile);
}

$sql = file_get_contents($migrationFile);

// Split by semicolons to get individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

echo "<h1>Running Database Migration</h1>";
echo "<pre>";

try {
    $conn = db()->getConnection();
    $successCount = 0;
    $errorCount = 0;

    foreach ($statements as $statement) {
        // Skip empty statements and comments
        if (empty($statement) || strpos(trim($statement), '--') === 0) {
            continue;
        }

        echo "\n--- Executing Statement ---\n";
        echo substr($statement, 0, 100) . "...\n";

        try {
            $conn->query($statement);
            echo "✓ SUCCESS\n";
            $successCount++;
        } catch (Exception $e) {
            echo "✗ ERROR: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }

    echo "\n\n=================================\n";
    echo "Migration Complete!\n";
    echo "=================================\n";
    echo "Successful: $successCount\n";
    echo "Errors: $errorCount\n";
    echo "\n";

    if ($errorCount === 0) {
        echo "✓ All migrations applied successfully!\n";
        echo "\n⚠️  IMPORTANT: Please delete this file (database/run_migration.php) for security reasons!\n";
    } else {
        echo "⚠️  Some migrations failed. Please review the errors above.\n";
    }

} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
