<?php
/**
 * Database Configuration for Vercel
 * Support environment variables
 */

// Get environment variables (Vercel akan set ini)
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: 'db_ngaji';
$port = getenv('DB_PORT') ?: 3306;

// Create connection
$conn = new mysqli($host, $user, $pass, $db, $port);

// Check connection
if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error);
    die(json_encode([
        'status' => 'error',
        'message' => 'Database connection failed',
        'error' => $conn->connect_error
    ]));
}

// Set charset
$conn->set_charset("utf8mb4");

// Set timezone
$conn->query("SET time_zone = '+07:00'");

// Helper function untuk escape string
function escape_string($conn, $string) {
    return $conn->real_escape_string($string);
}
?>
