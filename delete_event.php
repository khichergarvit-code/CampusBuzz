<?php
require 'db.php';
checkLogin(true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $_POST['event_id']);
    $stmt->execute();
}
header("Location: admin.php?msg=Event+deleted");
exit;
?>
