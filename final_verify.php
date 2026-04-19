<?php
include "db_connection.php";
$res = mysqli_query($conn, "SELECT id, status FROM contact_messages WHERE email = \"test2@example.com\" ORDER BY id DESC LIMIT 1");
$contact = mysqli_fetch_assoc($res);
if ($contact) {
    echo "ID: " . $contact["id"] . "\n";
    echo "Status: " . $contact["status"] . "\n";
    $cid = $contact["id"];
    $res2 = mysqli_query($conn, "SELECT count(*) as count FROM admin_contact_notifications WHERE contact_id = $cid");
    echo "Notification Count: " . mysqli_fetch_assoc($res2)["count"] . "\n";
} else {
    echo "Not found\n";
}
?>