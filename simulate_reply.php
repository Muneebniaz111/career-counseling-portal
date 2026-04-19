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
$reply_message = "Thank you for your message. We will address your concerns shortly.";
$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO contact_replies (contact_id, admin_id, reply_message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $message_id, $admin_id, $reply_message);
    $stmt->execute();
    $stmt = $conn->prepare("UPDATE contact_messages SET status = \"replied\" WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $conn->commit();
    echo "Reply inserted and status updated.\n";
} catch (Exception $e) {
    $conn->rollback();
    die("Error: " . $e->getMessage() . "\n");
}
$stmt = $conn->prepare("SELECT status FROM contact_messages WHERE id = ?");
$stmt->bind_param("i", $message_id);
$stmt->execute();
$status = $stmt->get_result()->fetch_assoc()["status"];
echo "Message Status: $status\n";
$stmt = $conn->prepare("SELECT cr.*, a.full_name FROM contact_replies cr JOIN admin a ON cr.admin_id = a.id WHERE cr.contact_id = ?");
$stmt->bind_param("i", $message_id);
$stmt->execute();
$reply = $stmt->get_result()->fetch_assoc();
echo "Reply message: " . $reply["reply_message"] . "\n";
echo "Admin name: " . $reply["full_name"] . "\n";
?>
