<?php
include "db_connection.php";

echo "=== COMPLETE MESSAGE FLOW TEST ===\n\n";

// 1. Check current state
echo "1. CURRENT DATABASE STATE:\n";
$result = $conn->query("SELECT COUNT(*) as total FROM contact_messages");
$total = $result->fetch_assoc()['total'];
echo "Total messages: $total\n";

$result = $conn->query("SELECT user_id, COUNT(*) as count FROM contact_messages GROUP BY user_id");
while ($row = $result->fetch_assoc()) {
    echo "  - User ID " . $row['user_id'] . ": " . $row['count'] . " messages\n";
}

// 2. Test message submission (simulate)
echo "\n2. SIMULATING MESSAGE SUBMISSION:\n";
$stmt = $conn->prepare("INSERT INTO contact_messages (user_id, name, email, subject, message, phone, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
$_user_id = 1;
$_name = "Test User";
$_email = "test@example.com";
$_subject = "Test Subject";
$_message = "This is a test message";
$_phone = "+92-300-1234567";
$_status = "open";

$stmt->bind_param("issssss", $_user_id, $_name, $_email, $_subject, $_message, $_phone, $_status);
if ($stmt->execute()) {
    $msg_id = $conn->insert_id;
    echo "✓ Message created successfully (ID: $msg_id)\n";
    
    // 3. Verify message was stored with user_id
    echo "\n3. VERIFYING MESSAGE STORAGE:\n";
    $result = $conn->query("SELECT id, user_id, name, email, subject, status FROM contact_messages WHERE id = $msg_id");
    if ($row = $result->fetch_assoc()) {
        echo "✓ Message found in database:\n";
        echo "  - ID: " . $row['id'] . "\n";
        echo "  - User ID: " . $row['user_id'] . "\n";
        echo "  - Name: " . $row['name'] . "\n";
        echo "  - Email: " . $row['email'] . "\n";
        echo "  - Subject: " . $row['subject'] . "\n";
        echo "  - Status: " . $row['status'] . "\n";
    }
    
    // 4. Check admin notifications
    echo "\n4. ADMIN NOTIFICATIONS:\n";
    // Create notifications (simulate what contact_form.php does)
    $admin_result = $conn->query("SELECT id FROM admin_users");
    $admin_count = 0;
    while ($admin = $admin_result->fetch_assoc()) {
        $admin_id = $admin['id'];
        $notif_stmt = $conn->prepare("INSERT INTO admin_contact_notifications (contact_id, admin_id, is_read) VALUES (?, ?, ?)");
        $is_read = 0;
        $notif_stmt->bind_param("iii", $msg_id, $admin_id, $is_read);
        if ($notif_stmt->execute()) {
            $admin_count++;
        }
    }
    echo "✓ Notifications created for $admin_count admins\n";
    
    // Verify notifications were created
    $result = $conn->query("SELECT COUNT(*) as notif_count FROM admin_contact_notifications WHERE contact_id = $msg_id");
    $notif_count = $result->fetch_assoc()['notif_count'];
    echo "✓ Verified: $notif_count notifications in database\n";
    
    // 5. Simulate admin reply
    echo "\n5. SIMULATING ADMIN REPLY:\n";
    $stmt = $conn->prepare("INSERT INTO contact_replies (contact_id, admin_id, reply_message) VALUES (?, ?, ?)");
    $_contact_id = $msg_id;
    $_admin_id = 1;
    $_reply = "Thank you for your message. We will respond shortly.";
    $stmt->bind_param("iis", $_contact_id, $_admin_id, $_reply);
    if ($stmt->execute()) {
        echo "✓ Reply created successfully\n";
        
        // Update message status
        $conn->query("UPDATE contact_messages SET status = 'replied' WHERE id = $msg_id");
        echo "✓ Message status updated to 'replied'\n";
    }
    
    // 6. Verify message with replies
    echo "\n6. MESSAGE WITH REPLIES:\n";
    $result = $conn->query("SELECT status FROM contact_messages WHERE id = $msg_id");
    $status = $result->fetch_assoc()['status'];
    echo "  - Message status: $status\n";
    
    $result = $conn->query("SELECT COUNT(*) as reply_count FROM contact_replies WHERE contact_id = $msg_id");
    $reply_count = $result->fetch_assoc()['reply_count'];
    echo "  - Replies: $reply_count\n";
    
    // 7. Test user filtering (my_messages.php)
    echo "\n7. USER-SPECIFIC MESSAGE FILTERING:\n";
    $user_id = 1;
    $result = $conn->query("SELECT COUNT(*) as user_messages FROM contact_messages WHERE user_id = $user_id");
    $user_msg_count = $result->fetch_assoc()['user_messages'];
    echo "✓ User ID 1 can see: $user_msg_count messages\n";
    
    // 8. Test delete functionality
    echo "\n8. DELETE FUNCTIONALITY TEST:\n";
    echo "  - Attempting to delete message as User ID 1...\n";
    
    // Must delete related records first due to foreign key constraints
    $conn->query("DELETE FROM contact_replies WHERE contact_id = $msg_id");
    $conn->query("DELETE FROM admin_contact_notifications WHERE contact_id = $msg_id");
    
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ? AND user_id = ?");
    $_msg_id = $msg_id;
    $_uid = 1;
    $stmt->bind_param("ii", $_msg_id, $_uid);
    if ($stmt->execute()) {
        echo "  - Affected rows: " . $stmt->affected_rows . "\n";
        if ($stmt->affected_rows > 0) {
            echo "✓ Message deleted successfully\n";
            
            // Verify deletion
            $result = $conn->query("SELECT COUNT(*) as msg_count FROM contact_messages WHERE id = $msg_id");
            $msg_count = $result->fetch_assoc()['msg_count'];
            echo "  - Message exists after delete: " . ($msg_count > 0 ? "Yes (ERROR)" : "No (OK)") . "\n";
        } else {
            echo "✗ Delete failed (no rows affected)\n";
        }
    }
    
    // 9. Test unauthorized delete
    echo "\n9. SECURITY TEST - Unauthorized Delete:\n";
    $stmt = $conn->prepare("INSERT INTO contact_messages (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
    $_uid2 = 2;
    $_n = "User 2";
    $_e = "user2@example.com";
    $_s = "User 2 Message";
    $_m = "This is user 2's message";
    $stmt->bind_param("issss", $_uid2, $_n, $_e, $_s, $_m);
    $stmt->execute();
    $user2_msg_id = $conn->insert_id;
    echo "  - Created message for User ID 2 (ID: $user2_msg_id)\n";
    
    echo "  - Attempting User 1 to delete User 2's message...\n";
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ? AND user_id = ?");
    $_msg_id = $user2_msg_id;
    $_uid = 1;
    $stmt->bind_param("ii", $_msg_id, $_uid);
    $stmt->execute();
    echo "  - Affected rows: " . $stmt->affected_rows . "\n";
    
    if ($stmt->affected_rows === 0) {
        echo "✓ Security verified: User 1 cannot delete User 2's message\n";
        // Clean up
        $conn->query("DELETE FROM contact_messages WHERE id = $user2_msg_id");
    } else {
        echo "✗ SECURITY ISSUE: User 1 was able to delete User 2's message\n";
    }
    
} else {
    echo "✗ Failed to create message: " . $stmt->error . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
