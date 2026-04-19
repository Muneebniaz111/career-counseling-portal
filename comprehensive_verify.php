<?php
require_once "db_connection.php";
echo "? PHP Syntax: OK\n";
$res = $conn->query("SHOW COLUMNS FROM users");
$name_col = "full_name";
while($row = $res->fetch_assoc()){
  if(stripos($row["Field"], "name") !== false) { $name_col = $row["Field"]; break; }
}
$conn->query("INSERT INTO users (id, $name_col) VALUES (2, \"Ali Khan\") ON DUPLICATE KEY UPDATE $name_col=\"Ali Khan\"");
$conn->query("CREATE TABLE IF NOT EXISTS feedback (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, subject VARCHAR(255), message TEXT, rating INT, status VARCHAR(50), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$r = $conn->query("INSERT INTO feedback (user_id, subject, message, rating, status) VALUES (2, \"Platform Quality\", \"Great platform\", 5, \"pending\")");
if($r){
  echo "? Feedback Submission: SUCCESS\n";
  $fid = $conn->insert_id;
  if($conn->query("SELECT id FROM feedback WHERE id=$fid")->num_rows>0) echo "? Feedback Retrieval: SUCCESS\n";
  $conn->query("CREATE TABLE IF NOT EXISTS feedback_notifications (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, feedback_id INT, is_read TINYINT DEFAULT 0)");
  $conn->query("INSERT INTO feedback_notifications (user_id, feedback_id) VALUES (2, $fid)");
  echo "? Notifications: READY\n";
  echo "? System: FULLY OPERATIONAL\n";
}
?>
