<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get every event this student registered for (JOIN to get the event details)
$stmt = $conn->prepare(
    "SELECT e.id, e.title, e.category, e.venue, e.event_date, e.event_time, r.registered_at
     FROM registrations r
     JOIN events e ON r.event_id = e.id
     WHERE r.user_id = ?
     ORDER BY e.event_date ASC"
);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$my_events = $stmt->get_result();

$today = date("Y-m-d");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Events - CampusBuzz</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav class="navbar">
        <span class="logo">🎓 CampusBuzz</span>
        <div>
            <a href="dashboard.php" class="nav-link">All Events</a>
            <a href="logout.php" class="nav-link">Log Out</a>
        </div>
    </nav>

    <div class="container">
        <h2>My Registered Events (<?php echo $my_events->num_rows; ?>)</h2>

        <?php if (isset($_GET['msg'])) { ?>
            <p class="success-box"><?php echo htmlspecialchars($_GET['msg']); ?></p>
        <?php } ?>

        <?php if ($my_events->num_rows == 0) { ?>
            <p>You haven't registered for any events yet. <a href="dashboard.php">Browse events →</a></p>
        <?php } else { ?>
            <table class="admin-table">
                <tr>
                    <th>Event</th>
                    <th>Category</th>
                    <th>Date &amp; Time</th>
                    <th>Venue</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php while ($e = $my_events->fetch_assoc()) {
                    if ($e['event_date'] < $today)       $status = "Completed";
                    elseif ($e['event_date'] == $today)  $status = "Today";
                    else                                 $status = "Upcoming";
                ?>
                    <tr>
                        <td><b><?php echo htmlspecialchars($e['title']); ?></b></td>
                        <td><?php echo $e['category']; ?></td>
                        <td><?php echo date("d M Y", strtotime($e['event_date'])); ?>,
                            <?php echo date("h:i A", strtotime($e['event_time'])); ?></td>
                        <td><?php echo htmlspecialchars($e['venue']); ?></td>
                        <td><span class="badge <?php echo strtolower($status); ?>"><?php echo $status; ?></span></td>
                        <td>
                            <?php if ($status != "Completed") { ?>
                                <!-- Cancel uses POST + a confirm popup, like the admin Delete button -->
                                <form method="POST" action="cancel_registration.php" class="inline-form"
                                      onsubmit="return confirm('Cancel your registration for this event?');">
                                    <input type="hidden" name="event_id" value="<?php echo $e['id']; ?>">
                                    <button type="submit" class="small-btn danger">Cancel</button>
                                </form>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
        <p class="footer">CampusBuzz · Built with PHP, MySQL, JavaScript &amp; AJAX</p>
    </div>
</body>
</html>
