<?php
session_start();
require 'db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Find the user with this email
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    // 2. Check the password against the stored hash
    if ($user && password_verify($password, $user['password'])) {
        // 3. Remember who is logged in (session)
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];

        // Admins go to the admin panel, students to the event list
        if ($user['role'] == 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $message = "Wrong email or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - CampusBuzz</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="form-box">
        <div class="auth-brand">🎓</div>
        <h2>CampusBuzz</h2>
        <p class="tagline">Know what's happening on campus</p>

        <?php if (isset($_GET['registered'])) { ?>
            <p class="success">Account created! Please log in.</p>
        <?php } ?>

        <?php if ($message != "") { ?>
            <p class="error"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>

        <form method="POST">
            <input type="email"    name="email"    placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Log In</button>
        </form>

        <p>New here? <a href="signup.php">Create an account</a></p>
    </div>
</body>
</html>
