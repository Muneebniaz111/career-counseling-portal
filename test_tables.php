<?php
require 'db_connection.php';
$res = $conn->query('SHOW TABLES');
if($res) {
    while($row = $res->fetch_array()) {
        echo $row[0] . PHP_EOL;
    }
} else {
    echo $conn->error;
}
?>
