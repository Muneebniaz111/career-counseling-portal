<?php
// Database setup script - Run once to initialize database and tables
error_reporting(E_ALL);
ini_set('display_errors', 1);

$server = "localhost";
$username = "root";
$password = "";

// First, create connection without database selection
$conn = new mysqli($server, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read and execute SQL file
$sql_file = __DIR__ . '/Query.sql';

if (!file_exists($sql_file)) {
    die("Query.sql file not found at: " . $sql_file);
}

$sql_content = file_get_contents($sql_file);

if ($sql_content === false) {
    die("Failed to read Query.sql file");
}

// Split SQL statements by semicolon (simple approach)
$statements = array_filter(array_map('trim', explode(';', $sql_content)));

if (empty($statements)) {
    die("No SQL statements found in Query.sql");
}

echo "<h2>Database Setup Progress</h2>";
echo "<ul>";

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    if (empty($statement)) {
        continue;
    }

    // Add semicolon back
    $statement = $statement . ';';

    if ($conn->multi_query($statement)) {
        // Clear all results
        while ($conn->more_results()) {
            $conn->next_result();
        }
        echo "<li style='color: green;'>✓ Executed: " . substr($statement, 0, 60) . "...</li>";
        $success_count++;
    } else {
        echo "<li style='color: red;'>✗ Error: " . $conn->error . "<br>Statement: " . substr($statement, 0, 100) . "...</li>";
        $error_count++;
    }
}

echo "</ul>";

if ($error_count == 0) {
    echo "<h3 style='color: green;'>✓ Database setup completed successfully!</h3>";
    echo "<p>All tables have been created. You can now delete this file or <a href='Admin_Dashboard.php'>go to Admin Dashboard</a></p>";
} else {
    echo "<h3 style='color: orange;'>⚠ Setup completed with " . $error_count . " error(s)</h3>";
    echo "<p>Some tables may already exist. This is normal if you've run this script before.</p>";
}

$conn->close();
?>
