<?php
/**
 * Admin Settings API Backend
 * Handles all settings operations: profile, security, notifications, appearance
 * Implements secure validation, error handling, and state persistence
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Allow CORS for same origin
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/bootstrap.php';

// CRITICAL FIX: Use $conn from bootstrap.php or create $mysqli alias
$mysqli = $conn ?? new mysqli("localhost", "root", "", "career_counseling");

// Check connection
if ($mysqli->connect_error) {
    http_response_code(500);
    $response['message'] = 'Database connection failed: ' . $mysqli->connect_error;
    echo json_encode($response);
    exit();
}

// Initialize response structure
$response = [
    'success' => false,
    'message' => '',
    'data' => null,
    'timestamp' => date('Y-m-d H:i:s')
];

// Verify admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    $response['message'] = 'Unauthorized. Please login first.';
    echo json_encode($response);
    exit();
}

$admin_id = $_SESSION['admin_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Error logging function
function log_error($message) {
    $log_file = __DIR__ . '/logs/settings_errors.log';
    $dir = dirname($log_file);
    
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $log_message = "[" . date('Y-m-d H:i:s') . "] [Admin: {$GLOBALS['admin_id']}] " . $message . "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

try {
    switch ($action) {
        // ===== PROFILE OPERATIONS =====
        case 'get_profile':
            handle_get_profile($mysqli, $admin_id, $response);
            break;

        case 'update_profile':
            handle_update_profile($mysqli, $admin_id, $response);
            break;

        // ===== SECURITY OPERATIONS =====
        case 'change_password':
            handle_change_password($mysqli, $admin_id, $response);
            break;

        case 'get_sessions':
            handle_get_sessions($admin_id, $response);
            break;

        case 'logout_all_sessions':
            handle_logout_all_sessions($mysqli, $admin_id, $response);
            break;

        case 'toggle_2fa':
            handle_toggle_2fa($mysqli, $admin_id, $response);
            break;

        // ===== NOTIFICATION OPERATIONS =====
        case 'get_notifications':
            handle_get_notifications($mysqli, $admin_id, $response);
            break;

        case 'update_notifications':
            handle_update_notifications($mysqli, $admin_id, $response);
            break;

        // ===== APPEARANCE OPERATIONS =====
        case 'get_appearance':
            handle_get_appearance($mysqli, $admin_id, $response);
            break;

        case 'update_appearance':
            handle_update_appearance($mysqli, $admin_id, $response);
            break;

        case 'set_theme':
            handle_set_theme($mysqli, $admin_id, $response);
            break;

        case 'set_accent_color':
            handle_set_accent_color($mysqli, $admin_id, $response);
            break;

        default:
            http_response_code(400);
            $response['message'] = 'Invalid action: ' . htmlspecialchars($action);
    }
} catch (Exception $e) {
    http_response_code(500);
    $response['success'] = false;
    $response['message'] = $e->getMessage(); // Return actual error message for debugging
    log_error('Exception: ' . $e->getMessage());
}

echo json_encode($response);
exit();

// ===== PROFILE HANDLERS =====

function handle_get_profile(&$mysqli, $admin_id, &$response) {
    $query = "SELECT 
        id, name, email, created_at
        FROM admin_users 
        WHERE id = ?";
    
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $mysqli->error);
    }
    
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Admin profile not found");
    }
    
    $profile = $result->fetch_assoc();
    $response['success'] = true;
    $response['message'] = 'Profile retrieved successfully';
    $response['data'] = $profile;
}

function handle_update_profile(&$mysqli, $admin_id, &$response) {
    // Validate input
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    $errors = [];
    
    if (empty($fullname) || strlen($fullname) < 2) {
        $errors[] = 'Full name must be at least 2 characters';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        $response['message'] = implode('; ', $errors);
        return;
    }
    
    // Check if email already exists (for another admin)
    $check_email = $mysqli->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
    $check_email->bind_param("si", $email, $admin_id);
    $check_email->execute();
    
    if ($check_email->get_result()->num_rows > 0) {
        http_response_code(400);
        $response['message'] = 'Email already in use by another admin';
        return;
    }
    
    // Update profile - only update columns that exist
    $query = "UPDATE admin_users 
        SET name = ?, email = ?
        WHERE id = ?";
    
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $mysqli->error);
    }
    
    $stmt->bind_param("ssi", $fullname, $email, $admin_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Update failed: " . $stmt->error);
    }
    
    // Update session data
    $_SESSION['admin_name'] = $fullname;
    
    $response['success'] = true;
    $response['message'] = 'Profile updated successfully';
    $response['data'] = [
        'name' => $fullname,
        'email' => $email,
        'phone' => $phone,
        'bio' => $bio
    ];
}

// ===== SECURITY HANDLERS =====

function handle_change_password(&$mysqli, $admin_id, &$response) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate
    if (empty($current_password) || empty($new_password)) {
        http_response_code(400);
        $response['message'] = 'All password fields are required';
        return;
    }
    
    if ($new_password !== $confirm_password) {
        http_response_code(400);
        $response['message'] = 'New passwords do not match';
        return;
    }
    
    if (strlen($new_password) < 8) {
        http_response_code(400);
        $response['message'] = 'New password must be at least 8 characters';
        return;
    }
    
    if (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        http_response_code(400);
        $response['message'] = 'Password must contain uppercase and numbers';
        return;
    }
    
    // Get current password hash
    $stmt = $mysqli->prepare("SELECT password FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Admin not found");
    }
    
    $user = $result->fetch_assoc();
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        http_response_code(401);
        $response['message'] = 'Current password is incorrect';
        return;
    }
    
    // Hash and update new password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    
    $update_stmt = $mysqli->prepare("UPDATE admin_users SET password = ?, updated_at = NOW() WHERE id = ?");
    $update_stmt->bind_param("si", $hashed_password, $admin_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Password update failed");
    }
    
    $response['success'] = true;
    $response['message'] = 'Password changed successfully';
}

function handle_get_sessions($admin_id, &$response) {
    // Return session info (simplified)
    $response['success'] = true;
    $response['message'] = 'Sessions retrieved';
    $response['data'] = [
        'sessions' => [
            [
                'device' => 'Current Device',
                'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                'last_activity' => date('Y-m-d H:i:s'),
                'status' => 'Active'
            ]
        ]
    ];
}

function handle_logout_all_sessions(&$mysqli, $admin_id, &$response) {
    // Destroy current session
    session_destroy();
    
    $response['success'] = true;
    $response['message'] = 'All sessions have been terminated';
    $response['data'] = ['redirect' => 'Log-in (Admin).php'];
}

function handle_toggle_2fa(&$mysqli, $admin_id, &$response) {
    $enabled = $_POST['enabled'] === 'true' || $_POST['enabled'] === '1';
    
    // Update 2FA setting in database
    $stmt = $mysqli->prepare("UPDATE admin_users SET two_factor_enabled = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ii", $enabled, $admin_id);
    
    if (!$stmt->execute()) {
        throw new Exception("2FA toggle failed");
    }
    
    $response['success'] = true;
    $response['message'] = '2FA setting updated successfully';
    $response['data'] = ['two_factor_enabled' => (bool)$enabled];
}

// ===== NOTIFICATION HANDLERS =====

function handle_get_notifications(&$mysqli, $admin_id, &$response) {
    $query = "SELECT 
        contact_messages, contact_replies, feedback_notifications,
        feedback_responses, system_updates, security_alerts
        FROM admin_notifications
        WHERE admin_id = ?";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Create default notification preferences
        create_default_notifications($mysqli, $admin_id);
        $result = $stmt->get_result();
    }
    
    $notifications = $result->fetch_assoc();
    $response['success'] = true;
    $response['message'] = 'Notification settings retrieved';
    $response['data'] = $notifications;
}

function handle_update_notifications(&$mysqli, $admin_id, &$response) {
    $preferences = [
        'contact_messages' => isset($_POST['contact_messages']),
        'contact_replies' => isset($_POST['contact_replies']),
        'feedback_notifications' => isset($_POST['feedback_notifications']),
        'feedback_responses' => isset($_POST['feedback_responses']),
        'system_updates' => isset($_POST['system_updates']),
        'security_alerts' => isset($_POST['security_alerts'])
    ];
    
    // Check if preferences exist
    $check = $mysqli->prepare("SELECT id FROM admin_notifications WHERE admin_id = ?");
    $check->bind_param("i", $admin_id);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        create_default_notifications($mysqli, $admin_id);
    }
    
    // Update preferences
    $update = $mysqli->prepare("UPDATE admin_notifications 
        SET contact_messages = ?, contact_replies = ?, feedback_notifications = ?,
            feedback_responses = ?, system_updates = ?, security_alerts = ?,
            updated_at = NOW()
        WHERE admin_id = ?");
    
    $update->bind_param(
        "iiiiiii",
        $preferences['contact_messages'],
        $preferences['contact_replies'],
        $preferences['feedback_notifications'],
        $preferences['feedback_responses'],
        $preferences['system_updates'],
        $preferences['security_alerts'],
        $admin_id
    );
    
    if (!$update->execute()) {
        throw new Exception("Notification update failed");
    }
    
    $response['success'] = true;
    $response['message'] = 'Notification preferences updated successfully';
    $response['data'] = $preferences;
}

// ===== APPEARANCE HANDLERS =====

function handle_get_appearance(&$mysqli, $admin_id, &$response) {
    $query = "SELECT theme, accent_color, animations_enabled 
        FROM admin_preferences 
        WHERE admin_id = ?";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Create default preferences (light mode as default)
        create_default_preferences($mysqli, $admin_id);
        $result = $stmt->get_result();
    }
    
    $appearance = $result->fetch_assoc();
    $response['success'] = true;
    $response['message'] = 'Appearance settings retrieved';
    $response['data'] = $appearance;
}

function handle_update_appearance(&$mysqli, $admin_id, &$response) {
    $theme = $_POST['theme'] ?? 'light';
    $accent_color = $_POST['accent_color'] ?? '#800080';
    $animations = isset($_POST['animations']);
    
    // Validate theme
    if (!in_array($theme, ['light', 'dark', 'auto'])) {
        http_response_code(400);
        $response['message'] = 'Invalid theme value';
        return;
    }
    
    // Validate accent color (basic hex validation)
    if (!preg_match('/^#[0-9A-F]{6}$/i', $accent_color)) {
        http_response_code(400);
        $response['message'] = 'Invalid color format';
        return;
    }
    
    // Check if preferences exist
    $check = $mysqli->prepare("SELECT id FROM admin_preferences WHERE admin_id = ?");
    $check->bind_param("i", $admin_id);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        create_default_preferences($mysqli, $admin_id);
    }
    
    // Update preferences
    $update = $mysqli->prepare("UPDATE admin_preferences 
        SET theme = ?, accent_color = ?, animations_enabled = ?, updated_at = NOW()
        WHERE admin_id = ?");
    
    $update->bind_param("ssii", $theme, $accent_color, $animations, $admin_id);
    
    if (!$update->execute()) {
        throw new Exception("Appearance update failed");
    }
    
    // Update session
    $_SESSION['user_theme'] = $theme;
    $_SESSION['accent_color'] = $accent_color;
    
    $response['success'] = true;
    $response['message'] = 'Appearance updated successfully';
    $response['data'] = [
        'theme' => $theme,
        'accent_color' => $accent_color,
        'animations_enabled' => (bool)$animations
    ];
}

function handle_set_theme(&$mysqli, $admin_id, &$response) {
    $theme = $_POST['theme'] ?? 'light';
    
    if (!in_array($theme, ['light', 'dark', 'auto'])) {
        http_response_code(400);
        $response['message'] = 'Invalid theme';
        return;
    }
    
    $check = $mysqli->prepare("SELECT id FROM admin_preferences WHERE admin_id = ?");
    $check->bind_param("i", $admin_id);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        create_default_preferences($mysqli, $admin_id);
    }
    
    $update = $mysqli->prepare("UPDATE admin_preferences SET theme = ?, updated_at = NOW() WHERE admin_id = ?");
    $update->bind_param("si", $theme, $admin_id);
    $update->execute();
    
    $_SESSION['user_theme'] = $theme;
    
    $response['success'] = true;
    $response['message'] = 'Theme updated to ' . $theme;
    $response['data'] = ['theme' => $theme];
}

function handle_set_accent_color(&$mysqli, $admin_id, &$response) {
    $color = $_POST['color'] ?? '#800080';
    
    if (!preg_match('/^#[0-9A-F]{6}$/i', $color)) {
        http_response_code(400);
        $response['message'] = 'Invalid color format';
        return;
    }
    
    $check = $mysqli->prepare("SELECT id FROM admin_preferences WHERE admin_id = ?");
    $check->bind_param("i", $admin_id);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        create_default_preferences($mysqli, $admin_id);
    }
    
    $update = $mysqli->prepare("UPDATE admin_preferences SET accent_color = ?, updated_at = NOW() WHERE admin_id = ?");
    $update->bind_param("si", $color, $admin_id);
    $update->execute();
    
    $response['success'] = true;
    $response['message'] = 'Accent color updated';
    $response['data'] = ['accent_color' => $color];
}

// ===== HELPER FUNCTIONS =====

function create_default_notifications(&$mysqli, $admin_id) {
    $insert = $mysqli->prepare("INSERT INTO admin_notifications 
        (admin_id, contact_messages, contact_replies, feedback_notifications, 
         feedback_responses, system_updates, security_alerts, created_at, updated_at)
        VALUES (?, 1, 1, 1, 1, 1, 1, NOW(), NOW())");
    $insert->bind_param("i", $admin_id);
    $insert->execute();
}

function create_default_preferences(&$mysqli, $admin_id) {
    $theme = 'light'; // Default theme is LIGHT
    $color = '#800080';
    $animations = 1;
    
    $insert = $mysqli->prepare("INSERT INTO admin_preferences 
        (admin_id, theme, accent_color, animations_enabled, created_at, updated_at)
        VALUES (?, ?, ?, ?, NOW(), NOW())");
    $insert->bind_param("issi", $admin_id, $theme, $color, $animations);
    $insert->execute();
}

