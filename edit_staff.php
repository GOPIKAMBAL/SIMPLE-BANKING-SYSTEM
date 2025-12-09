<?php
session_start();
require_once 'config/db_connect.php';

// Check if manager is logged in
if (!isset($_SESSION['manager_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: manager_login.php");
    exit();
}

$staff = null;
if (isset($_GET['id'])) {
    $staff_id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT * FROM staff WHERE id = '$staff_id'";
    $result = mysqli_query($conn, $sql);
    $staff = mysqli_fetch_assoc($result);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Only update password if a new one is provided
    $password_update = "";
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_update = ", password = '$password'";
    }
    
    $sql = "UPDATE staff SET 
            name = '$name',
            email = '$email',
            phone_number = '$phone',
            role = '$role'
            $password_update
            WHERE id = '$staff_id'";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: manager_home.php");
        exit();
    } else {
        $error = "Error updating staff: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELITE BANK - Edit Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bank-title {
            color: #0d6efd;
            font-size: 2rem;
            font-weight: bold;
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
        <h2 class="mb-4">Edit Staff Account</h2>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($staff): ?>
        <form method="POST" action="">
            <input type="hidden" name="staff_id" value="<?php echo $staff['id']; ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo $staff['name']; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo $staff['email']; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone_number" class="form-control" value="<?php echo $staff['phone_number']; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="cashier" <?php echo $staff['role'] == 'cashier' ? 'selected' : ''; ?>>Cashier</option>
                        <option value="manager" <?php echo $staff['role'] == 'manager' ? 'selected' : ''; ?>>Manager</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control">
                </div>
            </div>
            
            <div class="text-end">
                <a href="manager_home.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Staff</button>
            </div>
        </form>
        <?php else: ?>
            <div class="alert alert-danger">Staff member not found</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 