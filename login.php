<?php
require 'db.php';
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];
        header("Location: " . ($user['role'] == 'admin' ? "admin.php" : "dashboard.php"));
        exit;
    }
    $msg = "Invalid email or password.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - CampusBuzz</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="center-page">
    <div class="box">
        <h2>CampusBuzz</h2>
        <p class="muted">Campus Event Portal</p>
        <?php if (isset($_GET['registered'])) echo "<p class='msg ok'>Account created! Please login.</p>"; ?>
        <?php if ($msg) echo "<p class='msg err'>" . htmlspecialchars($msg) . "</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p class="small">New student? <a href="signup.php">Sign up</a></p>
    </div>
</body>
</html>
