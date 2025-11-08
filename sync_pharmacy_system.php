<?php
include 'db.php';

echo "<h2>Pharmacy System Synchronization</h2>";
echo "<p>Setting up the synchronized pharmacy system...</p>";

$errors = [];
$success_count = 0;

// Disable foreign key checks temporarily
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Step 1: Drop and recreate tables in correct order
echo "<h3>Step 1: Setting up database tables</h3>";

// Drop tables in reverse dependency order
$tables_to_drop = ['activities', 'medicines', 'user_pharmacies', 'pharmacies'];
foreach ($tables_to_drop as $table) {
    if ($conn->query("DROP TABLE IF EXISTS $table") === TRUE) {
        echo "<p style='color: blue;'>ℹ Dropped table: $table</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Could not drop table $table (may not exist): " . $conn->error . "</p>";
    }
}

// Create pharmacies table
$sql = "CREATE TABLE pharmacies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    contact VARCHAR(100),
    lat DECIMAL(10,8),
    lng DECIMAL(11,8),
    verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color: green;'>✓ pharmacies table created successfully</p>";
    $success_count++;
} else {
    echo "<p style='color: red;'>✗ Error creating pharmacies table: " . $conn->error . "</p>";
    $errors[] = "pharmacies table: " . $conn->error;
}

// Create user_pharmacies table
$sql = "CREATE TABLE user_pharmacies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pharmacy_id INT NOT NULL,
    user_role ENUM('owner', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_pharmacy (user_id, pharmacy_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color: green;'>✓ user_pharmacies table created successfully</p>";
    $success_count++;
} else {
    echo "<p style='color: red;'>✗ Error creating user_pharmacies table: " . $conn->error . "</p>";
    $errors[] = "user_pharmacies table: " . $conn->error;
}

// Create medicines table
$sql = "CREATE TABLE medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    generic_name VARCHAR(255),
    manufacturer VARCHAR(255),
    dosage_form VARCHAR(100),
    strength VARCHAR(100),
    description TEXT,
    uses TEXT,
    side_effects TEXT,
    contraindications TEXT,
    stock_quantity INT DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color: green;'>✓ Medicines table created successfully</p>";
    $success_count++;
} else {
    echo "<p style='color: red;'>✗ Error creating medicines table: " . $conn->error . "</p>";
    $errors[] = "medicines table: " . $conn->error;
}

// Create activities table
$sql = "CREATE TABLE activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color: green;'>✓ Activities table created successfully</p>";
    $success_count++;
} else {
    echo "<p style='color: red;'>✗ Error creating activities table: " . $conn->error . "</p>";
    $errors[] = "activities table: " . $conn->error;
}

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Step 2: Create sample pharmacy
echo "<h3>Step 2: Creating sample pharmacy</h3>";

$sample_pharmacy = [
    'name' => 'Main City Pharmacy',
    'address' => '123 Healthcare Avenue, Medical District',
    'contact' => 'contact@citypharmacy.com',
    'lat' => 40.7128,
    'lng' => -74.0060
];

$stmt = $conn->prepare("INSERT INTO pharmacies (name, address, contact, lat, lng, verified) VALUES (?, ?, ?, ?, ?, 1)");
if ($stmt) {
    $stmt->bind_param("sssdd", 
        $sample_pharmacy['name'],
        $sample_pharmacy['address'],
        $sample_pharmacy['contact'],
        $sample_pharmacy['lat'],
        $sample_pharmacy['lng']
    );
    
    if ($stmt->execute()) {
        $pharmacy_id = $stmt->insert_id;
        echo "<p style='color: green;'>✓ Created sample pharmacy: " . $sample_pharmacy['name'] . " (ID: " . $pharmacy_id . ")</p>";
        $success_count++;
    } else {
        echo "<p style='color: red;'>✗ Error creating sample pharmacy: " . $stmt->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<p style='color: red;'>✗ Error preparing pharmacy statement</p>";
}

// Step 3: Link users to pharmacy
echo "<h3>Step 3: Linking users to pharmacy</h3>";

// Get all users
$users_result = $conn->query("SELECT id, name, role FROM users");
if ($users_result && $users_result->num_rows > 0) {
    $linked_count = 0;
    while ($user = $users_result->fetch_assoc()) {
        $user_role = ($user['role'] == 'owner' || $user['role'] == 'admin') ? 'owner' : 'staff';
        
        $stmt = $conn->prepare("INSERT IGNORE INTO user_pharmacies (user_id, pharmacy_id, user_role) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iis", $user['id'], $pharmacy_id, $user_role);
            if ($stmt->execute()) {
                $linked_count++;
                echo "<p style='color: green;'>✓ Linked " . $user['name'] . " as " . $user_role . "</p>";
                $success_count++;
            }
            $stmt->close();
        }
    }
    echo "<p style='color: green;'>✓ Linked $linked_count users to pharmacy</p>";
} else {
    echo "<p style='color: orange;'>⚠ No users found to link</p>";
}

