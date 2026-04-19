<?php require "bootstrap.php"; $res = $conn->query("SELECT * FROM admin_contact_notifications ORDER BY id DESC LIMIT 1"); print_r($res->fetch_assoc()); ?>
