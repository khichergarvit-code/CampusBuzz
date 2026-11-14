<?php
require 'db.php';
checkLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $_POST['event_id']);
    $stmt->execute();
}
header("Location: my_events.php?msg=Registration+cancelled");
exit;
?>