// Step 4: Add sample medicines
echo "<h3>Step 4: Adding sample medicines</h3>";

$sample_medicines = [
    ['Paracetamol 500mg', 'Paracetamol', 'Generic Pharma', 'Tablet', '500mg', 'Pain reliever and fever reducer', 'Headache, fever, pain', 'Rare side effects', 'None known', 100, 5.50],
    ['Amoxicillin 250mg', 'Amoxicillin', 'Antibio Inc', 'Capsule', '250mg', 'Antibiotic for bacterial infections', 'Bacterial infections', 'Nausea, diarrhea', 'Penicillin allergy', 50, 12.75],
    ['Vitamin C 1000mg', 'Ascorbic Acid', 'Health Supplements', 'Tablet', '1000mg', 'Vitamin C supplement', 'Immune support', 'Generally well tolerated', 'None', 200, 8.99],
    ['Ibuprofen 400mg', 'Ibuprofen', 'Pain Relief Inc', 'Tablet', '400mg', 'Anti-inflammatory pain reliever', 'Arthritis, muscle pain', 'Stomach upset', 'Stomach ulcers', 75, 7.25],
    ['Cetirizine 10mg', 'Cetirizine', 'Allergy Care', 'Tablet', '10mg', 'Antihistamine for allergies', 'Hay fever, allergies', 'Drowsiness', 'None known', 150, 4.99]
];

