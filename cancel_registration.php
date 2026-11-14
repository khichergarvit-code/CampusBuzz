<?php
session_start();
require 'db.php';

// Must be logged in to cancel a registration
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// This page only accepts POST (the form on my_events.php)
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: my_events.php");
    exit;
}

$event_id = (int) $_POST['event_id'];
$user_id  = (int) $_SESSION['user_id'];

// Delete only THIS student's registration for this event.
// Matching user_id to the logged-in user means a student can never
// cancel someone else's registration.
$stmt = $conn->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
$stmt->bind_param("ii", $user_id, $event_id);
$stmt->execute();

// Go back to the list with a short success message
header("Location: my_events.php?msg=" . urlencode("Registration cancelled."));
exit;
?>
