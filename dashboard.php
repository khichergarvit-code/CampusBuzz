<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ---------- 1. CATEGORY FILTER ----------
$categories = ["Tech", "Cultural", "Sports", "Workshop"];
$icons = ["Tech" => "💻", "Cultural" => "🎭", "Sports" => "🏆", "Workshop" => "💡"];   // placeholder icons when an event has no image
$selected = "All";

if (isset($_GET['category']) && in_array($_GET['category'], $categories)) {
    $selected = $_GET['category'];
}

// ---------- 2. GET EVENTS + HOW MANY REGISTERED ----------
// LEFT JOIN keeps events that have 0 registrations.
// COUNT(r.id) counts the registrations for each event.
$sql = "SELECT e.*, COUNT(r.id) AS registered_count
        FROM events e
        LEFT JOIN registrations r ON e.id = r.event_id";

if ($selected != "All") {
    $sql .= " WHERE e.category = ?";
}
$sql .= " GROUP BY e.id ORDER BY e.event_date ASC";

$stmt = $conn->prepare($sql);
if ($selected != "All") {
    $stmt->bind_param("s", $selected);
}
$stmt->execute();
$events = $stmt->get_result();

// ---------- 3. NOTIFICATIONS: events added in the last 3 days ----------
$new_events = $conn->query(
    "SELECT title, event_date FROM events
     WHERE created_at >= NOW() - INTERVAL 3 DAY
     ORDER BY created_at DESC LIMIT 5"
);

// ---------- 4. WHICH EVENTS HAS THIS STUDENT ALREADY REGISTERED FOR? ----------
$my_event_ids = [];
$stmt = $conn->prepare("SELECT event_id FROM registrations WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $my_event_ids[] = $row['event_id'];
}

$today = date("Y-m-d");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Events - CampusBuzz</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Top navigation bar -->
    <nav class="navbar">
        <span class="logo">🎓 CampusBuzz</span>
        <div>
            Hi, <?php echo htmlspecialchars($_SESSION['name']); ?>
            <a href="my_events.php" class="nav-link">My Events</a>
            <?php if ($_SESSION['role'] == 'admin') { ?>
                <a href="admin.php" class="nav-link">Admin Panel</a>
            <?php } ?>
            <a href="logout.php" class="nav-link">Log Out</a>
        </div>
    </nav>

    <div class="container">

        <!-- Notification box -->
        <?php if ($new_events->num_rows > 0) { ?>
            <div class="notify-box">
                <h3>🔔 New Events</h3>
                <?php while ($n = $new_events->fetch_assoc()) { ?>
                    <p>
                        <b><?php echo htmlspecialchars($n['title']); ?></b>
                        on <?php echo date("d M", strtotime($n['event_date'])); ?>
                    </p>
                <?php } ?>
            </div>
        <?php } ?>

        <h2>Upcoming Events</h2>

        <!-- Category filter buttons -->
        <div class="filters">
            <a href="dashboard.php" class="filter-btn <?php if ($selected == 'All') echo 'active'; ?>">All</a>
            <?php foreach ($categories as $cat) { ?>
                <a href="dashboard.php?category=<?php echo $cat; ?>"
                   class="filter-btn <?php if ($selected == $cat) echo 'active'; ?>">
                    <?php echo $cat; ?>
                </a>
            <?php } ?>
        </div>

        <!-- Event cards -->
        <div class="event-grid">
            <?php if ($events->num_rows == 0) { ?>
                <p>No events in this category yet.</p>
            <?php } ?>

            <?php
            $card_number = 0;  
            while ($event = $events->fetch_assoc()) {
                $card_number++;

                // Seats left = total seats - students registered
                $seats_left = $event['max_seats'] - $event['registered_count'];

                // Status badge based on the date
                if ($event['event_date'] < $today) {
                    $status = "Completed";
                } elseif ($event['event_date'] == $today) {
                    $status = "Today";
                } else {
                    $status = "Upcoming";
                }
            ?>
               
                <div class="event-card cat-<?php echo strtolower($event['category']); ?>"
                     style="animation-delay: <?php echo $card_number * 0.08; ?>s;">

                    <!-- Poster: show the image if there is a link, otherwise a coloured placeholder -->
                    <?php if (!empty($event['image_url'])) { ?>
                       
                        <img class="card-img" src="<?php echo htmlspecialchars($event['image_url']); ?>"
                             alt="<?php echo htmlspecialchars($event['title']); ?> poster"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="card-img placeholder" style="display: none;"><?php echo $icons[$event['category']]; ?></div>
                    <?php } else { ?>
                        <div class="card-img placeholder"><?php echo $icons[$event['category']]; ?></div>
                    <?php } ?>

                    <div class="card-top">
                        <span class="category"><?php echo $event['category']; ?></span>
                        <span class="badge <?php echo strtolower($status); ?>"><?php echo $status; ?></span>
                    </div>

                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                    <p class="desc"><?php echo htmlspecialchars($event['description'] ?? ''); ?></p>

                    <p>📅 <?php echo date("d M Y", strtotime($event['event_date'])); ?>
                       &nbsp; ⏰ <?php echo date("h:i A", strtotime($event['event_time'])); ?></p>
                    <p>📍 <?php echo htmlspecialchars($event['venue']); ?></p>

                   
                    <p class="seats" id="seats-<?php echo $event['id']; ?>">
                        <?php if ($seats_left > 0) { ?>
                            🎟️ <?php echo $seats_left; ?> of <?php echo $event['max_seats']; ?> seats left
                        <?php } else { ?>
                            <span class="full">❌ Event Full</span>
                        <?php } ?>
                    </p>

                    <!-- Register button: shows a different state depending on the situation -->
                    <?php if (in_array($event['id'], $my_event_ids)) { ?>
                        <button class="register-btn registered" disabled>Registered ✓</button>
                    <?php } elseif ($status == "Completed") { ?>
                        <button class="register-btn" disabled>Event Ended</button>
                    <?php } elseif ($seats_left <= 0) { ?>
                        <button class="register-btn" disabled>Event Full</button>
                    <?php } else { ?>
                        <button class="register-btn"
                                onclick="registerEvent(<?php echo $event['id']; ?>, this)">Register</button>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

        <p class="footer">CampusBuzz · Built with PHP, MySQL, JavaScript &amp; AJAX</p>
    </div>

    <!-- Popup message area used by script.js -->
    <div id="toast"></div>

    <script src="script.js"></script>
</body>
</html>
