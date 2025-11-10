<?php
/**
 * Smart Database Migration Runner
 * Checks if columns exist before adding them
 * Access via: http://yourdomain.com/database/run_migration_smart.php
 *
 * IMPORTANT: Delete this file after running the migration for security!
 */

require_once '../includes/config.php';

// Security check
if (APP_ENV !== 'development') {
    die('Migration can only be run in development mode. Please update APP_ENV in .env file temporarily.');
}

echo "<h1>Running Smart Database Migration</h1>";
echo "<pre>";

try {
    $conn = db()->getConnection();
    $successCount = 0;
    $errorCount = 0;
    $skippedCount = 0;

    // Helper function to check if column exists
    function columnExists($conn, $table, $column) {
        $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $result && $result->num_rows > 0;
    }

    // Helper function to check if index exists
    function indexExists($conn, $table, $index) {
        $result = $conn->query("SHOW INDEX FROM `$table` WHERE Key_name = '$index'");
        return $result && $result->num_rows > 0;
    }

    // Define all columns to add
    $columns = [
        // Guest Information
        ['name' => 'guest_full_name', 'definition' => 'VARCHAR(255)', 'after' => 'phone'],
        ['name' => 'guest_email', 'definition' => 'VARCHAR(255)', 'after' => 'guest_full_name'],
        ['name' => 'guest_nationality', 'definition' => 'VARCHAR(100)', 'after' => 'guest_email'],
        ['name' => 'guest_date_of_birth', 'definition' => 'DATE', 'after' => 'guest_nationality'],
        ['name' => 'guest_gender', 'definition' => "ENUM('male', 'female', 'other')", 'after' => 'guest_date_of_birth'],
        ['name' => 'guest_passport_number', 'definition' => 'VARCHAR(100)', 'after' => 'guest_gender'],
        ['name' => 'guest_address', 'definition' => 'TEXT', 'after' => 'guest_passport_number'],

        // Travel Information
        ['name' => 'purpose_of_visit', 'definition' => 'TEXT', 'after' => 'guest_address'],
        ['name' => 'arrival_date', 'definition' => 'DATE', 'after' => 'purpose_of_visit'],
        ['name' => 'arrival_flight', 'definition' => 'VARCHAR(100)', 'after' => 'arrival_date'],
        ['name' => 'departure_date', 'definition' => 'DATE', 'after' => 'arrival_flight'],
        ['name' => 'departure_flight', 'definition' => 'VARCHAR(100)', 'after' => 'departure_date'],

        // Emergency Contact
        ['name' => 'emergency_contact_name', 'definition' => 'VARCHAR(255)', 'after' => 'departure_flight'],
        ['name' => 'emergency_contact_relationship', 'definition' => 'VARCHAR(100)', 'after' => 'emergency_contact_name'],
        ['name' => 'emergency_contact_phone', 'definition' => 'VARCHAR(20)', 'after' => 'emergency_contact_relationship'],
        ['name' => 'emergency_contact_email', 'definition' => 'VARCHAR(255)', 'after' => 'emergency_contact_phone'],

        // Declaration and Agreement
        ['name' => 'terms_accepted', 'definition' => 'BOOLEAN DEFAULT 0', 'after' => 'emergency_contact_email'],
        ['name' => 'signature_data', 'definition' => 'TEXT', 'after' => 'terms_accepted'],
        ['name' => 'signature_date', 'definition' => 'TIMESTAMP NULL', 'after' => 'signature_data'],

        // Visa/Foreigner status
        ['name' => 'is_foreigner', 'definition' => 'BOOLEAN DEFAULT 0', 'after' => 'signature_date'],
        ['name' => 'requires_visa_letter', 'definition' => 'BOOLEAN DEFAULT 0', 'after' => 'is_foreigner'],

        // PDF Documents
        ['name' => 'booking_letter_path', 'definition' => 'VARCHAR(255)', 'after' => 'requires_visa_letter'],
        ['name' => 'visa_letter_path', 'definition' => 'VARCHAR(255)', 'after' => 'booking_letter_path'],
    ];

    echo "=================================\n";
    echo "Adding Columns to bookings table\n";
    echo "=================================\n\n";

    foreach ($columns as $column) {
        echo "Checking column: {$column['name']}... ";

        if (columnExists($conn, 'bookings', $column['name'])) {
            echo "SKIPPED (already exists)\n";
            $skippedCount++;
        } else {
            try {
                // Check if the AFTER column exists, otherwise add at end
                $afterClause = '';
                if (columnExists($conn, 'bookings', $column['after'])) {
                    $afterClause = " AFTER `{$column['after']}`";
                }

                $sql = "ALTER TABLE bookings ADD COLUMN `{$column['name']}` {$column['definition']}{$afterClause}";
                $conn->query($sql);
                echo "✓ SUCCESS\n";
                $successCount++;
            } catch (Exception $e) {
                echo "✗ ERROR: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
    }

    echo "\n=================================\n";
    echo "Adding Indexes\n";
    echo "=================================\n\n";

    // Add indexes
    $indexes = [
        ['name' => 'idx_is_foreigner', 'column' => 'is_foreigner'],
        ['name' => 'idx_guest_nationality', 'column' => 'guest_nationality'],
    ];

    foreach ($indexes as $index) {
        echo "Checking index: {$index['name']}... ";

        if (indexExists($conn, 'bookings', $index['name'])) {
            echo "SKIPPED (already exists)\n";
            $skippedCount++;
        } else {
            if (!columnExists($conn, 'bookings', $index['column'])) {
                echo "SKIPPED (column {$index['column']} doesn't exist yet)\n";
                $skippedCount++;
            } else {
                try {
                    $sql = "CREATE INDEX `{$index['name']}` ON bookings(`{$index['column']}`)";
                    $conn->query($sql);
                    echo "✓ SUCCESS\n";
                    $successCount++;
                } catch (Exception $e) {
                    echo "✗ ERROR: " . $e->getMessage() . "\n";
                    $errorCount++;
                }
            }
        }
    }

    echo "\n\n=================================\n";
    echo "Migration Complete!\n";
    echo "=================================\n";
    echo "Successful: $successCount\n";
    echo "Skipped: $skippedCount\n";
    echo "Errors: $errorCount\n";
    echo "\n";

    if ($errorCount === 0) {
        echo "✓ All migrations applied successfully!\n";
        echo "\n⚠️  IMPORTANT: Please delete this file (database/run_migration_smart.php) for security reasons!\n";
        echo "\nYou can now safely delete database/run_migration.php as well.\n";
    } else {
        echo "⚠️  Some migrations failed. Please review the errors above.\n";
    }

} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
