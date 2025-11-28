<?php
session_start();
require_once "includes/config.php";
require_once "includes/functions.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

// Filters
$user_filter   = $_GET['user'] ?? "";
$keyword       = $_GET['keyword'] ?? "";
$start         = $_GET['start'] ?? "";
$end           = $_GET['end'] ?? "";
$table_filter  = $_GET['table'] ?? "";

// Base query with LEFT JOIN so logs without users still appear
$query = "
    SELECT a.id, a.action, a.log_time, a.table_name, a.record_id, u.full_name 
    FROM activity_log a
    LEFT JOIN users u ON a.user_id = u.id
    WHERE 1
";

$params = [];
$types  = "";

// Apply filters safely
if ($user_filter !== "") {
    $query .= " AND a.user_id = ?";
    $params[] = intval($user_filter);
    $types .= "i";
}
if ($keyword !== "") {
    $query .= " AND a.action LIKE ?";
    $params[] = "%" . $conn->real_escape_string($keyword) . "%";
    $types .= "s";
}
if ($start !== "") {
    $query .= " AND DATE(a.log_time) >= ?";
    $params[] = $start;
    $types .= "s";
}
if ($end !== "") {
    $query .= " AND DATE(a.log_time) <= ?";
    $params[] = $end;
    $types .= "s";
}
if ($table_filter !== "") {
    $query .= " AND a.table_name = ?";
    $params[] = $table_filter;
    $types .= "s";
}

$query .= " ORDER BY a.log_time DESC";

// Prepare statement
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get users for filter dropdown
$users = $conn->query("SELECT id, full_name FROM users ORDER BY full_name ASC");

// Get distinct table names for filter dropdown
$tables = $conn->query("SELECT DISTINCT table_name FROM activity_log ORDER BY table_name ASC");

$full_name = $_SESSION['full_name'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Activity Log</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="dashboard">

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <img src="assets/images/brgy.png" class="sidebar-logo">
        <h3><?= htmlspecialchars($full_name) ?></h3>
        <p><?= htmlspecialchars(ucfirst($role)) ?></p>
    </div>

    <ul class="sidebar-menu">
        <li><a href="dashboard.php"><img src="assets/icons/home.svg" class="sidebar-icon"> Dashboard</a></li>
        <li><a href="user_management.php"><img src="assets/icons/user.svg" class="sidebar-icon"> User Management</a></li>
        <li><a class="active" href="activity_log.php"><img src="assets/icons/act-logs.svg" class="sidebar-icon"> Activity Log</a></li>
        <li><a href="logout.php"><img src="assets/icons/logout.svg" class="sidebar-icon"> Logout</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">

    <div class="page-header">
        <h1>Activity Log</h1>
        <p>Track user activities with filtering and export options.</p>
    </div>

    <form method="GET" class="filter-form">

        <select name="user">
            <option value="">All Users</option>
            <?php while ($u = $users->fetch_assoc()): ?>
                <option value="<?= $u['id'] ?>" <?= $user_filter == $u['id'] ? "selected" : "" ?>>
                    <?= htmlspecialchars($u['full_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <select name="table">
            <option value="">All Tables</option>
            <?php while ($t = $tables->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($t['table_name']) ?>" <?= $table_filter == $t['table_name'] ? "selected" : "" ?>>
                    <?= htmlspecialchars(ucfirst($t['table_name'])) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <input type="text" name="keyword" placeholder="Search keyword..." value="<?= htmlspecialchars($keyword) ?>">

        <button type="submit" >Filter</button>
        <a href="activity_log_export.php" class="btn btn-primary" >Export CSV</a>
        <a href="#" class="btn btn-primary" onclick="window.print()">Print</a>
    </form>


    <!-- Log Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Table</th>
                    <th>Record ID</th>
                    <th>Date & Time</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['full_name'] ?? 'Deleted User') ?></td>
                        <td><?= htmlspecialchars($row['action']) ?></td>
                        <td><?= htmlspecialchars($row['table_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['record_id'] ?? '-') ?></td>
                        <td><?= $row['log_time'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">No activity found.</td></tr>
            <?php endif; ?>
            </tbody>


        </table>
    </div>

</div>

</body>
</html>
