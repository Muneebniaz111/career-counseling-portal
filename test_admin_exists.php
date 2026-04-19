<?php
session_start();

// Try to get the admin_id from session or set a test value
$admin_id = $_SESSION['admin_id'] ?? 1;

$mysqli = new mysqli("localhost", "root", "", "career_counseling");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Admin ID: " . $admin_id . "<br>";
echo "Session Admin ID: " . ($_SESSION['admin_id'] ?? 'NOT SET') . "<br><br>";

// Check if admin exists
$stmt = $mysqli->prepare("SELECT id, name, email FROM admin_users WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $mysqli->error);
}

$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "❌ Admin user with ID $admin_id NOT FOUND in database<br><br>";
    echo "Checking all admins in database:<br>";
    
    $all_admins = $mysqli->query("SELECT id, name, email FROM admin_users");
    if ($all_admins && $all_admins->num_rows > 0) {
        while ($admin = $all_admins->fetch_assoc()) {
            echo "ID: " . $admin['id'] . " | Name: " . htmlspecialchars($admin['name']) . " | Email: " . htmlspecialchars($admin['email']) . "<br>";
        }
    } else {
        echo "❌ No admins exist in the database at all!<br>";
    }
} else {
    echo "✅ Admin user found!<br>";
    $admin = $result->fetch_assoc();
    echo "ID: " . $admin['id'] . "<br>";
    echo "Name: " . htmlspecialchars($admin['name']) . "<br>";
    echo "Email: " . htmlspecialchars($admin['email']) . "<br>";
}

$stmt->close();
$mysqli->close();
?>
