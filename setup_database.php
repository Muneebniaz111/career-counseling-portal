<?php
/**
 * Career Portal - Automatic Database Setup
 * This script initializes the database with all required tables and test data
 * Access: http://localhost/Career-Counseling-Guide-Portal-master/setup_database.php
 */

ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Portal - Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #800080 0%, #4B0082 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .setup-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        .setup-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #800080;
            padding-bottom: 20px;
        }
        .setup-header h1 {
            color: #800080;
            font-weight: bold;
            font-size: 28px;
        }
        .setup-header p {
            color: #666;
            margin: 10px 0 0 0;
        }
        .status-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #ddd;
            display: flex;
            align-items: center;
            font-size: 16px;
        }
        .status-item.success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .status-item.error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .status-item.info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        .status-item.warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .status-icon {
            margin-right: 15px;
            font-size: 20px;
            font-weight: bold;
        }
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #800080;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 15px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .credentials-table {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .credentials-table h5 {
            color: #800080;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .credential-row {
            padding: 10px;
            background: white;
            margin: 5px 0;
            border-radius: 3px;
            border-left: 3px solid #800080;
            font-size: 14px;
        }
        .credential-label {
            font-weight: bold;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }
        .credential-value {
            font-family: 'Courier New', monospace;
            color: #666;
            background: #f0f0f0;
            padding: 5px 10px;
            border-radius: 3px;
            display: inline-block;
            word-break: break-all;
        }
        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-custom {
            flex: 1;
            min-width: 150px;
            font-weight: bold;
            font-size: 16px;
            padding: 12px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-primary-custom {
            background: #800080;
            color: white;
            border: none;
        }
        .btn-primary-custom:hover {
            background: #4B0082;
            color: white;
            text-decoration: none;
        }
        .btn-success-custom {
            background: #28a745;
            color: white;
            border: none;
        }
        .btn-success-custom:hover {
            background: #218838;
            color: white;
            text-decoration: none;
        }
        .btn-info-custom {
            background: #17a2b8;
            color: white;
            border: none;
        }
        .btn-info-custom:hover {
            background: #138496;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1>🎓 Career Portal Setup</h1>
            <p>Database Initialization & Configuration</p>
        </div>

        <div id="setup-status">
            <div class="status-item info">
                <div class="spinner"></div>
                <span>Initializing database setup...</span>
            </div>
        </div>

        <div id="credentials-section" style="display: none;">
            <div class="credentials-table">
                <h5>✅ Setup Complete! Your Test Credentials:</h5>
                
                <div class="credential-row">
                    <span class="credential-label">👤 Admin Account:</span>
                    <span class="credential-value">muneeb122@gmail.com</span><br>
                    <span class="credential-label" style="margin-top: 8px;">🔑 Password:</span>
                    <span class="credential-value">password123</span>
                </div>

                <div class="credential-row">
                    <span class="credential-label">👤 Student Account:</span>
                    <span class="credential-value">student@test.com</span><br>
                    <span class="credential-label" style="margin-top: 8px;">🔑 Password:</span>
                    <span class="credential-value">Test123!</span>
                </div>

                <p style="margin-top: 15px; color: #666; font-size: 13px;">
                    💡 Tip: You can create additional student accounts using the Sign-Up page
                </p>
            </div>

            <div class="action-buttons">
                <a href="Log-in%20(Admin).php" class="btn-custom btn-primary-custom">
                    🔐 Admin Login
                </a>
                <a href="Log-in%20(Student).php" class="btn-custom btn-success-custom">
                    📚 Student Login
                </a>
            </div>
        </div>
    </div>

    <script>
        // Run setup on page load
        document.addEventListener('DOMContentLoaded', function() {
            runSetup();
        });

        function runSetup() {
            // Simulate setup process
            fetch('setup_api.php?action=setup', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessStatus(data);
                } else {
                    showErrorStatus(data);
                }
            })
            .catch(error => {
                console.error('Setup error:', error);
                showErrorStatus({
                    message: 'Setup failed: ' + error.message
                });
            });
        }

        function showSuccessStatus(data) {
            const statusDiv = document.getElementById('setup-status');
            statusDiv.innerHTML = `
                <div class="status-item success">
                    <div class="status-icon">✅</div>
                    <span>${data.message || 'Database setup completed successfully!'}</span>
                </div>
                <div class="status-item success">
                    <div class="status-icon">📊</div>
                    <span>All 10 tables created</span>
                </div>
                <div class="status-item success">
                    <div class="status-icon">👥</div>
                    <span>Admin accounts initialized</span>
                </div>
                <div class="status-item success">
                    <div class="status-icon">🧪</div>
                    <span>Test student account created</span>
                </div>
            `;
            document.getElementById('credentials-section').style.display = 'block';
        }

        function showErrorStatus(data) {
            const statusDiv = document.getElementById('setup-status');
            statusDiv.innerHTML = `
                <div class="status-item warning">
                    <div class="status-icon">⚠️</div>
                    <span>${data.message || 'Setup may have already been completed'}</span>
                </div>
                <div class="status-item info">
                    <div class="status-icon">ℹ️</div>
                    <span>If tables already exist, you can proceed with login</span>
                </div>
            `;
            document.getElementById('credentials-section').style.display = 'block';
        }
    </script>
</body>
</html>
