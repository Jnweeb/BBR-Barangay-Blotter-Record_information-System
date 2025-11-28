<?php
session_start();
require_once "includes/config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$full_name = $_SESSION['full_name'] ?? 'User';
$role = $_SESSION['role'] ?? 'User';

// --- Handle search/filter ---
$search = trim($_GET['search'] ?? '');
$filter_case = trim($_GET['case_type'] ?? '');

// --- Pagination setup ---
$perPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// --- Build query with search/filter ---
$sql = "
    SELECT b.id, b.incident_datetime, b.location, b.case_type,
           c.full_name AS complainant_name,
           r.full_name AS respondent_name
    FROM blotter_records b
    JOIN complainants c ON b.complainant = c.id
    JOIN respondents r ON b.respondent = r.id
    WHERE 1
";
$params = [];
$types = "";

// Add search by location, complainant, or respondent
if (!empty($search)) {
    $sql .= " AND (b.location LIKE ? OR c.full_name LIKE ? OR r.full_name LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    $types .= "sss";
}

// Filter by case type
if (!empty($filter_case)) {
    $sql .= " AND b.case_type = ?";
    $params[] = $filter_case;
    $types .= "s";
}

$sql .= " ORDER BY b.incident_datetime ASC LIMIT ?, ?";
$params[] = $offset;
$params[] = $perPage;
$types .= "ii";

// Prepare and execute
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// --- Get all case types for filter dropdown ---
$caseTypesResult = $conn->query("SELECT DISTINCT case_type FROM blotter_records ORDER BY case_type ASC");
$allCaseTypes = [];
while ($row = $caseTypesResult->fetch_assoc()) {
    $allCaseTypes[] = $row['case_type'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blotter Management</title>
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
        <header><h1>Barangay Blotter Management (BBR)</h1></header>

        <a class="btn" href="BLOTTER/blotter_add.php">Add Blotter Record</a>

        <form class="filter-form" method="GET">
            <input type="text" name="search" placeholder="Search location, complainant, respondent..." value="<?= htmlspecialchars($search) ?>">
            <select name="case_type">
                <option value="">All Case Types</option>
                <?php foreach ($allCaseTypes as $type): ?>
                    <option value="<?= htmlspecialchars($type) ?>" <?= $filter_case === $type ? 'selected' : '' ?>><?= htmlspecialchars($type) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">Filter</button>
        </form>

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
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($row['incident_datetime']))) ?></td>
                            <td><?= htmlspecialchars($row['location']) ?></td>
                            <td><?= htmlspecialchars($row['case_type']) ?></td>
                            <td><?= htmlspecialchars($row['complainant_name']) ?></td>
                            <td><?= htmlspecialchars($row['respondent_name']) ?></td>
                            <td>
                                <a class="btn" href="BLOTTER/blotter_view.php?id=<?= urlencode($row['id']) ?>">View</a>
                                <a class="btn" href="BLOTTER/blotter_edit.php?id=<?= urlencode($row['id']) ?>">Edit</a>
                                <a class="btn-danger btn" href="BLOTTER/blotter_delete.php?id=<?= urlencode($row['id']) ?>" 
                                onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center;">No blotter records found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>


        <!-- Pagination -->
        <div style="margin-top:10px;">
            <?php
            // Count total records for pagination
            $countSql = "SELECT COUNT(*) as total FROM blotter_records";
            $countResult = $conn->query($countSql);
            $totalRecords = $countResult->fetch_assoc()['total'];
            $totalPages = ceil($totalRecords / $perPage);
            if ($totalPages > 1):
                for ($p = 1; $p <= $totalPages; $p++):
                    $queryString = http_build_query(array_merge($_GET, ['page' => $p]));
                    $active = $p === $page ? 'style="font-weight:bold;"' : '';
                    echo "<a href='?{$queryString}' {$active}>{$p}</a> ";
                endfor;
            endif;
            ?>
        </div>
    </div>

</body>
</html>
