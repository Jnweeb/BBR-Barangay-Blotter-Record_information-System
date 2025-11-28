<?php
session_start();
require_once "../includes/config.php";
require_once "../includes/functions.php"; // log_activity() should be defined here

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if (!isset($_GET['id'])) {
    die("Error: Record ID not specified.");
}

$id = intval($_GET['id']);

// Start transaction
$conn->begin_transaction();
try {
    // Get complainant, respondent, and blotter details for logging
    $stmt = $conn->prepare("
        SELECT b.case_type, b.location, b.complainant, b.respondent,
               c.full_name AS c_name,
               r.full_name AS r_name
        FROM blotter_records b
        JOIN complainants c ON b.complainant = c.id
        JOIN respondents r ON b.respondent = r.id
        WHERE b.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Record not found.");
    }
    $row = $result->fetch_assoc();

    // --- Delete blotter record ---
    $stmt = $conn->prepare("DELETE FROM blotter_records WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    log_activity($user_id, "Deleted blotter record #$id ({$row['case_type']} at {$row['location']})", 'blotter_records', $id);

    // --- Delete complainant ---
    $stmt = $conn->prepare("DELETE FROM complainants WHERE id=?");
    $stmt->bind_param("i", $row['complainant']);
    $stmt->execute();
    log_activity($user_id, "Deleted complainant #{$row['complainant']} ({$row['c_name']})", 'complainants', $row['complainant']);

    // --- Delete respondent ---
    $stmt = $conn->prepare("DELETE FROM respondents WHERE id=?");
    $stmt->bind_param("i", $row['respondent']);
    $stmt->execute();
    log_activity($user_id, "Deleted respondent #{$row['respondent']} ({$row['r_name']})", 'respondents', $row['respondent']);

    $conn->commit();
    header("Location: blotter_view.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
?>
