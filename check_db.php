<?php
include "db_connection.php";
$res = $conn->query("DESCRIBE admin_users");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
