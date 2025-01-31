<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Delete employee from database
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: manage_users.php?success=Employee deleted");
        exit();
    } else {
        header("Location: manage_users.php?error=Failed to delete employee");
        exit();
    }
} else {
    header("Location: manage_users.php");
    exit();
}
?>
