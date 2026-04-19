<?php
include "config.php";

function log_message($msg) {
    echo $msg . "\n";
}

// 1. Get contact message ID (we assume ID 14 based on query but let's check what exists)
$stmt = $conn->prepare("SELECT id, user_id FROM contact_messages ORDER BY id DESC LIMIT 5");
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
log_message("Recent messages: " . json_encode($messages));

$target_id = 14; 
$user_id = 1;

// 2. Simulate delete operation as user_id = 1
log_message("Step 2: Simulating delete for ID $target_id by User $user_id");
// In the actual app, it uses a GET/POST request. Let's see how my_messages.php handles it.
// Actually, looking at typical PHP apps, it might be a DELETE query.
$delete_query = "DELETE FROM contact_messages WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($delete_query);
$stmt->bind_param("ii", $target_id, $user_id);
$stmt->execute();
log_message("Rows affected by delete: " . $stmt->affected_rows);

// 3. Verify message is deleted
log_message("Step 3: Verifying deletion");
$stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id = ?");
$stmt->bind_param("i", $target_id);
$stmt->execute();
$result = $stmt->get_result();
log_message("Query for ID $target_id returns " . $result->num_rows . " rows.");

// Verify replies are removed
$stmt = $conn->prepare("SELECT * FROM contact_replies WHERE contact_id = ?");
$stmt->bind_param("i", $target_id);
$stmt->execute();
$result = $stmt->get_result();
log_message("Query for replies to ID $target_id returns " . $result->num_rows . " rows.");

// 5. Test that other messages are NOT affected
log_message("Step 5: Testing isolation");
// Insert for user 2
$conn->query("INSERT INTO contact_messages (user_id, name, email, query) VALUES (2, 'User Two', 'user2@example.com', 'Test Query 2')");
$user2_msg_id = $conn->insert_id;
log_message("Created message for user 2 with ID: $user2_msg_id");

// Attempt to delete user 2's message as user 1
$stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $user2_msg_id, $user_id);
$stmt->execute();
log_message("Rows affected by unauthorized delete attempt: " . $stmt->affected_rows);

// Verify user 2's message still exists
$stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id = ?");
$stmt->bind_param("i", $user2_msg_id);
$stmt->execute();
$result = $stmt->get_result();
log_message("User 2's message exists: " . ($result->num_rows > 0 ? "Yes" : "No"));

// Cleanup
$conn->query("DELETE FROM contact_messages WHERE id = $user2_msg_id");

?>
