<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "db_connection.php";
$res = $conn->query("SELECT id, user_id, status FROM contact_messages");
while($row = $res->fetch_assoc()){
    print_r($row);
}
?>
