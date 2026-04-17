<?php
/**
 * Resource Download Handler
 * ========================
 * Handles secure downloading of PDF resources
 */

require_once __DIR__ . '/bootstrap.php';

// Check if user is logged in
if (!is_logged_in()) {
    send_json('Unauthorized access', false);
}

// Check if filename is provided
if (!isset($_GET['file'])) {
    send_json('No file specified', false);
}

$filename = basename($_GET['file']); // Prevent directory traversal
$resource_dir = PUBLIC_DIR . '/resources/';
$file_path = $resource_dir . $filename;

// Verify file exists and is within resources directory
if (!file_exists($file_path) || !is_file($file_path)) {
    send_json('File not found', false);
}

// Verify it's a PDF file
if (pathinfo($file_path, PATHINFO_EXTENSION) !== 'pdf') {
    send_json('Invalid file type', false);
}

// Log the download
log_activity('RESOURCE_DOWNLOAD', $filename, get_user_id());

// Prepare download headers
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($file_path));
header('Pragma: no-cache');
header('Expires: 0');

// Send file
readfile($file_path);
exit();
?>

