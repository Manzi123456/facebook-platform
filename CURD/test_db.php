<?php
require __DIR__ . '/config.php';

echo "<h2>Database Connection Test</h2>";

try {
    $conn = get_db_connection();
    echo "<p style='color:green;'>✓ Database connection successful!</p>";
    
    echo "<h3>Current Database:</h3>";
    $result = $conn->query("SELECT DATABASE() as db");
    $row = $result->fetch_assoc();
    echo "<p>Database: <strong>" . htmlspecialchars($row['db']) . "</strong></p>";
    
    echo "<h3>Tables in database:</h3>";
    $result = $conn->query("SHOW TABLES");
    echo "<ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
    }
    echo "</ul>";
    
    echo "<h3>Users table structure:</h3>";
    $result = $conn->query("DESCRIBE users");
    if ($result) {
        echo "<table border='1' cellpadding='5'>";
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
    } else {
        echo "<p style='color:red;'>✗ Error describing users table: " . $conn->error . "</p>";
    }
    
    echo "<h3>Users count:</h3>";
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Total users: <strong>" . $row['count'] . "</strong></p>";
    } else {
        echo "<p style='color:red;'>✗ Error counting users: " . $conn->error . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

