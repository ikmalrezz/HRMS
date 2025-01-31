<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

// Fetch employees
$employees = $conn->query("SELECT id, name FROM users WHERE role = 'Employee'");

// Handle Payroll Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_POST['employee_id'];
    $base_salary = $_POST['base_salary'];
    $deductions = $_POST['deductions'];
    $allowances = $_POST['allowances'];
    $payment_date = $_POST['payment_date'];

    // Insert or Update Payroll
    $stmt = $conn->prepare("INSERT INTO payroll (user_id, base_salary, deductions, allowances, payment_date) 
                            VALUES (?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE base_salary=?, deductions=?, allowances=?, payment_date=?");

    if (!$stmt) {
        die("SQL Prepare Error: " . $conn->error);
    }

    // Bind parameters correctly
    $stmt->bind_param("idddsddds", 
        $employee_id, $base_salary, $deductions, $allowances, $payment_date, 
        $base_salary, $deductions, $allowances, $payment_date
    );

    if ($stmt->execute()) {
        header("Location: admin_payroll.php?success=Payroll updated successfully");
        exit();
    } else {
        die("Payroll update failed: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll Management | Workfusion</title>
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
        .form-control { background: #232323; color: white; border: 1px solid #444; }
        .btn-primary { background-color: #007bff; border: none; }
    </style>
    <script>
        function calculateSalary() {
            let baseSalary = parseFloat(document.getElementById("base_salary").value) || 0;
            let deductions = parseFloat(document.getElementById("deductions").value) || 0;
            let allowances = parseFloat(document.getElementById("allowances").value) || 0;
            let netSalary = baseSalary + allowances - deductions;
            document.getElementById("net_salary").innerText = "RM " + netSalary.toFixed(2);
        }
    </script>
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
            <li><a href="admin_kpi.php">KPI Reports</a></li>
            <li class="active"><a href="admin_payroll.php">Payroll</a></li>
            <li><a href="logout.php" class="text-danger">Logout</a></li>
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand">Payroll Management</a>
        </nav>

        <h2 class="text-center">Manage Employee Payroll</h2>

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
                <label class="form-label">Base Salary (RM)</label>
                <input type="number" name="base_salary" id="base_salary" class="form-control" step="0.01" required oninput="calculateSalary()">
            </div>
            <div class="mb-3">
                <label class="form-label">Deductions (RM)</label>
                <input type="number" name="deductions" id="deductions" class="form-control" step="0.01" oninput="calculateSalary()">
            </div>
            <div class="mb-3">
                <label class="form-label">Allowances (RM)</label>
                <input type="number" name="allowances" id="allowances" class="form-control" step="0.01" oninput="calculateSalary()">
            </div>
            <div class="mb-3">
                <label class="form-label">Payment Date</label>
                <input type="date" name="payment_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Net Salary:</label>
                <h3 id="net_salary" class="text-success">RM 0.00</h3>
            </div>
            <button type="submit" class="btn btn-primary w-100">Submit Payroll</button>
        </form>
    </div>
</div>

</body>
</html>

