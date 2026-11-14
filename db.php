<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

$conn = new mysqli("127.0.0.1", "root", "", "campus_events", 3306);
if ($conn->connect_error) die("DB Error: " . $conn->connect_error);
$conn->query("SET time_zone = '+05:30'");

function checkLogin($adminOnly = false) {
    if (!isset($_SESSION['user_id']) || ($adminOnly && $_SESSION['role'] != 'admin')) {
        header("Location: login.php");
        exit;
    }
}
?>
