<?php
$host = "localhost";
$username = "root";
$password = "";

// Create connection without database
$conn = mysqli_connect($host, $username, $password);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Connected to MySQL successfully<br>";

// Drop database if exists
$sql = "DROP DATABASE IF EXISTS elite_bank";
if (mysqli_query($conn, $sql)) {
    echo "Old database dropped successfully<br>";
} else {
    echo "Error dropping database: " . mysqli_error($conn) . "<br>";
}

// Create database
$sql = "CREATE DATABASE elite_bank";
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . mysqli_error($conn) . "<br>";
}

// Select the database
if (mysqli_select_db($conn, "elite_bank")) {
    echo "Database selected successfully<br>";
} else {
    echo "Error selecting database: " . mysqli_error($conn) . "<br>";
}

// Create staff table
$sql = "CREATE TABLE staff (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('manager', 'cashier') NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Staff table created successfully<br>";
} else {
    echo "Error creating staff table: " . mysqli_error($conn) . "<br>";
}

// Insert default manager
$sql = "INSERT INTO staff (username, password, name, email, role, phone_number)
        VALUES ('manager', 'manager123', 'Default Manager', 'manager@elitebank.com', 'manager', '1234567890')";

if (mysqli_query($conn, $sql)) {
    echo "Default manager account created successfully<br>";
} else {
    echo "Error creating default manager: " . mysqli_error($conn) . "<br>";
}

// Verify table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'staff'");
if ($result && mysqli_num_rows($result) > 0) {
    echo "Staff table exists and is accessible<br>";
} else {
    echo "Staff table does not exist or is not accessible<br>";
}

// Verify manager account
$result = mysqli_query($conn, "SELECT * FROM staff WHERE username = 'manager'");
if ($result && mysqli_num_rows($result) > 0) {
    echo "Manager account exists and is accessible<br>";
} else {
    echo "Manager account does not exist or is not accessible<br>";
}

mysqli_close($conn);
echo "<br>Test completed. <a href='index.php'>Go to login page</a>";
?> 