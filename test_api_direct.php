<?php
// Test the admin_settings_api directly
session_start();
$_SESSION['admin_id'] = 1;

// Simulate the GET request
$_GET['action'] = 'get_profile';

// Capture and output the response
ob_start();
include __DIR__ . '/admin_settings_api.php';
$response = ob_get_clean();

// Display the raw response
echo "Response length: " . strlen($response) . " bytes\n";
echo "First 200 chars:\n";
echo substr($response, 0, 200) . "\n\n";

// Try to parse as JSON
$json = json_decode($response, true);
if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Error: " . json_last_error_msg() . "\n";
    echo "\nRaw response:\n";
    echo htmlspecialchars($response);
} else {
    echo "JSON parsed successfully!\n";
    echo "Success: " . ($json['success'] ? 'true' : 'false') . "\n";
    echo "Message: " . $json['message'] . "\n";
}
?>
