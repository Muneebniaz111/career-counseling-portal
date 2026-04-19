<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$response = array('success' => false, 'message' => '');

try {
    // 1. CHECK ADMIN AUTHENTICATION
    if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
        throw new Exception("Unauthorized: Admin login required");
    }

    // 2. CHECK REQUEST METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // 3. VALIDATE CSRF TOKEN BEFORE PROCESSING
    $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (empty($csrf_token) || !isset($_SESSION['csrf_token'])) {
        throw new Exception("CSRF validation failed");
    }
    
    // Use hash_equals to prevent timing attacks
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        error_log("CSRF validation failed for admin {$_SESSION['admin_id']}");
        throw new Exception("CSRF validation failed");
    }

    // 4. GET AND VALIDATE INPUT
    $contact_id = isset($_POST['contact_id']) ? intval($_POST['contact_id']) : 0;
    $reply_message = isset($_POST['reply_message']) ? trim($_POST['reply_message']) : '';
    $admin_id = $_SESSION['admin_id'];

    // 5. VALIDATE CONTACT ID
    if (empty($contact_id) || $contact_id <= 0) {
        throw new Exception("Contact ID is required");
    }

    // 6. VALIDATE REPLY MESSAGE
    if (empty($reply_message)) {
        throw new Exception("Reply message cannot be empty");
    }

    if (strlen($reply_message) < 10) {
        throw new Exception("Reply message must be at least 10 characters long");
    }

    if (strlen($reply_message) > 5000) {
        throw new Exception("Reply message cannot exceed 5000 characters");
    }

    // 7. DATABASE CONNECTION
    $mysqli = new mysqli("localhost", "root", "", "career_counseling");
    if ($mysqli->connect_error) {
        error_log("Database connection error: " . $mysqli->connect_error);
        throw new Exception("Database connection failed");
    }

    // 8. VERIFY CONTACT EXISTS (using prepared statement)
    $verify_stmt = $mysqli->prepare("SELECT id, user_id FROM contact_messages WHERE id = ?");
    if (!$verify_stmt) {
        throw new Exception("Database error");
    }
    $verify_stmt->bind_param("i", $contact_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows === 0) {
        $verify_stmt->close();
        throw new Exception("Contact message not found");
    }
    
    $contact_data = $verify_result->fetch_assoc();
    $verify_stmt->close();

    // 9. INSERT REPLY (using prepared statement)
    $reply_stmt = $mysqli->prepare(
        "INSERT INTO contact_replies (contact_id, admin_id, reply_message, created_at) 
         VALUES (?, ?, ?, NOW())"
    );
    if (!$reply_stmt) {
        throw new Exception("Database error");
    }

    $reply_stmt->bind_param("iis", $contact_id, $admin_id, $reply_message);
    if (!$reply_stmt->execute()) {
        $reply_stmt->close();
        throw new Exception("Reply submission failed");
    }
    $reply_stmt->close();

    // 10. UPDATE CONTACT STATUS (using prepared statement)
    $status_stmt = $mysqli->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?");
    if ($status_stmt) {
        $status_stmt->bind_param("i", $contact_id);
        if (!$status_stmt->execute()) {
            error_log("Warning: Failed to update contact status: " . $status_stmt->error);
        }
        $status_stmt->close();
    }

    // 11. GET ADMIN NAME (for response)
    $admin_name = "Administrator";
    $admin_query = $mysqli->prepare("SELECT name FROM admin_users WHERE id = ?");
    if ($admin_query) {
        $admin_query->bind_param("i", $admin_id);
        $admin_query->execute();
        $admin_result = $admin_query->get_result();
        
        if ($admin_result && $admin_result->num_rows > 0) {
            $admin_data = $admin_result->fetch_assoc();
            $admin_name = htmlspecialchars($admin_data['name'] ?? "Administrator", ENT_QUOTES, 'UTF-8');
        }
        $admin_query->close();
    }

    // 12. SUCCESS RESPONSE
    $response['success'] = true;
    $response['message'] = "Reply sent successfully!";
    $response['admin_name'] = $admin_name;
    $response['reply_message'] = htmlspecialchars($reply_message, ENT_QUOTES, 'UTF-8');

    $mysqli->close();

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Reply contact error: " . $e->getMessage());
}

echo json_encode($response);

