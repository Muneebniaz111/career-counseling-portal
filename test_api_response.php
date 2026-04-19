<?php
session_start();

// Set a fake admin_id for testing
$_SESSION['admin_id'] = 1;

// Get the raw response from the API
ob_start();
require_once __DIR__ . '/admin_settings_api.php';
$output = ob_get_clean();

// Display it
echo "Raw response bytes (first 200 chars): ";
echo bin2hex(substr($output, 0, 200));
echo "\n\n";

echo "First 500 chars of response:\n";
echo htmlspecialchars(substr($output, 0, 500));
echo "\n\n";

echo "Last 100 chars of response:\n";
echo htmlspecialchars(substr($output, -100));
?>
