<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mypharma_v1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to utf8
$conn->set_charset("utf8mb4");

// Error reporting for development
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>