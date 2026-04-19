<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "career_counseling";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$new_password = "Ali@123456";
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$email = "ali.khan786@gmail.com";

$sql = "UPDATE users SET password = \"$hashed_password\" WHERE email = \"$email\"";

if ($conn->query($sql) === TRUE) {
    echo "Password updated successfully for $email\n";
    echo "New password: $new_password\n";
} else {
    echo "Error updating password: " . $conn->error . "\n";
}

$conn->close();
?>