$added_medicines = 0;
foreach ($sample_medicines as $medicine) {
    $stmt = $conn->prepare("
        INSERT INTO medicines (pharmacy_id, name, generic_name, manufacturer, dosage_form, strength, description, uses, side_effects, contraindications, stock_quantity, price) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if ($stmt) {
        $stmt->bind_param("isssssssssid", 
            $pharmacy_id, 
            $medicine[0], $medicine[1], $medicine[2], $medicine[3], $medicine[4],
            $medicine[5], $medicine[6], $medicine[7], $medicine[8], $medicine[9], $medicine[10]
        );
        
        if ($stmt->execute()) {
            $added_medicines++;
            echo "<p style='color: green;'>✓ Added: " . $medicine[0] . " - Stock: " . $medicine[9] . " - Price: $" . $medicine[10] . "</p>";
            $success_count++;
        } else {
            echo "<p style='color: red;'>✗ Error adding " . $medicine[0] . ": " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color: red;'>✗ Error preparing statement for: " . $medicine[0] . "</p>";
    }
}
echo "<p style='color: green;'>✓ Added $added_medicines sample medicines</p>";

// Step 5: Add sample activities
echo "<h3>Step 5: Adding sample activities</h3>";

// Get first user ID
$user_result = $conn->query("SELECT id, name FROM users LIMIT 1");
if ($user_result && $user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    $user_id = $user['id'];
    $user_name = $user['name'];
    
    $sample_activities = [
        ['System Setup', 'Pharmacy management system initialized successfully'],
        ['User Login', $user_name . ' logged into the system'],
        ['Inventory Update', 'Added ' . $added_medicines . ' sample medicines to inventory'],
        ['Pharmacy Registration', 'Main City Pharmacy registered in system'],
        ['Staff Assignment', 'All users linked to pharmacy with appropriate roles']
    ];
    
    $added_activities = 0;
    foreach ($sample_activities as $activity) {
        $stmt = $conn->prepare("INSERT INTO activities (user_id, action, description) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iss", $user_id, $activity[0], $activity[1]);
            
            if ($stmt->execute()) {
                $added_activities++;
                echo "<p style='color: green;'>✓ Activity: " . $activity[0] . "</p>";
                $success_count++;
            }
            $stmt->close();
        }
    }
    echo "<p style='color: green;'>✓ Added $added_activities sample activities</p>";
} else {
    echo "<p style='color: orange;'>⚠ No users found for activities</p>";
}

// Step 6: Migrate existing user pharmacy data (if any)
echo "<h3>Step 6: Checking for existing pharmacy data</h3>";
$result = $conn->query("
    SELECT id, pharmacy_name, pharmacy_address, pharmacy_contact, pharmacy_lat, pharmacy_lng 
    FROM users 
    WHERE pharmacy_name IS NOT NULL AND pharmacy_name != '' 
    AND pharmacy_address IS NOT NULL AND pharmacy_address != ''
");

if ($result && $result->num_rows > 0) {
    echo "<p>Found " . $result->num_rows . " users with pharmacy data to migrate</p>";
    
    $migrated_count = 0;
    while ($user = $result->fetch_assoc()) {
        // Create pharmacy from user data
        $stmt = $conn->prepare("INSERT INTO pharmacies (name, address, contact, lat, lng, verified) VALUES (?, ?, ?, ?, ?, 0)");
        if ($stmt) {
            $stmt->bind_param(
                "sssdd", 
                $user['pharmacy_name'], 
                $user['pharmacy_address'], 
                $user['pharmacy_contact'],
                $user['pharmacy_lat'],
                $user['pharmacy_lng']
            );
            
            if ($stmt->execute()) {
                $new_pharmacy_id = $stmt->insert_id;
                
                // Link user to their pharmacy
                $link_stmt = $conn->prepare("INSERT INTO user_pharmacies (user_id, pharmacy_id, user_role) VALUES (?, ?, 'owner')");
                if ($link_stmt) {
                    $link_stmt->bind_param("ii", $user['id'], $new_pharmacy_id);
                    
                    if ($link_stmt->execute()) {
                        $migrated_count++;
                        $success_count++;
                        echo "<p style='color: green;'>✓ Migrated: " . htmlspecialchars($user['pharmacy_name']) . "</p>";
                    }
                    $link_stmt->close();
                }
            }
            $stmt->close();
        }
    }
    echo "<p style='color: green;'>✓ Migrated $migrated_count user pharmacies</p>";
} else {
    echo "<p>No additional pharmacy data found in users table</p>";
}

// Final Summary
echo "<h3>🎉 Synchronization Complete!</h3>";

// Show database status
echo "<h4>Database Status Report:</h4>";
$tables = [
    'pharmacies' => 'Pharmacies',
    'user_pharmacies' => 'User Links', 
    'medicines' => 'Medicines',
    'activities' => 'Activities'
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
foreach ($tables as $table => $label) {
    $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($count_result) {
        $count = $count_result->fetch_assoc()['count'];
        $status = $count > 0 ? '✅' : '⚠️';
        echo "<p><strong>$status $label:</strong> $count records</p>";
    }
}
echo "</div>";

if (count($errors) > 0) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "<h4 style='color: #721c24;'>❌ Errors Encountered:</h4>";
    foreach ($errors as $error) {
        echo "<p style='color: #721c24;'>• $error</p>";
    }
    echo "</div>";
}

echo "<div class='mt-4 p-4 bg-success text-white rounded'>";
echo "<h4>✅ Pharmacy System Ready!</h4>";
echo "<p><strong>Total successful operations: $success_count</strong></p>";
echo "<p>Your pharmacy management system is now fully set up and ready to use.</p>";
echo "<div class='mt-3'>";
echo "<a href='staff_dashboard.php' class='btn btn-light btn-lg me-2'>🚀 Go to Staff Dashboard</a>";
echo "<a href='admin_dashboard.php' class='btn btn-outline-light btn-lg'>⚙️ Go to Admin Dashboard</a>";
echo "</div>";
echo "</div>";

$conn->close();
?>