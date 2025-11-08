<?php
include 'db.php';

// Create invitations table
$sql = "CREATE TABLE IF NOT EXISTS invitations (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(32) NOT NULL UNIQUE,
    invite_type ENUM('staff', 'pharmacy_owner') NOT NULL,
    created_by INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used TINYINT(1) DEFAULT 0,
    used_by INT(11) NULL,
    used_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (used_by) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Invitations table created successfully or already exists.";
} else {
    echo "Error creating invitations table: " . $conn->error;
}

$conn->close();
?>