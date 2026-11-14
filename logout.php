<?php
// Start session, destroy all session data, and redirect to login
session_start();
session_destroy();
header("Location: login.php");
exit;
?>
