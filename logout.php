<?php
session_start();

// Destroy all session data
$_SESSION = array();

// Destroy the session completely
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>
