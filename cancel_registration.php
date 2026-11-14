<?php
require 'db.php';
checkLogin(); // Ensure user is logged in

// Delete registration record on POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $_POST['event_id']);
    $stmt->execute();
}

// Redirect back to My Events page
header("Location: my_events.php?msg=Registration+cancelled");
exit;
?>
