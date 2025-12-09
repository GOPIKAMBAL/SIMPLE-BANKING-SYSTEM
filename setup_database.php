<?php
$host = "localhost";
$username = "root";
$password = "";

// Create connection without database
$conn = mysqli_connect($host, $username, $password);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS elite_bank";
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully or already exists<br>";
} else {
    die("Error creating database: " . mysqli_error($conn));
}

// Select the database
if (!mysqli_select_db($conn, "elite_bank")) {
    die("Error selecting database: " . mysqli_error($conn));
}

// Create staff table
$sql = "CREATE TABLE IF NOT EXISTS staff (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('manager', 'cashier') NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!mysqli_query($conn, $sql)) {
    die("Error creating staff table: " . mysqli_error($conn));
}

// Verify staff table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'staff'");
if (mysqli_num_rows($result) == 0) {
    die("Staff table was not created successfully");
}

echo "Staff table created successfully or already exists<br>";

// Insert default manager account if it doesn't exist
$check_sql = "SELECT * FROM staff WHERE username = 'manager'";
$result = mysqli_query($conn, $check_sql);

if (!$result) {
    die("Error checking for existing manager: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) == 0) {
    $sql = "INSERT INTO staff (username, password, name, email, role, phone_number)
            VALUES ('manager', 'manager123', 'Default Manager', 'manager@elitebank.com', 'manager', '1234567890')";
    
    if (!mysqli_query($conn, $sql)) {
        die("Error creating default manager account: " . mysqli_error($conn));
    }
    echo "Default manager account created successfully<br>";
} else {
    echo "Default manager account already exists<br>";
}

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    aadhar_number VARCHAR(12) UNIQUE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    profile_picture VARCHAR(255),
    account_number VARCHAR(20) UNIQUE NOT NULL,
    date_of_birth DATE NOT NULL,
    account_type ENUM('Savings', 'Current') NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    occupation VARCHAR(100) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!mysqli_query($conn, $sql)) {
    die("Error creating users table: " . mysqli_error($conn));
}
echo "Users table created successfully or already exists<br>";

// Create transactions table
$sql = "CREATE TABLE IF NOT EXISTS transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_number VARCHAR(20) NOT NULL,
    transaction_type ENUM('Deposit', 'Withdrawal', 'Transfer') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    recipient_account VARCHAR(20),
    description TEXT,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_number) REFERENCES users(account_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!mysqli_query($conn, $sql)) {
    die("Error creating transactions table: " . mysqli_error($conn));
}
echo "Transactions table created successfully or already exists<br>";

// Create feedback table
$sql = "CREATE TABLE IF NOT EXISTS feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!mysqli_query($conn, $sql)) {
    die("Error creating feedback table: " . mysqli_error($conn));
}
echo "Feedback table created successfully or already exists<br>";

// Create notices table
$sql = "CREATE TABLE IF NOT EXISTS notices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!mysqli_query($conn, $sql)) {
    die("Error creating notices table: " . mysqli_error($conn));
}
echo "Notices table created successfully or already exists<br>";

mysqli_close($conn);
echo "<br>Database setup completed successfully! <a href='index.php'>Go to login page</a>";
?> 