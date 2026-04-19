<?php
/**
 * AUTOMATIC DATABASE SETUP
 * This script creates database, tables, and test data
 * Visit: http://localhost/Career-Counseling-Guide-Portal-master/setup_complete.php
 */

$servername = "localhost";
$username = "root";
$password = "";
$database = "career_counseling";

// Create connection (without database first)
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Database Setup</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        .container { background: white; padding: 20px; max-width: 800px; margin: 0 auto; border-radius: 8px; }
        .step { margin: 15px 0; padding: 10px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        h1 { color: #333; }
        code { background: #f5f5f5; padding: 2px 4px; border-radius: 3px; }
    </style>
</head>
<body>
<div class='container'>
<h1>🗄️ Database Setup</h1>";

// Step 1: Create Database
echo "<div class='step success'>Step 1: Creating database...</div>";
if ($conn->query("CREATE DATABASE IF NOT EXISTS " . $database) === TRUE) {
    echo "<div class='step success'>✅ Database created successfully</div>";
} else {
    echo "<div class='step error'>❌ Error creating database: " . $conn->error . "</div>";
}

// Select the database
$conn->select_db($database);

// Step 2: Create Tables
echo "<div class='step success'>Step 2: Creating tables...</div>";

// Admin Users Table
$sql = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);
echo "<div class='step success'>✅ admin_users table ready</div>";

// Users Table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    gender VARCHAR(10),
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    contact VARCHAR(15),
    city VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);
echo "<div class='step success'>✅ users table ready</div>";

// Counselors Table
$sql = "CREATE TABLE IF NOT EXISTS counselors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    specialization VARCHAR(100),
    phone VARCHAR(15),
    available_hours VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);
echo "<div class='step success'>✅ counselors table ready</div>";

// Appointments Table
$sql = "CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    counselor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status VARCHAR(20) DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);
echo "<div class='step success'>✅ appointments table ready</div>";

// Resources Table
$sql = "CREATE TABLE IF NOT EXISTS resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(50),
    description TEXT,
    file_path VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);
echo "<div class='step success'>✅ resources table ready</div>";

// Feedback Table
$sql = "CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    rating INT,
    status VARCHAR(20) DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);
echo "<div class='step success'>✅ feedback table ready</div>";

// Contact Messages Table
$sql = "CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    phone VARCHAR(15),
    status VARCHAR(20) DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);
echo "<div class='step success'>✅ contact_messages table ready</div>";

// Step 3: Insert Test Data
echo "<div class='step success'>Step 3: Inserting test data...</div>";

// Check if admin already exists
$adminCheck = $conn->query("SELECT id FROM admin_users WHERE email = 'muneeb122@gmail.com'");
if ($adminCheck->num_rows == 0) {
    $adminPassword = password_hash('password123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO admin_users (email, password, name) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $adminEmail, $adminPassword, $adminName);
    
    $adminEmail = "muneeb122@gmail.com";
    $adminName = "Muneeb Niaz";
    
    if ($stmt->execute()) {
        echo "<div class='step success'>✅ Admin account created: muneeb122@gmail.com / password123</div>";
    }
    $stmt->close();
} else {
    echo "<div class='step success'>✅ Admin account already exists</div>";
}

// Check if student already exists
$studentCheck = $conn->query("SELECT id FROM users WHERE email = 'student@test.com'");
if ($studentCheck->num_rows == 0) {
    $studentPassword = password_hash('Test123!', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, gender, email, username, password, contact, city) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    $name = "Test Student";
    $gender = "Male";
    $email = "student@test.com";
    $username = "teststudent";
    $contact = "+92300000000";
    $city = "Karachi";
    
    $stmt->bind_param("sssssss", $name, $gender, $email, $username, $studentPassword, $contact, $city);
    
    if ($stmt->execute()) {
        echo "<div class='step success'>✅ Student account created: student@test.com / Test123!</div>";
    }
    $stmt->close();
} else {
    echo "<div class='step success'>✅ Student account already exists</div>";
}

// Step 4: Verification
echo "<div class='step success'>Step 4: Verifying setup...</div>";

$adminCount = $conn->query("SELECT COUNT(*) as cnt FROM admin_users")->fetch_assoc();
$studentCount = $conn->query("SELECT COUNT(*) as cnt FROM users")->fetch_assoc();

echo "<div class='step success'>✅ Admin accounts: " . $adminCount['cnt'] . "</div>";
echo "<div class='step success'>✅ Student accounts: " . $studentCount['cnt'] . "</div>";

echo "<hr>";
echo "<h2>✅ Setup Complete!</h2>";
echo "<p style='font-size: 18px; margin: 20px 0;'><strong>Your database is now ready!</strong></p>";

echo "<h3>Login Credentials:</h3>";
echo "<p><strong>Admin Login:</strong><br>";
echo "Email: <code>muneeb122@gmail.com</code><br>";
echo "Password: <code>password123</code><br>";
echo "URL: <a href='Log-in%20(Admin).php'>Admin Login Page</a></p>";

echo "<p><strong>Student Login:</strong><br>";
echo "Email: <code>student@test.com</code><br>";
echo "Password: <code>Test123!</code><br>";
echo "URL: <a href='Log-in%20(Student).php'>Student Login Page</a></p>";

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Click one of the login links above to verify setup</li>";
echo "<li>Use the credentials provided to login</li>";
echo "<li>Explore the platform features</li>";
echo "<li>Create new student accounts via Sign-Up</li>";
echo "</ol>";

echo "<p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;'>";
echo "<a href='index.html' style='color: #007bff; text-decoration: none;'>← Back to Home</a>";
echo "</p>";

$conn->close();

echo "</div>
</body>
</html>";
?>
