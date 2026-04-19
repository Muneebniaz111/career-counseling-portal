<?php require "bootstrap.php"; $res = $conn->query("SELECT COUNT(*) as count FROM admin_users WHERE status = \"active\" OR status IS NULL OR status = \"\""); print_r($res->fetch_assoc()); ?>
