<?php
/**
 * SYSTEM STATUS DASHBOARD
 * Complete overview and login links
 */

$servername = "localhost";
$username = "root";
$password = "";
$database = "career_counseling";

$conn = new mysqli($servername, $username, $password, $database);
$dbConnected = !$conn->connect_error;

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Career Portal - System Status</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .status-item {
            padding: 12px 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .status-item.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .status-item.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .login-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        .login-card {
            border: 2px solid #667eea;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .login-card h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        .credentials {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            text-align: left;
            font-family: monospace;
            font-size: 0.9rem;
        }
        .credentials p {
            margin: 8px 0;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        .setup-required {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .setup-required h3 {
            color: #856404;
            margin-bottom: 10px;
        }
        .setup-required ol {
            margin-left: 20px;
            color: #856404;
        }
        .setup-required li {
            margin: 8px 0;
        }
        @media (max-width: 600px) {
            .login-grid {
                grid-template-columns: 1fr;
            }
            .header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class='container'>
    <div class='header'>
        <h1>🎓 Career Counseling Portal</h1>
        <p>System Status & Login</p>
    </div>
    
    <div class='content'>
        <!-- System Status -->";

if (!$dbConnected) {
    echo "<div class='setup-required'>
        <h3>⚠️ Database Connection Issue</h3>
        <p>MySQL is not responding. Please ensure:</p>
        <ol>
            <li>XAMPP is open</li>
            <li>MySQL is running (green status)</li>
            <li>Apache is running (green status)</li>
        </ol>
    </div>";
} else {
    // Check tables
    $adminCount = 0;
    $studentCount = 0;
    $tablesReady = true;
    
    $result = $conn->query("SELECT COUNT(*) as cnt FROM admin_users");
    if ($result) {
        $row = $result->fetch_assoc();
        $adminCount = $row['cnt'];
    } else {
        $tablesReady = false;
    }
    
    $result = $conn->query("SELECT COUNT(*) as cnt FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        $studentCount = $row['cnt'];
    } else {
        $tablesReady = false;
    }
    
    if (!$tablesReady) {
        echo "<div class='setup-required'>
            <h3>⚠️ Database Tables Not Found</h3>
            <p>The database exists but tables are not created. Run setup:</p>
            <ol>
                <li><a href='setup_complete.php' class='btn' style='display: inline-block; margin-top: 10px;'>✨ Run Setup Now</a></li>
                <li>Wait for page to complete</li>
                <li>Refresh this page</li>
            </ol>
        </div>";
    } else {
        echo "<div class='section'>
            <h2>✅ System Status</h2>
            <div class='status-item success'>✅ Database Connected</div>
            <div class='status-item success'>✅ Tables Created</div>
            <div class='status-item success'>✅ Admin Accounts: " . $adminCount . "</div>
            <div class='status-item success'>✅ Student Accounts: " . $studentCount . "</div>
        </div>";
    }
}

echo "        <!-- Login Section -->
        <div class='section'>
            <h2>🔐 Login to System</h2>
            
            <div class='login-grid'>
                <div class='login-card'>
                    <h3>👨‍💼 Admin Login</h3>
                    <div class='credentials'>
                        <p><strong>Email:</strong><br>muneeb122@gmail.com</p>
                        <p><strong>Password:</strong><br>password123</p>
                    </div>
                    <a href='Log-in%20(Admin).php' class='btn'>Login as Admin</a>
                </div>
                
                <div class='login-card'>
                    <h3>👤 Student Login</h3>
                    <div class='credentials'>
                        <p><strong>Email:</strong><br>student@test.com</p>
                        <p><strong>Password:</strong><br>Test123!</p>
                    </div>
                    <a href='Log-in%20(Student).php' class='btn'>Login as Student</a>
                </div>
            </div>
        </div>
        
        <!-- Help Section -->
        <div class='section'>
            <h2>📖 Help & Resources</h2>
            <div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;'>
                <a href='verify_setup.php' class='btn' style='text-align: center;'>Verify Setup</a>
                <a href='LOGIN_SETUP_GUIDE.md' class='btn' style='text-align: center;'>Setup Guide</a>
                <a href='README.md' class='btn' style='text-align: center;'>Documentation</a>
                <a href='index.html' class='btn' style='text-align: center;'>Home Page</a>
            </div>
        </div>
        
        <!-- Info -->
        <div class='section'>
            <h2>ℹ️ Information</h2>
            <p style='color: #666; line-height: 1.8;'>
                <strong>First Time Setup?</strong> If tables haven't been created yet, click the 'Run Setup Now' button above. 
                This will create all necessary database tables and insert test credentials for you to use.
            </p>
            <p style='color: #666; line-height: 1.8; margin-top: 10px;'>
                <strong>Create New Account?</strong> Students can create their own accounts by going to 
                <a href='Sign-Up.php' style='color: #667eea;'>Sign-Up</a> page.
            </p>
        </div>
    </div>
</div>

</body>
</html>";

if ($conn->connect_error === null) {
    $conn->close();
}
?>
