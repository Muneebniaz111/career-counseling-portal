<?php
// Comprehensive test of admin delete via AJAX
session_start();
require_once __DIR__ . '/db_connection.php';

// Set admin session
$_SESSION['admin_id'] = 1;
$_SESSION['user_type'] = 'admin';
$_SESSION['admin_name'] = 'Test Admin';
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

echo "=== TESTING ADMIN DELETE VIA AJAX (SIMULATED) ===\n\n";

// 1. Create a test message
echo "1. Creating test message...\n";
$conn->query("INSERT INTO contact_messages (user_id, name, email, subject, message) VALUES (3, 'Test User 3', 'test3@example.com', 'Delete Test', 'This message will be deleted')");
$msg_id = $conn->insert_id;
echo "✓ Message created (ID: $msg_id)\n";

// 2. Create admin notifications
echo "\n2. Creating admin notifications...\n";
$result = $conn->query("SELECT id FROM admin_users");
while ($admin = $result->fetch_assoc()) {
    $admin_id = $admin['id'];
    $stmt = $conn->prepare("INSERT INTO admin_contact_notifications (contact_id, admin_id, is_read) VALUES (?, ?, ?)");
    $is_read = 0;
    $stmt->bind_param("iii", $msg_id, $admin_id, $is_read);
    $stmt->execute();
}
echo "✓ Notifications created\n";

// 3. Verify message exists
echo "\n3. Verifying message exists...\n";
$result = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE id = $msg_id");
$count = $result->fetch_assoc()['count'];
echo "✓ Message exists: " . ($count > 0 ? "Yes" : "No") . "\n";

// 4. Simulate AJAX POST request
echo "\n4. Simulating AJAX DELETE request...\n";
$_POST['action'] = 'delete';
$_POST['contact_id'] = $msg_id;
$_POST['csrf_token'] = $_SESSION['csrf_token'];

// Simulate the delete logic from admin_contacts.php
try {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        throw new Exception("CSRF token validation failed");
    }
    
    $contact_id = intval($_POST['contact_id']);
    
    // Delete related records first
    $conn->query("DELETE FROM contact_replies WHERE contact_id = $contact_id");
    $conn->query("DELETE FROM admin_contact_notifications WHERE contact_id = $contact_id");
    
    // Delete the message
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $contact_id);
    $stmt->execute();
    
    echo "✓ CSRF validation passed\n";
    echo "✓ Delete executed (affected rows: " . $stmt->affected_rows . ")\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// 5. Verify deletion
echo "\n5. Verifying deletion...\n";
$result = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE id = $msg_id");
$count = intval($result->fetch_assoc()['count']);
if ($count === 0) {
    echo "✓ Message deleted successfully\n";
} else {
    echo "✗ Message still exists (ERROR)\n";
}

$result = $conn->query("SELECT COUNT(*) as count FROM admin_contact_notifications WHERE contact_id = $msg_id");
$notif_count = intval($result->fetch_assoc()['count']);
if ($notif_count === 0) {
    echo "✓ Notifications deleted successfully\n";
} else {
    echo "✗ " . $notif_count . " notifications still exist (ERROR)\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
