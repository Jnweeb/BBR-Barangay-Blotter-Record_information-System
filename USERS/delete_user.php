<?php
session_start();
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../index.php");
    exit();
}

$id = $_GET["id"];

// Prevent admin from deleting own account
if ($id == $_SESSION["user_id"]) {
    die("You cannot delete your own account!");
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: ../user_management.php");
exit();
?>
