<?php
/**
 * Database Schema Migration for Admin Settings
 * Creates necessary tables for admin profile, preferences, and notifications
 * Run this script once to set up the database structure
 */

include 'db_connection.php';

$response = [
    'success' => false,
    'migrations' => [],
    'errors' => []
];

try {
    // ===== MIGRATION 1: AddColumns to admin_users table =====
    $migration_name = "Add settings columns to admin_users";
    
    // Check if columns exist
    $check = $mysqli->query("SHOW COLUMNS FROM admin_users LIKE 'phone'");
    
    if ($check->num_rows === 0) {
        // Column doesn't exist, add it
        $migrations = [
            "ALTER TABLE admin_users ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER email",
            "ALTER TABLE admin_users ADD COLUMN bio TEXT DEFAULT NULL AFTER phone",
            "ALTER TABLE admin_users ADD COLUMN avatar_url VARCHAR(255) DEFAULT NULL AFTER bio",
            "ALTER TABLE admin_users ADD COLUMN two_factor_enabled BOOLEAN DEFAULT FALSE AFTER avatar_url",
            "ALTER TABLE admin_users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
        ];
        
        foreach ($migrations as $sql) {
            if (!$mysqli->query($sql)) {
                throw new Exception("Failed to add column: " . $mysqli->error);
            }
        }
        
        $response['migrations'][] = [
            'migration' => $migration_name,
            'status' => 'success',
            'details' => 'Added phone, bio, avatar_url, two_factor_enabled, updated_at columns'
        ];
    } else {
        $response['migrations'][] = [
            'migration' => $migration_name,
            'status' => 'skipped',
            'details' => 'Columns already exist'
        ];
    }
    
    // ===== MIGRATION 2: Create admin_preferences table =====
    $migration_name = "Create admin_preferences table";
    
    $check = $mysqli->query("SHOW TABLES LIKE 'admin_preferences'");
    
    if ($check->num_rows === 0) {
        $create_table = "CREATE TABLE IF NOT EXISTS admin_preferences (
            id INT PRIMARY KEY AUTO_INCREMENT,
            admin_id INT NOT NULL UNIQUE,
            theme VARCHAR(20) DEFAULT 'light' COMMENT 'light, dark, or auto',
            accent_color VARCHAR(7) DEFAULT '#800080' COMMENT 'Hex color code',
            animations_enabled BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE,
            INDEX idx_admin_id (admin_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (!$mysqli->query($create_table)) {
            throw new Exception("Failed to create admin_preferences table: " . $mysqli->error);
        }
        
        $response['migrations'][] = [
            'migration' => $migration_name,
            'status' => 'success',
            'details' => 'Table created with theme, accent_color, and animations_enabled columns'
        ];
    } else {
        $response['migrations'][] = [
            'migration' => $migration_name,
            'status' => 'skipped',
            'details' => 'Table already exists'
        ];
    }
    
    // ===== MIGRATION 3: Create admin_notifications table =====
    $migration_name = "Create admin_notifications table";
    
    $check = $mysqli->query("SHOW TABLES LIKE 'admin_notifications'");
    
    if ($check->num_rows === 0) {
        $create_table = "CREATE TABLE IF NOT EXISTS admin_notifications (
            id INT PRIMARY KEY AUTO_INCREMENT,
            admin_id INT NOT NULL UNIQUE,
            contact_messages BOOLEAN DEFAULT TRUE,
            contact_replies BOOLEAN DEFAULT TRUE,
            feedback_notifications BOOLEAN DEFAULT TRUE,
            feedback_responses BOOLEAN DEFAULT TRUE,
            system_updates BOOLEAN DEFAULT TRUE,
            security_alerts BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE,
            INDEX idx_admin_id (admin_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (!$mysqli->query($create_table)) {
            throw new Exception("Failed to create admin_notifications table: " . $mysqli->error);
        }
        
        $response['migrations'][] = [
            'migration' => $migration_name,
            'status' => 'success',
            'details' => 'Table created with notification preference columns'
        ];
    } else {
        $response['migrations'][] = [
            'migration' => $migration_name,
            'status' => 'skipped',
            'details' => 'Table already exists'
        ];
    }
    
    // ===== MIGRATION 4: Create admin_sessions table (optional, for session tracking) =====
    $migration_name = "Create admin_sessions table";
    
    $check = $mysqli->query("SHOW TABLES LIKE 'admin_sessions'");
    
    if ($check->num_rows === 0) {
        $create_table = "CREATE TABLE IF NOT EXISTS admin_sessions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            admin_id INT NOT NULL,
            session_id VARCHAR(255) UNIQUE,
            ip_address VARCHAR(45),
            user_agent TEXT,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE,
            INDEX idx_admin_id (admin_id),
            INDEX idx_session_id (session_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (!$mysqli->query($create_table)) {
            throw new Exception("Failed to create admin_sessions table: " . $mysqli->error);
        }
        
        $response['migrations'][] = [
            'migration' => $migration_name,
            'status' => 'success',
            'details' => 'Table created for session tracking'
        ];
    } else {
        $response['migrations'][] = [
            'migration' => $migration_name,
            'status' => 'skipped',
            'details' => 'Table already exists'
        ];
    }
    
    // ===== Populate default preferences and notifications for existing admins =====
    $migration_name = "Populate default preferences and notifications";
    
    $admins = $mysqli->query("SELECT id FROM admin_users");
    $populated_count = 0;
    
    while ($admin = $admins->fetch_assoc()) {
        $admin_id = $admin['id'];
        
        // Check if preferences exist
        $check_pref = $mysqli->prepare("SELECT id FROM admin_preferences WHERE admin_id = ?");
        $check_pref->bind_param("i", $admin_id);
        $check_pref->execute();
        
        if ($check_pref->get_result()->num_rows === 0) {
            $insert_pref = $mysqli->prepare("INSERT INTO admin_preferences 
                (admin_id, theme, accent_color, animations_enabled, created_at, updated_at)
                VALUES (?, 'light', '#800080', 1, NOW(), NOW())");
            $insert_pref->bind_param("i", $admin_id);
            $insert_pref->execute();
            $populated_count++;
        }
        
        // Check if notifications exist
        $check_notif = $mysqli->prepare("SELECT id FROM admin_notifications WHERE admin_id = ?");
        $check_notif->bind_param("i", $admin_id);
        $check_notif->execute();
        
        if ($check_notif->get_result()->num_rows === 0) {
            $insert_notif = $mysqli->prepare("INSERT INTO admin_notifications 
                (admin_id, contact_messages, contact_replies, feedback_notifications,
                 feedback_responses, system_updates, security_alerts, created_at, updated_at)
                VALUES (?, 1, 1, 1, 1, 1, 1, NOW(), NOW())");
            $insert_notif->bind_param("i", $admin_id);
            $insert_notif->execute();
        }
    }
    
    $response['migrations'][] = [
        'migration' => $migration_name,
        'status' => 'success',
        'details' => "Default preferences and notifications created for {$populated_count} admin(s)"
    ];
    
    // ===== Verification =====
    $response['verification'] = [
        'admin_users_columns' => [],
        'admin_preferences_exists' => false,
        'admin_notifications_exists' => false,
        'admin_sessions_exists' => false
    ];
    
    // Verify columns in admin_users
    $columns = $mysqli->query("SHOW COLUMNS FROM admin_users");
    while ($col = $columns->fetch_assoc()) {
        $response['verification']['admin_users_columns'][] = $col['Field'];
    }
    
    // Verify tables exist
    $tables = $mysqli->query("SHOW TABLES LIKE 'admin_preferences'");
    $response['verification']['admin_preferences_exists'] = $tables->num_rows > 0;
    
    $tables = $mysqli->query("SHOW TABLES LIKE 'admin_notifications'");
    $response['verification']['admin_notifications_exists'] = $tables->num_rows > 0;
    
    $tables = $mysqli->query("SHOW TABLES LIKE 'admin_sessions'");
    $response['verification']['admin_sessions_exists'] = $tables->num_rows > 0;
    
    $response['success'] = true;
    $response['message'] = 'Database migration completed successfully';
    
} catch (Exception $e) {
    $response['errors'][] = $e->getMessage();
    $response['message'] = 'Migration failed: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
