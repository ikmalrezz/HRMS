<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Employee') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT name, email, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Handle Profile Picture Upload
    $profile_picture = $user['profile_picture'];
    if (!empty($_FILES['profile_picture']['name'])) {
        $upload_dir = "uploads/";
        $file_name = time() . "_" . basename($_FILES["profile_picture"]["name"]);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $profile_picture = $file_name;
        }
    }

    // Update user data
    if ($password) {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=?, profile_picture=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $email, $password, $profile_picture, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, profile_picture=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $email, $profile_picture, $user_id);
    }

    if ($stmt->execute()) {
        header("Location: employee_profile.php?success=Profile updated successfully");
        exit();
    } else {
        die("Update failed: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | Workfusion</title>
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
        .profile-img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; }
    </style>
</head>
<body>

<div class="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header text-center"><h3>Workfusion</h3></div>
        <ul class="list-unstyled components">
        <li><a href="employee_dashboard.php">Dashboard</a></li>
        <li class="active"><a href="employee_profile.php">My Profile</a></li>
            <li><a href="employee_leave_requests.php">Leave Requests</a></li>
            <li><a href="employee_kpi.php">KPI Report</a></li>
            <li><a href="employee_payroll.php">Salary Details</a></li>
            <li><a href="logout.php" class="text-danger">Logout</a></li>
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand">My Profile</a>
        </nav>

        <h2 class="text-center">Profile Information</h2>

        <!-- Success Message -->
        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
        <?php } ?>

        <form method="post" enctype="multipart/form-data" class="card p-4 text-white">
            <div class="text-center">
                <img src="uploads/<?= $user['profile_picture']; ?>" class="profile-img mb-3">
            </div>
            <div class="mb-3">
                <label class="form-label">Profile Picture</label>
                <input type="file" name="profile_picture" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">New Password (Leave blank to keep current)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary w-100">Update Profile</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
