<?php
require_once __DIR__ . "/bootstrap.php";

// Check if user is already logged in
if (isset($_SESSION["user_id"])) {
    if (isset($_SESSION["user_type"])) {
        if ($_SESSION["user_type"] === "admin") {
            header("Location: Admin_Dashboard.php");
            exit();
        } else if ($_SESSION["user_type"] === "student") {
            header("Location: Student_Dashboard.php");
            exit();
        }
    }
}

// Not logged in - show home page
$home_file = __DIR__ . "/index.html";
if (file_exists($home_file)) {
    include $home_file;
}
?>
