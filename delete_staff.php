<?php
session_start();
require_once 'config/db_connect.php';

// Check if manager is logged in
if (!isset($_SESSION['manager_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: manager_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $staff_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Don't allow deleting managers
    $sql = "SELECT role FROM staff WHERE id = '$staff_id'";
    $result = mysqli_query($conn, $sql);
    $staff = mysqli_fetch_assoc($result);
    
    if ($staff && $staff['role'] !== 'manager') {
        mysqli_query($conn, "DELETE FROM staff WHERE id = '$staff_id'");
    }
}

header("Location: manager_home.php");
exit();
?> 