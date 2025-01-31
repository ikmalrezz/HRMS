<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Employee') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];

// Fetch employee details
$stmt = $conn->prepare("SELECT name, profile_picture, attendance_rate, leave_balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Default profile picture
$profile_picture = !empty($user['profile_picture']) ? 'uploads/' . $user['profile_picture'] : 'uploads/default_profile.png';

// Fetch salary details
$salary_stmt = $conn->prepare("SELECT base_salary FROM payroll WHERE user_id = ?");
$salary_stmt->bind_param("i", $user_id);
$salary_stmt->execute();
$salary_result = $salary_stmt->get_result();
$salary = $salary_result->fetch_assoc();
$salary_amount = $salary ? number_format($salary['base_salary'], 2) : "0.00";

// Fetch KPI score
$kpi_stmt = $conn->prepare("SELECT kpi_score FROM kpi_scores WHERE user_id = ?");
$kpi_stmt->bind_param("i", $user_id);
$kpi_stmt->execute();
$kpi_result = $kpi_stmt->get_result();
$kpi = $kpi_result->fetch_assoc();
$kpi_score = $kpi ? $kpi['kpi_score'] : 0;

// Fetch total approved leaves
$leave_query = $conn->prepare("SELECT COUNT(*) FROM leave_requests WHERE user_id = ? AND status = 'Approved'");
$leave_query->bind_param("i", $user_id);
$leave_query->execute();
$approved_leaves = $leave_query->get_result()->fetch_row()[0];

// Fetch leave requests to display in dashboard
$leave_requests = $conn->prepare("SELECT leave_type, start_date, end_date, status FROM leave_requests WHERE user_id = ? ORDER BY start_date DESC");
$leave_requests->bind_param("i", $user_id);
$leave_requests->execute();
$leave_results = $leave_requests->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Dashboard | Workfusion</title>
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
        .card { border-radius: 10px; background: #1e1e1e; padding: 20px; }
        .navbar { background-color: #232323; padding: 10px 20px; }
        .navbar-brand { color: white !important; font-weight: bold; }
        .badge-pending { background-color: #ffc107; color: black; }
        .badge-approved { background-color: #28a745; }
        .badge-rejected { background-color: #dc3545; }
    </style>
</head>
<body>

<div class="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header text-center"><h3>Workfusion</h3></div>
        <ul class="list-unstyled components">
            <li class="active"><a href="employee_dashboard.php">Dashboard</a></li>
            <li><a href="employee_profile.php">My Profile</a></li>
            <li><a href="employee_leave_requests.php">Leave Requests</a></li>
            <li><a href="employee_kpi.php">KPI Report</a></li>
            <li><a href="employee_payroll.php">Salary Details</a></li>
            <li><a href="logout.php" class="text-danger">Logout</a></li>
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand" href="employee_dashboard.php">Employee Dashboard</a>
            <div class="dropdown ms-auto">
                <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <img src="<?php echo $profile_picture; ?>" class="rounded-circle" width="40" height="40"> 
                    <?php echo htmlspecialchars($user['name']); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="employee_profile.php">My Profile</a></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>

        <h2 class="text-center mb-4 mt-3">Dashboard</h2>

        <!-- Stats Cards -->
        <div class="row text-center">
            <div class="col-md-3">
                <div class="card bg-primary text-white p-3">
                    <h5>Total Approved Leaves</h5>
                    <h2><?php echo $approved_leaves; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark p-3">
                    <h5>Salary</h5>
                    <h2>RM <?php echo $salary_amount; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white p-3">
                    <h5>Attendance Rate</h5>
                    <h2><?php echo $user['attendance_rate']; ?>%</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white p-3">
                    <h5>Current KPI Score</h5>
                    <h2><?php echo $kpi_score; ?></h2>
                </div>
            </div>
        </div>

        <!-- Leave Requests Display -->
        <div class="card mt-4 p-4 text-white">
            <h5>Leave Requests</h5>
            <table class="table table-dark table-striped">
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $leave_results->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                            <td><?php echo $row['start_date']; ?></td>
                            <td><?php echo $row['end_date']; ?></td>
                            <td>
                                <span class="badge 
                                    <?php echo ($row['status'] == 'Pending') ? 'badge-pending' : ($row['status'] == 'Approved' ? 'badge-approved' : 'badge-rejected'); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


