<?php
session_start();

// Simulate an admin session
$_SESSION['admin_id'] = 1;
$_GET['action'] = 'get_profile';

// Capture the API response
ob_start();
include __DIR__ . '/admin_settings_api.php';
$response = ob_get_clean();

// Parse and display
$json = json_decode($response, true);

echo "=== API Response Test ===\n";
echo "Response Length: " . strlen($response) . " bytes\n\n";

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ JSON Parse Error: " . json_last_error_msg() . "\n";
    echo "Raw Response:\n" . htmlspecialchars($response) . "\n";
} else {
    echo "✅ JSON parsed successfully!\n";
    echo "Success: " . ($json['success'] ? 'true' : 'false') . "\n";
    echo "Message: " . $json['message'] . "\n\n";
    
    if ($json['success'] && $json['data']) {
        echo "Profile Data:\n";
        echo "  ID: " . $json['data']['id'] . "\n";
        echo "  Name: " . $json['data']['name'] . "\n";
        echo "  Email: " . $json['data']['email'] . "\n";
        echo "  Created: " . $json['data']['created_at'] . "\n";
    }
}
?>
