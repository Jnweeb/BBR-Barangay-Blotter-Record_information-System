<?php
session_start();
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../index.php");
    exit();
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full = $_POST["full_name"];
    $user = $_POST["username"];
    $pass = $_POST["password"];
    $role = $_POST["role"];
    $status = $_POST["status"];

    $check = $conn->prepare("SELECT id FROM users WHERE username=?");
    $check->bind_param("s", $user);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "Username already exists!";
    } else {
        $hash = password_hash($pass, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("
            INSERT INTO users (full_name, username, password_hash, role, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssss", $full, $user, $hash, $role, $status);

        if ($stmt->execute()) {
            header("Location: ../user_management.php");
            exit();
        } else {
            $message = "Error adding user.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Users</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <div>
        <div class="blotter-card">
            <h1>Admin User Management</h1>
            <p>Add User Information in Database</p>
        </div>   
        <div class="blotter-card">
            <a href="../user_management.php" class="btn btn-outline" style="margin-bottom:15px;">← Back</a>
            <form method="POST" class="blotter-form">

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" required>
                        <img src="../assets/icons/eye.svg" class="toggle-password" alt="Toggle Password" data-eye="../assets/icons/eye.svg" data-eye-slash="../assets/icons/eye-slash.svg">
                    </div>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="Admin">Admin</option>
                        <option value="Official">Official</option>
                        <option value="Secretary">Secretary</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Add User</button>
            </form>
        </div>
    </div>
<script src="../assets/javascript/script.js"></script>
</body>
</html>
