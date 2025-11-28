<?php
require_once "includes/config.php";
require_once "includes/functions.php";

// Function to insert into activity_log
function insert_log($conn, $user_id, $action, $table_name, $record_id, $log_time) {
    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, action, table_name, record_id, log_time) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issis", $user_id, $action, $table_name, $record_id, $log_time);
    $stmt->execute();
    $stmt->close();
}

// --- 1. Import blotter_records ---
$result = $conn->query("SELECT id, created_by, created_at FROM blotter_records");
while ($row = $result->fetch_assoc()) {
    $user_id = $row['created_by'] ?? null;
    $action = "Existing blotter record #{$row['id']}";
    insert_log($conn, $user_id, $action, 'blotter_records', $row['id'], $row['created_at']);
}
echo "Blotter records imported.\n";

// --- 2. Import complainants ---
$result = $conn->query("SELECT id, created_at, full_name FROM complainants");
while ($row = $result->fetch_assoc()) {
    $user_id = null; // no creator info
    $action = "Existing complainant #{$row['id']} ({$row['full_name']})";
    insert_log($conn, $user_id, $action, 'complainants', $row['id'], $row['created_at']);
}
echo "Complainants imported.\n";

// --- 3. Import respondents ---
$result = $conn->query("SELECT id, created_at, full_name FROM respondents");
while ($row = $result->fetch_assoc()) {
    $user_id = null; // no creator info
    $action = "Existing respondent #{$row['id']} ({$row['full_name']})";
    insert_log($conn, $user_id, $action, 'respondents', $row['id'], $row['created_at']);
}
echo "Respondents imported.\n";

echo "Import completed successfully.\n";
?>
