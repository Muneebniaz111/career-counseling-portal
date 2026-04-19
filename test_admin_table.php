<?php
$mysqli = new mysqli("localhost", "root", "", "career_counseling");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "=== admin_users table structure ===<br>";
$result = $mysqli->query("DESCRIBE admin_users");
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . ($row['Key'] ?? '') . "</td>";
        echo "<td>" . ($row['Default'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $mysqli->error;
}

echo "<br><br>=== Sample admin record ===<br>";
$result = $mysqli->query("SELECT * FROM admin_users LIMIT 1");
if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "<pre>";
    print_r($admin);
    echo "</pre>";
} else {
    echo "No admin records found";
}

$mysqli->close();
?>
