<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'baguixplore');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Google Maps API Key
define('GOOGLE_MAPS_API_KEY', 'AIzaSyCiZlSTrJxoYQtMIML4B7XZJbGVKBBEnCg');

// Base URL
$base_url = "http://localhost/neww/baguixplore";

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function to sanitize input
function sanitize($data) {
    global $conn;
    $data = str_replace(["\r\n", "\r", "\n"], " ", trim($data));
    return mysqli_real_escape_string($conn, htmlspecialchars($data));
}
?>
