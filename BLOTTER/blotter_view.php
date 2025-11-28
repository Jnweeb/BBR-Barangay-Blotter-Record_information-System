<?php
session_start();
require_once "../includes/config.php"; // contains $conn for DB

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$full_name = $_SESSION["full_name"] ?? "User";
$role = $_SESSION["role"] ?? "Unknown";

// Get selected blotter record if id is set
$selected_id = $_GET['id'] ?? null;
$selected_record = null;

if ($selected_id) {
    $stmt = $conn->prepare("
        SELECT 
            b.id,
            b.incident_datetime,
            b.location,
            b.case_type,
            c.full_name AS complainant_name,
            r.full_name AS respondent_name,
            u.full_name AS creator_name,
            b.incident_summary,
            b.created_at
        FROM blotter_records b
        LEFT JOIN complainants c ON b.complainant = c.id
        LEFT JOIN respondents r ON b.respondent = r.id
        LEFT JOIN users u ON b.created_by = u.id
        WHERE b.id = ? LIMIT 1
    ");
    $stmt->bind_param("i", $selected_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $selected_record = $result->fetch_assoc();
    $stmt->close();
}

// Fetch all blotter records for the table
$records_sql = "
    SELECT 
        b.id,
        b.incident_datetime,
        b.case_type,
        c.full_name AS complainant_name,
        r.full_name AS respondent_name
    FROM blotter_records b
    LEFT JOIN complainants c ON b.complainant = c.id
    LEFT JOIN respondents r ON b.respondent = r.id
    ORDER BY b.incident_datetime DESC
";

$records_result = $conn->query($records_sql);
$blotter_records = [];
if($records_result) {
    while($row = $records_result->fetch_assoc()) {
        $blotter_records[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Blotter Details</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard">

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../assets/icons/brgy.svg" alt="Logo" class="sidebar-logo">
            <div class="user-info-sidebar">
                <h3><?php echo htmlspecialchars($full_name); ?></h3>
                <p><?php echo htmlspecialchars($role); ?></p>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="../dashboard.php">
                    <img src="../assets/icons/home.svg" alt="Dashboard Icon" class="sidebar-icon"> Dashboard
                </a>
            </li>
            <li>
                <a href="../blotter_management.php">
                    <img src="../assets/icons/blotter.svg" alt="Blotter Icon" class="sidebar-icon"> Blotter Management
                </a>
            </li>
            <li>
                <a href="../reports.php">
                    <img src="../assets/icons/report.svg" alt="Reports Icon" class="sidebar-icon"> Reports
                </a>
            </li>
            <?php if ($_SESSION['role'] === 'Admin'): ?>
            <li>
                <a href="../user_management.php">
                    <img src="../assets/icons/user.svg" alt="User Icon" class="sidebar-icon"> User Management
                </a>
            </li>
            <li>
                <a href="../activity_log.php">
                    <img src="../assets/icons/act-logs.svg" alt="Activity Log Icon" class="sidebar-icon"> Activity Log
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="../logout.php">
                    <img src="../assets/icons/logout.svg" alt="Logout Icon" class="sidebar-icon"> Logout
                </a>
            </li>
        </ul>
    </div>

<!-- Main Content -->
<div class="main-content">
    <div class="page-header">
        <h1>View Blotter Information</h1>
        <p>View full details of a blotter record.</p>
    </div>

    <?php if ($selected_record): ?>
        <!-- Full Details Card -->
        <div class="card">
            <table class="table-container">
                <tbody>
                    <tr>
                        <th>ID</th>
                        <td><?= htmlspecialchars($selected_record['id']) ?></td>
                    </tr>
                    <tr>
                        <th>Date & Time</th>
                        <td><?= htmlspecialchars($selected_record['incident_datetime']) ?></td>
                    </tr>
                    <tr>
                        <th>Location</th>
                        <td><?= htmlspecialchars($selected_record['location']) ?></td>
                    </tr>
                    <tr>
                        <th>Case Type</th>
                        <td><?= htmlspecialchars($selected_record['case_type']) ?></td>
                    </tr>
                    <tr>
                        <th>Complainant</th>
                        <td><?= htmlspecialchars($selected_record['complainant_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Respondent</th>
                        <td><?= htmlspecialchars($selected_record['respondent_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Summary</th>
                        <td><?= nl2br(htmlspecialchars($selected_record['incident_summary'])) ?></td>
                    </tr>
                    <tr>
                        <th>Created By</th>
                        <td><?= htmlspecialchars($selected_record['creator_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td><?= htmlspecialchars($selected_record['created_at']) ?></td>
                    </tr>
                </tbody>
            </table>
            <br>
            <a href="../blotter_management.php" class="btn btn-outline">← Back to Blotter Management</a>
        </div>
    <?php else: ?>
    <?php endif; ?>
</div>

</body>
</html>
