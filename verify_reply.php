<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "db_connection.php";

$message_id = 14;

// 1. Check contact_messages status
$res = $conn->query("SELECT status FROM contact_messages WHERE id = $message_id");
$status = $res->fetch_assoc()["status"];
echo "Message status: $status\n";

// 2. Check contact_replies
$res = $conn->query("SELECT * FROM contact_replies WHERE contact_id = $message_id");
$reply = $res->fetch_assoc();
echo "Reply message: " . $reply["reply_message"] . "\n";
echo "Admin ID: " . $reply["admin_id"] . "\n";

// 3. Check admin name linkage (assume table 'admin' or 'admin_users' exists)
// Based on previous Query.sql check it was 'admin_users' or 'admin'? 
// Let's check which admin table exists
$tables = $conn->query("SHOW TABLES");
while($t = $tables->fetch_row()){
    if(strpos($t[0], "admin") !== false) echo "Found admin table: " . $t[0] . "\n";
}

?>
