<?php
// Start user session to track login state across pages
session_start();

// Set default PHP timezone to India (IST)
date_default_timezone_set('Asia/Kolkata');

// Connect to MySQL database (host, username, password, database_name, port)
$conn = new mysqli("127.0.0.1", "root", "", "campus_events", 3306);

// Stop script if database connection fails
if ($conn->connect_error) die("DB Error: " . $conn->connect_error);

// Set MySQL session timezone to match PHP (+05:30)
$conn->query("SET time_zone = '+05:30'");

// Helper function to protect pages from unauthorized access
function checkLogin($adminOnly = false) {
    // Redirect to login if user is not logged in, or if admin role is required but missing
    if (!isset($_SESSION['user_id']) || ($adminOnly && $_SESSION['role'] != 'admin')) {
        header("Location: login.php");
        exit;
    }
}
?>
