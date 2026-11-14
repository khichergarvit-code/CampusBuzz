<?php
require 'db.php';
checkLogin(true);

$id = (int)($_GET['event_id'] ?? 0);
$stmt = $conn->prepare("SELECT title, event_date, max_seats FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$ev = $stmt->get_result()->fetch_assoc();
if (!$ev) { header("Location: admin.php"); exit; }

$stmt = $conn->prepare("SELECT u.name, u.email, r.registered_at FROM registrations r JOIN users u ON r.user_id = u.id WHERE r.event_id = ? ORDER BY r.registered_at ASC");
$stmt->bind_param("i", $id);
$stmt->execute();
$students = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registrations - CampusBuzz</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="nav">
        <b>CampusBuzz Admin</b>
        <a href="admin.php">Back</a>
    </nav>

    <div class="container">
        <h2><?= htmlspecialchars($ev['title']) ?></h2>
        <p class="muted"><?= date("d M Y", strtotime($ev['event_date'])) ?> | <?= $students->num_rows ?> / <?= $ev['max_seats'] ?> registered</p>

        <?php if ($students->num_rows == 0): ?>
            <p>No registrations yet.</p>
        <?php else: ?>
            <table>
                <tr><th>#</th><th>Name</th><th>Email</th><th>Registered On</th></tr>
                <?php $i = 1; while ($s = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($s['name']) ?></td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td><?= date("d M Y, h:i A", strtotime($s['registered_at'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
