<?php
require 'db.php';
header('Content-Type: application/json'); // Return JSON response for AJAX call

// Helper function to send JSON response and stop execution
function send($ok, $msg, $left = null, $max = null) {
    echo json_encode(["success" => $ok, "message" => $msg, "seats_left" => $left, "max_seats" => $max]);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) send(false, "Please login first.");

$uid = (int)$_SESSION['user_id'];
$eid = (int)($_POST['event_id'] ?? 0);

// Fetch event details and current registration count
$stmt = $conn->prepare("SELECT e.max_seats, e.event_date, COUNT(r.id) AS reg FROM events e LEFT JOIN registrations r ON e.id = r.event_id WHERE e.id = ? GROUP BY e.id");
$stmt->bind_param("i", $eid);
$stmt->execute();
$ev = $stmt->get_result()->fetch_assoc();

// Validate event existence and date
if (!$ev) send(false, "Event not found.");
if ($ev['event_date'] < date("Y-m-d")) send(false, "Event already ended.");

// Prevent duplicate registration by the same student
$chk = $conn->query("SELECT id FROM registrations WHERE user_id = $uid AND event_id = $eid");
if ($chk->num_rows > 0) send(false, "Already registered.");

// Check if seats are available
$left = $ev['max_seats'] - $ev['reg'];
if ($left <= 0) send(false, "Event is full.");

// Insert new registration record
$stmt = $conn->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
$stmt->bind_param("ii", $uid, $eid);
$stmt->execute();

// Return success with updated remaining seats
send(true, "Registered!", $left - 1, $ev['max_seats']);
?>
