<?php
// Database connection configuration
$host = "localhost";
$user = "root";
$password = "";
$database = "online_learning";

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>