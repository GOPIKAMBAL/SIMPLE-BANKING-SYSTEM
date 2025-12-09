<?php
session_start();
require_once 'config/db_connect.php';

// Check if manager is logged in
if (!isset($_SESSION['manager_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: manager_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $feedback_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Delete the feedback
    $sql = "DELETE FROM feedback WHERE id = '$feedback_id'";
    if (mysqli_query($conn, $sql)) {
        $success = "Feedback deleted successfully!";
    } else {
        $error = "Error deleting feedback: " . mysqli_error($conn);
    }
}

header("Location: manager_home.php");
exit();
?> 