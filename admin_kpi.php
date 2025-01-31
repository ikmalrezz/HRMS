<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

// Fetch employees
$employees = $conn->query("SELECT id, name FROM users WHERE role = 'Employee'");

// Handle KPI Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_POST['employee_id'];
    $kpi_score = $_POST['kpi_score'];
    $evaluation_period = $_POST['evaluation_period'];

    // Insert or Update KPI Score
    $stmt = $conn->prepare("INSERT INTO kpi_scores (user_id, kpi_score, evaluation_period) 
                            VALUES (?, ?, ?)
                            ON DUPLICATE KEY UPDATE kpi_score=?, evaluation_period=?");

    if (!$stmt) {
        die("SQL Prepare Error: " . $conn->error);
    }

    $stmt->bind_param("idssd", 
        $employee_id, $kpi_score, $evaluation_period, 
        $kpi_score, $evaluation_period
    );

    if ($stmt->execute()) {
        header("Location: admin_kpi.php?success=KPI updated successfully");
        exit();
    } else {
        die("KPI update failed: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KPI Management | Workfusion</title>
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
        .card { border-radius: 10px; background: #1e1e1e; padding: 20px; }
    </style>
</head>
<body>

<div class="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header text-center"><h3>Workfusion</h3></div>
        <ul class="list-unstyled components">
        <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="add_employee.php">Add Employee</a></li>
            <li><a href="manage_users.php">Manage Employee</a></li>
            <li><a href="admin_leave_requests.php">Leave Requests</a></li>
            <li class="active"><a href="admin_kpi.php">KPI Reports</a></li>
            <li class="active"><a href="admin_payroll.php">Payroll</a></li>
            <li><a href="logout.php" class="text-danger">Logout</a></li>  
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand">KPI Management</a>
        </nav>

        <h2 class="text-center">Manage Employee KPI Scores</h2>

        <!-- Success Message -->
        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
        <?php } ?>

        <form method="post" class="card p-4 text-white">
            <div class="mb-3">
                <label class="form-label">Select Employee</label>
                <select name="employee_id" class="form-control" required>
                    <?php while ($row = $employees->fetch_assoc()) { ?>
                        <option value="<?= $row['id']; ?>"><?= htmlspecialchars($row['name']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">KPI Score (0 - 100)</label>
                <input type="number" name="kpi_score" class="form-control" step="0.01" min="0" max="100" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Evaluation Period</label>
                <input type="date" name="evaluation_period" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Submit KPI</button>
        </form>
    </div>
</div>

</body>
</html>
