<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "db_connection.php";
$message_id = 14;
$res = $conn->query("
    SELECT cr.*, au.name as admin_name 
    FROM contact_replies cr 
    LEFT JOIN admin_users au ON cr.admin_id = au.id 
    WHERE cr.contact_id = $message_id
");
$reply = $res->fetch_assoc();
echo "FINAL VERIFICATION:\n";
echo "Message ID: $message_id\n";
echo "Admin Name: " . ($reply["admin_name"] ?? "Admin") . "\n";
echo "Reply: " . $reply["reply_message"] . "\n";
echo "Timestamp: " . $reply["created_at"] . "\n";
?>
