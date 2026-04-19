<?php
session_start();
require_once __DIR__ . '/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Not logged in. User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
    exit();
}

$user_id = $_SESSION['user_id'];
echo "Current User ID: " . $user_id . "<br>";
echo "Session User Name: " . ($_SESSION['user_name'] ?? 'NOT SET') . "<br><br>";

$mysqli = new mysqli("localhost", "root", "", "career_counseling");

// Check all feedback in database
echo "<h3>ALL FEEDBACK IN DATABASE:</h3>";
$all_result = $mysqli->query("SELECT id, user_id, name, subject, created_at, status FROM feedback ORDER BY created_at DESC LIMIT 20");
if ($all_result && $all_result->num_rows > 0) {
    echo "Total feedback records: " . $all_result->num_rows . "<br><br>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Name</th><th>Subject</th><th>Created</th><th>Status</th></tr>";
    while ($row = $all_result->fetch_assoc()) {
        $match = ($row['user_id'] == $user_id) ? "✓ MATCHES" : "✗ NO MATCH";
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . ($row['user_id'] ?? 'NULL') . " " . $match . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No feedback found in database";
}

echo "<br><br><h3>FEEDBACK FOR THIS USER (user_id = $user_id):</h3>";
$user_result = $mysqli->query("SELECT id, user_id, name, subject, created_at, status FROM feedback WHERE user_id = $user_id ORDER BY created_at DESC");
if ($user_result && $user_result->num_rows > 0) {
    echo "Found: " . $user_result->num_rows . " feedback records<br><br>";
    while ($row = $user_result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Subject: " . htmlspecialchars($row['subject']) . " | Status: " . $row['status'] . "<br>";
    }
} else {
    echo "No feedback found for user_id = $user_id<br>";
}

echo "<br><br><h3>FEEDBACK WITH NULL USER_ID:</h3>";
$null_result = $mysqli->query("SELECT id, user_id, name, subject, created_at, status FROM feedback WHERE user_id IS NULL ORDER BY created_at DESC LIMIT 10");
if ($null_result && $null_result->num_rows > 0) {
    echo "Found: " . $null_result->num_rows . " feedback records with NULL user_id<br>";
} else {
    echo "No feedback with NULL user_id";
}
?>
