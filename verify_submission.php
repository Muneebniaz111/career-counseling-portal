<?php
include "db_connection.php";
$result = mysqli_query($conn, "SELECT * FROM contact_messages WHERE email = \"test2@example.com\" ORDER BY contact_id DESC LIMIT 1");
$contact = mysqli_fetch_assoc($result);
if ($contact) {
    echo "--- Contact Message Row ---\n";
    print_r($contact);
    $contact_id = $contact["contact_id"];
    
    $admin_count_res = mysqli_query($conn, "SELECT count(*) as count FROM admins");
    $admin_count = mysqli_fetch_assoc($admin_count_res)["count"];
    echo "\nTotal admins: " . $admin_count . "\n";
    
    echo "\n--- Notifications count for contact_id $contact_id ---\n";
    $notif_result = mysqli_query($conn, "SELECT count(*) as count FROM admin_contact_notifications WHERE contact_id = $contact_id");
    $notif_count = mysqli_fetch_assoc($notif_result);
    echo "Count: " . $notif_count["count"] . "\n";
} else {
    echo "No contact message found\n";
} ?>
