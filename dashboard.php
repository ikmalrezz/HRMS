<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Fetch admin details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Default profile picture
$profile_picture = !empty($user['profile_picture']) ? 'uploads/' . $user['profile_picture'] : 'uploads/default_profile.png';

// Fetch statistics
$total_users = $conn->query("SELECT COUNT(*) FROM users WHERE role != 'Admin'")->fetch_row()[0];
$total_hr = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'HR'")->fetch_row()[0];
$pending_leaves = $conn->query("SELECT COUNT(*) FROM leave_requests WHERE status='Pending'")->fetch_row()[0];

// Fetch Payroll Total
$total_salary = $conn->query("SELECT SUM(base_salary) FROM payroll")->fetch_row()[0] ?? 0.00;

// Fetch Approved Leaves
$approved_leaves = $conn->query("SELECT COUNT(*) FROM leave_requests WHERE status='Approved'")->fetch_row()[0] ?? 0;

// Fetch KPI data for chart
$kpi_query = $conn->query("SELECT users.name, COALESCE(kpi_scores.kpi_score, 0) AS kpi_score FROM users 
                           LEFT JOIN kpi_scores ON users.id = kpi_scores.user_id 
                           WHERE users.role = 'Employee'");
$kpi_labels = [];
$kpi_scores = [];
while ($row = $kpi_query->fetch_assoc()) {
    $kpi_labels[] = $row['name'];
    $kpi_scores[] = $row['kpi_score'];
}

// Fetch Leave Requests
$leave_requests = $conn->query("SELECT users.name, leave_requests.leave_type, leave_requests.start_date, leave_requests.end_date, leave_requests.status FROM leave_requests JOIN users ON leave_requests.user_id = users.id ORDER BY leave_requests.start_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Workfusion</title>
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
        .card { border-radius: 10px; }
        .navbar { background-color: #232323; padding: 10px 20px; }
        .navbar-brand { color: white !important; font-weight: bold; }
    </style>
</head>
<body>

<div class="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header text-center"><h3>Workfusion</h3></div>
        <ul class="list-unstyled components">
            <li class="active"><a href="dashboard.php">Dashboard</a></li>
            <li><a href="add_employee.php">Add Employee</a></li>
            <li><a href="manage_users.php">Manage Employees</a></li>
            <li><a href="admin_leave_requests.php">Leave Requests</a></li>
            <li><a href="admin_kpi.php">KPI Reports</a></li>
            <li><a href="admin_payroll.php">Payroll</a></li>
            <li><a href="logout.php" class="text-danger">Logout</a></li>
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand">Admin Dashboard</a>
            <div class="dropdown ms-auto">
                <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <img src="<?php echo $profile_picture; ?>" class="rounded-circle" width="40" height="40"> 
                    <?php echo htmlspecialchars($user['name']); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>

        <h2 class="text-center mb-4 mt-3">Dashboard</h2>

        <!-- Stats Cards -->
        <div class="row text-center">
            <div class="col-md-3">
                <div class="card bg-primary text-white p-3">
                    <h5>Total Employees</h5>
                    <h2><?php echo $total_users; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark p-3">
                    <h5>Pending Leaves</h5>
                    <h2><?php echo $pending_leaves; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white p-3">
                    <h5>HR Staff</h5>
                    <h2><?php echo $total_hr; ?></h2>
                </div>
            </div>
        </div>

        <!-- KPI Chart -->
        <div class="card mt-4 p-4 bg-dark text-white">
            <h5>Employee KPI Performance</h5>
            <canvas id="kpiChart"></canvas>
        </div>
    </div>
</div>

<script>
    window.onload = function () {
        const ctx = document.getElementById('kpiChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($kpi_labels); ?>,
                datasets: [{
                    label: 'KPI Score',
                    data: <?php echo json_encode($kpi_scores); ?>,
                    backgroundColor: ['blue', 'green', 'red', 'orange']
                }]
            }
        });
    };
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>