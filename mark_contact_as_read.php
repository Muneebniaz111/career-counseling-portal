<?php
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User not authenticated");
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    $contact_id = isset($_POST['contact_id']) ? intval($_POST['contact_id']) : 0;
    $user_id = $_SESSION['user_id'];

    if (empty($contact_id)) {
        throw new Exception("Contact ID is required");
    }

    // CRITICAL FIX: Add CSRF token validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        throw new Exception("CSRF token validation failed");
    }

    // Database connection
    $mysqli = new mysqli("localhost", "root", "", "career_counseling");
    if ($mysqli->connect_error) {
        throw new Exception("Database connection failed: " . $mysqli->connect_error);
    }

    // Verify the message belongs to the user (using prepared statement)
    $verify_stmt = $mysqli->prepare("SELECT user_id FROM contact_messages WHERE id = ? AND user_id = ?");
    if (!$verify_stmt) {
        throw new Exception("Database error");
    }
    
    $verify_stmt->bind_param("ii", $contact_id, $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows === 0) {
        $verify_stmt->close();
        throw new Exception("Unauthorized: Message not found or does not belong to you");
    }
    
    $message = $verify_result->fetch_assoc();
    $verify_stmt->close();

    // Check if is_read column exists and create if needed
    $has_is_read_column = false;
    try {
        $check_column = $mysqli->query("SHOW COLUMNS FROM contact_messages LIKE 'is_read'");
        if (!$check_column) {
            // Query might not be supported, try alternative method
            $check_column = $mysqli->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='contact_messages' AND COLUMN_NAME='is_read'");
        }
        $has_is_read_column = ($check_column && $check_column->num_rows > 0);
    } catch (Exception $e) {
        error_log("Column check error: " . $e->getMessage());
    }
    
    // If column doesn't exist, create it
    if (!$has_is_read_column) {
        try {
            // Add column with IF NOT EXISTS approach (safe for repetitive calls)
            $alter_result = @$mysqli->query("ALTER TABLE contact_messages ADD COLUMN is_read TINYINT(1) DEFAULT 0");
            
            if (!$alter_result && strpos($mysqli->error, 'Duplicate') === false && !empty($mysqli->error)) {
                throw new Exception("Failed to create is_read column: " . $mysqli->error);
            }
            
            // Verify column now exists
            $recheck = $mysqli->query("SHOW COLUMNS FROM contact_messages LIKE 'is_read'");
            $has_is_read_column = ($recheck && $recheck->num_rows > 0);
            
            if (!$has_is_read_column) {
                throw new Exception("Column still does not exist after creation attempt");
            }
        } catch (Exception $e) {
            throw new Exception("Column creation failed: " . $e->getMessage());
        }
    }

    // Mark message as read using prepared statement for security
    $update_stmt = $mysqli->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ? AND user_id = ?");
    if (!$update_stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }
    
    if (!$update_stmt->bind_param("ii", $contact_id, $user_id)) {
        throw new Exception("Bind failed: " . $update_stmt->error);
    }
    
    if (!$update_stmt->execute()) {
        throw new Exception("Update failed: " . $update_stmt->error);
    }
    
    $update_stmt->close();
    
    // Verify the update was successful
    $verify_stmt = $mysqli->prepare("SELECT is_read FROM contact_messages WHERE id = ? AND user_id = ?");
    if ($verify_stmt) {
        $verify_stmt->bind_param("ii", $contact_id, $user_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result && $verify_result->num_rows > 0) {
            $row = $verify_result->fetch_assoc();
            if (!$row['is_read']) {
                error_log("Warning: is_read is still 0 after update for contact_id: " . $contact_id);
                throw new Exception("Update verification failed: is_read is still 0");
            }
        }
        $verify_stmt->close();
    }

    $response['success'] = true;
    $response['message'] = "Message marked as read successfully";

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Mark as read error: " . $e->getMessage());
}

echo json_encode($response);

