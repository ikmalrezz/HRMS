<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Fetch all employees except admins
$result = $conn->query("SELECT id, name, email, role, profile_picture FROM users WHERE role != 'Admin'");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees | Workfusion</title>
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
            <li class="active"><a href="manage_users.php">Manage Employee</a></li>
            <li><a href="admin_leave_requests.php">Leave Requests</a></li>
            <li><a href="admin_kpi.php">KPI Reports</a></li>
            <li><a href="admin_payroll.php">Payroll</a></li>
            <li><a href="logout.php" class="text-danger">Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div id="content">
        <!-- Navbar Header -->
        <nav class="navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand" href="manage_users.php">Manage Users</a>
            <div class="dropdown ms-auto">
                <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <img src="uploads/default_profile.png" class="rounded-circle" width="40" height="40">
                    Admin
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                    <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>

        <h2 class="text-center mb-4 mt-3">Employee Management</h2>

        <!-- Employee Table -->
        <div class="card p-4 bg-dark text-white">
            <table class="table table-dark table-striped">
                <thead>
                    <tr>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <img src="<?php echo (!empty($row['profile_picture'])) ? 'uploads/' . $row['profile_picture'] : 'uploads/default_profile.png'; ?>" 
                                     class="rounded-circle" width="40" height="40">
                            </td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['role']); ?></td>
                            <td>
                                <a href="edit_employee.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $row['id']; ?>">Delete</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const deleteButtons = document.querySelectorAll(".delete-btn");
        
        deleteButtons.forEach(button => {
            button.addEventListener("click", function() {
                const userId = this.getAttribute("data-id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "This employee will be permanently deleted!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "delete_employee.php?id=" + userId;
                    }
                });
            });
        });
    });
</script>

</body>
</html>

