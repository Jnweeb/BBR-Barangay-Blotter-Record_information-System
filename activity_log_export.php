<?php
require_once "includes/config.php";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="activity_log.csv"');

$output = fopen("php://output", "w");

// Update header to show Date & Time
fputcsv($output, ["ID", "User", "Action", "Table", "Record ID", "Date & Time"]);

$query = "
    SELECT a.id, u.full_name, a.action, a.table_name, a.record_id, a.log_time
    FROM activity_log a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.log_time DESC
";

$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $row['table_name'] = $row['table_name'] ?? '-';
    $row['record_id']  = $row['record_id'] ?? '-';
    $row['full_name']  = $row['full_name'] ?? 'Deleted User';

    // Optionally, format datetime as YYYY-MM-DD HH:MM:SS
    $log_datetime = date("Y-m-d H:i:s", strtotime($row['log_time']));

    fputcsv($output, [
        $row['id'],
        $row['full_name'],
        $row['action'],
        $row['table_name'],
        $row['record_id'],
        $log_datetime
    ]);
}

fclose($output);
exit;
?>
