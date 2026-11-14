<?php
require 'db.php';
checkLogin(true);

$categories = ["Tech", "Cultural", "Sports", "Workshop"];
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $desc  = trim($_POST['description']);
    $cat   = $_POST['category'];
    $venue = trim($_POST['venue']);
    $date  = $_POST['event_date'];
    $time  = $_POST['event_time'];
    $seats = (int)$_POST['max_seats'];
    $img   = trim($_POST['image_url']) ?: null;

    if (!$title || !$venue || !$date || !$time || !in_array($cat, $categories) || $seats < 1) {
        $msg = "Please fill all required fields properly.";
    } elseif ($img && !preg_match('/^https?:\/\//', $img)) {
        $msg = "Image URL must start with http:// or https://";
    } else {
        $stmt = $conn->prepare("INSERT INTO events (title, description, category, venue, event_date, event_time, max_seats, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssis", $title, $desc, $cat, $venue, $date, $time, $seats, $img);
        $stmt->execute();
        header("Location: admin.php?msg=Event+added");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Event - CampusBuzz</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="nav">
        <b>CampusBuzz Admin</b>
        <a href="admin.php">Back</a>
    </nav>

    <div class="box wide">
        <h2>Add Event</h2>
        <?php if ($msg) echo "<p class='msg err'>" . htmlspecialchars($msg) . "</p>"; ?>
        <form method="POST">
            <input type="text" name="title" placeholder="Event Title *" required>
            <textarea name="description" placeholder="Description" rows="3"></textarea>
            <select name="category" required>
                <option value="">-- Category * --</option>
                <?php foreach ($categories as $c) echo "<option value='$c'>$c</option>"; ?>
            </select>
            <input type="text" name="venue" placeholder="Venue *" required>
            <label>Date *</label><input type="date" name="event_date" required>
            <label>Time *</label><input type="time" name="event_time" required>
            <input type="number" name="max_seats" placeholder="Max Seats *" min="1" required>
            <input type="url" name="image_url" placeholder="Poster Image URL (optional)">
            <button type="submit">Save Event</button>
        </form>
    </div>
</body>
</html>
