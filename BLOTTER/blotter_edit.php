<?php
session_start();
require_once "../includes/config.php";
require_once "../includes/functions.php"; // log_activity() should be defined here

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$case_types = [
    "Theft", "Assault", "Domestic Violence", "Vandalism", "Noise Complaint",
    "Missing Person", "Drug-related Offense", "Traffic Violation", "Fraud", "Others"
];

// Get record ID
if (!isset($_GET['id'])) {
    die("Error: Record ID not specified.");
}
$id = intval($_GET['id']);

// Fetch record with related complainant/respondent
$stmt = $conn->prepare("
    SELECT b.*, c.full_name AS c_name, c.age AS c_age, c.contact AS c_contact, c.address AS c_address,
           r.full_name AS r_name, r.age AS r_age, r.contact AS r_contact, r.address AS r_address
    FROM blotter_records b
    JOIN complainants c ON b.complainant = c.id
    JOIN respondents r ON b.respondent = r.id
    WHERE b.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Error: Record not found.");
}
$row = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $incident_datetime = trim($_POST["incident_datetime"]);
    $location = trim($_POST["location"]);
    $case_type = trim($_POST["case_type"]);
    $incident_summary = trim($_POST["incident_summary"]);

    $complainant_name = trim($_POST["complainant_name"]);
    $complainant_age = !empty($_POST["complainant_age"]) ? intval($_POST["complainant_age"]) : NULL;
    $complainant_contact = trim($_POST["complainant_contact"]);
    $complainant_address = trim($_POST["complainant_address"]);

    $respondent_name = trim($_POST["respondent_name"]);
    $respondent_age = !empty($_POST["respondent_age"]) ? intval($_POST["respondent_age"]) : NULL;
    $respondent_contact = trim($_POST["respondent_contact"]);
    $respondent_address = trim($_POST["respondent_address"]);

    $conn->begin_transaction();
    try {
        // --- Update complainant ---
        $stmt = $conn->prepare("UPDATE complainants SET full_name=?, age=?, contact=?, address=? WHERE id=?");
        $stmt->bind_param("sissi", $complainant_name, $complainant_age, $complainant_contact, $complainant_address, $row['complainant']);
        $stmt->execute();
        log_activity($user_id, "Updated complainant #".$row['complainant']." ($complainant_name)", 'complainants', $row['complainant']);

        // --- Update respondent ---
        $stmt = $conn->prepare("UPDATE respondents SET full_name=?, age=?, contact=?, address=? WHERE id=?");
        $stmt->bind_param("sissi", $respondent_name, $respondent_age, $respondent_contact, $respondent_address, $row['respondent']);
        $stmt->execute();
        log_activity($user_id, "Updated respondent #".$row['respondent']." ($respondent_name)", 'respondents', $row['respondent']);

        // --- Update blotter record ---
        $stmt = $conn->prepare("UPDATE blotter_records SET incident_datetime=?, location=?, case_type=?, incident_summary=? WHERE id=?");
        $stmt->bind_param("ssssi", $incident_datetime, $location, $case_type, $incident_summary, $id);
        $stmt->execute();
        log_activity($user_id, "Updated blotter record #$id ($case_type at $location)", 'blotter_records', $id);

        $conn->commit();
        header("Location: blotter_view.php?id=".$id);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blotter Record</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div>
        <div class="blotter-card">
            <a href="../blotter_management.php" class="btn btn-outline">← Back</a><br></br>
            <form class="blotter-form" method="POST">
                <div class="form-group">
                    <h3>Incident Information</h3>
                    <label for="incident_datetime">Date & Time</label>
                    <input type="datetime-local" name="incident_datetime" value="<?= htmlspecialchars($row['incident_datetime']) ?>" required>
        
                    <label for="location">Location</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($row['location']) ?>" required>
               
                    <label for="case_type">Case Type</label>
                    <select name="case_type" required>
                        <option value="">--Select Case Type--</option>
                        <?php foreach ($case_types as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= $row['case_type'] === $type ? 'selected' : '' ?>><?= htmlspecialchars($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <h3>Complainant Details</h3>
                    <label for="complainant_name">Full Name</label>
                    <input type="text" name="complainant_name" value="<?= htmlspecialchars($row['c_name']) ?>" required>

                    <label for="complainant_age">Age</label>
                    <input type="number" name="complainant_age" value="<?= htmlspecialchars($row['c_age']) ?>">

                    <label for="complainant_contact">Contact</label>
                    <input type="text" name="complainant_contact" value="<?= htmlspecialchars($row['c_contact']) ?>">

                    <label for="complainant_address">Address</label>
                    <input type="text" name="complainant_address" value="<?= htmlspecialchars($row['c_address']) ?>">
                </div>

                <div class="form-group">
                    <h3>Respondent Details</h3>
                    <label for="respondent_name">Full Name</label>
                    <input type="text" name="respondent_name" value="<?= htmlspecialchars($row['r_name']) ?>" required>

                    <label for="respondent_age">Age</label>
                    <input type="number" name="respondent_age" value="<?= htmlspecialchars($row['r_age']) ?>">

                    <label for="respondent_contact">Contact</label>
                    <input type="text" name="respondent_contact" value="<?= htmlspecialchars($row['r_contact']) ?>">

                    <label for="respondent_address">Address</label>
                    <input type="text" name="respondent_address" value="<?= htmlspecialchars($row['r_address']) ?>">
                </div>

                <div class="form-group">
                    <h3>Incident Summary</h3>
                    <textarea name="incident_summary"><?= htmlspecialchars($row['incident_summary']) ?></textarea>
                </div>

                <button type="submit">Update Record</button>
            </form>
        </div>
    </div>
</body>
</html>
