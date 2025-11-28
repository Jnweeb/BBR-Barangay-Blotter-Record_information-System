<?php
session_start();
require_once "includes/config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$full_name = $_SESSION["full_name"] ?? "User";
$role = $_SESSION["role"] ?? "Unknown";

// --- Fetch Counts ---
$user_count = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0;
$blotter_count = $conn->query("SELECT COUNT(*) AS total FROM blotter_records")->fetch_assoc()['total'] ?? 0;
$case_count = $blotter_count; // total cases = blotter records

// --- Case types in last 7 days ---
$seven_days_ago = date('Y-m-d', strtotime('-6 days'));
$today = date('Y-m-d');

$case_types_res = $conn->query("
    SELECT case_type, COUNT(*) as total
    FROM blotter_records
    WHERE DATE(incident_datetime) BETWEEN '$seven_days_ago' AND '$today'
    GROUP BY case_type
    ORDER BY total DESC
");

$case_labels = [];
$case_counts = [];

while ($row = $case_types_res->fetch_assoc()) {
    $case_labels[] = $row['case_type'];
    $case_counts[] = (int)$row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard">

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="assets/icons/brgy.svg" alt="Logo" class="sidebar-logo">
            <div class="user-info-sidebar">
                <h3><?= htmlspecialchars($full_name) ?></h3>
                <p><?= htmlspecialchars($role) ?></p>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><img src="assets/icons/home.svg" class="sidebar-icon"> Dashboard</a></li>
            <li><a href="blotter_management.php"><img src="assets/icons/blotter.svg" class="sidebar-icon"> Blotter Management</a></li>
            <li><a href="reports.php"><img src="assets/icons/report.svg" class="sidebar-icon"> Reports</a></li>
            <?php if ($role === 'Admin'): ?>
            <li><a href="user_management.php"><img src="assets/icons/user.svg" class="sidebar-icon"> User Management</a></li>
            <li><a href="activity_log.php"><img src="assets/icons/act-logs.svg" class="sidebar-icon"> Activity Log</a></li>
            <?php endif; ?>
            <li><a href="logout.php"><img src="assets/icons/logout.svg" class="sidebar-icon"> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <h1>Dashboard</h1>
        </header>

        <!-- Dashboard Cards -->
        <div class="cards-container">
            <div class="card">
                <i class="fas fa-users card-icon"></i>
                <h3>Users</h3>
                <p>Total registered users: <?= $user_count ?></p>
            </div>
            <div class="card">
                <i class="fas fa-file-alt card-icon"></i>
                <h3>Blotters</h3>
                <p>Total blotter entries: <?= $blotter_count ?></p>
            </div>
            <div class="card">
                <i class="fas fa-gavel card-icon"></i>
                <h3>Cases</h3>
                <p>Total cases recorded: <?= $case_count ?></p>
            </div>
        </div>

        <!-- Case Type Chart -->
        <div class="card" style="margin-top: 2rem;">
            <h3>Cases by Type in the Last 7 Days</h3>
            <canvas id="casesChart" height="200"></canvas>
        </div>
    </div>

    <script>
    const ctx = document.getElementById('casesChart').getContext('2d');
    const casesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($case_labels) ?>,
            datasets: [{
                label: 'Cases',
                data: <?= json_encode($case_counts) ?>,
                backgroundColor: 'rgba(0, 123, 255, 0.7)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    }
                }
            }
        }
    });
    </script>

</body>
</html>
