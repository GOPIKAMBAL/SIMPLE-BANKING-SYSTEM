<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$username = "root";
$password = "";

try {
    // Create connection without database
    $conn = mysqli_connect($host, $username, $password);
    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }
    echo "Connected to MySQL successfully<br>";

    // Drop and recreate database
    mysqli_query($conn, "DROP DATABASE IF EXISTS elite_bank");
    echo "Dropped existing database if any<br>";
    
    mysqli_query($conn, "CREATE DATABASE elite_bank");
    echo "Created new database<br>";
    
    mysqli_select_db($conn, "elite_bank");
    echo "Selected database<br>";

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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!mysqli_query($conn, $sql)) {
        throw new Exception("Error creating staff table: " . mysqli_error($conn));
    }
    echo "Created staff table<br>";

    // Insert default manager
    $sql = "INSERT INTO staff (username, password, name, email, role, phone_number)
            VALUES ('manager', 'manager123', 'Default Manager', 'manager@elitebank.com', 'manager', '1234567890')";
    
    if (!mysqli_query($conn, $sql)) {
        throw new Exception("Error creating default manager: " . mysqli_error($conn));
    }
    echo "Created default manager account<br>";

    // Verify setup
    $result = mysqli_query($conn, "SELECT * FROM staff WHERE username = 'manager'");
    if (!$result || mysqli_num_rows($result) == 0) {
        throw new Exception("Failed to verify manager account");
    }
    echo "Verified manager account exists<br>";

    echo "<br>Database setup completed successfully!<br>";
    echo "You can now <a href='manager_login.php'>login as manager</a> with:<br>";
    echo "Username: manager<br>";
    echo "Password: manager123<br>";

} catch (Exception $e) {
    die("Setup failed: " . $e->getMessage());
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?> 