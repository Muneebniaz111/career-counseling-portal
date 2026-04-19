<?php
require 'db_connection.php';

$plaintext = 'TestPass123!';
$target_hash = '$2y$10$Ctg0YWkxYqdSwcN9EoGPVe8rbVdnWG431OMfoJlaTb2xdR3hJBrTG';
$email = 'testuser@example.com';

echo "1. Password Verify Test: ";
if (password_verify($plaintext, $target_hash)) {
    echo "SUCCESS\n";
} else {
    echo "FAILED\n";
}

echo "2. Database Query Test: ";
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "SUCCESS (User found in 'users' table)\n";
    echo "3. Email match: " . ($user['email'] == $email ? "YES" : "NO") . "\n";
    echo "Database hash match target hash: " . ($user['password'] == $target_hash ? "YES" : "NO (Found hash: " . $user['password'] . ")") . "\n";
} else {
    echo "FAILED (User not found or table 'users' does not exist)\n";
}
?>
