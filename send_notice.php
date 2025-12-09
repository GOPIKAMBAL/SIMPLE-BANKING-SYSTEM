<?php
session_start();
require_once 'config/db_connect.php';

// Check if manager is logged in
if (!isset($_SESSION['manager_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: manager_login.php");
    exit();
}

$user = null;
if (isset($_GET['id'])) {
    $user_id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT * FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $user = mysqli_fetch_assoc($result);
    } else {
        $error = "Error fetching user: " . mysqli_error($conn);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // First verify that the user exists
    $check_sql = "SELECT id FROM users WHERE id = '$user_id'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Get the actual structure of the notices table
        $table_info_sql = "DESCRIBE notices";
        $table_info_result = mysqli_query($conn, $table_info_sql);
        
        if ($table_info_result) {
            $columns = array();
            while ($column = mysqli_fetch_assoc($table_info_result)) {
                $columns[] = $column['Field'];
            }
            
            // Prepare the SQL based on the actual columns
            $sql_columns = array();
            $sql_values = array();
            
            // Always include these fields
            if (in_array('title', $columns)) {
                $sql_columns[] = 'title';
                $sql_values[] = "'$title'";
            }
            
            if (in_array('message', $columns)) {
                $sql_columns[] = 'message';
                $sql_values[] = "'$message'";
            }
            
            // Check for user reference column options
            if (in_array('user_id', $columns)) {
                $sql_columns[] = 'user_id';
                $sql_values[] = "'$user_id'";
            } elseif (in_array('userid', $columns)) {
                $sql_columns[] = 'userid';
                $sql_values[] = "'$user_id'";
            }
            // Removed the problematic code that was using 'id' column as primary key
            
            // Add created_at timestamp if the column exists
            if (in_array('created_at', $columns)) {
                $sql_columns[] = 'created_at';
                $sql_values[] = "NOW()";
            }
            
            // Construct the SQL query
            $sql = "INSERT INTO notices (" . implode(", ", $sql_columns) . ") VALUES (" . implode(", ", $sql_values) . ")";
            
            if (mysqli_query($conn, $sql)) {
                $success = "Notice sent successfully!";
            } else {
                $error = "Error sending notice: " . mysqli_error($conn);
                // For debugging - show the SQL that failed
                $error .= " (SQL: $sql)";
            }
        } else {
            $error = "Error retrieving table structure: " . mysqli_error($conn);
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELITE BANK - Send Notice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bank-title {
            color: #0d6efd;
            font-size: 2rem;
            font-weight: bold;
        }
        .notice-form {
            max-width: 800px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <i class="fas fa-university"></i>
                <span>ELITE BANK</span>
            </a>
            <div class="d-flex">
                <a href="manager_home.php" class="btn btn-light me-2">Back to Dashboard</a>
                <a href="logout.php" class="btn btn-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Send Notice to User</h2>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if($user): ?>
        <div class="notice-form">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-user"></i> User Information</h5>
                    <p class="card-text">
                        <strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?><br>
                        <strong>Account Number:</strong> <?php echo htmlspecialchars($user['account_number']); ?><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                </div>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                
                <div class="mb-3">
                    <label class="form-label">Notice Title</label>
                    <input type="text" name="title" class="form-control" required 
                           placeholder="Enter a title for the notice"
                           maxlength="255">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="5" required 
                              placeholder="Enter your message here"></textarea>
                </div>
                
                <div class="text-end">
                    <a href="manager_home.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Notice
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> User not found
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>