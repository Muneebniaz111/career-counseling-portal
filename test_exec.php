<?php
include "db_connection.php";
$res = $conn->query("SELECT COUNT(*) FROM contact_messages");
$count1 = $res->fetch_row()[0];
echo "Count before: $count1\n";

$res = $conn->query("SELECT id FROM contact_messages WHERE user_id = 1 ORDER BY id DESC LIMIT 1");
$row = $res->fetch_row();
$tid = $row ? $row[0] : 14;
echo "Target ID: $tid\n";

$stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ? AND user_id = 1");
$stmt->bind_param("i", $tid);
$stmt->execute();
echo "Rows deleted: " . $stmt->affected_rows . "\n";

$res = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE id = $tid");
echo "Message exists: " . ($res->fetch_row()[0] ? "Yes" : "No") . "\n";

$count2 = $conn->query("SELECT COUNT(*) FROM contact_messages")->fetch_row()[0];
echo "Count after: $count2\n";

$conn->query("INSERT INTO contact_messages (user_id, name, email, subject, message) VALUES (2, \"U2\", \"u2@e.com\", \"S\", \"M\")");
$u2id = $conn->insert_id;
$stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ? AND user_id = 1");
$stmt->bind_param("i", $u2id);
$stmt->execute();
echo "Unauthorized delete affected: " . $stmt->affected_rows . "\n";

$res = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE id = $u2id");
echo "User 2 message exists: " . ($res->fetch_row()[0] ? "Yes" : "No") . "\n";
$conn->query("DELETE FROM contact_messages WHERE id = $u2id");
?>
