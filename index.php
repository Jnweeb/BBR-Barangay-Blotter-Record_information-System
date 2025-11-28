<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "includes/config.php";

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Prepare SQL
    $stmt = $conn->prepare("SELECT id, full_name, username, password_hash, role, status 
                            FROM users 
                            WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {

        $stmt->bind_result($id, $full_name, $user, $hash, $role, $status);
        $stmt->fetch();

        if ($status !== "active") {
            $error = "Your account is inactive. Please contact the administrator.";
        } 
        elseif (password_verify($password, $hash)) {

            session_regenerate_id(true);
            $_SESSION["user_id"] = $id;
            $_SESSION["full_name"] = $full_name;
            $_SESSION["username"] = $user;
            $_SESSION["role"] = $role;

            header("Location: dashboard.php");
            exit();

        } else {
            $error = "Invalid username or password.";
        }

    } else {
        $error = "Invalid username or password.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Blotter Record - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="login-box">
        <img src="assets/images/brgy.png" alt="Barangay Logo">
        <h2>Barangay Blotter Record Management System</h2>

        <?php if ($error != ""): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" required>
                    <img src="assets/icons/eye.svg" class="toggle-password" alt="Toggle Password"data-eye="assets/icons/eye.svg"data-eye-slash="assets/icons/eye-slash.svg">
                </div>
            </div>

            <button class="btn" type="submit">Login</button>
        </form>


        <footer>
            <p>Barangay Blotter Record (BBR) © <?php echo date('Y'); ?></p>
            <p>Created By John Jeff B. Dublin BSCS - 3A</p>
        </footer>
    </div>
<script src="assets/javascript/script.js"></script>
</body>
</html>
