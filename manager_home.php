<?php
session_start();
require_once 'config/db_connect.php';

// Check if manager is logged in
if (!isset($_SESSION['manager_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: manager_login.php");
    exit();
}

// Handle user account creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_user'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $aadhar = mysqli_real_escape_string($conn, $_POST['aadhar_number']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $dob = mysqli_real_escape_string($conn, $_POST['date_of_birth']);
    $account_type = mysqli_real_escape_string($conn, $_POST['account_type']);
    $deposit = mysqli_real_escape_string($conn, $_POST['deposit_amount']);
    $occupation = mysqli_real_escape_string($conn, $_POST['occupation']);
    $profile_picture = '';
    // Handle profile picture upload if provided
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);
        $target_file = $target_dir . basename($_FILES['profile_picture']['name']);
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture = $target_file;
        }
    }
    $account_number = 'ACC' . time(); // Generate unique account number
    $sql = "INSERT INTO users (name, aadhar_number, gender, email, password, phone_number, address, city, date_of_birth, account_type, balance, occupation, profile_picture, account_number) 
            VALUES ('$name', '$aadhar', '$gender', '$email', '$password', '$phone', '$address', '$city', '$dob', '$account_type', '$deposit', '$occupation', '$profile_picture', '$account_number')";
    if (mysqli_query($conn, $sql)) {
        $success = "User account created successfully!";
    } else {
        $error = "Error creating user account: " . mysqli_error($conn);
    }
}

// Handle staff account creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_staff'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO staff (username, name, email, role, phone_number, password) 
            VALUES ('$username', '$name', '$email', '$role', '$phone', '$password')";
    if (mysqli_query($conn, $sql)) {
        $success = "Staff account created successfully!";
    } else {
        $error = "Error creating staff account: " . mysqli_error($conn);
    }
}

// Get all users
$users_sql = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = mysqli_query($conn, $users_sql);

// Get all staff (excluding managers)
$staff_sql = "SELECT * FROM staff WHERE role != 'manager' ORDER BY created_at DESC";
$staff_result = mysqli_query($conn, $staff_sql);

// Get all feedback
$feedback_sql = "SELECT f.*, u.name as user_name FROM feedback f 
                 JOIN users u ON f.user_id = u.id 
                 ORDER BY f.created_at DESC";
