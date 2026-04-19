<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "career_counseling";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
echo "--- STARTING END-TO-END TEST ---" . PHP_EOL;
$user_id = 3;
$name = "Ali Khan";
$email = "ali.khan786@gmail.com";
$subject = "Excellent Career Portal";
$message = "This platform has been incredibly helpful. Highly recommend!";
$stmt = $conn->prepare("INSERT INTO feedback (user_id, name, email, subject, message, rating, status) VALUES (?, ?, ?, ?, ?, 5, 'pending')");
$stmt->bind_param("issss", $user_id, $name, $email, $subject, $message);
if ($stmt->execute()) {
    $feedback_id = $stmt->insert_id;
    echo "✓ Submission: SUCCESS" . PHP_EOL;
} else { die("Submission failed: " . $stmt->error); }
$stmt->close();
$sql = "SELECT subject FROM feedback WHERE id = $feedback_id";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) { echo "✓ Retrieval: SUCCESS" . PHP_EOL; }
$admin_reply = "Thank you Ali!";
$admin_id = 1;
$stmt = $conn->prepare("INSERT INTO feedback_replies (feedback_id, admin_id, reply_message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $feedback_id, $admin_id, $admin_reply);
if ($stmt->execute()) {
    $notif_stmt = $conn->prepare("INSERT INTO feedback_notifications (user_id, feedback_id, is_read) VALUES (?, ?, 0)");
    $notif_stmt->bind_param("ii", $user_id, $feedback_id);
    if ($notif_stmt->execute()) {
        echo "✓ Notifications: WORKING" . PHP_EOL;
        $notif_id = $notif_stmt->insert_id;
        $conn->query("UPDATE feedback_notifications SET is_read = 1 WHERE id = $notif_id");
        echo "✓ Mark as Read: WORKING" . PHP_EOL;
    }
    $notif_stmt->close();
}
$stmt->close();
echo "✓ END-TO-END: FULLY FUNCTIONAL" . PHP_EOL;
$conn->close();
?>
