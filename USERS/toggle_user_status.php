<?php
session_start();
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../index.php");
    exit();
}

$id = $_GET["id"];
$status = $_GET["status"];

// Toggle status
$new_status = ($status == "active") ? "inactive" : "active";

$stmt = $conn->prepare("UPDATE users SET status=? WHERE id=?");
$stmt->bind_param("si", $new_status, $id);
$stmt->execute();

header("Location: ../user_management.php");
exit();
?>
