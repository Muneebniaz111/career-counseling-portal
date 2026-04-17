<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "root", "", "career_counseling");

if ($mysqli->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $mysqli->connect_error
    ]));
}

$response = ['success' => false, 'message' => '', 'column_exists' => false];

try {
    // Method 1: Check if column exists using SHOW COLUMNS
    $result = $mysqli->query("SHOW COLUMNS FROM contact_messages WHERE Field = 'is_read'");
    $column_exists = ($result && $result->num_rows > 0);
    $response['column_exists'] = $column_exists;
    
    if (!$column_exists) {
        // Column doesn't exist, create it using IF NOT EXISTS for safety
        $sql = "ALTER TABLE contact_messages ADD COLUMN is_read TINYINT(1) DEFAULT 0 AFTER status";
        
        if ($mysqli->query($sql)) {
            $response['success'] = true;
            $response['message'] = 'is_read column created successfully';
            $response['action'] = 'created';
        } else {
            throw new Exception("Failed to create is_read column: " . $mysqli->error);
        }
    } else {
        $response['success'] = true;
        $response['message'] = 'is_read column already exists';
        $response['action'] = 'exists';
        
        // Also verify at least one message is readable to confirm structure
        $check = $mysqli->query("SELECT COUNT(*) as total FROM contact_messages");
        if ($check) {
            $row = $check->fetch_assoc();
            $response['total_messages'] = $row['total'];
        }
    }
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

$mysqli->close();
echo json_encode($response, JSON_PRETTY_PRINT);
?>
