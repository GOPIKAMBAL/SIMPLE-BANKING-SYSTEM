<?php
session_start();
require_once 'config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

// Get user details
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);

// Get transaction history
$account_number = $user['account_number'];
$transactions_sql = "SELECT *,
    CASE 
        WHEN transaction_type = 'Transfer' AND account_number = '$account_number' THEN 'Transfer Sent'
        WHEN transaction_type = 'Transfer' AND recipient_account = '$account_number' THEN 'Transfer Received'
        ELSE transaction_type
    END AS display_type
FROM transactions
WHERE account_number = '$account_number' OR recipient_account = '$account_number'
ORDER BY transaction_date DESC LIMIT 20";
$transactions_result = mysqli_query($conn, $transactions_sql);

// Get notices
$notices_sql = "SELECT * FROM notices ORDER BY created_at DESC LIMIT 5";
$notices_result = mysqli_query($conn, $notices_sql);

// Handle feedback submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_feedback'])) {
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    $feedback_sql = "INSERT INTO feedback (user_id, message) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $feedback_sql);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $message);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Feedback sent successfully!";
    } else {
        $error = "Error sending feedback: " . mysqli_error($conn);
    }
}

// Handle money transfer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['transfer'])) {
    $recipient_account = mysqli_real_escape_string($conn, $_POST['recipient_account']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // Prevent transfer to self
    if ($recipient_account == $user['account_number']) {
        $error = "You cannot transfer money to your own account!";
    } else {
        // Check if sufficient balance
        if ($user['balance'] >= $amount) {
            // Check if recipient account exists
            $check_sql = "SELECT * FROM users WHERE account_number = '$recipient_account'";
            $check_result = mysqli_query($conn, $check_sql);
            
            if (mysqli_num_rows($check_result) == 1) {
                // Start transaction
                mysqli_begin_transaction($conn);
                
                try {
                    // Deduct from sender
                    $update_sender = "UPDATE users SET balance = balance - $amount WHERE account_number = '{$user['account_number']}'";
                    mysqli_query($conn, $update_sender);
                    
                    // Add to recipient
                    $update_recipient = "UPDATE users SET balance = balance + $amount WHERE account_number = '$recipient_account'";
                    mysqli_query($conn, $update_recipient);
                    
                    // Record transaction
                    $transaction_sql = "INSERT INTO transactions (account_number, transaction_type, amount, recipient_account, description) 
                                      VALUES ('{$user['account_number']}', 'Transfer', $amount, '$recipient_account', '$description')";
                    mysqli_query($conn, $transaction_sql);
                    
                    mysqli_commit($conn);
                    $success = "Transfer successful!";
                    
                    // Refresh user data
                    $user_result = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
                    $user = mysqli_fetch_assoc($user_result);
                    
                    // Refresh transactions - FIXED QUERY
                    $transactions_result = mysqli_query($conn, "SELECT *,
                        CASE 
                            WHEN transaction_type = 'Transfer' AND account_number = '{$user['account_number']}' THEN 'Transfer Sent'
                            WHEN transaction_type = 'Transfer' AND recipient_account = '{$user['account_number']}' THEN 'Transfer Received'
                            ELSE transaction_type
                        END AS display_type
                    FROM transactions
                    WHERE account_number = '{$user['account_number']}' OR recipient_account = '{$user['account_number']}'
                    ORDER BY transaction_date DESC LIMIT 20");
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $error = "Transfer failed: " . $e->getMessage();
                }
            } else {
                $error = "Recipient account not found!";
            }
        } else {
            $error = "Insufficient balance!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELITE BANK - User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bank-title {
            color: #0d6efd;
            font-size: 2rem;
            font-weight: bold;
        }
        .account-summary {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .transaction-row {
            transition: background-color 0.2s;
        }
        .transaction-row:hover {
            background-color: #f8f9fa;
        }
        .notice-badge {
            position: relative;
            top: -8px;
            right: -8px;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            border-radius: 50%;
            background-color: #dc3545;
            color: white;
        }
        /* Card Action Styles */
        .action-card {
            background: #fff;
            border-radius: 24px;
            transition: box-shadow 0.2s, transform 0.2s;
            cursor: pointer;
            box-shadow: 0 4px 24px 0 rgba(0,0,0,0.04);
            min-width: 400px;
            min-height: 400px;
            font-size: 1.7rem;
            padding: 4rem 2rem !important;
        }
        .action-card:hover, .action-card.active {
            box-shadow: 0 8px 32px 0 rgba(80, 80, 200, 0.10);
            transform: translateY(-4px) scale(1.05);
        }
        .icon-circle {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            background: #f8f9fa;
            box-shadow: 0 2px 8px 0 rgba(0,0,0,0.04);
        }
        .icon-circle i {
            font-size: 6rem;
        }
        .bg-warning {
            background: #ffe082 !important;
        }
        .text-orange {
            color: #d17b0f !important;
        }
        .bg-success.bg-opacity-25 {
            background: #b9f6ca !important;
        }
        .bg-purple {
            background: #d1b3ff !important;
        }
        .text-purple {
            color: #7c4dff !important;
        }
        .action-card.active, .action-card.card-transfer:hover {
            background: #e6d6fa !important;
        }
        @media (max-width: 991px) {
            .action-card {
                min-width: 250px !important;
                min-height: 180px !important;
                font-size: 1.1rem !important;
                padding: 1.5rem 0.5rem !important;
            }
            .icon-circle {
                width: 90px;
                height: 90px;
                margin-bottom: 1rem;
            }
            .icon-circle i {
                font-size: 2.5rem;
            }
        }
        .tab-content-section { display: block; }
        .account-summary-enhanced {
            background: linear-gradient(135deg, #e3f0ff 0%, #f8f9fa 100%);
            border-radius: 2rem;
            box-shadow: 0 8px 32px 0 rgba(80, 80, 200, 0.10);
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .account-summary-enhanced:hover {
            box-shadow: 0 12px 48px 0 rgba(80, 80, 200, 0.18);
            transform: translateY(-2px) scale(1.01);
        }
        /* Enhanced Profile Picture Styling */
        .profile-pic-frame {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d6efd 60%, #0dcaf0 100%);
            box-shadow: 0 4px 20px 0 rgba(13,110,253,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 5px solid #fff;
            overflow: hidden;
            margin: 0 auto 1.5rem auto;
        }
        .profile-pic-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        .profile-pic-placeholder {
            width: 100%;
            height: 100%;
            background: #b0c4de;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .summary-field {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            word-break: break-word;
            padding: 0.5rem 0;
        }
        .summary-field i {
            font-size: 1.3rem;
            min-width: 30px;
            text-align: center;
        }
        .summary-field strong {
            min-width: 150px;
            margin-right: 10px;
        }
        .summary-field span {
            flex: 1;
        }
        .profile-section {
            background-color: rgba(255, 255, 255, 0.5);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        .account-info-section {
            background-color: rgba(255, 255, 255, 0.6);
            border-radius: 1.5rem;
            padding: 1.5rem 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        @media (max-width: 767px) {
            .account-summary-enhanced {
                padding: 1.2rem !important;
            }
            .profile-pic-frame {
                width: 100px;
                height: 100px;
                margin-bottom: 1rem;
            }
            .summary-field {
                font-size: 0.98rem;
                margin-bottom: 1rem;
            }
            .summary-field strong {
                min-width: 110px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-university me-2"></i>
                ELITE BANK
            </a>
            <div class="d-flex align-items-center">
                <div class="me-3 d-none d-md-block">
                    <span class="badge bg-light text-primary fs-5 fw-bold" style="background:rgba(255,255,255,0.9);border-radius:1.5rem;padding:0.5rem 1.2rem;">
                        <i class="fas fa-wallet me-2"></i>₹<?php echo number_format($user['balance'], 2); ?>
                    </span>
                </div>
                <a href="generate_statement.php" class="btn btn-light me-2" target="_blank">
                    <i class="fas fa-file-pdf me-2"></i>Generate Report
                </a>
                <button type="button" class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#noticesModal">
                    <i class="fas fa-bell"></i>
                    <?php if(mysqli_num_rows($notices_result) > 0): ?>
                        <span class="notice-badge"><?php echo mysqli_num_rows($notices_result); ?></span>
                    <?php endif; ?>
                </button>
                <button type="button" class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#feedbackModal">
                    <i class="fas fa-envelope"></i>
                </button>
                <a href="logout.php" class="btn btn-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if(isset($error) && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['transfer'])): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if(isset($_GET['error']) && $_GET['error'] == 'pdf_generation_failed'): ?>
            <div class="alert alert-danger">Failed to generate PDF. Please try again later.</div>
        <?php endif; ?>

        <!-- Modern Card Actions as Tabs -->
        <div class="row justify-content-center mb-5 gx-4 gy-4">
            <div class="col-auto">
                <div class="action-card card-summary h-100 text-center shadow-lg active" id="tab-summary" onclick="showTab('summary')">
                    <div class="icon-circle bg-warning mb-3 mx-auto">
                        <i class="fas fa-ruler-combined fa-3x text-orange"></i>
                    </div>
                    <div class="fw-semibold fs-4">Account Summary</div>
                </div>
            </div>
            <div class="col-auto">
                <div class="action-card card-statement h-100 text-center shadow-lg" id="tab-statement" onclick="showTab('statement')">
                    <div class="icon-circle bg-success bg-opacity-25 mb-3 mx-auto">
                        <i class="fas fa-wallet fa-3x text-success"></i>
                    </div>
                    <div class="fw-semibold fs-4">Account Statement</div>
                </div>
            </div>
            <div class="col-auto">
                <div class="action-card card-transfer h-100 text-center shadow-lg" id="tab-transfer" onclick="showTab('transfer')">
                    <div class="icon-circle bg-purple mb-3 mx-auto">
                        <i class="fas fa-paper-plane fa-3x text-purple"></i>
                    </div>
                    <div class="fw-semibold fs-4">Transfer Money</div>
                </div>
            </div>
        </div>

        <!-- Tab Contents -->
        <div id="content-summary" class="tab-content-section">
            <div class="account-summary-enhanced mb-4 p-4 p-md-5 mx-auto" style="max-width: 1000px;">
                <!-- Profile Section -->
                <div class="profile-section text-center">
                    <div class="profile-pic-frame">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-pic-img">
                        <?php else: ?>
                            <div class="profile-pic-placeholder">
                                <i class="fas fa-user fa-4x text-white"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h2 class="fw-bold mb-2 mt-3"><?php echo htmlspecialchars($user['name']); ?></h2>
                    <span class="badge bg-primary bg-gradient fs-6 mb-2 px-3 py-2"><?php echo isset($user['account_type']) ? htmlspecialchars($user['account_type']) : 'Not set'; ?> Account</span>
                    <p class="text-muted fs-5 mt-2">Account Holder</p>
                </div>
                
                <!-- Account Information Section -->
                <div class="account-info-section">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h4 class="mb-3 text-primary"><i class="fas fa-info-circle me-2"></i>Basic Information</h4>
                            <div class="summary-field">
                                <i class="fas fa-id-card text-primary"></i>
                                <strong>Account Number:</strong>
                                <span><?php echo isset($user['account_number']) ? htmlspecialchars($user['account_number']) : 'Not set'; ?></span>
                            </div>
                            <div class="summary-field">
                                <i class="fas fa-calendar-alt text-primary"></i>
                                <strong>Date of Birth:</strong>
                                <span><?php echo isset($user['date_of_birth']) && $user['date_of_birth'] ? htmlspecialchars($user['date_of_birth']) : 'Not set'; ?></span>
                            </div>
                            <div class="summary-field">
                                <i class="fas fa-envelope text-primary"></i>
                                <strong>Email:</strong>
                                <span><?php echo isset($user['email']) ? htmlspecialchars($user['email']) : 'Not set'; ?></span>
                            </div>
                            <div class="summary-field">
                                <i class="fas fa-phone text-primary"></i>
                                <strong>Phone:</strong>
                                <span><?php echo isset($user['phone_number']) ? htmlspecialchars($user['phone_number']) : 'Not set'; ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4 class="mb-3 text-primary"><i class="fas fa-address-card me-2"></i>Additional Details</h4>
                            <div class="summary-field">
                                <i class="fas fa-home text-primary"></i>
                                <strong>Address:</strong>
                                <span><?php echo isset($user['address']) ? htmlspecialchars($user['address']) : 'Not set'; ?></span>
                            </div>
                            <div class="summary-field">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                                <strong>City:</strong>
                                <span><?php echo isset($user['city']) ? htmlspecialchars($user['city']) : 'Not set'; ?></span>
                            </div>
                            <div class="summary-field">
                                <i class="fas fa-briefcase text-primary"></i>
                                <strong>Occupation:</strong>
                                <span><?php echo isset($user['occupation']) ? htmlspecialchars($user['occupation']) : 'Not set'; ?></span>
                            </div>
                            <div class="summary-field">
                                <i class="fas fa-id-badge text-primary"></i>
                                <strong>Aadhar Number:</strong>
                                <span><?php echo isset($user['aadhar_number']) ? htmlspecialchars($user['aadhar_number']) : 'Not set'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="content-statement" class="tab-content-section" style="display:none;">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php mysqli_data_seek($transactions_result, 0); while($transaction = mysqli_fetch_assoc($transactions_result)): ?>
                                <tr class="transaction-row">
                                    <td><?php echo date('M d, Y H:i', strtotime($transaction['transaction_date'])); ?></td>
                                    <td>
                                        <?php if($transaction['display_type'] == 'Deposit'): ?>
                                            <span class="badge bg-success">Deposit</span>
                                        <?php elseif($transaction['display_type'] == 'Withdrawal'): ?>
                                            <span class="badge bg-danger">Withdrawal</span>
                                        <?php elseif($transaction['display_type'] == 'Transfer Sent'): ?>
                                            <span class="badge bg-info">Transfer Sent</span>
                                        <?php elseif($transaction['display_type'] == 'Transfer Received'): ?>
                                            <span class="badge bg-primary">Transfer Received</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($transaction['display_type'] == 'Transfer Received') {
                                            echo '+ ₹' . number_format($transaction['amount'], 2);
                                        } else {
                                            echo '₹' . number_format($transaction['amount'], 2);
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if($transaction['display_type'] == 'Transfer Sent') {
                                            echo "To: " . htmlspecialchars($transaction['recipient_account']);
                                        } elseif($transaction['display_type'] == 'Transfer Received') {
                                            echo "From: " . htmlspecialchars($transaction['account_number']);
                                        }
                                        echo $transaction['description'] ? " - " . htmlspecialchars($transaction['description']) : '';
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div id="content-transfer" class="tab-content-section" style="display:none;">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Transfer Money</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Recipient Account Number</label>
                            <input type="text" name="recipient_account" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" name="amount" class="form-control" required min="1" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control">
                        </div>
                        <button type="submit" name="transfer" class="btn btn-primary w-100">Transfer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Notices Modal -->
    <div class="modal fade" id="noticesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bank Notices</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php 
                    mysqli_data_seek($notices_result, 0);
                    while($notice = mysqli_fetch_assoc($notices_result)): 
                    ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo htmlspecialchars($notice['title']); ?></h6>
                                <p class="card-text"><?php echo htmlspecialchars($notice['message']); ?></p>
                                <small class="text-muted">
                                    <?php echo date('M d, Y H:i', strtotime($notice['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" name="send_feedback" class="btn btn-primary">Send Feedback</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Show default tab on load
    document.addEventListener('DOMContentLoaded', function() {
        showTab('summary');
    });
    
    function showTab(tab) {
        // Hide all tab content
        document.getElementById('content-summary').style.display = 'none';
        document.getElementById('content-statement').style.display = 'none';
        document.getElementById('content-transfer').style.display = 'none';
        
        // Remove active class from all tabs
        document.getElementById('tab-summary').classList.remove('active');
        document.getElementById('tab-statement').classList.remove('active');
        document.getElementById('tab-transfer').classList.remove('active');
        
        // Show selected tab content and add active class
        if(tab === 'summary') {
            document.getElementById('content-summary').style.display = 'block';
            document.getElementById('tab-summary').classList.add('active');
        } else if(tab === 'statement') {
            document.getElementById('content-statement').style.display = 'block';
            document.getElementById('tab-statement').classList.add('active');
        } else if(tab === 'transfer') {
            document.getElementById('content-transfer').style.display = 'block';
            document.getElementById('tab-transfer').classList.add('active');
        }
    }
    </script>
</body>
</html>