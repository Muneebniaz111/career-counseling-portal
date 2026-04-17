<?php
// Database migration script - Adds missing columns to contact_messages table
error_reporting(E_ALL);
ini_set('display_errors', 1);

$server = "localhost";
$username = "root";
$password = "";
$database = "career_counseling";

$conn = new mysqli($server, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Migration - Adding Missing Columns</h2>";
echo "<ul>";

// List of migrations to perform
$migrations = [
    "ALTER TABLE contact_messages ADD COLUMN user_id INT AFTER id" => "Add user_id to contact_messages",
    "ALTER TABLE contact_messages ADD CONSTRAINT fk_contact_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL" => "Add foreign key constraint"
];

$success_count = 0;
$error_count = 0;

foreach ($migrations as $sql => $description) {
    // Check if column already exists
    if (strpos($sql, 'ADD COLUMN') !== false) {
        // Extract column name
        preg_match('/ADD COLUMN (\w+)/', $sql, $matches);
        $column_name = $matches[1] ?? null;
        
        if ($column_name) {
            $check_result = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='contact_messages' AND COLUMN_NAME='$column_name'");
            if ($check_result && $check_result->num_rows > 0) {
                echo "<li style='color: blue;'>ℹ Column '$column_name' already exists, skipping...</li>";
                continue;
            }
        }
    }
    
    if ($conn->query($sql)) {
        echo "<li style='color: green;'>✓ " . htmlspecialchars($description) . "</li>";
        $success_count++;
    } else {
        // Check if error is due to already existing constraint
        if (strpos($conn->error, 'Duplicate key name') !== false || strpos($conn->error, 'already exists') !== false) {
            echo "<li style='color: blue;'>ℹ " . htmlspecialchars($description) . " (already exists)</li>";
        } else {
            echo "<li style='color: orange;'>⚠ " . htmlspecialchars($description) . "<br>Error: " . htmlspecialchars($conn->error) . "</li>";
            $error_count++;
        }
    }
}

echo "</ul>";

// Check current table structure
echo "<h3>Current contact_messages Table Structure:</h3>";
$columns = $conn->query("DESCRIBE contact_messages");
if ($columns) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr style='background-color: #f2f2f2;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $columns->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error retrieving table structure: " . htmlspecialchars($conn->error) . "</p>";
}

echo "<hr>";
echo "<h3>Migration Status:</h3>";
if ($error_count == 0) {
    echo "<p style='color: green;'><strong>✓ Migration completed successfully!</strong></p>";
    echo "<p>The contact system is now ready to use. You can:</p>";
    echo "<ul>";
    echo "<li><a href='Student_Dashboard.php'>Go to Student Dashboard</a></li>";
    echo "<li><a href='Admin_Dashboard.php'>Go to Admin Dashboard</a></li>";
    echo "<li><a href='Contact.html'>Send a Contact Message</a></li>";
    echo "</ul>";
} else {
    echo "<p style='color: orange;'><strong>⚠ Migration completed with some warnings</strong></p>";
    echo "<p>Please check the errors above. The system may still work if the required columns exist.</p>";
}

$conn->close();
?>
