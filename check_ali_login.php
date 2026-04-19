<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "career_counseling";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "--- All Users (First 10) ---\n";
$sql = "SELECT id, name, username, email FROM users LIMIT 10";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"]. " - Name: " . $row["name"]. " - Username: " . $row["username"]. " - Email: " . $row["email"]. "\n";
    }
} else {
    echo "No users found.\n";
}

echo "\n--- Searching for Ali ---\n";
$sql = "SELECT * FROM users WHERE name LIKE \"%Ali%\"";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Found: " . $row["name"] . " (Email: " . $row["email"] . ")\n";
        $hash = $row["password"];
        echo "Password Hash (First 30): " . substr($hash, 0, 30) . "...\n";
        
        $passwords_to_test = ["Ali@123", "password123", "Ali123", "Ali@123456"];
        foreach ($passwords_to_test as $pass) {
            if (password_verify($pass, $hash)) {
                echo "SUCCESS: Password \"$pass\" VERIFIES for user " . $row["name"] . "\n";
            } else {
                echo "FAIL: Password \"$pass\" does not verify.\n";
            }
        }
    }
} else {
    echo "No user with name Ali found.\n";
}

$conn->close();
?>