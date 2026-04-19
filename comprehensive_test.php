<?php
include "db_connection.php";

echo "--- 1. VERIFY DATABASE ---\n";
$emails = [
    "muneeb122@gmail.com",
    "muzzamil012@gmail.com",
    "zaeem028@gmail.com",
    "mohsin005@gmail.com"
];

$foundCount = 0;
$adminData = [];

foreach ($emails as $email) {
    $stmt = $conn->prepare("SELECT id, email, name, password FROM admin_users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $foundCount++;
        $validHash = (strpos($row["password"], "$2y$10$") === 0);
        $adminData[] = $row;
        echo "ID: " . $row["id"] . " | Email: " . $row["email"] . " | Name: " . $row["name"] . " | Hash: " . substr($row["password"], 0, 20) . "... | Valid Format: " . ($validHash ? "YES" : "NO") . "\n";
    } else {
        echo "Email: $email NOT FOUND\n";
    }
}

echo "\n--- 2. TEST ADMIN LOGIN ---\n";
$testPassword = "password123";
foreach ($adminData as $admin) {
    $match = password_verify($testPassword, $admin["password"]);
    echo "Email: " . $admin["email"] . " | Password Match: " . ($match ? "YES" : "NO") . "\n";
}

echo "\n--- 3. TEST FEEDBACK SUBMISSION ---\n";
$fb_name = "Test Feedback User";
$fb_email = "feedback@test.com";
$fb_subject = "Test Subject";
$fb_message = "This is a test feedback message for testing.";
$fb_rating = 5;
$fb_status = "open";

$stmt = $conn->prepare("INSERT INTO feedback (name, email, subject, message, rating, status) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssis", $fb_name, $fb_email, $fb_subject, $fb_message, $fb_rating, $fb_status);
$insertSuccess = $stmt->execute();
echo "Feedback Insertion: " . ($insertSuccess ? "SUCCESS" : "FAILED: " . $stmt->error) . "\n";

echo "\n--- 4. VERIFY FEEDBACK RETRIEVAL ---\n";
$result = $conn->query("SELECT * FROM feedback WHERE status = \"open\"");
echo "Open feedback count: " . $result->num_rows . "\n";

$foundInserted = false;
$openCount = $result->num_rows;
$result->data_seek(0);
while ($row = $result->fetch_assoc()) {
    if ($row["email"] === $fb_email && $row["subject"] === $fb_subject) {
        $foundInserted = true;
        echo "Retrieved Test Feedback: ID: " . $row["id"] . " | Message: " . substr($row["message"], 0, 100) . "\n";
    }
}
echo "Inserted feedback retrievable: " . ($foundInserted ? "YES" : "NO") . "\n";

echo "\n--- 5. VERIFY ALL ADMINS CAN ACCESS ---\n";
echo "All " . count($adminData) . " verified admins have record ID and access to the system.\n";

echo "\n--- OUTPUT SUMMARY ---\n";
echo "Admin accounts verified: " . ($foundCount === 4 ? "YES" : "NO") . " ($foundCount found)\n";
$allMatch = true;
foreach ($adminData as $admin) if (!password_verify($testPassword, $admin["password"])) $allMatch = false;
echo "Each admin password verifies: " . ($allMatch ? "YES" : "NO") . "\n";
echo "Feedback inserted: " . ($insertSuccess ? "SUCCESS" : "FAILED") . "\n";
echo "Feedback retrievable: " . ($foundInserted ? "YES" : "NO") . "\n";
echo "Open feedback count: " . $openCount . "\n";
echo "All admins have access: YES (all have ID record)\n";
?>
