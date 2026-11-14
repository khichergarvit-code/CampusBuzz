<?php
require 'db.php'; // Include database connection and session
$msg = ""; // Store error messages

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // Fetch user record matching the entered email
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    // Verify hashed password
    if ($user && password_verify($_POST['password'], $user['password'])) {
        // Save user details in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];

        // Redirect admin to admin panel and students to dashboard
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
        <!-- Show success message after new account registration -->
        <?php if (isset($_GET['registered'])) echo "<p class='msg ok'>Account created! Please login.</p>"; ?>
        <!-- Show login error message if any -->
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
