<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

// Fetch admin details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$profile_picture = !empty($user['profile_picture']) ? 'uploads/' . $user['profile_picture'] : 'uploads/default_profile.png';

// Handle Leave Approval/Rejection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['leave_id']) && isset($_POST['action'])) {
    $leave_id = $_POST['leave_id'];
    $action = $_POST['action'];

    if ($action == "approve") {
        $stmt = $conn->prepare("UPDATE leave_requests SET status = 'Approved' WHERE id = ?");
    } else {
        $stmt = $conn->prepare("UPDATE leave_requests SET status = 'Rejected' WHERE id = ?");
    }

    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    header("Location: admin_leave_requests.php?success=Leave request updated");
    exit();
}

// Handle Setting Leave Balance
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['employee_id']) && isset($_POST['leave_balance'])) {
    $employee_id = $_POST['employee_id'];
    $leave_balance = $_POST['leave_balance'];

    $stmt = $conn->prepare("UPDATE users SET leave_balance = ? WHERE id = ?");
    $stmt->bind_param("ii", $leave_balance, $employee_id);
    $stmt->execute();
    header("Location: admin_leave_requests.php?success=Leave balance updated");
    exit();
}

// Fetch all pending leave requests
$leave_requests = $conn->query("SELECT leave_requests.id, users.id as employee_id, users.name, users.leave_balance, leave_requests.leave_type, leave_requests.start_date, leave_requests.end_date, leave_requests.status 
                                FROM leave_requests 
                                JOIN users ON leave_requests.user_id = users.id 
                                WHERE leave_requests.status = 'Pending'");

// Fetch all employees for setting leave balance
$employees = $conn->query("SELECT id, name, leave_balance FROM users WHERE role = 'Employee'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Leave Management | Workfusion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: white;
        }

        /* Sidebar */
        .wrapper {
            display: flex;
        }

        #sidebar {
            width: 250px;
            height: 100vh;
            background: #232323;
            color: white;
            position: fixed;
            padding-top: 20px;
        }

        #sidebar ul {
            padding: 0;
            list-style: none;
        }

        #sidebar ul li {
            padding: 15px;
            border-bottom: 1px solid #555;
        }

        #sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
        }

        #sidebar ul li a:hover {
            background: #007bff;
        }

        /* Page Content */
        #content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
        }

        .navbar {
            background-color: #232323;
            padding: 10px 20px;
        }

        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }
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
            <li class="active"><a href="admin_leave_requests.php">Leave Requests</a></li>
            <li><a href="admin_kpi.php">KPI Reports</a></li>
            <li><a href="admin_payroll.php">Payroll</a></li>
            <li><a href="logout.php" class="text-danger">Logout</a></li>
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand">Leave Requests</a>
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

        <h2 class="text-center">Manage Leave Requests & Set Leave Balance</h2>

        <!-- Success or Error Message -->
        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
        <?php } elseif (isset($_GET['error'])) { ?>
            <div class="alert alert-danger"><?php echo $_GET['error']; ?></div>
        <?php } ?>

        <!-- Set Leave Balance -->
        <div class="card p-4 bg-dark text-white">
            <h5>Set Employee Leave Balance</h5>
            <form method="post">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Select Employee</label>
                        <select name="employee_id" class="form-control" required>
                            <?php while ($emp = $employees->fetch_assoc()) { ?>
                                <option value="<?= $emp['id']; ?>"><?= htmlspecialchars($emp['name']) . " (Current: " . $emp['leave_balance'] . " days)"; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Set Leave Days</label>
                        <input type="number" name="leave_balance" class="form-control" min="0" required>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="submit" class="btn btn-primary w-100">Update</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Pending Leave Requests -->
        <div class="card mt-4 p-4 bg-dark text-white">
            <h5>Pending Leave Requests</h5>
            <table class="table table-dark table-striped">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Leave Balance</th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $leave_requests->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo $row['leave_balance']; ?> days</td>
                            <td><?php echo $row['leave_type']; ?></td>
                            <td><?php echo $row['start_date']; ?></td>
                            <td><?php echo $row['end_date']; ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="leave_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>


