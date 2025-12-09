<?php
session_start();
require_once 'config/db_connect.php';

// Check if manager is logged in
if (!isset($_SESSION['manager_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: manager_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Get account number before deleting
    $sql = "SELECT account_number FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);
    
    if ($user) {
        // Delete related transactions first
        mysqli_query($conn, "DELETE FROM transactions WHERE account_number = '{$user['account_number']}'");
        
        // Delete related feedback
        mysqli_query($conn, "DELETE FROM feedback WHERE user_id = '$user_id'");
        
        // Delete user
        mysqli_query($conn, "DELETE FROM users WHERE id = '$user_id'");
    }
}

header("Location: manager_home.php");
exit();
?> 