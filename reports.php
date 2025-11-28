<?php
session_start();
require_once "includes/config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$full_name = $_SESSION["full_name"];
$role = $_SESSION["role"];

// Filters
$start_date = $_GET["start_date"] ?? "";
$end_date = $_GET["end_date"] ?? "";
$case_type = $_GET["case_type"] ?? "";

// Base query with proper joins
$sql = "
    SELECT 
        br.id,
        br.incident_datetime,
        br.location,
        br.case_type,
        c.full_name AS complainant_name,
        r.full_name AS respondent_name
    FROM blotter_records br
    JOIN complainants c ON br.complainant = c.id
    JOIN respondents r ON br.respondent = r.id
    WHERE 1
";

// Apply filters
if (!empty($start_date)) $sql .= " AND DATE(br.incident_datetime) >= '$start_date'";
if (!empty($end_date)) $sql .= " AND DATE(br.incident_datetime) <= '$end_date'";
if (!empty($case_type)) $sql .= " AND br.case_type = '$case_type'";

$sql .= " ORDER BY br.incident_datetime DESC";

$result = $conn->query($sql);

// Predefined case types
$case_types = [
    "Theft","Assault","Domestic Violence","Vandalism","Noise Complaint",
    "Missing Person","Drug-related Offense","Traffic Violation","Fraud","Others"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports</title>
<link rel="stylesheet" href="assets/css/style.css">

</head>
<body class="dashboard">

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="assets/icons/brgy.svg" alt="Logo" class="sidebar-logo">
            <div class="user-info-sidebar">
                <h3><?php echo htmlspecialchars($full_name); ?></h3>
                <p><?php echo htmlspecialchars($role); ?></p>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php">
                    <img src="assets/icons/home.svg" alt="Dashboard Icon" class="sidebar-icon"> Dashboard
                </a>
            </li>
            <li>
                <a href="blotter_management.php">
                    <img src="assets/icons/blotter.svg" alt="Blotter Icon" class="sidebar-icon"> Blotter Management
                </a>
            </li>
            <li>
                <a href="reports.php">
                    <img src="assets/icons/report.svg" alt="Reports Icon" class="sidebar-icon"> Reports
                </a>
            </li>
            <?php if ($_SESSION['role'] === 'Admin'): ?>
            <li>
                <a href="user_management.php">
                    <img src="assets/icons/user.svg" alt="User Icon" class="sidebar-icon"> User Management
                </a>
            </li>
            <li>
                <a href="activity_log.php">
                    <img src="assets/icons/act-logs.svg" alt="Activity Log Icon" class="sidebar-icon"> Activity Log
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="logout.php">
                    <img src="assets/icons/logout.svg" alt="Logout Icon" class="sidebar-icon"> Logout
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <header class="page-header">
            <h1>Reports</h1>
            <p>Generate filtered blotter reports</p>
        </header>
        <form method="GET" class="filter-form"> 
            <div class="date-range-box">
                <div>
                    <label>Start Date</label>
                    <input type="date" name="start_date" value="<?= $start_date ?>">
                </div>
                <div>
                    <label>End Date</label>
                    <input type="date" name="end_date" value="<?= $end_date ?>">
                </div>
                <div>
                    <label>Case Type</label>
                    <select name="case_type">
                        <option value="">All</option>
                        <?php
                        $case_types = ["Theft","Assault","Domestic Violence","Vandalism","Noise Complaint","Missing Person","Drug-related Offense","Traffic Violation","Fraud","Others"];
                        foreach ($case_types as $type) {
                            $selected = ($case_type == $type) ? "selected" : "";
                            echo "<option value='$type' $selected>$type</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        <div class="filter-actions">
            <button type="submit">Filter</button>
            <a href="reports.php" class="btn btn-outline">Clear</a>
            <a href="#" class="btn btn-primary" onclick="window.print()">Print</a>
        </div>
        </form>                


        <!-- Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date & Time</th>
                        <th>Location</th>
                        <th>Case Type</th>
                        <th>Complainant</th>
                        <th>Respondent</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= date('M d, Y H:i', strtotime($row['incident_datetime'])) ?></td>
                        <td><?= htmlspecialchars($row['location']) ?></td>
                        <td><?= htmlspecialchars($row['case_type']) ?></td>
                        <td><?= htmlspecialchars($row['complainant_name']) ?></td>
                        <td><?= htmlspecialchars($row['respondent_name']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No records found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>
