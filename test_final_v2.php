<?php
include "config.php";
$count_before = $conn->query("SELECT COUNT(*) FROM contact_messages")->fetch_row()[0];
echo "Count before: $count_before\n";

$target = $conn->query("SELECT id FROM contact_messages WHERE user_id = 1 ORDER BY id DESC LIMIT 1")->fetch_row()[0] ?? 14;
echo "Target ID: $target\n";

$stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ? AND user_id = 1");
$stmt->bind_param("i", $target);
$stmt->execute();
echo "Rows deleted: " . $stmt->affected_rows . "\n";

$msg_exists = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE id = $target")->fetch_row()[0];
echo "Message exists: " . ($msg_exists ? "Yes" : "No") . "\n";

$replies_exist = $conn->query("SELECT COUNT(*) FROM contact_replies WHERE contact_id = $target")->fetch_row()[0];
echo "Replies exist: " . ($replies_exist ? "Yes" : "No") . "\n";

$count_after = $conn->query("SELECT COUNT(*) FROM contact_messages")->fetch_row()[0];
echo "Count after: $count_after\n";

$conn->query("INSERT INTO contact_messages (user_id, name, email, query) VALUES (2, 'U2', 'u2@e.com', 'Q')");
$u2_id = $conn->insert_id;
$stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ? AND user_id = 1");
$stmt->bind_param("i", $u2_id);
$stmt->execute();
echo "Unauthorized delete affected: " . $stmt->affected_rows . "\n";

$u2_exists = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE id = $u2_id")->fetch_row()[0];
echo "User 2 message exists: " . ($u2_exists ? "Yes" : "No") . "\n";
$conn->query("DELETE FROM contact_messages WHERE id = $u2_id");
?>
