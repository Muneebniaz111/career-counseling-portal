<?php
session_start();
require_once "bootstrap.php";
$_SESSION["user_id"] = 1;
$_SESSION["name"] = "Test User";
$_SESSION["email"] = "test@example.com";
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
echo "USER_ID: " . $_SESSION["user_id"] . PHP_EOL;
echo "CSRF_TOKEN: " . $_SESSION["csrf_token"] . PHP_EOL;
$_POST["name"] = "Test User";
$_POST["email"] = "test@example.com";
$_POST["subject"] = "Test Message Flow";
$_POST["message"] = "This is a comprehensive test of the complete message flow.";
$_POST["phone"] = "+92-300-1111111";
$_POST["csrf_token"] = $_SESSION["csrf_token"];
$_SERVER["REQUEST_METHOD"] = "POST";
ob_start();
include "contact_form.php";
$response = ob_get_clean();
echo "RESPONSE: " . $response . PHP_EOL;
echo "--- DATABASE CHECKS ---" . PHP_EOL;
$sql = "SELECT * FROM contact_messages WHERE user_id = 1 AND subject = \"Test Message Flow\" ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $msg = $result->fetch_assoc();
    echo "MESSAGE_FOUND: YES" . PHP_EOL;
    echo "MESSAGE_ID: " . $msg["id"] . PHP_EOL;
    $msg_id = $msg["id"];
    $table_check = $conn->query("SHOW TABLES LIKE \"admin_contact_notifications\"");
    if ($table_check->num_rows > 0) {
        $notif_sql = "SELECT count(*) as count FROM admin_contact_notifications WHERE message_id = $msg_id";
        $notif_res = $conn->query($notif_sql);
        $notif_row = $notif_res->fetch_assoc();
        echo "ADMIN_NOTIFICATIONS_COUNT: " . $notif_row["count"] . PHP_EOL;
    }
}
$sql_my = "SELECT count(*) as count FROM contact_messages WHERE user_id = 1";
$res_my = $conn->query($sql_my);
$row_my = $res_my->fetch_assoc();
echo "MY_MESSAGES_COUNT: " . $row_my["count"] . PHP_EOL;
if (isset($msg_id)) {
    $table_check_replies = $conn->query("SHOW TABLES LIKE \"contact_replies\"");
    if ($table_check_replies->num_rows > 0) {
        $reply_sql = "SELECT count(*) as count FROM contact_replies WHERE message_id = $msg_id";
        $reply_res = $conn->query($reply_sql);
        $reply_row = $reply_res->fetch_assoc();
        echo "REPLIES_COUNT: " . $reply_row["count"] . PHP_EOL;
    }
}
?>
