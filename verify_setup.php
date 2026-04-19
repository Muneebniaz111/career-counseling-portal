<?php
/**
 * LOGIN SYSTEM VERIFICATION
 * Check if everything is working
 */

$servername = "localhost";
$username = "root";
$password = "";
$database = "career_counseling";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Login System Verification</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        .container { background: white; padding: 20px; max-width: 800px; margin: 0 auto; border-radius: 8px; }
        .item { margin: 10px 0; padding: 10px; border-radius: 4px; }
        .pass { background: #d4edda; color: #155724; }
        .fail { background: #f8d7da; color: #721c24; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔍 Login System Verification</h1>";

// Check 1: Database Connection
echo "<div class='item pass'>✅ Database Connected</div>";

// Check 2: Tables
$tables = ['admin_users', 'users', 'feedback', 'contact_messages'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<div class='item pass'>✅ Table '$table' exists</div>";
    } else {
        echo "<div class='item fail'>❌ Table '$table' missing</div>";
    }
}

// Check 3: Admin Users
echo "<h2>Admin Users</h2>";
$result = $conn->query("SELECT id, email, name FROM admin_users");
if ($result && $result->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Email</th><th>Name</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['id'] . "</td><td>" . $row['email'] . "</td><td>" . $row['name'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<div class='item fail'>❌ No admin users found</div>";
}

// Check 4: Student Users
echo "<h2>Student Users</h2>";
$result = $conn->query("SELECT id, email, name FROM users");
if ($result && $result->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Email</th><th>Name</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['id'] . "</td><td>" . $row['email'] . "</td><td>" . $row['name'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<div class='item fail'>❌ No student users found</div>";
}

echo "<h2>🚀 Try Logging In:</h2>";
echo "<p><a href='Log-in%20(Admin).php' class='btn' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>Admin Login</a></p>";
echo "<p><a href='Log-in%20(Student).php' class='btn' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>Student Login</a></p>";

$conn->close();

echo "</div>
</body>
</html>";
?>
