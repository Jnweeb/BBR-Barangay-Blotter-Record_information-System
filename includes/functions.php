<?php
function log_activity($user_id, $action, $table_name = null, $record_id = null) {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO activity_log (user_id, action, table_name, record_id, log_time)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("issi", $user_id, $action, $table_name, $record_id);
    $stmt->execute();
    $stmt->close();
}

?>
