<?php
session_start();
require_once "includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: index.php");
    exit();
}

$full_name = $_SESSION["full_name"];
$role = $_SESSION["role"];

$sql = "SELECT * FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>User Management</title>
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
        <li><a class="active" href="user_management.php"><img src="assets/icons/user.svg" class="sidebar-icon"> User Management</a></li>
        <li><a href="activity_log.php"><img src="assets/icons/act-logs.svg" class="sidebar-icon"> Activity Log</a></li>
        <li><a href="logout.php"><img src="assets/icons/logout.svg" class="sidebar-icon"> Logout</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <header>
        <h1>User Management</h1>
        <p>Manage accounts, roles and access control.</p>
    </header>

    <div class="actions">
        <a href="USERS/add_user.php" class="btn">
            <img src="assets/icons/add-user.svg" class="btn-icon"> Add New User
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($u = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $u["id"] ?></td>
                    <td><?= htmlspecialchars($u["full_name"]) ?></td>
                    <td><?= htmlspecialchars($u["username"]) ?></td>
                    <td><?= htmlspecialchars(ucfirst($u["role"])) ?></td>
                    <td>
                        <span class="status <?= $u['status'] ?>">
                            <?= ucfirst($u['status']) ?>
                        </span>
                    </td>
                    <td><?= $u["created_at"] ?></td>

                    <td>
                        <a href="USERS/edit_user.php?id=<?= $u["id"] ?>" class="btn">Edit</a>

                        <!-- Activate / Deactivate -->
                        <a href="USERS/toggle_user_status.php?id=<?= $u['id'] ?>&status=<?= $u['status'] ?>"
                           class="btn <?= $u['status'] == 'active' ? 'btn-deact' : 'btn-success' ?>">
                           <?= $u['status'] == 'active' ? 'Deactivate' : 'Activate' ?>
                        </a>

                        <!-- Delete -->
                        <a href="USERS/delete_user.php?id=<?= $u["id"] ?>" 
                           class="btn-danger btn"
                           onclick="return confirm('Delete this user?')">
                           Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">No users found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