$feedback_result = mysqli_query($conn, $feedback_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELITE BANK - Manager Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bank-title {
            color: #0d6efd;
            font-size: 2rem;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .action-buttons .btn {
            margin: 0 2px;
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 8px 12px;
        }
        .action-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .dashboard-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: none;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .dashboard-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(45deg, transparent, rgba(13,110,253,0.05));
            border-radius: 50%;
            transform: translate(50%, -50%);
        }
        .stat-card {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1));
            z-index: 1;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(13,110,253,0.2);
        }
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 5px;
            position: relative;
            z-index: 2;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            padding: 12px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .nav-tabs .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 3px;
            background: #0d6efd;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        .nav-tabs .nav-link:hover {
            color: #0d6efd;
            background: rgba(13,110,253,0.05);
        }
        .nav-tabs .nav-link:hover::after {
            width: 100%;
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
            background: none;
        }
        .nav-tabs .nav-link.active::after {
            width: 100%;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .table thead th {
            background: #f8f9fa;
            border-bottom: none;
            padding: 15px;
            font-weight: 600;
            color: #2c3e50;
        }
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            transition: all 0.2s ease;
        }
        .table tbody tr {
            transition: all 0.2s ease;
        }
        .table tbody tr:hover {
            background-color: rgba(13,110,253,0.05);
            transform: scale(1.01);
        }
        .btn-primary {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(13,110,253,0.2);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13,110,253,0.3);
        }
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .modal-header {
            background: #f8f9fa;
            border-radius: 15px 15px 0 0;
            border-bottom: none;
            padding: 20px;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
            border-color: #0d6efd;
        }
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .profile-pic:hover {
            transform: scale(1.1);
        }
        .user-name {
            display: flex;
            align-items: center;
            font-weight: 500;
            color: #2c3e50;
        }
        .badge {
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .badge:hover {
            transform: translateY(-2px);
        }
        .search-box {
            position: relative;
            margin-bottom: 20px;
        }
        .search-box input {
            padding-left: 40px;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .search-box input:focus {
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
        }
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            transition: all 0.3s ease;
        }
        .search-box input:focus + i {
            color: #0d6efd;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .tab-pane {
            animation: fadeIn 0.3s ease;
        }
        .modal-content {
            animation: fadeIn 0.3s ease;
        }
        .alert {
            animation: fadeIn 0.3s ease;
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
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="fas fa-user-circle me-2"></i>
                    Manager Dashboard
                </span>
                <a href="logout.php" class="btn btn-light">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if(isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo mysqli_num_rows($users_result); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-value"><?php echo mysqli_num_rows($staff_result); ?></div>
                    <div class="stat-label">Total Staff</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-value"><?php echo mysqli_num_rows($feedback_result); ?></div>
                    <div class="stat-label">Total Feedback</div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                    <i class="fas fa-users me-2"></i>User Accounts
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff" type="button" role="tab">
                    <i class="fas fa-user-tie me-2"></i>Staff Accounts
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="feedback-tab" data-bs-toggle="tab" data-bs-target="#feedback" type="button" role="tab">
                    <i class="fas fa-comments me-2"></i>User Feedback
                </button>
            </li>
        </ul>

        <div class="tab-content mt-3" id="myTabContent">
            <!-- User Accounts Tab -->
            <div class="tab-pane fade show active" id="users" role="tabpanel">
                <div class="dashboard-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="mb-0">User Accounts</h3>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-plus me-2"></i>Add New User
                        </button>
                    </div>
                    
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" id="userSearch" placeholder="Search users...">
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Account Number</th>
                                    <th>Balance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php mysqli_data_seek($users_result, 0); while($user = mysqli_fetch_assoc($users_result)): ?>
                                <tr>
                                    <td>
                                        <div class="user-name">
                                            <?php if (!empty($user['profile_picture'])): ?>
                                                <img src="<?php echo $user['profile_picture']; ?>" alt="Profile" class="profile-pic">
                                            <?php else: ?>
                                                <div class="profile-pic bg-light d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <?php echo $user['name']; ?>
                                        </div>
                                    </td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo $user['phone_number']; ?></td>
                                    <td><span class="badge bg-light text-dark"><?php echo $user['account_number']; ?></span></td>
                                    <td>
                                        <span class="badge bg-success">
                                            ₹<?php echo number_format($user['balance'], 2); ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="send_notice.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info" title="Send Notice">
                                            <i class="fas fa-bell"></i>
                                        </a>
                                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this user?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Staff Accounts Tab -->
            <div class="tab-pane fade" id="staff" role="tabpanel">
                <div class="dashboard-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="mb-0">Staff Accounts</h3>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                            <i class="fas fa-plus me-2"></i>Add New Staff
                        </button>
                    </div>
                    
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" id="staffSearch" placeholder="Search staff...">
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php mysqli_data_seek($staff_result, 0); while($staff = mysqli_fetch_assoc($staff_result)): ?>
                                <tr>
                                    <td>
                                        <div class="user-name">
                                            <div class="profile-pic bg-light d-flex align-items-center justify-content-center">
                                                <i class="fas fa-user-tie text-muted"></i>
                                            </div>
                                            <?php echo $staff['name']; ?>
                                        </div>
                                    </td>
                                    <td><?php echo $staff['email']; ?></td>
                                    <td><?php echo $staff['phone_number']; ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo ucfirst($staff['role']); ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="edit_staff.php?id=<?php echo $staff['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_staff.php?id=<?php echo $staff['id']; ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this staff member?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Feedback Tab -->
            <div class="tab-pane fade" id="feedback" role="tabpanel">
                <div class="dashboard-card p-4">
                    <h3 class="mb-4">User Feedback</h3>
                    
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" id="feedbackSearch" placeholder="Search feedback...">
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php mysqli_data_seek($feedback_result, 0); while($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                                <tr>
                                    <td>
                                        <div class="user-name">
                                            <div class="profile-pic bg-light d-flex align-items-center justify-content-center">
                                                <i class="fas fa-user text-muted"></i>
                                            </div>
                                            <?php echo $feedback['user_name']; ?>
                                        </div>
                                    </td>
                                    <td><?php echo $feedback['message']; ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($feedback['created_at'])); ?></td>
                                    <td class="action-buttons">
                                        <a href="delete_feedback.php?id=<?php echo $feedback['id']; ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this feedback?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Aadhar Number</label>
                                <input type="text" name="aadhar_number" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone_number" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Account Type</label>
                                <select name="account_type" class="form-select" required>
                                    <option value="Savings">Savings</option>
                                    <option value="Current">Current</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Initial Deposit</label>
                                <input type="number" name="deposit_amount" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Occupation</label>
                                <input type="text" name="occupation" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Profile Picture</label>
                                <input type="file" name="profile_picture" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="create_user" class="btn btn-primary">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Staff Modal -->
    <div class="modal fade" id="addStaffModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-control" required>
                                <option value="cashier">Cashier</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone_number" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="create_staff" class="btn btn-primary">Create Staff</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('userSearch').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const table = document.querySelector('#users table tbody');
            const rows = table.getElementsByTagName('tr');
            
            for (let row of rows) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            }
        });

        document.getElementById('staffSearch').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const table = document.querySelector('#staff table tbody');
            const rows = table.getElementsByTagName('tr');
            
            for (let row of rows) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            }
        });

        document.getElementById('feedbackSearch').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const table = document.querySelector('#feedback table tbody');
            const rows = table.getElementsByTagName('tr');
            
            for (let row of rows) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            }
        });
    </script>
</body>
</html> 