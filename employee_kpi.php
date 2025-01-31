<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Employee') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch KPI details
$stmt = $conn->prepare("SELECT kpi_score, evaluation_period FROM kpi_scores WHERE user_id = ?");
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$kpi = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My KPI | Workfusion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <li><a href="employee_dashboard.php">Dashboard</a></li>
            <li><a href="pemployee_profile.php">My Profile</a></li>
            <li><a href="employee_leave_requests.php">Leave Requests</a></li>
            <li class="active"><a href="employee_kpi.php">KPI Report</a></li>
            <li><a href="employee_payroll.php">Salary Details</a></li>
            <li><a href="logout.php" class="text-danger">Logout</a></li>
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand">My KPI</a>
        </nav>

        <h2 class="text-center">KPI Details</h2>

        <div class="card p-4 bg-dark text-white">
            <p><strong>KPI Score:</strong> <?= $kpi ? $kpi['kpi_score'] : 'Not Assigned'; ?></p>
            <p><strong>Evaluation Date:</strong> <?= $kpi ? $kpi['evaluation_period'] : 'Not Available'; ?></p>
        </div>

        <!-- KPI Chart -->
        <div class="card mt-4 p-4 bg-dark text-white">
            <h5>Performance Chart</h5>
            <canvas id="kpiChart"></canvas>
        </div>
    </div>
</div>

<script>
    // KPI Chart using Chart.js
    const ctx = document.getElementById('kpiChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ["My KPI"],
            datasets: [{
                label: "KPI Score",
                data: [<?= $kpi ? $kpi['kpi_score'] : 0; ?>],
                backgroundColor: ["#007bff"]
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true, max: 100 }
            }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
