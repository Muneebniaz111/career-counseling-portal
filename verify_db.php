require "bootstrap.php";
$res = $conn->query("SELECT * FROM contact_messages ORDER BY id DESC LIMIT 1");
print_r($res->fetch_assoc());
