<?php
// db.php - Database connection helper

$host = 'localhost';
$db   = 'authentication_db'; // Change to your database name
$user = 'root'; // Default XAMPP user
$pass = '';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?>