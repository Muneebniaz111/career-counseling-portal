<?php
$mysqli = new mysqli("localhost", "root", "", "career_counseling");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Creating admin_preferences table...\n";

$sql = "CREATE TABLE IF NOT EXISTS admin_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL UNIQUE,
    theme VARCHAR(20) DEFAULT 'light',
    accent_color VARCHAR(7) DEFAULT '#800080',
    animations_enabled BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
)";

if ($mysqli->query($sql)) {
    echo "✅ Table created successfully!\n";
} else {
    echo "❌ Error: " . $mysqli->error . "\n";
}

$mysqli->close();
?>
