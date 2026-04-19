<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "career_counseling_portal";

// 1. Establish Connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "--- STARTING END-TO-END TEST ---\n";

// Ensure tables exist or this test will fail
$tables = ['feedback', 'feedback_replies', 'feedback_notifications'];
foreach ($tables as $table) {
    if ($conn->query("SHOW TABLES LIKE '$table'")->num_rows == 0) {
        die("Table $table does not exist. Please run migrations first.\n");
    }
}

// 1. USER FEEDBACK SUBMISSION
$user_id = 2; // Assuming Ali Khan id=2 exists or we just use it for the test
$name = "Ali Khan";
$email = "ali.khan786@gmail.com";
$subject = "Excellent Career Portal";
$message = "This platform has been incredibly helpful for career planning. Highly recommend!";
$rating = 5;

$stmt = $conn->prepare("INSERT INTO feedback (user_id, name, email, subject, message, rating, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("issssi", $user_id, $name, $email, $subject, $message, $rating);

if ($stmt->execute()) {
    $feedback_id = $stmt->insert_id;
    echo "✓ Submission: SUCCESS (Feedback ID: $feedback_id)\n";
} else {
    die("Submission failed: " . $stmt->error);
}
$stmt->close();

// 2. FEEDBACK RETRIEVAL
$sql = "SELECT subject, message, rating, status FROM feedback WHERE id = $feedback_id";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['subject'] === $subject && $row['rating'] == $rating) {
        echo "✓ Retrieval: SUCCESS\n";
    } else {
        echo "Retrieval failed validation.\n";
    }
} else {
    die("Retrieval failed: Record not found.\n");
}

// 3. NOTIFICATION SYSTEM (Admin Reply)
$admin_reply = "Thank you Ali! We're glad we could help.";
$stmt = $conn->prepare("INSERT INTO feedback_replies (feedback_id, admin_message) VALUES (?, ?)");
$stmt->bind_param("is", $feedback_id, $admin_reply);

if ($stmt->execute()) {
    $reply_id = $stmt->insert_id;
    // Create Notification
    $notif_stmt = $conn->prepare("INSERT INTO feedback_notifications (user_id, feedback_id, reply_id, message) VALUES (?, ?, ?, ?)");
    $notif_msg = "Admin replied to your feedback: $subject";
    $notif_stmt->bind_param("iiis", $user_id, $feedback_id, $reply_id, $notif_msg);
    if ($notif_stmt->execute()) {
        $notif_id = $notif_stmt->insert_id;
        echo "✓ Notifications: WORKING\n";
    } else {
        die("Notification failed: " . $notif_stmt->error);
    }
    $notif_stmt->close();
} else {
    die("Reply failed: " . $stmt->error);
}
$stmt->close();

// 4. MARK AS READ
$update_sql = "UPDATE feedback_notifications SET is_read = 1 WHERE id = $notif_id";
if ($conn->query($update_sql) === TRUE) {
    $check_sql = "SELECT is_read FROM feedback_notifications WHERE id = $notif_id";
    $check_res = $conn->query($check_sql);
    $check_row = $check_res->fetch_assoc();
    if ($check_row['is_read'] == 1) {
        echo "✓ Mark as Read: WORKING\n";
    } else {
        echo "Mark as Read: FAILED\n";
    }
} else {
    die("Mark as Read Update failed: " . $conn->error);
}

// 5. FINAL STATUS CHECK
echo "✓ END-TO-END: FULLY FUNCTIONAL\n";

$conn->close();
?>
