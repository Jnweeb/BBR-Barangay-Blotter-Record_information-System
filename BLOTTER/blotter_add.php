<?php
session_start();
require_once "../includes/config.php";
require_once "../includes/functions.php"; // make sure log_activity() is defined here

// Ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    die("Error: You must be logged in to add a blotter record.");
}

$user_id = $_SESSION["user_id"];

// Verify that this user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    die("Error: Logged-in user not found in users table.");
}
$stmt->close();

// Predefined case types
$case_types = [
    "Theft", "Assault", "Domestic Violence", "Vandalism", "Noise Complaint",
    "Missing Person", "Drug-related Offense", "Traffic Violation", "Fraud", "Others"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Trim and sanitize inputs
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

    // Basic validation
    if (empty($complainant_name) || empty($respondent_name) || empty($incident_datetime) || empty($location) || empty($case_type)) {
        die("Error: Please fill in all required fields.");
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // --- Insert complainant ---
        $stmt = $conn->prepare("INSERT INTO complainants (full_name, age, contact, address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $complainant_name, $complainant_age, $complainant_contact, $complainant_address);
        $stmt->execute();
        $complainant_id = $stmt->insert_id;

        // Log complainant addition
        log_activity($user_id, "Added complainant #$complainant_id ($complainant_name)", 'complainants', $complainant_id);

        // --- Insert respondent ---
        $stmt = $conn->prepare("INSERT INTO respondents (full_name, age, contact, address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $respondent_name, $respondent_age, $respondent_contact, $respondent_address);
        $stmt->execute();
        $respondent_id = $stmt->insert_id;

        // Log respondent addition
        log_activity($user_id, "Added respondent #$respondent_id ($respondent_name)", 'respondents', $respondent_id);

        // --- Insert blotter record ---
        $stmt = $conn->prepare("
            INSERT INTO blotter_records
            (incident_datetime, location, case_type, complainant, respondent, incident_summary, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "sssissi",
            $incident_datetime,
            $location,
            $case_type,
            $complainant_id,
            $respondent_id,
            $incident_summary,
            $user_id
        );
        $stmt->execute();
        $blotter_id = $stmt->insert_id;

        // Log blotter record addition
        log_activity($user_id, "Added blotter record #$blotter_id ($case_type at $location)", 'blotter_records', $blotter_id);

        $conn->commit();
        header("Location: ../blotter_management.php");
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
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div>
        <div class="blotter-card">
            <h1>Add Blotter Details</h1>
            <p>Add Blotter Details Information of Complainant and Incident Summary</p>
        </div>
        <div class="blotter-card">
            <a href="../blotter_management.php" class="btn btn-outline">← Back</a><br></br>
            <form class="blotter-form" method="POST">
                <div class="form-group">
                    <h3>Incident Information</h3>
                    <label for="incident_datetime">Date & Time</label>
                    <input type="datetime-local" name="incident_datetime" required>
        
                    <label for="location">Location</label>
                    <input type="text" name="location" required>
               
                    <label for="case_type">Case Type:</label>
                    <select name="case_type" required>
                        <option value="">--Select Case Type--</option>
                        <?php foreach ($case_types as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <h3>Complainant Details</h3>
                    <label for="complainant_name">Full Name</label>
                    <input type="text" name="complainant_name" required>

                    <label for="complainant_age">Age</label>
                    <input type="number" name="complainant_age">
   
                    <label for="complainant_contact">Contact</label>
                    <input type="text" name="complainant_contact"> 

                    <label for="complainant_address">Address</label>
                    <input type="text" name="complainant_address">
                </div>
                <div class="form-group">
                    <h3>Respondent Details</h3>
                    <label for="respondent_name">Full Name</label>
                    <input type="text" name="respondent_name" required>

                    <label for="respondent_age">Age</label>
                    <input type="number" name="respondent_age">

                    <label for="respondent_contact">Contact</label>
                    <input type="text" name="respondent_contact">

                    <label for="respondent_address">Address</label>
                    <input type="text" name="respondent_address">
                </div>
                <div class="form-group">
                    <h3>Incident Summary</h3>
                    <textarea name="incident_summary"></textarea>
                </div>
                <button type="submit">Save Record</button>
            </form>
        </div>
    </div>
</body>
</html>
