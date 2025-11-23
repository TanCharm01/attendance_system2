<?php
// Database connection parameters
$host     = "127.0.0.1";   // MySQL server hostname or IP
$username = "root";        // MySQL username
$password = "";            // MySQL password
$database = "course_db";     // Database name

// Establish connection
$conn = mysqli_connect($host, $username, $password, $database, 3307);

// Check connection
if (!$conn) {
    // mysqli_connect_error() returns a string description of the last connect error
    die("Connection failed: " . mysqli_connect_error());
}
?>
