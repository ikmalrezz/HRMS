<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Employee') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch payroll details for the logged-in employee
$stmt = $conn->prepare("SELECT base_salary, deductions, allowances, net_salary, payment_date FROM payroll WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$payroll = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Payroll | Workfusion</title>
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
        @media print {
            body * { visibility: hidden; }
            #printSection, #printSection * { visibility: visible; }
            #printSection { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>
    <script>
        function printPayroll() {
            window.print();
        }
    </script>
</head>
<body>

<div class="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header text-center"><h3>Workfusion</h3></div>
        <ul class="list-unstyled components">
            <li><a href="employee_dashboard.php">Dashboard</a></li>
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="employee_leave_requests.php">Leave Requests</a></li>
            <li><a href="employee_kpi.php">KPI Report</a></li>
            <li class="active"><a href="employee_payroll.php">Salary Details</a></li>
            <li><a href="logout.php" class="text-danger">Logout</a></li>
            
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand">My Payroll</a>
        </nav>

     

        <?php if ($payroll) { ?>
            <div class="card text-white" id="printSection">
                <h3 class="text-center">Payroll Slip</h3>
                <p><strong>Base Salary:</strong> RM <?php echo number_format($payroll['base_salary'], 2); ?></p>
                <p><strong>Deductions:</strong> -RM <?php echo number_format($payroll['deductions'], 2); ?></p>
                <p><strong>Allowances:</strong> +RM <?php echo number_format($payroll['allowances'], 2); ?></p>
                <p><strong>Net Salary:</strong> <span class="text-success">RM <?php echo number_format($payroll['net_salary'], 2); ?></span></p>
                <p><strong>Payment Date:</strong> <?php echo $payroll['payment_date']; ?></p>
            </div>

            <button onclick="printPayroll()" class="btn btn-primary mt-3">Print Payroll</button>
        <?php } else { ?>
            <p class="text-warning text-center">No payroll data available.</p>
        <?php } ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
