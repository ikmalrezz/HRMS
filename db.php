<?php
$host = "localhost"; // Change if using an external database
$user = "root"; // Change if using a different MySQL user
$pass = ""; // Change if your MySQL has a password
$dbname = "workfusion_system"; // Ensure this matches your database name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
