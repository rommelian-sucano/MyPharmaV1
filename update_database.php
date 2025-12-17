<?php
include 'db.php';

echo "<h2>Database Update Script</h2>";
echo "<p>This script will add the missing columns to your database tables.</p>";

$errors = [];

// Add pharmacy columns to users table
try {
    $sql = "ALTER TABLE users ADD COLUMN pharmacy_name VARCHAR(100) DEFAULT NULL, ADD COLUMN pharmacy_address TEXT DEFAULT NULL, ADD COLUMN pharmacy_lat DECIMAL(10,8) DEFAULT NULL, ADD COLUMN pharmacy_lng DECIMAL(11,8) DEFAULT NULL, ADD COLUMN pharmacy_contact VARCHAR(20) DEFAULT NULL";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Added pharmacy columns to users table</p>";
    } else {
        // Check if columns already exist
        if (strpos($conn->error, 'Duplicate column name') !== false) {
            echo "<p style='color: blue;'>ℹ Pharmacy columns already exist in users table</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding pharmacy columns to users table: " . $conn->error . "</p>";
            $errors[] = "users table: " . $conn->error;
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Exception adding pharmacy columns to users table: " . $e->getMessage() . "</p>";
    $errors[] = "users table exception: " . $e->getMessage();
}

// Add created_at column to users table
try {
    $sql = "ALTER TABLE users ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Added created_at column to users table</p>";
    } else {
        // Check if column already exists
        if (strpos($conn->error, 'Duplicate column name') !== false) {
            echo "<p style='color: blue;'>ℹ Column created_at already exists in users table</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding created_at column to users table: " . $conn->error . "</p>";
            $errors[] = "users table: " . $conn->error;
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Exception adding created_at column to users table: " . $e->getMessage() . "</p>";
    $errors[] = "users table exception: " . $e->getMessage();
}

// Add created_at column to pharmacies table
try {
    $sql = "ALTER TABLE pharmacies ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Added created_at column to pharmacies table</p>";
    } else {
        // Check if column already exists
        if (strpos($conn->error, 'Duplicate column name') !== false) {
            echo "<p style='color: blue;'>ℹ Column created_at already exists in pharmacies table</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding created_at column to pharmacies table: " . $conn->error . "</p>";
            $errors[] = "pharmacies table: " . $conn->error;
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Exception adding created_at column to pharmacies table: " . $e->getMessage() . "</p>";
    $errors[] = "pharmacies table exception: " . $e->getMessage();
}

// Add created_at column to notifications table
try {
    $sql = "ALTER TABLE notifications ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Added created_at column to notifications table</p>";
    } else {
        // Check if column already exists
        if (strpos($conn->error, 'Duplicate column name') !== false) {
            echo "<p style='color: blue;'>ℹ Column created_at already exists in notifications table</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding created_at column to notifications table: " . $conn->error . "</p>";
            $errors[] = "notifications table: " . $conn->error;
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Exception adding created_at column to notifications table: " . $e->getMessage() . "</p>";
    $errors[] = "notifications table exception: " . $e->getMessage();
}

// Update existing records with created_at timestamps if they're still default
try {
    $sql = "UPDATE users SET created_at = CURRENT_TIMESTAMP WHERE created_at = '0000-00-00 00:00:00' OR created_at IS NULL";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Updated existing user records with timestamps</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠ Warning updating user records: " . $e->getMessage() . "</p>";
}

try {
    $sql = "UPDATE pharmacies SET created_at = CURRENT_TIMESTAMP WHERE created_at = '0000-00-00 00:00:00' OR created_at IS NULL";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Updated existing pharmacy records with timestamps</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠ Warning updating pharmacy records: " . $e->getMessage() . "</p>";
}

try {
    $sql = "UPDATE notifications SET created_at = CURRENT_TIMESTAMP WHERE created_at = '0000-00-00 00:00:00' OR created_at IS NULL";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Updated existing notification records with timestamps</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠ Warning updating notification records: " . $e->getMessage() . "</p>";
}

if (empty($errors)) {
    echo "<h3 style='color: green;'>✅ Database update completed successfully!</h3>";
    echo "<p>You can now refresh your admin dashboard.</p>";
} else {
    echo "<h3 style='color: red;'>❌ Database update completed with some errors:</h3>";
    foreach ($errors as $error) {
        echo "<p style='color: red;'>- " . $error . "</p>";
    }
    echo "<p>Please check your database manually if issues persist.</p>";
}

$conn->close();
?>