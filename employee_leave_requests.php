<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Employee') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch employee details
$stmt = $conn->prepare("SELECT name, profile_picture FROM users WHERE id = ?");
if (!$stmt) {
    die("SQL Prepare Error: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("SQL Execution Error: " . $stmt->error);
}

$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    $user = ['name' => 'Unknown', 'profile_picture' => 'uploads/default_profile.png'];
}

$profile_picture = !empty($user['profile_picture']) ? $user['profile_picture'] : 'uploads/default_profile.png';

// Handle Leave Request Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $leave_type = htmlspecialchars($_POST['leave_type']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Debugging: Check if form data is received
    if (empty($leave_type) || empty($start_date) || empty($end_date)) {
        die("Error: All fields are required.");
    }

    // Insert into leave_requests table
    $stmt = $conn->prepare("INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, status) VALUES (?, ?, ?, ?, 'Pending')");
    
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("isss", $user_id, $leave_type, $start_date, $end_date);

    if ($stmt->execute()) {
        header("Location: employee_dashboard.php?success=Leave request submitted successfully");
        exit();
    } else {
        die("Execution failed: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Leave Request | Workfusion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: white; }
        .wrapper { display: flex; }
        #sidebar { width: 250px; height: 100vh; background: #232323; color: white; position: fixed; padding-top: 20px; }
        #sidebar ul { padding: 0; list-style: none; }
        #sidebar ul li { padding: 15px; border-bottom: 1px solid #555; }
        #sidebar ul li a { color: white; text-decoration: none; display: block; }
        #sidebar ul li a:hover { background: #007bff; }
        #content { margin-left: 250px; padding: 20px; width: 100%; }
        .navbar { background-color: #232323; padding: 10px 20px; }
        .navbar-brand { color: white !important; font-weight: bold; }
    </style>
</head>
<body>

<div class="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header text-center"><h3>Workfusion</h3></div>
        <ul class="list-unstyled components">
            <li><a href="employee_dashboard.php">Dashboard</a></li>
            <li><a href="employee_profile.php">My Profile</a></li>
            <li class="active"><a href="employee_leave_requests.php">Leave Requests</a></li>
            <li><a href="employee_kpi.php">KPI Report</a></li>
            <li><a href="employee_payroll.php">Salary Details</a></li>
            <li><a href="logout.php" class="text-danger">Logout</a></li>
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand" href="employee_dashboard.php">Leave Request</a>
            <div class="dropdown ms-auto">
                <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" class="rounded-circle" width="40" height="40"> 
                    <?php echo htmlspecialchars($user['name']); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>

        <h2 class="text-center">Submit Leave Request</h2>

        <form action="employee_leave_requests.php" method="post" class="card p-4 bg-dark text-white">
            <div class="mb-3">
                <label class="form-label">Leave Type</label>
                <select name="leave_type" class="form-control" required>
                    <option value="Sick">Sick</option>
                    <option value="Vacation">Vacation</option>
                    <option value="Personal">Personal</option>
                    <option value="Unpaid">Unpaid</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Submit Request</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




