<?php
require 'db.php';
checkLogin();

$stmt = $conn->prepare("SELECT e.* FROM registrations r JOIN events e ON r.event_id = e.id WHERE r.user_id = ? ORDER BY e.event_date ASC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$list = $stmt->get_result();
$today = date("Y-m-d");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Events - CampusBuzz</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="nav">
        <b>CampusBuzz</b>
        <div>
            <a href="dashboard.php">All Events</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h2>My Events (<?= $list->num_rows ?>)</h2>
        <?php if (isset($_GET['msg'])) echo "<p class='msg ok'>" . htmlspecialchars($_GET['msg']) . "</p>"; ?>

        <?php if ($list->num_rows == 0): ?>
            <p>No registrations yet. <a href="dashboard.php">Browse events</a></p>
        <?php else: ?>
            <table>
                <tr><th>Event</th><th>Category</th><th>Date & Time</th><th>Venue</th><th>Status</th><th>Action</th></tr>
                <?php while ($e = $list->fetch_assoc()):
                    $status = ($e['event_date'] < $today) ? "Completed" : (($e['event_date'] == $today) ? "Today" : "Upcoming");
                ?>
                    <tr>
                        <td><?= htmlspecialchars($e['title']) ?></td>
                        <td><?= $e['category'] ?></td>
                        <td><?= date("d M Y", strtotime($e['event_date'])) ?>, <?= date("h:i A", strtotime($e['event_time'])) ?></td>
                        <td><?= htmlspecialchars($e['venue']) ?></td>
                        <td><span class="badge <?= strtolower($status) ?>"><?= $status ?></span></td>
                        <td>
                            <?php if ($status != "Completed"): ?>
                                <form method="POST" action="cancel_registration.php" onsubmit="return confirm('Cancel registration?');">
                                    <input type="hidden" name="event_id" value="<?= $e['id'] ?>">
                                    <button type="submit" class="btn-sm danger">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
