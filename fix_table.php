<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "db_connection.php";

$sql = "CREATE TABLE IF NOT EXISTS contact_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    admin_id INT NOT NULL,
    reply_message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contact_messages(id) ON DELETE CASCADE
)";
if($conn->query($sql)){
    echo "Table contact_replies CREATED\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}
?>
