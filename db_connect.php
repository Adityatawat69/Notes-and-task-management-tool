<?php
// Database configuration
$config = [
    'servername' => 'localhost',
    'username' => 'root',     // Change this to a dedicated user in production
    'password' => '',         // Use a strong password in production
    'dbname' => 'users'       // Your database name
];

// Create a connection
try {
    $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset to utf8mb4
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error setting charset: " . $conn->error);
    }

    // Set error mode (comment out in production)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

} catch (Exception $e) {
    // Log the error (in a production environment)
    error_log("Database connection error: " . $e->getMessage());
    
    // Display a generic error message
    die("Sorry, a database error occurred. Please try again later.");
}
?>