<?php
require_once __DIR__ . '/bootstrap.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: Log-in (Admin).php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// Get current theme from session or database
$current_theme = $_SESSION['user_theme'] ?? 'light';
$current_color = $_SESSION['accent_color'] ?? '#800080';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - Career Counseling Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style id="theme-styles">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #800080;
            --secondary: #4B0082;
            --dark: #1a1a1a;
            --light: #f8f9fa;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.7);
            --bg-primary: linear-gradient(135deg, var(--dark) 0%, var(--secondary) 100%);
        }

        /* ===== LIGHT MODE ===== */
        html.light-mode {
            --primary: #800080;
            --secondary: #4B0082;
            --dark: #ffffff;
            --light: #f5f7fa;
            --glass-bg: rgba(128, 0, 128, 0.03);
            --glass-border: rgba(128, 0, 128, 0.15);
            --text-primary: #1a1a1a;
            --text-secondary: #666666;
            --bg-primary: linear-gradient(135deg, #f5f7fa 0%, #ede7f6 100%);
        }

        html.light-mode body {
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        html.light-mode .navbar {
            background: linear-gradient(90deg, #ffffff 0%, #f5f7fa 100%) !important;
            box-shadow: 0 4px 12px rgba(128, 0, 128, 0.1);
            border-bottom: 1px solid rgba(128, 0, 128, 0.1);
        }

        html.light-mode .navbar-brand,
        html.light-mode .nav-link {
            color: var(--text-primary) !important;
        }

        html.light-mode .page-header h1 {
            color: var(--text-primary);
            text-shadow: none;
        }

        html.light-mode .page-header p {
            color: var(--text-secondary);
        }

        html.light-mode .settings-nav-item {
            background: rgba(128, 0, 128, 0.05);
            border: 1px solid rgba(128, 0, 128, 0.15);
            color: var(--text-primary);
        }

        html.light-mode .settings-card {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(128, 0, 128, 0.15);
        }

        html.light-mode .settings-card h3,
        html.light-mode .settings-panel h2 {
            color: var(--text-primary);
        }

        html.light-mode .settings-card p,
        html.light-mode .toggle-label-desc {
            color: var(--text-secondary);
        }

        html.light-mode .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(128, 0, 128, 0.2);
            color: var(--text-primary);
        }

        html.light-mode .form-group label {
            color: var(--text-primary);
        }

        html.light-mode footer {
            background: linear-gradient(90deg, #ffffff 0%, #f5f7fa 100%);
            color: var(--text-secondary);
            border-top: 1px solid rgba(128, 0, 128, 0.1);
        }

        html.light-mode .toggle-container {
            background: rgba(128, 0, 128, 0.04);
            border: 1px solid rgba(128, 0, 128, 0.1);
        }

        html.light-mode .profile-section {
            background: rgba(128, 0, 128, 0.08);
            border: 1px solid rgba(128, 0, 128, 0.15);
        }

        /* ===== DARK MODE (Default) ===== */
        html, body {
            height: 100%;
            background: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        /* ===== Navigation Bar ===== */
        .navbar {
            background-color: black;
            padding: 20px 40px;
            margin-bottom: 40px;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-primary) !important;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .nav-link {
            color: var(--text-secondary) !important;
            margin: 0 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* ===== Back Link ===== */
        .back-link {
            color: #ff6b6b;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #ff5252;
        }

        .back-link i {
            font-size: 1.1rem;
        }

        /* ===== Main Content ===== */
        main {
            margin-top: 80px;
            padding: 2rem;
            min-height: calc(100vh - 80px);
        }

        /* ===== Page Header ===== */
        .page-header {
            margin-bottom: 3rem;
            animation: slideInDown 0.6s ease-out;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .page-header p {
            font-size: 1rem;
            color: var(--text-secondary);
            margin: 0;
        }

        /* ===== Settings Container ===== */
        .settings-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* ===== Sidebar Navigation ===== */
        .settings-sidebar {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            animation: slideInLeft 0.6s ease-out;
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        .settings-nav-item {
            padding: 1rem 1.5rem;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            backdrop-filter: blur(10px);
        }

        .settings-nav-item i {
            font-size: 1.1rem;
            width: 25px;
            text-align: center;
            color: var(--primary);
        }

        .settings-nav-item:hover {
            background: rgba(128, 0, 128, 0.15);
            border-color: var(--primary);
            transform: translateX(5px);
            box-shadow: 0 8px 24px rgba(128, 0, 128, 0.2);
        }

        .settings-nav-item.active {
            background: linear-gradient(135deg, var(--primary) 0%, rgba(128, 0, 128, 0.8) 100%);
            border-color: var(--primary);
            box-shadow: 0 8px 32px rgba(128, 0, 128, 0.3);
        }

        /* ===== Settings Panels ===== */
        .settings-panel {
            display: none;
            animation: fadeInUp 0.5s ease-out;
        }

        .settings-panel.active {
            display: block;
        }

        .settings-panel h2 {
            font-size: 1.8rem;
            color: var(--text-primary);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .settings-panel h2 i {
            color: var(--primary);
            font-size: 2rem;
        }

        /* ===== Settings Cards ===== */
        .settings-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .settings-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        }

        .settings-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(128, 0, 128, 0.3);
            box-shadow: 0 12px 40px rgba(128, 0, 128, 0.15);
            transform: translateY(-2px);
        }

        .settings-card h3 {
            font-size: 1.3rem;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .settings-card h3 i {
            color: var(--primary);
            font-size: 1.5rem;
        }

        .settings-card p {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        /* ===== Form Controls ===== */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group label i {
            color: var(--primary);
            font-size: 0.9rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary);
            color: var(--text-primary);
            box-shadow: 0 0 0 3px rgba(128, 0, 128, 0.2);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-control option {
            background: var(--dark);
            color: var(--text-primary);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        /* ===== Theme Select Dropdown ===== */
        #theme {
            padding: 1rem 1.25rem !important;
            min-height: 50px !important;
            height: 55px !important;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.5rem;
            padding-right: 3rem !important;
        }

        #theme:hover {
            border-color: var(--primary) !important;
            background-color: rgba(255, 255, 255, 0.12) !important;
        }

        #theme option {
            padding: 0.75rem 1rem;
        }

        html.light-mode #theme {
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        }

        /* ===== Toggle Switch ===== */
        .toggle-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 12px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .toggle-container:hover {
            background: rgba(255, 255, 255, 0.06);
        }

        .toggle-label {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .toggle-label-title {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .toggle-label-desc {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 28px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.2);
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 28px;
            border: 1px solid var(--glass-border);
        }

        .slider:before {
            position: absolute;
            content: '';
            height: 22px;
            width: 22px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--success);
            border-color: var(--success);
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.3);
        }

        input:checked + .slider:before {
            transform: translateX(22px);
        }

        /* ===== Buttons ===== */
        .btn {
            font-weight: 600;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, rgba(128, 0, 128, 0.8) 100%);
            color: white;
            box-shadow: 0 8px 20px rgba(128, 0, 128, 0.3);
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(128, 0, 128, 0.4);
            color: white;
        }

        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: var(--glass-bg);
            color: var(--text-primary);
            border: 1px solid var(--glass-border);
        }

        .btn-secondary:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary);
            color: var(--text-primary);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(239, 68, 68, 0.4);
            color: white;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        /* ===== Loading Spinner ===== */
        .spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ===== Alert Messages ===== */
        .alert {
            border: none;
            border-radius: 12px;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
            border-left: 4px solid;
            animation: slideInDown 0.4s ease-out;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border-left-color: var(--success);
            color: #a7f3d0;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border-left-color: var(--danger);
            color: #fca5a5;
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.15);
            border-left-color: var(--info);
            color: #bfdbfe;
        }

        /* ===== Section Divider ===== */
        .section-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            margin: 2rem 0;
        }

        /* ===== Profile Section ===== */
        .profile-section {
            display: flex;
            align-items: center;
            gap: 2rem;
            padding: 2rem;
            background: rgba(128, 0, 128, 0.1);
            border-radius: 12px;
            margin-bottom: 2rem;
            border: 1px solid var(--glass-border);
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            box-shadow: 0 8px 20px rgba(128, 0, 128, 0.3);
        }

        .profile-info h3 {
            color: var(--text-primary);
            font-size: 1.3rem;
            margin-bottom: 0.25rem;
        }

        .profile-info p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 0.9rem;
        }

        /* ===== Settings Groups ===== */
        .settings-group {
            margin-bottom: 2rem;
        }

        .settings-group-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .settings-group-title::before {
            content: '';
            width: 3px;
            height: 16px;
            background: var(--primary);
            border-radius: 2px;
        }

        /* ===== Animations ===== */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== Responsive Design ===== */
        @media (max-width: 992px) {
            .settings-container {
                grid-template-columns: 1fr;
            }

            .settings-sidebar {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                position: relative;
                top: 0;
                gap: 0.5rem;
            }

            main {
                padding: 1.5rem;
            }

            .page-header h1 {
                font-size: 1.8rem;
            }

            .profile-section {
                flex-direction: column;
                text-align: center;
            }
        }

        @media (max-width: 600px) {
            .settings-nav-item {
                padding: 0.75rem;
                font-size: 0.85rem;
                gap: 0.5rem;
            }

            .settings-nav-item span {
                display: none;
            }

            .settings-card {
                padding: 1.5rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            main {
                padding: 1rem;
                margin-top: 70px;
            }
        }

        /* ===== Footer ===== */
        footer {
            background: linear-gradient(90deg, #000 0%, var(--dark) 100%);
            color: var(--text-secondary);
            text-align: center;
            padding: 2rem;
            border-top: 1px solid var(--glass-border);
            margin-top: 4rem;
            font-size: 0.9rem;
        }

        footer a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        footer a:hover {
            color: #ff69b4;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 1rem;">
            <a href="Admin_Dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-sliders-h"></i> Settings</h1>
            <p>Manage your account preferences, security, and notifications</p>
        </div>

        <!-- Success Message -->
        <div id="alert-container"></div>

        <!-- Settings Container -->
        <div class="settings-container">
            <!-- Sidebar Navigation -->
            <div class="settings-sidebar">
                <div class="settings-nav-item active" data-panel="profile">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </div>
                <div class="settings-nav-item" data-panel="security">
                    <i class="fas fa-lock"></i>
                    <span>Security</span>
                </div>
                <div class="settings-nav-item" data-panel="notifications">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </div>
                <div class="settings-nav-item" data-panel="appearance">
                    <i class="fas fa-palette"></i>
                    <span>Appearance</span>
                </div>
            </div>

            <!-- Settings Panels -->
            <div class="settings-content">
                <!-- Profile Settings Panel -->
                <div id="profile" class="settings-panel active">
                    <h2><i class="fas fa-user-circle"></i> Profile Settings</h2>

                    <div class="profile-section">
                        <div class="profile-avatar">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="profile-info">
                            <h3 id="profile-name"><?php echo htmlspecialchars($admin_name); ?></h3>
                            <p>Administrator Account</p>
                            <p style="font-size: 0.8rem; margin-top: 0.5rem;">
                                <i class="fas fa-envelope"></i> <span id="profile-email">admin@careerportal.com</span>
                            </p>
                        </div>
                    </div>

                    <div class="settings-card">
                        <h3><i class="fas fa-edit"></i> Personal Information</h3>
                        <p>Update your account details and profile information</p>

                        <form id="profile-form">
                            <div class="form-group">
                                <label for="fullname"><i class="fas fa-id-card"></i> Full Name</label>
                                <input type="text" class="form-control" id="fullname" placeholder="Enter your full name">
                            </div>

                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                                <input type="email" class="form-control" id="email" placeholder="admin@careerportal.com">
                            </div>

                            <div class="form-group">
                                <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                                <input type="tel" class="form-control" id="phone" placeholder="+92-300-1234567">
                            </div>

                            <div class="form-group">
                                <label for="bio"><i class="fas fa-align-left"></i> Bio</label>
                                <textarea class="form-control" id="bio" placeholder="Tell us about yourself..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Security Settings Panel -->
                <div id="security" class="settings-panel">
                    <h2><i class="fas fa-shield-alt"></i> Security Settings</h2>

                    <div class="settings-card">
                        <h3><i class="fas fa-key"></i> Password Management</h3>
                        <p>Keep your account secure by maintaining a strong password</p>

                        <form id="password-form">
                            <div class="form-group">
                                <label for="current-password"><i class="fas fa-lock"></i> Current Password</label>
                                <input type="password" class="form-control" id="current-password" placeholder="••••••••">
                            </div>

                            <div class="form-group">
                                <label for="new-password"><i class="fas fa-lock"></i> New Password</label>
                                <input type="password" class="form-control" id="new-password" placeholder="••••••••">
                            </div>

                            <div class="form-group">
                                <label for="confirm-password"><i class="fas fa-lock"></i> Confirm Password</label>
                                <input type="password" class="form-control" id="confirm-password" placeholder="••••••••">
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-shield-alt"></i> Update Password
                            </button>
                        </form>
                    </div>

                    <div class="settings-card">
                        <h3><i class="fas fa-lock-open"></i> Two-Factor Authentication</h3>
                        <p>Add an extra layer of security to your account (Optional)</p>

                        <div class="toggle-container">
                            <div class="toggle-label">
                                <div class="toggle-label-title">Enable 2FA</div>
                                <div class="toggle-label-desc">Require authentication code on login</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="2fa-toggle">
                                <span class="slider"></span>
                            </label>
                        </div>

                        <p style="font-size: 0.85rem; color: rgba(255, 255, 255, 0.6); margin-top: 1rem;">
                            <i class="fas fa-info-circle"></i> Two-factor authentication significantly improves your account security. This feature is optional.
                        </p>
                    </div>
                </div>

                <!-- Notifications Settings Panel -->
                <div id="notifications" class="settings-panel">
                    <h2><i class="fas fa-bell"></i> Notification Settings</h2>

                    <div class="settings-card">
                        <h3><i class="fas fa-envelope"></i> Email Notifications</h3>
                        <p>Control how and when you receive email notifications</p>

                        <div class="settings-group">
                            <div class="settings-group-title">
                                <i class="fas fa-users"></i> Contact Messages
                            </div>

                            <div class="toggle-container">
                                <div class="toggle-label">
                                    <div class="toggle-label-title">New Contact Messages</div>
                                    <div class="toggle-label-desc">Get notified when users send contact messages</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" class="notification-toggle" data-key="contact_messages">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="toggle-container">
                                <div class="toggle-label">
                                    <div class="toggle-label-title">Message Replies</div>
                                    <div class="toggle-label-desc">Get notified when a message you replied to gets acknowledged</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" class="notification-toggle" data-key="contact_replies">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="section-divider"></div>

                        <div class="settings-group">
                            <div class="settings-group-title">
                                <i class="fas fa-star"></i> Feedback & Reviews
                            </div>

                            <div class="toggle-container">
                                <div class="toggle-label">
                                    <div class="toggle-label-title">New Feedback</div>
                                    <div class="toggle-label-desc">Get notified when users submit feedback</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" class="notification-toggle" data-key="feedback_notifications">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="toggle-container">
                                <div class="toggle-label">
                                    <div class="toggle-label-title">Feedback Responses</div>
                                    <div class="toggle-label-desc">Get notified when someone responds to your feedback</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" class="notification-toggle" data-key="feedback_responses">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="section-divider"></div>

                        <div class="settings-group">
                            <div class="settings-group-title">
                                <i class="fas fa-calendar-alt"></i> System Alerts
                            </div>

                            <div class="toggle-container">
                                <div class="toggle-label">
                                    <div class="toggle-label-title">System Updates</div>
                                    <div class="toggle-label-desc">Get notified about important system updates</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" class="notification-toggle" data-key="system_updates">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="toggle-container">
                                <div class="toggle-label">
                                    <div class="toggle-label-title">Security Alerts</div>
                                    <div class="toggle-label-desc">Get notified about suspicious login attempts</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" class="notification-toggle" data-key="security_alerts">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary" id="save-notifications" style="margin-top: 1.5rem;">
                            <i class="fas fa-save"></i> Save Preferences
                        </button>
                    </div>
                </div>

                <!-- Appearance Settings Panel -->
                <div id="appearance" class="settings-panel">
                    <h2><i class="fas fa-palette"></i> Appearance Settings</h2>

                    <div class="settings-card">
                        <h3><i class="fas fa-moon"></i> Theme</h3>
                        <p>Customize the look and feel of your interface</p>

                        <div class="form-group">
                            <label for="theme"><i class="fas fa-brush"></i> Color Theme</label>
                            <select class="form-control" id="theme">
                                <option value="light">Light Mode (Recommended)</option>
                                <option value="dark">Dark Mode</option>
                                <option value="auto">Auto (System Preference)</option>
                            </select>
                        </div>

                        <div class="section-divider"></div>

                        <div class="toggle-container">
                            <div class="toggle-label">
                                <div class="toggle-label-title">Animations</div>
                                <div class="toggle-label-desc">Enable smooth transitions and animations</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="animations-toggle" checked>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <button type="button" class="btn btn-primary" id="save-appearance" style="margin-top: 1rem;">
                            <i class="fas fa-save"></i> Save Appearance
                        </button>
                    </div>

                    <div class="settings-card">
                        <h3><i class="fas fa-palette"></i> Accent Color</h3>
                        <p>Choose your favorite accent color for the interface</p>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(60px, 1fr)); gap: 1rem; margin-top: 1.5rem;">
                            <div style="width: 60px; height: 60px; background: #800080; border-radius: 8px; cursor: pointer; border: 3px solid white; transition: transform 0.2s;" 
                                 class="color-selector" data-color="#800080" title="Purple">
                            </div>
                            <div style="width: 60px; height: 60px; background: #3b82f6; border-radius: 8px; cursor: pointer; border: 2px solid transparent; transition: transform 0.2s;" 
                                 class="color-selector" data-color="#3b82f6" title="Blue">
                            </div>
                            <div style="width: 60px; height: 60px; background: #10b981; border-radius: 8px; cursor: pointer; border: 2px solid transparent; transition: transform 0.2s;" 
                                 class="color-selector" data-color="#10b981" title="Green">
                            </div>
                            <div style="width: 60px; height: 60px; background: #f59e0b; border-radius: 8px; cursor: pointer; border: 2px solid transparent; transition: transform 0.2s;" 
                                 class="color-selector" data-color="#f59e0b" title="Amber">
                            </div>
                            <div style="width: 60px; height: 60px; background: #ef4444; border-radius: 8px; cursor: pointer; border: 2px solid transparent; transition: transform 0.2s;" 
                                 class="color-selector" data-color="#ef4444" title="Red">
                            </div>
                            <div style="width: 60px; height: 60px; background: #ec4899; border-radius: 8px; cursor: pointer; border: 2px solid transparent; transition: transform 0.2s;" 
                                 class="color-selector" data-color="#ec4899" title="Pink">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p style="margin-bottom: 0.5rem;">
            <i class="fas fa-copyright"></i> 2026 Career Counseling & Guide Portal
        </p>
        <p style="margin: 0; font-size: 0.85rem;">
            Designed for a premium user experience | <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
        </p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Application state
        const AppState = {
            currentTheme: localStorage.getItem('user_theme') || 'light',
            currentColor: localStorage.getItem('accent_color') || '#800080',
            animationsEnabled: localStorage.getItem('animations_enabled') !== 'false',
            notifications: {},
            profile: {}
        };

        // Initialize app
        $(document).ready(function() {
            initializeTheme();
            loadProfileData();
            loadNotificationSettings();
            loadAppearanceSettings();
            attachEventHandlers();
        });

        /**
         * Initialize application theme
         */
        function initializeTheme() {
            const theme = AppState.currentTheme;
            applyTheme(theme);
        }

        /**
         * Apply theme to the application
         */
        function applyTheme(theme) {
            const html = document.documentElement;
            
            // Remove all theme classes
            html.classList.remove('light-mode', 'dark-mode', 'auto-mode');
            
            // Apply new theme
            if (theme === 'light') {
                html.classList.add('light-mode');
            } else if (theme === 'dark') {
                html.classList.remove('light-mode');
            }
            
            // Update CSS variables
            document.documentElement.style.setProperty('--primary', AppState.currentColor);
            
            // Save to localStorage for persistence
            localStorage.setItem('user_theme', theme);
            localStorage.setItem('accent_color', AppState.currentColor);
            
            AppState.currentTheme = theme;
        }

        /**
         * Load profile data from backend
         */
        function loadProfileData() {
            fetch('admin_settings_api.php?action=get_profile')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const profile = data.data;
                        AppState.profile = profile;
                        
                        // Populate form fields
                        $('#fullname').val(profile.name || '');
                        $('#email').val(profile.email || '');
                        $('#phone').val(profile.phone || '');
                        $('#bio').val(profile.bio || '');
                        
                        // Update header
                        $('#profile-name').text(profile.name || 'Admin');
                        $('#profile-email').text(profile.email || 'admin@careerportal.com');
                    } else {
                        console.error('API Error:', data.message);
                        showAlert(data.message || 'Failed to load profile data', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error loading profile:', error);
                    showAlert('Failed to load profile data: ' + error.message, 'danger');
                });
        }

        /**
         * Load notification settings from backend
         */
        function loadNotificationSettings() {
            fetch('admin_settings_api.php?action=get_notifications')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        AppState.notifications = data.data;
                        
                        // Populate notification toggles
                        $('.notification-toggle').each(function() {
                            const key = $(this).data('key');
                            const value = data.data[key];
                            $(this).prop('checked', value == 1 || value === true);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                });
        }

        /**
         * Load appearance settings from backend
         */
        function loadAppearanceSettings() {
            fetch('admin_settings_api.php?action=get_appearance')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        // Set theme dropdown
                        $('#theme').val(data.data.theme || 'light');
                        
                        // Set animations toggle
                        $('#animations-toggle').prop('checked', data.data.animations_enabled == 1);
                        
                        // Set accent color and update UI
                        AppState.currentColor = data.data.accent_color || '#800080';
                        updateColorSelector(AppState.currentColor);
                        applyTheme(data.data.theme || 'light');
                    }
                })
                .catch(error => {
                    console.error('Error loading appearance:', error);
                });
        }

        /**
         * Update color selector visual state
         */
        function updateColorSelector(color) {
            $('.color-selector').each(function() {
                if ($(this).data('color') === color) {
                    $(this).css('border-color', 'white').css('border-width', '3px');
                } else {
                    $(this).css('border-color', 'transparent').css('border-width', '2px');
                }
            });
        }

        /**
         * Attach all event handlers
         */
        function attachEventHandlers() {
            // Panel navigation
            $('.settings-nav-item').click(function() {
                const panelId = $(this).data('panel');
                navigateToPanel(panelId);
            });

            // Profile form
            $('#profile-form').submit(function(e) {
                e.preventDefault();
                submitProfileForm();
            });

            // Password form
            $('#password-form').submit(function(e) {
                e.preventDefault();
                submitPasswordForm();
            });

            // Notification save button
            $('#save-notifications').click(function() {
                saveNotificationSettings();
            });

            // Theme dropdown
            $('#theme').change(function() {
                const theme = $(this).val();
                applyTheme(theme);
                saveAppearanceSettings();
            });

            // Animations toggle
            $('#animations-toggle').change(function() {
                localStorage.setItem('animations_enabled', this.checked);
            });

            // Accent color selector
            $('.color-selector').click(function() {
                const color = $(this).data('color');
                AppState.currentColor = color;
                updateColorSelector(color);
                applyTheme(AppState.currentTheme);
                saveAppearanceSettings();
            });

            // 2FA toggle
            $('#2fa-toggle').change(function() {
                const enabled = this.checked;
                fetch('admin_settings_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=toggle_2fa&enabled=' + (enabled ? '1' : '0')
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('2FA setting updated', 'success');
                    } else {
                        showAlert(data.message || 'Failed to update 2FA', 'danger');
                        this.checked = !enabled;
                    }
                })
                .catch(error => {
                    console.error('Error updating 2FA:', error);
                    showAlert('Error updating 2FA setting', 'danger');
                    this.checked = !enabled;
                });
            });
        }

        /**
         * Navigate to a settings panel
         */
        function navigateToPanel(panelId) {
            // Update active nav item
            $('.settings-nav-item').removeClass('active');
            $('[data-panel="' + panelId + '"]').addClass('active');
            
            // Update active panel
            $('.settings-panel').removeClass('active');
            $('#' + panelId).addClass('active');
        }

        /**
         * Submit profile form
         */
        function submitProfileForm() {
            const $btn = $('#profile-form button[type="submit"]');
            const originalHTML = $btn.html();
            
            // Disable button and show loading state
            $btn.prop('disabled', true).html('<span class="spinner"></span> Saving...');
            
            const formData = new FormData();
            formData.append('action', 'update_profile');
            formData.append('fullname', $('#fullname').val());
            formData.append('email', $('#email').val());
            formData.append('phone', $('#phone').val());
            formData.append('bio', $('#bio').val());
            
            fetch('admin_settings_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                $btn.prop('disabled', false).html(originalHTML);
                
                if (data.success) {
                    showAlert('Profile updated successfully!', 'success');
                    AppState.profile = data.data;
                    $('#profile-name').text(data.data.name);
                    $('#profile-email').text(data.data.email);
                } else {
                    showAlert(data.message || 'Failed to update profile', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                $btn.prop('disabled', false).html(originalHTML);
                showAlert('An error occurred. Please try again.', 'danger');
            });
        }

        /**
         * Submit password change form
         */
        function submitPasswordForm() {
            const $btn = $('#password-form button[type="submit"]');
            const originalHTML = $btn.html();
            
            // Basic validation
            const current = $('#current-password').val();
            const newPassword = $('#new-password').val();
            const confirm = $('#confirm-password').val();
            
            if (!current || !newPassword || !confirm) {
                showAlert('All password fields are required', 'danger');
                return;
            }
            
            if (newPassword !== confirm) {
                showAlert('New passwords do not match', 'danger');
                return;
            }
            
            if (newPassword.length < 8) {
                showAlert('Password must be at least 8 characters', 'danger');
                return;
            }
            
            // Disable button and show loading state
            $btn.prop('disabled', true).html('<span class="spinner"></span> Updating...');
            
            const formData = new FormData();
            formData.append('action', 'change_password');
            formData.append('current_password', current);
            formData.append('new_password', newPassword);
            formData.append('confirm_password', confirm);
            
            fetch('admin_settings_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                $btn.prop('disabled', false).html(originalHTML);
                
                if (data.success) {
                    showAlert('Password changed successfully!', 'success');
                    $('#password-form')[0].reset();
                } else {
                    showAlert(data.message || 'Failed to change password', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                $btn.prop('disabled', false).html(originalHTML);
                showAlert('An error occurred. Please try again.', 'danger');
            });
        }

        /**
         * Save notification settings
         */
        function saveNotificationSettings() {
            const $btn = $('#save-notifications');
            const originalHTML = $btn.html();
            
            $btn.prop('disabled', true).html('<span class="spinner"></span> Saving...');
            
            const formData = new FormData();
            formData.append('action', 'update_notifications');
            
            // Add checked notification preferences
            $('.notification-toggle:checked').each(function() {
                formData.append($(this).data('key'), '1');
            });
            
            fetch('admin_settings_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                $btn.prop('disabled', false).html(originalHTML);
                
                if (data.success) {
                    showAlert('Notification preferences updated!', 'success');
                    AppState.notifications = data.data;
                } else {
                    showAlert(data.message || 'Failed to update notifications', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                $btn.prop('disabled', false).html(originalHTML);
                showAlert('An error occurred. Please try again.', 'danger');
            });
        }

        /**
         * Save appearance settings
         */
        function saveAppearanceSettings() {
            const formData = new FormData();
            formData.append('action', 'update_appearance');
            formData.append('theme', AppState.currentTheme);
            formData.append('accent_color', AppState.currentColor);
            formData.append('animations', $('#animations-toggle').is(':checked') ? '1' : '0');
            
            fetch('admin_settings_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Failed to save appearance:', data.message);
                }
            })
            .catch(error => {
                console.error('Error saving appearance:', error);
            });
        }

        /**
         * Show alert message
         */
        function showAlert(message, type = 'info') {
            const alertHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'}"></i>
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: inherit;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            
            $('#alert-container').append(alertHTML);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                $('#alert-container .alert:first-child').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        }

        /**
         * Sync theme across tabs/windows
         */
        window.addEventListener('storage', function(e) {
            if (e.key === 'user_theme' || e.key === 'accent_color') {
                AppState.currentTheme = localStorage.getItem('user_theme') || 'light';
                AppState.currentColor = localStorage.getItem('accent_color') || '#800080';
                applyTheme(AppState.currentTheme);
                updateColorSelector(AppState.currentColor);
            }
        });
    </script>
</body>
</html>

