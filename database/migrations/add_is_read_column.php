<?php
// Auto-run migration to add is_read column if it doesn't exist
$mysqli = new mysqli("localhost", "root", "", "career_counseling");

if ($mysqli->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $mysqli->connect_error
    ]));
}

header('Content-Type: application/json');

try {
    // Check if is_read column exists
    $check = $mysqli->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='contact_messages' AND COLUMN_NAME='is_read'");
    
    if (!$check || $check->num_rows == 0) {
        // Column doesn't exist, add it
        $alter = $mysqli->query("ALTER TABLE contact_messages ADD COLUMN is_read TINYINT(1) DEFAULT 0");
        
        if ($alter) {
            echo json_encode([
                'success' => true,
                'message' => 'is_read column added successfully to contact_messages table'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add column: ' . $mysqli->error
            ]);
        }
    } else {
        // Column already exists
        echo json_encode([
            'success' => true,
            'message' => 'is_read column already exists'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$mysqli->close();
?>
