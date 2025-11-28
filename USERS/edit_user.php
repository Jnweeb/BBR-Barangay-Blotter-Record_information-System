<?php
session_start();
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../index.php");
    exit();
}

$id = $_GET["id"];

$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) { die("User not found."); }

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full = $_POST["full_name"];
    $uname = $_POST["username"];
    $role = $_POST["role"];
    $status = $_POST["status"];

    if ($_POST["password"] == "") {
        $stmt = $conn->prepare("
            UPDATE users SET full_name=?, username=?, role=?, status=? WHERE id=?
        ");
        $stmt->bind_param("ssssi", $full, $uname, $role, $status, $id);
    } else {
        $hash = password_hash($_POST["password"], PASSWORD_BCRYPT);
        $stmt = $conn->prepare("
            UPDATE users SET full_name=?, username=?, password_hash=?, role=?, status=? WHERE id=?
        ");
        $stmt->bind_param("sssssi", $full, $uname, $hash, $role, $status, $id);
    }

    if ($stmt->execute()) {
        header("Location: ../user_management.php");
        exit();
    } else {
        $message = "Error updating user.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit User</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div>
    <div class="blotter-card">
        <h1>Edit User Management</h1>
        <p>Update user information and permissions.</p>
    </div>
    <div class="blotter-card">
        <a href="../user_management.php" class="btn btn-outline" style="margin-bottom:15px;">← Back</a><br></br>
        <form method="POST" class="blotter-form">

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?= $user['full_name'] ?>" required>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= $user['username'] ?>" required>
            </div>

            <div class="form-group">
                <label>New Password (optional)</label>
                <input type="password" name="password">
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="Admin" <?= $user['role']=="Admin"?"selected":"" ?>>Admin</option>
                    <option value="Official" <?= $user['role']=="Official"?"selected":"" ?>>Official</option>
                    <option value="Secretary" <?= $user['role']=="Secretary"?"selected":"" ?>>Secretary</option>
                </select>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active" <?= $user['status']=="active"?"selected":"" ?>>Active</option>
                    <option value="inactive" <?= $user['status']=="inactive"?"selected":"" ?>>Inactive</option>
                </select>
            </div>

            <button class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>

</body>
</html>
