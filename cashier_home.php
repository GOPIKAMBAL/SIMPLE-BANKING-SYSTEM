<?php
session_start();
require_once 'config/db_connect.php';

// Check if cashier is logged in
if (!isset($_SESSION['cashier_id'])) {
    header("Location: cashier_login.php");
    exit();
}

$account_details = null;
$error = null;
$success = null;

// Handle account lookup
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['lookup'])) {
    $account_number = mysqli_real_escape_string($conn, $_POST['account_number']);
    
    $sql = "SELECT * FROM users WHERE account_number = '$account_number'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $account_details = mysqli_fetch_assoc($result);
    } else {
        $error = "Account not found!";
    }
}

// Handle deposit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deposit'])) {
    $account_number = mysqli_real_escape_string($conn, $_POST['account_number']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Update balance
        $update_sql = "UPDATE users SET balance = balance + $amount WHERE account_number = '$account_number'";
        mysqli_query($conn, $update_sql);
        
        // Record transaction
        $transaction_sql = "INSERT INTO transactions (account_number, transaction_type, amount) 
                          VALUES ('$account_number', 'Deposit', $amount)";
        mysqli_query($conn, $transaction_sql);
        
        mysqli_commit($conn);
        $success = "Deposit successful!";
        
        // Refresh account details
        $sql = "SELECT * FROM users WHERE account_number = '$account_number'";
        $result = mysqli_query($conn, $sql);
        $account_details = mysqli_fetch_assoc($result);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Deposit failed: " . $e->getMessage();
    }
}

