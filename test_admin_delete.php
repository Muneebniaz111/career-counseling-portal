<?php
// Simulate admin delete request
session_start();
require_once __DIR__ . '/db_connection.php';

echo "=== TESTING ADMIN DELETE FUNCTIONALITY ===\n\n";

// 1. Create a test message
echo "1. Creating test message for user_id = 2...\n";
$conn->query("INSERT INTO contact_messages (user_id, name, email, subject, message) VALUES (2, 'Test User 2', 'test2@example.com', 'Test', 'Test Message')");
$msg_id = $conn->insert_id;
echo "✓ Message created (ID: $msg_id)\n";

// 2. Create admin notifications
echo "\n2. Creating admin notifications...\n";
$result = $conn->query("SELECT id FROM admin_users");
$notif_count = 0;
while ($admin = $result->fetch_assoc()) {
    $admin_id = $admin['id'];
    $stmt = $conn->prepare("INSERT INTO admin_contact_notifications (contact_id, admin_id, is_read) VALUES (?, ?, ?)");
    $is_read = 0;
    $stmt->bind_param("iii", $msg_id, $admin_id, $is_read);
    if ($stmt->execute()) {
        $notif_count++;
    }
}
echo "✓ Created $notif_count admin notifications\n";

// 3. Verify message exists
echo "\n3. Verifying message exists...\n";
$result = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE id = $msg_id");
$count = $result->fetch_assoc()['count'];
echo "✓ Message exists: " . ($count > 0 ? "Yes" : "No") . "\n";

// 4. Test delete logic (same as admin_contacts.php)
echo "\n4. Testing delete logic...\n";
echo "   - Deleting contact_replies...\n";
$conn->query("DELETE FROM contact_replies WHERE contact_id = $msg_id");
echo "   - Deleting admin_contact_notifications...\n";
$conn->query("DELETE FROM admin_contact_notifications WHERE contact_id = $msg_id");
echo "   - Deleting contact_message...\n";
$stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
$stmt->bind_param("i", $msg_id);
if ($stmt->execute()) {
    echo "✓ Delete executed (affected rows: " . $stmt->affected_rows . ")\n";
}
$stmt->close();

// 5. Verify deletion
echo "\n5. Verifying deletion...\n";
$result = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE id = $msg_id");
$count = $result->fetch_assoc()['count'];
echo "✓ Message exists after delete: " . ($count > 0 ? "Yes (ERROR!)" : "No (OK)") . "\n";

echo "\n=== TEST COMPLETE ===\n";
?>
