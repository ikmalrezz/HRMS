<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage_users.php?error=Invalid employee ID");
    exit();
}

$employee_id = intval($_GET['id']);

// Fetch employee details
$stmt = $conn->prepare("SELECT name, email, role, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_users.php?error=Employee not found");
    exit();
}

$employee = $result->fetch_assoc();

// Update employee details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $role = $_POST['role'];

    // Check if a new profile picture was uploaded
    if (!empty($_FILES['profile_picture']['name'])) {
        $upload_dir = "uploads/";
        $file_name = time() . "_" . basename($_FILES["profile_picture"]["name"]);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            // Update profile picture in the database
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $file_name, $employee_id);
            $stmt->execute();
            $employee['profile_picture'] = $file_name;
        }
    }

    // Update user details
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $email, $role, $employee_id);

    if ($stmt->execute()) {
        header("Location: manage_users.php?success=Employee updated successfully");
        exit();
    } else {
        $error = "Error updating employee details.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee | Workfusion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .dropdown-menu {
            background: #232323;
            border: none;
        }

        .dropdown-menu a {
            color: white;
        }

        .dropdown-menu a:hover {
            background: #007bff;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header text-center">
            <h3>Workfusion</h3>
        </div>
        <ul class="list-unstyled components">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="add_employee.php">Add Employee</a></li>
            <li class="active"><a href="manage_users.php">Manage Employees</a></li>
            <li><a href="leave_requests.php">Leave Requests</a></li>
            <li><a href="kpi_reports.php">KPI Reports</a></li>
            <li><a href="payroll.php">Payroll</a></li>
            <li><a href="logout.php" class="text-danger">Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div id="content">
        <!-- Navbar Header -->
        <nav class="navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand" href="manage_users.php">Edit Employee</a>
        </nav>

        <h2 class="text-center mb-4 mt-3">Edit Employee Details</h2>

        <!-- Edit Form -->
        <div class="card p-4 bg-dark text-white">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="text-center">
                    <img src="<?php echo (!empty($employee['profile_picture'])) ? 'uploads/' . $employee['profile_picture'] : 'uploads/default_profile.png'; ?>" 
                         class="rounded-circle mb-3" width="100" height="100">
                </div>
                
                <div class="mb-3">
                    <label for="profile_picture" class="form-label">Change Profile Picture</label>
                    <input type="file" name="profile_picture" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($employee['name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="Employee" <?php echo ($employee['role'] == 'Employee') ? 'selected' : ''; ?>>Employee</option>
                        <option value="HR" <?php echo ($employee['role'] == 'HR') ? 'selected' : ''; ?>>HR</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100">Update Employee</button>
            </form>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