// Handle withdrawal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['withdraw'])) {
    $account_number = mysqli_real_escape_string($conn, $_POST['account_number']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    
    // Check if sufficient balance
    $check_sql = "SELECT balance FROM users WHERE account_number = '$account_number'";
    $check_result = mysqli_query($conn, $check_sql);
    $current_balance = mysqli_fetch_assoc($check_result)['balance'];
    
    if ($current_balance >= $amount) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Update balance
            $update_sql = "UPDATE users SET balance = balance - $amount WHERE account_number = '$account_number'";
            mysqli_query($conn, $update_sql);
            
            // Record transaction
            $transaction_sql = "INSERT INTO transactions (account_number, transaction_type, amount) 
                              VALUES ('$account_number', 'Withdrawal', $amount)";
            mysqli_query($conn, $transaction_sql);
            
            mysqli_commit($conn);
            $success = "Withdrawal successful!";
            
            // Refresh account details
            $sql = "SELECT * FROM users WHERE account_number = '$account_number'";
            $result = mysqli_query($conn, $sql);
            $account_details = mysqli_fetch_assoc($result);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Withdrawal failed: " . $e->getMessage();
        }
    } else {
        $error = "Insufficient balance!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELITE BANK - Cashier Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #0dcaf0;
            --success-color: #198754;
            --danger-color: #dc3545;
        }

        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .bank-title {
            color: var(--primary-color);
            font-size: 2rem;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .navbar {
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .navbar-brand i {
            font-size: 1.8rem;
            margin-right: 8px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            background: white;
            position: relative;
            z-index: 1;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .card::after {
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

        .card-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .card-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 3px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15);
        }

        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::after {
            width: 300px;
            height: 300px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #2ecc71);
            border: none;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color), #e74c3c);
            border: none;
        }

        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .account-details {
            background: linear-gradient(135deg, #fff, #f8f9fa);
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1rem;
        }

        .account-details p {
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .account-details strong {
            color: var(--primary-color);
            min-width: 150px;
        }

        .transaction-form {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1.5rem;
        }

        .transaction-form h6 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .balance-amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--success-color);
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .logout-btn {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
            color: white;
            transform: translateY(-2px);
        }

        /* New Enhanced Styles */
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1));
            z-index: -1;
            transition: all 0.3s ease;
        }

        .card:hover::before {
            transform: scale(1.1);
        }

        .input-group {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .input-group-text {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 12px 15px;
        }

        .form-control {
            border-left: none;
            padding-left: 0;
        }

        .form-control:focus {
            box-shadow: none;
        }

        .balance-amount {
            position: relative;
            display: inline-block;
            padding: 5px 15px;
            background: linear-gradient(135deg, rgba(25,135,84,0.1), rgba(46,204,113,0.1));
            border-radius: 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .transaction-form {
            position: relative;
            overflow: hidden;
        }

        .transaction-form::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255,255,255,0.1),
                transparent
            );
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        .btn {
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s;
        }

        .btn:hover::before {
            transform: translateX(100%);
        }

        .account-details p {
            position: relative;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .account-details p:hover {
            background: rgba(13,110,253,0.05);
            transform: translateX(5px);
        }

        .account-details i {
            transition: all 0.3s ease;
        }

        .account-details p:hover i {
            transform: scale(1.2) rotate(10deg);
        }

        /* Loading Animation */
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .loading.active {
            opacity: 1;
            visibility: visible;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .toast {
            background: white;
            border-radius: 10px;
            padding: 15px 25px;
            margin-bottom: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transform: translateX(120%);
            transition: transform 0.3s ease;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.success {
            border-left: 4px solid var(--success-color);
        }

        .toast.error {
            border-left: 4px solid var(--danger-color);
        }

        /* Enhanced Transaction Buttons */
        .transaction-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .transaction-btn {
            flex: 1;
            padding: 1rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: 60px;
        }

        .transaction-btn i {
            font-size: 1.3rem;
            transition: all 0.3s ease;
        }

        .transaction-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .transaction-btn:hover i {
            transform: scale(1.2);
        }

        .transaction-btn.deposit {
            background: linear-gradient(135deg, var(--success-color), #2ecc71);
            border: none;
            color: white;
        }

        .transaction-btn.withdraw {
            background: linear-gradient(135deg, var(--danger-color), #e74c3c);
            border: none;
            color: white;
        }

        .transaction-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s;
        }

        .transaction-btn:hover::before {
            transform: translateX(100%);
        }

        /* Adjust input group for better alignment */
        .transaction-form .input-group {
            margin-bottom: 1rem;
        }

        .transaction-form .input-group .form-control {
            height: 60px;
            font-size: 1.1rem;
        }

        .transaction-form .input-group-text {
            height: 60px;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading">
        <div class="loading-spinner"></div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container"></div>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-university"></i>
                <span>ELITE BANK</span>
            </a>
            <div class="d-flex">
                <a href="logout.php" class="btn logout-btn">
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

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-search me-2"></i>Account Lookup
                        </h5>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Account Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-hashtag"></i>
                                    </span>
                                    <input type="text" name="account_number" class="form-control" required 
                                           placeholder="Enter account number">
                                </div>
                            </div>
                            <button type="submit" name="lookup" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Lookup
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <?php if($account_details): ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-user-circle me-2"></i>Account Details
                        </h5>
                        <div class="account-details">
                            <p>
                                <i class="fas fa-user text-primary"></i>
                                <strong>Name:</strong> 
                                <?php echo $account_details['name']; ?>
                            </p>
                            <p>
                                <i class="fas fa-hashtag text-primary"></i>
                                <strong>Account Number:</strong> 
                                <?php echo $account_details['account_number']; ?>
                            </p>
                            <p>
                                <i class="fas fa-credit-card text-primary"></i>
                                <strong>Account Type:</strong> 
                                <?php echo $account_details['account_type']; ?>
                            </p>
                            <p>
                                <i class="fas fa-wallet text-primary"></i>
                                <strong>Current Balance:</strong> 
                                <span class="balance-amount">
                                    ₹<?php echo number_format($account_details['balance'], 2); ?>
                                </span>
                            </p>
                        </div>

                        <div class="transaction-form">
                            <h6>
                                <i class="fas fa-exchange-alt me-2"></i>Perform Transaction
                            </h6>
                            <form method="POST" action="">
                                <input type="hidden" name="account_number" value="<?php echo $account_details['account_number']; ?>">
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" name="amount" class="form-control" 
                                           placeholder="Enter amount" required min="1" step="0.01">
                                </div>
                                <div class="transaction-buttons">
                                    <button type="submit" name="deposit" class="transaction-btn deposit">
                                        <i class="fas fa-plus-circle"></i>
                                        Deposit
                                    </button>
                                    <button type="submit" name="withdraw" class="transaction-btn withdraw">
                                        <i class="fas fa-minus-circle"></i>
                                        Withdraw
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show loading overlay
        function showLoading() {
            document.querySelector('.loading').classList.add('active');
        }

        // Hide loading overlay
        function hideLoading() {
            document.querySelector('.loading').classList.remove('active');
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
            `;
            document.querySelector('.toast-container').appendChild(toast);
            
            // Show toast
            setTimeout(() => toast.classList.add('show'), 100);
            
            // Remove toast after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Add loading and toast to forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', () => {
                showLoading();
            });
        });

        // Show success/error toasts
        <?php if(isset($success)): ?>
            showToast('<?php echo $success; ?>', 'success');
        <?php endif; ?>
        <?php if(isset($error)): ?>
            showToast('<?php echo $error; ?>', 'error');
        <?php endif; ?>

        // Hide loading when page is fully loaded
        window.addEventListener('load', hideLoading);

        // Add hover effects to account details
        document.querySelectorAll('.account-details p').forEach(p => {
            p.addEventListener('mouseenter', () => {
                p.style.transform = 'translateX(5px)';
            });
            p.addEventListener('mouseleave', () => {
                p.style.transform = 'translateX(0)';
            });
        });

        // Add ripple effect to buttons
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function(e) {
                const x = e.clientX - e.target.getBoundingClientRect().left;
                const y = e.clientY - e.target.getBoundingClientRect().top;
                
                const ripple = document.createElement('span');
                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;
                
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });
    </script>
</body>
</html> 