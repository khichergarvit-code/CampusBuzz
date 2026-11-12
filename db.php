<?php
// db.php - connects PHP to the MySQL database.
// Every other page includes this file, so the connection is written only once.

// Use Indian time for every date/time check (badges, "event ended", etc.)
date_default_timezone_set('Asia/Kolkata');

$host     = "127.0.0.1";       // the database is on this same computer
$port     = 8889;              // MAMP's MySQL port (XAMPP on Windows uses 3306)
$username = "root";
$password = "root";            // MAMP's default password (XAMPP's is empty: "")
$database = "campus_events";

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Make MySQL use Indian time too, so PHP and the database always agree
$conn->query("SET time_zone = '+05:30'");
?>
