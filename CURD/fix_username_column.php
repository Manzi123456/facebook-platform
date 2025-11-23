<?php
/**
 * Quick fix script to add username column to users table
 * Run this once by visiting: http://localhost/CURD/fix_username_column.php
 */

require __DIR__ . '/config.php';

try {
    $conn = get_db_connection();
    
    // Check if username column exists
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'username'");
    
    if ($result->num_rows === 0) {
        // Add username column
        $conn->query("ALTER TABLE `users` ADD COLUMN `username` VARCHAR(50) NULL AFTER `name`");
        $conn->query("ALTER TABLE `users` ADD UNIQUE KEY `username` (`username`)");
        
        // Update existing users to have username (use name as username, lowercase, no spaces)
        $conn->query("UPDATE `users` SET `username` = LOWER(REPLACE(REPLACE(`name`, ' ', ''), '.', '')) WHERE `username` IS NULL");
        
        echo "<h2 style='color: green;'>✓ Username column added successfully!</h2>";
        echo "<p>The username column has been added to your users table.</p>";
        echo "<p>Existing users have been assigned usernames based on their names.</p>";
        echo "<p><a href='login.php'>Go to Login Page</a></p>";
    } else {
        echo "<h2 style='color: blue;'>ℹ Username column already exists</h2>";
        echo "<p>The username column is already in your users table.</p>";
        echo "<p><a href='login.php'>Go to Login Page</a></p>";
    }
    
    // Show current table structure
    echo "<h3>Current Users Table Structure:</h3>";
    $result = $conn->query("DESCRIBE users");
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>✗ Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please run the SQL manually in phpMyAdmin:</p>";
    echo "<pre>";
    echo "ALTER TABLE `users` ADD COLUMN `username` VARCHAR(50) NULL AFTER `name`;\n";
    echo "ALTER TABLE `users` ADD UNIQUE KEY `username` (`username`);\n";
    echo "UPDATE `users` SET `username` = LOWER(REPLACE(REPLACE(`name`, ' ', ''), '.', '')) WHERE `username` IS NULL;";
    echo "</pre>";
}
?>

