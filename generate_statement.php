<?php
// Prevent any output
ob_start();

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require_once 'config/db_connect.php';
require_once 'tcpdf/tcpdf.php'; // Make sure to install TCPDF library

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

try {
    // Get user details
    $user_id = $_SESSION['user_id'];
    $user_sql = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $user_sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $user_result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($user_result);

    if (!$user) {
        throw new Exception("User not found");
    }

    // Get transaction history - using prepared statement to prevent SQL injection
    $account_number = $user['account_number'];
    $transactions_sql = "SELECT *,
        CASE 
            WHEN transaction_type = 'Transfer' AND account_number = ? THEN 'Transfer Sent'
            WHEN transaction_type = 'Transfer' AND recipient_account = ? THEN 'Transfer Received'
            ELSE transaction_type
        END AS display_type
    FROM transactions
    WHERE account_number = ? OR recipient_account = ?
    ORDER BY transaction_date DESC";
    
    $stmt = mysqli_prepare($conn, $transactions_sql);
    mysqli_stmt_bind_param($stmt, "ssss", $account_number, $account_number, $account_number, $account_number);
    mysqli_stmt_execute($stmt);
    $transactions_result = mysqli_stmt_get_result($stmt);

    if (!$transactions_result) {
        throw new Exception("Error fetching transactions");
    }

    // Create new PDF document
    class MYPDF extends TCPDF {
        public function Header() {
            $this->SetFont('helvetica', 'B', 20);
            $this->Cell(0, 15, 'ELITE BANK', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        }
        
        public function Footer() {
            $this->SetY(-15);
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
    }

    // Create new PDF instance
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('ELITE BANK');
    $pdf->SetTitle('Account Statement - ' . $user['name']);

    // Set default header data
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // Add content
    $pdf->Ln(20);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Account Statement', 0, 1, 'C');
    $pdf->Ln(10);

    // Account Information
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Account Information', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);

    $pdf->Cell(40, 7, 'Name:', 0, 0);
    $pdf->Cell(0, 7, $user['name'], 0, 1);

    $pdf->Cell(40, 7, 'Account Number:', 0, 0);
    $pdf->Cell(0, 7, $user['account_number'], 0, 1);

    $pdf->Cell(40, 7, 'Account Type:', 0, 0);
    $pdf->Cell(0, 7, $user['account_type'], 0, 1);

    $pdf->Cell(40, 7, 'Current Balance:', 0, 0);
    $pdf->Cell(0, 7, '₹' . number_format($user['balance'], 2), 0, 1);

    $pdf->Ln(10);

    // Transaction History
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Transaction History', 0, 1, 'L');
    $pdf->Ln(5);

    // Table header
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 7, 'Date', 1, 0, 'C');
    $pdf->Cell(30, 7, 'Type', 1, 0, 'C');
    $pdf->Cell(40, 7, 'Amount', 1, 0, 'C');
    $pdf->Cell(70, 7, 'Description', 1, 1, 'C');

    // Table data
    $pdf->SetFont('helvetica', '', 10);
    while($transaction = mysqli_fetch_assoc($transactions_result)) {
        $pdf->Cell(40, 7, date('Y-m-d H:i', strtotime($transaction['transaction_date'])), 1, 0, 'C');
        $pdf->Cell(30, 7, $transaction['display_type'], 1, 0, 'C');
        
        // Ensure amount is properly cast as a float before formatting
        $transactionAmount = (float)$transaction['amount'];
        
        // Format amount based on transaction type
        $amount = '₹' . number_format($transactionAmount, 2);
        if ($transaction['display_type'] == 'Transfer Received') {
            $amount = '+' . $amount;
        }
        $pdf->Cell(40, 7, $amount, 1, 0, 'C');
        
        // Description
        $description = '';
        if($transaction['display_type'] == 'Transfer Sent') {
            $description = "To: " . $transaction['recipient_account'];
        } elseif($transaction['display_type'] == 'Transfer Received') {
            $description = "From: " . $transaction['account_number'];
        }
        if($transaction['description']) {
            $description .= " - " . $transaction['description'];
        }
        $pdf->Cell(70, 7, $description, 1, 1, 'C');
    }

    // Clear any output buffer
    ob_end_clean();

    // Output the PDF
    $pdf->Output('Account_Statement_' . $user['account_number'] . '.pdf', 'D');
    exit();

} catch (Exception $e) {
    // Clear any output buffer
    ob_end_clean();
    
    // Log the error
    error_log("PDF Generation Error: " . $e->getMessage());
    
    // Redirect to error page or show error message
    header("Location: user_home.php?error=pdf_generation_failed");
    exit();
}
?>