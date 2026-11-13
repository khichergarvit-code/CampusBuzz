<?php
require 'db.php';
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    if (!$name || !$email || strlen($pass) < 6) {
        $msg = "Fill all fields (password min 6 chars).";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $msg = "Email already exists.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hash);
            $stmt->execute();
            header("Location: login.php?registered=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - CampusBuzz</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="center-page">
    <div class="box">
        <h2>Create Account</h2>
        <?php if ($msg) echo "<p class='msg err'>" . htmlspecialchars($msg) . "</p>"; ?>
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password (min 6 chars)" required>
            <button type="submit">Sign Up</button>
        </form>
        <p class="small">Already registered? <a href="login.php">Login</a></p>
    </div>
</body>
</html>
