<?php
header('Content-Type: application/json; charset=utf-8');

$mysqli = new mysqli("localhost", "root", "", "career_counseling");

$response = [
    'success' => false,
    'message' => '',
    'migrations' => []
];

try {
    // Check if status column exists
    $check = $mysqli->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='contact_messages' AND COLUMN_NAME='status'");
    
    if ($check && $check->num_rows === 0) {
        // Status column doesn't exist, add it
        echo "Adding status column to contact_messages...\n";
        
        if ($mysqli->query("ALTER TABLE contact_messages ADD COLUMN status VARCHAR(20) DEFAULT 'open' AFTER phone")) {
            $response['success'] = true;
            $response['migrations'][] = [
                'migration' => 'Add status column to contact_messages',
                'status' => 'success',
                'message' => 'Status column added successfully'
            ];
        } else {
            $response['success'] = false;
            $response['migrations'][] = [
                'migration' => 'Add status column to contact_messages',
                'status' => 'error',
                'message' => $mysqli->error
            ];
        }
    } else {
        $response['success'] = true;
        $response['migrations'][] = [
            'migration' => 'Check status column',
            'status' => 'ok',
            'message' => 'Status column already exists'
        ];
    }
    
    // Verify the column now exists
    $verify = $mysqli->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='contact_messages' AND COLUMN_NAME='status'");
    if ($verify && $verify->num_rows > 0) {
        $response['verification'] = 'Status column now exists in contact_messages';
    } else {
        $response['verification'] = 'Status column still missing - check database permissions';
    }
    
    $response['message'] = 'Migration completed';
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Migration error: ' . $e->getMessage();
}

$mysqli->close();

echo json_encode($response, JSON_PRETTY_PRINT);
?>
