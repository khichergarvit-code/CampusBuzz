<?php
require 'db.php';
checkLogin(true); // Restrict access to admin users only

// Fetch summary counts for dashboard cards
$ev_count  = $conn->query("SELECT COUNT(*) c FROM events")->fetch_assoc()['c'];
$stu_count = $conn->query("SELECT COUNT(*) c FROM users WHERE role='student'")->fetch_assoc()['c'];
$reg_count = $conn->query("SELECT COUNT(*) c FROM registrations")->fetch_assoc()['c'];

// Fetch all events with their registered student counts
$events = $conn->query("SELECT e.*, COUNT(r.id) AS reg FROM events e LEFT JOIN registrations r ON e.id = r.event_id GROUP BY e.id ORDER BY e.event_date ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - CampusBuzz</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="nav">
        <b>CampusBuzz Admin</b>
        <div>
            <a href="dashboard.php">View Site</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <!-- Show feedback message after add/edit/delete actions -->
        <?php if (isset($_GET['msg'])) echo "<p class='msg ok'>" . htmlspecialchars($_GET['msg']) . "</p>"; ?>

        <!-- Summary statistics -->
        <div class="stats">
            <div class="stat"><h3><?= $ev_count ?></h3><p>Events</p></div>
            <div class="stat"><h3><?= $stu_count ?></h3><p>Students</p></div>
            <div class="stat"><h3><?= $reg_count ?></h3><p>Registrations</p></div>
        </div>

        <div class="row">
            <h2>Manage Events</h2>
            <a href="add_event.php" class="btn">+ Add Event</a>
        </div>

        <!-- Events management table -->
        <table>
            <tr><th>Title</th><th>Category</th><th>Date</th><th>Seats</th><th>Actions</th></tr>
            <?php while ($e = $events->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($e['title']) ?></td>
                    <td><?= $e['category'] ?></td>
                    <td><?= date("d M Y", strtotime($e['event_date'])) ?></td>
                    <td><?= $e['reg'] ?> / <?= $e['max_seats'] ?></td>
                    <td>
                        <a href="view_registrations.php?event_id=<?= $e['id'] ?>" class="btn-sm">View</a>
                        <a href="edit_event.php?event_id=<?= $e['id'] ?>" class="btn-sm">Edit</a>
                        <!-- Delete event form with confirmation dialog -->
                        <form method="POST" action="delete_event.php" class="inline" onsubmit="return confirm('Delete this event?');">
                            <input type="hidden" name="event_id" value="<?= $e['id'] ?>">
                            <button type="submit" class="btn-sm danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
