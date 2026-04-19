<?php
/**
 * Setup API - Handles database initialization
 * Called by setup_database.php
 */

header('Content-Type: application/json');

try {
    // Create connection to MySQL (without selecting database)
    $conn = new mysqli('localhost', 'root', '', '');
    
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }

    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS career_counseling";
    if (!$conn->query($sql)) {
        throw new Exception('Create database failed: ' . $conn->error);
    }

    // Select database
    $conn->select_db('career_counseling');

    // Read and execute the Query.sql file
    $sql_file = __DIR__ . '/database/schema/Query.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception('Query.sql file not found at: ' . $sql_file);
    }

    $sql_content = file_get_contents($sql_file);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(
        array_map('trim', explode(';', $sql_content)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );

    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            if (!$conn->query($statement)) {
                // Some statements may already exist, continue
                if (strpos($conn->error, 'already exists') === false) {
                    throw new Exception('Query execution failed: ' . $conn->error . ' | Query: ' . substr($statement, 0, 100));
                }
            }
        }
    }

    $conn->close();

    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Database setup completed successfully!'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Setup error: ' . $e->getMessage()
    ]);
}
?>
