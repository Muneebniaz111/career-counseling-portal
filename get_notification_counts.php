<?php
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'notification_count' => 0,
    'new_replies_count' => 0,
    'message' => ''
];

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
        throw new Exception("User not authenticated");
    }

    $user_id = $_SESSION['user_id'];

    // Database connection
    $mysqli = new mysqli("localhost", "root", "", "career_counseling");
    if ($mysqli->connect_error) {
        throw new Exception("Database connection failed: " . $mysqli->connect_error);
    }

    // Get feedback notification count using prepared statement
    $notification_count = 0;
    try {
        $notif_stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM feedback_notifications WHERE user_id = ? AND is_read = 0");
        if ($notif_stmt) {
            $notif_stmt->bind_param("i", $user_id);
            $notif_stmt->execute();
            $notifications = $notif_stmt->get_result();
            if ($notifications) {
                $notification_count = intval($notifications->fetch_assoc()['count'] ?? 0);
            }
            $notif_stmt->close();
        }
    } catch (Exception $e) {
        error_log("Error getting feedback notifications: " . $e->getMessage());
        $notification_count = 0;
    }

    // Get contact message unread replies count using prepared statement
    $new_replies_count = 0;
    try {
        // Check if is_read column exists
        $check_is_read = $mysqli->query("SHOW COLUMNS FROM contact_messages LIKE 'is_read'");
        $has_is_read_column = ($check_is_read && $check_is_read->num_rows > 0);
        
        // Query based on column existence
        if ($has_is_read_column) {
            // Column exists - only count unread messages (status='replied' AND is_read=0)
            $new_replies_stmt = $mysqli->prepare(
                "SELECT COUNT(*) as count FROM contact_messages 
                 WHERE user_id = ? AND status = 'replied' AND is_read = 0"
            );
        } else {
            // Column doesn't exist - use status only (backward compatibility)
            $new_replies_stmt = $mysqli->prepare(
                "SELECT COUNT(*) as count FROM contact_messages 
                 WHERE user_id = ? AND status = 'replied'"
            );
        }
        
        if ($new_replies_stmt) {
            $new_replies_stmt->bind_param("i", $user_id);
            $new_replies_stmt->execute();
            $new_replies = $new_replies_stmt->get_result();
            
            if ($new_replies) {
                $new_replies_count = intval($new_replies->fetch_assoc()['count'] ?? 0);
            }
            $new_replies_stmt->close();
        }
    } catch (Exception $e) {
        error_log("Exception getting new replies count: " . $e->getMessage());
        $new_replies_count = 0;
    }

    $mysqli->close();

    $response['success'] = true;
    $response['notification_count'] = $notification_count;
    $response['new_replies_count'] = $new_replies_count;

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Get notification counts error: " . $e->getMessage());
}

echo json_encode($response);
?>

