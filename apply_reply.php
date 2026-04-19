<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "db_connection.php";

$stmt = $conn->prepare("SELECT id FROM contact_messages WHERE user_id = 1 ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
$message = $result->fetch_assoc();
if (!$message) { die("No message found\n"); }
$message_id = $message["id"];
$admin_id = 1;
$reply_txt = "Thank you for your message. We will address your concerns shortly.";

$stmt = $conn->prepare("INSERT INTO contact_replies (contact_id, admin_id, reply_message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $message_id, $admin_id, $reply_txt);
$stmt->execute();

$stmt = $conn->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?");
$stmt->bind_param("i", $message_id);
$stmt->execute();

echo "UPDATE SUCCESSFUL\n";
echo "MESSAGE_ID: $message_id\n";

$stmt = $conn->prepare("SELECT status FROM contact_messages WHERE id = ?");
$stmt->bind_param("i", $message_id);
$stmt->execute();
$status = $stmt->get_result()->fetch_assoc()["status"];
echo "STATUS: $status\n";

$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM contact_replies WHERE contact_id = ?");
$stmt->bind_param("i", $message_id);
$stmt->execute();
$cnt = $stmt->get_result()->fetch_assoc()["cnt"];
echo "REPLY_COUNT: $cnt\n";
?>
