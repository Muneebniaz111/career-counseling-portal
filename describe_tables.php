<?php
include "db_connection.php";
echo "--- contact_messages ---\n";
$result = mysqli_query($conn, "DESCRIBE contact_messages");
while ($row = mysqli_fetch_assoc($result)) { echo $row["Field"] . "(" . $row["Type"] . ")\n"; }
echo "\n--- admin_contact_notifications ---\n";
$result2 = mysqli_query($conn, "DESCRIBE admin_contact_notifications");
while ($row = mysqli_fetch_assoc($result2)) { echo $row["Field"] . "(" . $row["Type"] . ")\n"; }
?>
