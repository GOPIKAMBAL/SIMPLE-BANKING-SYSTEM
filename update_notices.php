<?php
require_once 'config/db_connect.php';

// First, check if the users table exists and has the correct structure
$check_users = "SHOW TABLES LIKE 'users'";
$result = mysqli_query($conn, $check_users);

if (mysqli_num_rows($result) == 0) {
    die("Error: Users table does not exist. Please create the users table first.");
}

// Check if the users table has an 'id' column
$check_id = "SHOW COLUMNS FROM users LIKE 'id'";
$result = mysqli_query($conn, $check_id);

if (mysqli_num_rows($result) == 0) {
    die("Error: Users table does not have an 'id' column. Please fix the users table structure first.");
}

// Now create the notices table
$sql = "DROP TABLE IF EXISTS notices;
        CREATE TABLE notices (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (user_id),
            CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;";

// Execute the SQL
if (mysqli_multi_query($conn, $sql)) {
    // Wait for all queries to complete
    do {
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($conn));
    
    echo "Notices table updated successfully!";
} else {
    echo "Error updating notices table: " . mysqli_error($conn);
}

// Close the connection
mysqli_close($conn);
?> 