<?php
// register_event.php - called by JavaScript (AJAX), NOT opened as a page.
// It does the registration and replies with JSON data instead of HTML.

session_start();
require 'db.php';

// Tell the browser: "my reply is JSON data"
header('Content-Type: application/json');

// A small helper to send the reply and stop
function reply($success, $message, $seats_left = null, $max_seats = null) {
    echo json_encode([
        "success"    => $success,
        "message"    => $message,
        "seats_left" => $seats_left,
        "max_seats"  => $max_seats
    ]);
    exit;
}

// 1. Must be logged in
if (!isset($_SESSION['user_id'])) {
    reply(false, "Please log in first.");
}

$user_id  = $_SESSION['user_id'];
$event_id = (int) $_POST['event_id'];

// 2. Get the event and how many have registered
$stmt = $conn->prepare(
    "SELECT e.max_seats, e.event_date, COUNT(r.id) AS registered_count
     FROM events e
     LEFT JOIN registrations r ON e.id = r.event_id
     WHERE e.id = ?
     GROUP BY e.id"
);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    reply(false, "Event not found.");
}

// 3. Can't register for an event that already happened
if ($event['event_date'] < date("Y-m-d")) {
    reply(false, "This event has already ended.");
}

// 4. Check if this student is already registered
$stmt = $conn->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
$stmt->bind_param("ii", $user_id, $event_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    reply(false, "You are already registered for this event.");
}

// 5. Check if seats are left
$seats_left = $event['max_seats'] - $event['registered_count'];
if ($seats_left <= 0) {
    reply(false, "Sorry, this event is full.");
}

// 6. All checks passed - save the registration
$stmt = $conn->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $event_id);
$stmt->execute();

// 7. Reply with the new seat count so the page can update itself
reply(true, "Registered successfully!", $seats_left - 1, $event['max_seats']);
?>
