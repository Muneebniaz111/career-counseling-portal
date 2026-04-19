<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "career_counseling";

$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

$tables = [
    "feedback" => "CREATE TABLE IF NOT EXISTS feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "feedback_replies" => "CREATE TABLE IF NOT EXISTS feedback_replies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        feedback_id INT NOT NULL,
        admin_id INT,
        reply TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (feedback_id) REFERENCES feedback(id) ON DELETE CASCADE
    )",
    "feedback_notifications" => "CREATE TABLE IF NOT EXISTS feedback_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        feedback_id INT NOT NULL,
        user_id INT,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (feedback_id) REFERENCES feedback(id) ON DELETE CASCADE
    )"
];

foreach ($tables as $tableName => $createQuery) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    if ($result->num_rows > 0) {
        $status = "EXISTS";
    } else {
        if ($conn->query($createQuery)) {
            $status = "CREATED";
        } else {
            $status = "ERROR: " . $conn->error;
        }
    }

    $colCount = 0;
    $keys = [];
    if ($status === "EXISTS" || $status === "CREATED") {
        $columns = $conn->query("DESCRIBE $tableName");
        while ($col = $columns->fetch_assoc()) {
            $colCount++;
            if ($col['Key']) $keys[] = $col['Field'] . "(" . $col['Key'] . ")";
        }
        echo "$tableName: $status (Columns: $colCount, Keys: " . implode(", ", $keys) . ")\n";
    } else {
        echo "$tableName: $status\n";
    }
}

echo "All required tables ready for use\n";
$conn->close();
?>
