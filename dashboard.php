<?php
require 'db.php';
checkLogin(); // Ensure user is logged in

// Allowed event categories for filtering
$categories = ["Tech", "Cultural", "Sports", "Workshop"];
$selected = (isset($_GET['category']) && in_array($_GET['category'], $categories)) ? $_GET['category'] : "All";

// Query events along with their total registration count
$sql = "SELECT e.*, COUNT(r.id) AS reg_count FROM events e LEFT JOIN registrations r ON e.id = r.event_id";
if ($selected != "All") $sql .= " WHERE e.category = ?";
$sql .= " GROUP BY e.id ORDER BY e.event_date ASC";

$stmt = $conn->prepare($sql);
if ($selected != "All") $stmt->bind_param("s", $selected);
$stmt->execute();
$events = $stmt->get_result();

// Fetch up to 5 events added in the last 3 days for the notice banner
$new_events = $conn->query("SELECT title, event_date FROM events WHERE created_at >= NOW() - INTERVAL 3 DAY ORDER BY created_at DESC LIMIT 5");

// Get list of event IDs the current student has already registered for
$my_ids = [];
$res = $conn->query("SELECT event_id FROM registrations WHERE user_id = " . (int)$_SESSION['user_id']);
while ($r = $res->fetch_assoc()) $my_ids[] = $r['event_id'];

$today = date("Y-m-d"); // Current date for status comparison
?>
<!DOCTYPE html>
<html>
<head>
    <title>Events - CampusBuzz</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Top navigation bar -->
    <nav class="nav">
        <b>CampusBuzz</b>
        <div>
            <span>Hi, <?= htmlspecialchars($_SESSION['name']) ?></span>
            <a href="my_events.php">My Events</a>
            <?php if ($_SESSION['role'] == 'admin') echo '<a href="admin.php">Admin</a>'; ?>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <!-- Banner showing newly added events -->
        <?php if ($new_events->num_rows > 0): ?>
            <div class="notice">
                <b>New Events:</b>
                <?php while ($n = $new_events->fetch_assoc()): ?>
                    <span><?= htmlspecialchars($n['title']) ?> (<?= date("d M", strtotime($n['event_date'])) ?>)</span>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <h2>Upcoming Events</h2>
        <!-- Category filter tabs -->
        <div class="filters">
            <a href="dashboard.php" class="<?= $selected == 'All' ? 'active' : '' ?>">All</a>
            <?php foreach ($categories as $c): ?>
                <a href="dashboard.php?category=<?= $c ?>" class="<?= $selected == $c ? 'active' : '' ?>"><?= $c ?></a>
            <?php endforeach; ?>
        </div>

        <!-- Event cards grid -->
        <div class="grid">
            <?php if ($events->num_rows == 0) echo "<p>No events found.</p>"; ?>
            <?php while ($e = $events->fetch_assoc()):
                $left = $e['max_seats'] - $e['reg_count']; // Remaining seats
                // Determine event status based on date
                $status = ($e['event_date'] < $today) ? "Completed" : (($e['event_date'] == $today) ? "Today" : "Upcoming");
            ?>
                <div class="card">
                    <!-- Display poster image if URL is provided -->
                    <?php if (!empty($e['image_url'])): ?>
                        <img src="<?= htmlspecialchars($e['image_url']) ?>" alt="Poster" onerror="this.style.display='none'">
                    <?php endif; ?>
                    <div class="row">
                        <span class="tag"><?= $e['category'] ?></span>
                        <span class="badge <?= strtolower($status) ?>"><?= $status ?></span>
                    </div>
                    <h3><?= htmlspecialchars($e['title']) ?></h3>
                    <p class="muted"><?= htmlspecialchars($e['description'] ?? '') ?></p>
                    <p>Date: <?= date("d M Y", strtotime($e['event_date'])) ?> | <?= date("h:i A", strtotime($e['event_time'])) ?></p>
                    <p>Venue: <?= htmlspecialchars($e['venue']) ?></p>
                    <!-- Seat availability counter -->
                    <p id="seats-<?= $e['id'] ?>">
                        <?= $left > 0 ? "<b>$left</b> / {$e['max_seats']} seats left" : "<span class='err'>Event Full</span>" ?>
                    </p>

                    <!-- Action button based on registration/event state -->
                    <?php if (in_array($e['id'], $my_ids)): ?>
                        <button class="btn-done" disabled>Registered</button>
                    <?php elseif ($status == "Completed"): ?>
                        <button disabled>Ended</button>
                    <?php elseif ($left <= 0): ?>
                        <button disabled>Full</button>
                    <?php else: ?>
                        <button onclick="registerEvent(<?= $e['id'] ?>, this)">Register</button>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Popup toast notification container -->
    <div id="toast"></div>
    <script src="script.js"></script>
</body>
</html>
