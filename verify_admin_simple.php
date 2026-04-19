<?php require "bootstrap.php"; $res = $conn->query("SELECT COUNT(*) as count FROM admin_users"); print_r($res->fetch_assoc()); ?>
