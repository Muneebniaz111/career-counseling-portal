<?php
require_once __DIR__ . '/bootstrap.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: Log-in (Admin).php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];

// Get statistics
$mysqli = new mysqli("localhost", "root", "", "career_counseling");

$user_count = $mysqli->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$appointment_count = $mysqli->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'];
$feedback_count = $mysqli->query("SELECT COUNT(*) as count FROM feedback")->fetch_assoc()['count'];
$contact_count = $mysqli->query("SELECT COUNT(*) as count FROM contact_messages")->fetch_assoc()['count'];

// Get recent users
$recent_users = $mysqli->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

// Get recent feedback
$recent_feedback = $mysqli->query("SELECT * FROM feedback ORDER BY created_at DESC LIMIT 5");

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Career Counseling Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom, #000, #4b0082);
            color: white;
            font-family: 'Arial', sans-serif;
        }

        .navbar {
            background-color: black;
            padding: 20px 40px;
        }

        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }

        .navbar-nav .nav-link {
            color: white !important;
            margin-left: 20px;
        }

        .navbar-nav .nav-link:hover {
            color: #ff6b6b !important;
        }

        .sidebar {
            background-color: rgba(0, 0, 0, 0.9);
            min-height: 100vh;
            padding: 20px;
            border-right: 2px solid #ff6b6b;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 5px;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: #ff6b6b;
            color: white;
        }

        .sidebar-title {
            color: #ff6b6b;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .main-content {
            padding: 30px;
        }

        .dashboard-header {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 5px solid #ff6b6b;
        }

        .stat-card {
            background-color: rgba(0, 0, 0, 0.8);
            border: 1px solid #ff6b6b;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            color: #ff6b6b;
            font-weight: bold;
        }

        .stat-label {
            font-size: 1rem;
            color: #ccc;
        }

        .card {
            background-color: rgba(0, 0, 0, 0.8);
            border: 1px solid #ff6b6b;
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #ff6b6b;
            border: none;
            color: white;
            font-weight: bold;
        }

        .card-body {
            color: white;
        }

        .table-dark td {
            border-color: #666;
        }

        .btn-danger {
            background-color: #ff6b6b;
            border-color: #ff6b6b;
        }

        .btn-danger:hover {
            background-color: #ff5252;
            border-color: #ff5252;
        }

        .user-item {
            background-color: rgba(255, 107, 107, 0.1);
            padding: 15px;
            border-left: 3px solid #ff6b6b;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .logout-btn {
            float: right;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><img src="shikshalogo.jpg" alt="Logo" height="40"> Career Portal Admin</a>
            <div class="ml-auto">
                <span style="margin-right: 20px;">Welcome, <?php echo htmlspecialchars($admin_name); ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="row no-gutters">
        <div class="col-md-2">
            <div class="sidebar">
                <div class="sidebar-title">MANAGEMENT</div>
                <a href="Admin_Dashboard.php">📊 Dashboard</a>
                <a href="manage_users.php">👥 Manage Users</a>
                <a href="manage_appointments.php">📅 Appointments</a>
                <a href="manage_resources.php">📚 Resources</a>

                <div class="sidebar-title">COMMUNICATION</div>
                <a href="view_feedback.php">⭐ Feedback</a>
                <a href="admin_contacts.php">📧 Contact Messages</a>

                <div class="sidebar-title">SYSTEM</div>
                <a href="admin_settings.php">⚙️ Settings</a>
                <a href="logout.php">🔐 Logout</a>
            </div>
        </div>

        <div class="col-md-10">
            <div class="main-content">
                <div class="dashboard-header">
                    <h1>⚙️ Admin Dashboard</h1>
                    <p>Manage users, appointments, resources, and system settings</p>
                </div>

                <!-- Statistics Row -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $user_count; ?></div>
                            <div class="stat-label">Total Users</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $appointment_count; ?></div>
                            <div class="stat-label">Appointments</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $feedback_count; ?></div>
                            <div class="stat-label">Feedback Entries</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $contact_count; ?></div>
                            <div class="stat-label">Contact Queries</div>
                        </div>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Users</h5>
                    </div>
                    <div class="card-body">
                        <?php while ($user = $recent_users->fetch_assoc()): ?>
                            <div class="user-item">
                                <strong><?php echo htmlspecialchars($user['name']); ?></strong><br>
                                Email: <?php echo htmlspecialchars($user['email']); ?><br>
                                Joined: <?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?>
                                <a href="manage_users.php" class="btn btn-sm btn-danger" style="float: right;">Manage</a>
                            </div>
                        <?php endwhile; ?>
                        <a href="manage_users.php" class="btn btn-danger btn-block mt-3">View All Users</a>
                    </div>
                </div>

                <!-- Recent Feedback -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Feedback</h5>
                    </div>
                    <div class="card-body">
                        <?php while ($feedback = $recent_feedback->fetch_assoc()): ?>
                            <div class="user-item">
                                <strong><?php echo htmlspecialchars($feedback['name']); ?></strong><br>
                                Rating: 
                                <?php for ($i = 0; $i < $feedback['rating']; $i++) echo '⭐'; ?>
                                <br>
                                Message: <?php echo htmlspecialchars(substr($feedback['message'], 0, 50)) . '...'; ?>
                            </div>
                        <?php endwhile; ?>
                        <a href="view_feedback.php" class="btn btn-danger btn-block mt-3">View All Feedback</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

