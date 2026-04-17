<?php
/**
 * Common Helper Functions
 * =====================
 * Reusable utility functions for the application
 */

/**
 * Redirect to a page
 * @param string $location URL to redirect to
 */
function redirect($location) {
    header("Location: $location");
    exit();
}

/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool
 */
function is_admin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

/**
 * Check if user is student
 * @return bool
 */
function is_student() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student';
}

/**
 * Get current logged in user ID
 * @return int|null
 */
function get_user_id() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Get current logged in user email
 * @return string|null
 */
function get_user_email() {
    return isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null;
}

/**
 * Format date for display
 * @param string $date Database date string
 * @return string Formatted date
 */
function format_date($date) {
    if (empty($date)) return 'N/A';
    $timestamp = strtotime($date);
    return date('M d, Y h:i A', $timestamp);
}

/**
 * Generate CSRF token
 * @return string
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token input field for forms
 * @return string HTML input element
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Check if request is AJAX
 * @return bool
 */
function is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
}

/**
 * Send JSON response
 * @param mixed $data Data to send
 * @param bool $success Success status
 */
function send_json($data, $success = true) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

/**
 * Sanitize output for display
 * @param string $text Text to sanitize
 * @return string
 */
function sanitize_output($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Truncate text to specified length
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $ellipsis Ellipsis character
 * @return string
 */
function truncate($text, $length = 100, $ellipsis = '...') {
    if (strlen($text) > $length) {
        return substr($text, 0, $length - strlen($ellipsis)) . $ellipsis;
    }
    return $text;
}

/**
 * Convert file size to human readable format
 * @param int $bytes File size in bytes
 * @return string
 */
function format_bytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Validate password strength
 * @param string $password Password to validate
 * @return array ['valid' => bool, 'errors' => array]
 */
function validate_password($password) {
    $errors = [];
    
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain uppercase letter';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain lowercase letter';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain number';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

?>
