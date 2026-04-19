<?php
/**
 * Database Configuration
 * =====================
 * Central database connection and utility functions
 * Location: app/config/db_connection.php
 */

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "career_counseling";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Sanitize user input
 */
function sanitize($input) {
    global $conn;
    return $conn->real_escape_string(htmlspecialchars(trim($input)));
}

/**
 * Validate email address
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Hash password using BCRYPT
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password against hash
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

?>
