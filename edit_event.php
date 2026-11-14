<?php
require 'db.php';
checkLogin(true);

$categories = ["Tech", "Cultural", "Sports", "Workshop"];

$id = (int)($_GET['event_id'] ?? $_POST['event_id'] ?? 0);
$msg = "";

$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$ev = $stmt->get_result()->fetch_assoc();
if (!$ev) { header("Location: admin.php"); exit; }

$reg = (int)$conn->query("SELECT COUNT(*) c FROM registrations WHERE event_id = $id")->fetch_assoc()['c'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ev['title']       = trim($_POST['title']);
    $ev['description'] = trim($_POST['description']);
    $ev['category']    = $_POST['category'];
    $ev['venue']       = trim($_POST['venue']);
    $ev['event_date']  = $_POST['event_date'];
    $ev['event_time']  = $_POST['event_time'];
    $ev['max_seats']   = (int)$_POST['max_seats'];
    $ev['image_url']   = trim($_POST['image_url']) ?: null;

    if (!$ev['title'] || !$ev['venue'] || !in_array($ev['category'], $categories)) {
        $msg = "Fill all required fields.";
    } elseif ($ev['max_seats'] < max(1, $reg)) {
        $msg = "Seats cannot be less than registered ($reg).";
    } elseif ($ev['image_url'] && !preg_match('/^https?:\/\//', $ev['image_url'])) {
        $msg = "Invalid image URL.";
    } else {
        $stmt = $conn->prepare("UPDATE events SET title=?, description=?, category=?, venue=?, event_date=?, event_time=?, max_seats=?, image_url=? WHERE id=?");
        $stmt->bind_param("ssssssisi", $ev['title'], $ev['description'], $ev['category'], $ev['venue'], $ev['event_date'], $ev['event_time'], $ev['max_seats'], $ev['image_url'], $id);
        $stmt->execute();
        header("Location: admin.php?msg=Event+updated");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Event - CampusBuzz</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="nav">
        <b>CampusBuzz Admin</b>
        <a href="admin.php">Back</a>
    </nav>

    <div class="box wide">
        <h2>Edit Event</h2>
        <?php if ($msg) echo "<p class='msg err'>" . htmlspecialchars($msg) . "</p>"; ?>
        <form method="POST">
            <input type="hidden" name="event_id" value="<?= $id ?>">
            <input type="text" name="title" value="<?= htmlspecialchars($ev['title']) ?>" required>
            <textarea name="description" rows="3"><?= htmlspecialchars($ev['description'] ?? '') ?></textarea>
            <select name="category" required>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c ?>" <?= $ev['category'] == $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="venue" value="<?= htmlspecialchars($ev['venue']) ?>" required>
            <label>Date *</label><input type="date" name="event_date" value="<?= $ev['event_date'] ?>" required>
            <label>Time *</label><input type="time" name="event_time" value="<?= date("H:i", strtotime($ev['event_time'])) ?>" required>
            <input type="number" name="max_seats" value="<?= $ev['max_seats'] ?>" min="1" required>
            <input type="url" name="image_url" value="<?= htmlspecialchars($ev['image_url'] ?? '') ?>" placeholder="Poster Image URL">
            <button type="submit">Update Event</button>
        </form>
    </div>
</body>
</html>
