<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "db_connection.php";

$message_id = 14;
$admin_id = 1;
$reply_txt = "Thank you for your message. We will address your concerns shortly.";

$stmt = $conn->prepare("INSERT INTO contact_replies (contact_id, admin_id, reply_message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $message_id, $admin_id, $reply_txt);
$stmt->execute();

$stmt = $conn->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?");
$stmt->bind_param("i", $message_id);
$stmt->execute();

echo "UPDATE SUCCESSFUL FOR ID: $message_id\n";
?>
