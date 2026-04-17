<?php
require_once __DIR__ . '/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Log-in (Student).php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$mysqli = new mysqli("localhost", "root", "", "career_counseling");

// Mark notifications as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_read') {
    // CRITICAL FIX: Add CSRF token validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die(json_encode(['error' => 'CSRF token validation failed']));
    }
    
    $feedback_id = intval($_POST['feedback_id']);
    $update_stmt = $mysqli->prepare("UPDATE feedback_notifications SET is_read = 1 WHERE feedback_id = ? AND user_id = ?");
    if ($update_stmt) {
        $update_stmt->bind_param("ii", $feedback_id, $user_id);
        $update_stmt->execute();
        $update_stmt->close();
    }
}

// Get unread notifications count using prepared statement
$notification_count = 0;
try {
    $notif_stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM feedback_notifications WHERE user_id = ? AND is_read = 0");
    if ($notif_stmt) {
        $notif_stmt->bind_param("i", $user_id);
        $notif_stmt->execute();
        $notifications = $notif_stmt->get_result();
        if ($notifications) {
            $notification_count = intval($notifications->fetch_assoc()['count'] ?? 0);
        }
        $notif_stmt->close();
    }
} catch (Exception $e) {
    error_log("Error getting notification count: " . $e->getMessage());
    $notification_count = 0;
}

// Get user's feedback using prepared statement
$feedback_stmt = $mysqli->prepare("SELECT * FROM feedback WHERE user_id = ? ORDER BY created_at DESC");
$feedback_result = null;
if ($feedback_stmt) {
    $feedback_stmt->bind_param("i", $user_id);
    $feedback_stmt->execute();
    $feedback_result = $feedback_stmt->get_result();
    $feedback_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Feedback - Career Counseling Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: #e0e0e0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 40px;
            min-height: 100vh;
        }

        body {
            padding-top: 70px;
        }

        .navbar {
            background: linear-gradient(90deg, #000 0%, var(--dark) 100%) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            padding: 1.2rem 0 !important;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.3rem;
            color: white !important;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
        }

        .navbar-brand:hover {
            color: var(--primary) !important;
        }

        .navbar-brand img {
            height: 45px;
            width: 45px;
            margin-right: 12px;
            border-radius: 50%;
        }

        .nav-link {
            color: white !important;
            font-weight: 600;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
            border-radius: 5px;
            margin: 0 0.5rem;
        }

        .nav-link:hover {
            background-color: #800080;
            color: white !important;
        }

        .page-header {
            margin: 30px auto;
            padding: 40px;
            background: linear-gradient(135deg, rgba(128, 0, 128, 0.15), rgba(255, 107, 107, 0.1));
            border-radius: 12px;
            max-width: 900px;
            border: 1px solid rgba(128, 0, 128, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .page-header h2 {
            margin: 0;
            color: #ffffff;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .page-header p {
            color: #b0b0b0;
            margin-top: 8px;
            font-size: 0.95rem;
        }

        /* Statistics Dashboard */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(128, 0, 128, 0.1), rgba(255, 107, 107, 0.05));
            border: 1px solid rgba(128, 0, 128, 0.3);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: #ff6b6b;
            box-shadow: 0 12px 24px rgba(128, 0, 128, 0.2);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 12px;
            color: #ff6b6b;
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 0.95rem;
            color: #b0b0b0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .alerts-container {
            max-width: 900px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .alert {
            border-radius: 10px;
            border: 1px solid rgba(255, 193, 7, 0.3);
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            padding: 16px;
            margin-bottom: 20px;
        }

        .alert i {
            margin-right: 12px;
        }

        /* Feedback Cards */
        .feedback-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .feedback-card {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgba(128, 0, 128, 0.1));
            border: 1px solid rgba(128, 0, 128, 0.3);
            margin-bottom: 30px;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }

        .feedback-card:hover {
            border-color: #ff6b6b;
            box-shadow: 0 12px 40px rgba(128, 0, 128, 0.2);
            transform: translateY(-3px);
        }

        .feedback-header {
            background: linear-gradient(90deg, rgba(128, 0, 128, 0.2), rgba(255, 107, 107, 0.15));
            padding: 25px;
            border-bottom: 1px solid rgba(128, 0, 128, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }

        .feedback-header-info {
            flex: 1;
            min-width: 250px;
        }

        .feedback-subject {
            font-weight: 700;
            color: #ff6b6b;
            font-size: 1.2rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .feedback-date {
            color: #9a9a9a;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
        }

        .rating-display {
            color: #ffc107;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }

        .rating-display i {
            font-size: 1rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-open {
            background: rgba(255, 152, 0, 0.2);
            color: #ff9800;
            border: 1px solid rgba(255, 152, 0, 0.4);
        }

        .status-replied {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.4);
            position: relative;
        }

        .new-reply-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: #ff6b6b;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .feedback-body {
            padding: 25px;
        }

        .feedback-subject-display {
            background: rgba(128, 0, 128, 0.1);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #800080;
            margin-bottom: 20px;
            color: #e0e0e0;
            line-height: 1.7;
        }

        .feedback-subject-display strong {
            color: #ff6b6b;
        }

        .feedback-message {
            background: rgba(128, 0, 128, 0.08);
            padding: 18px;
            border-radius: 8px;
            border-left: 4px solid #800080;
            margin-bottom: 20px;
            color: #d0d0d0;
            line-height: 1.8;
        }

        .feedback-message strong {
            color: #ff6b6b;
            display: block;
            margin-bottom: 10px;
        }

        .feedback-meta {
            display: flex;
            gap: 20px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(128, 0, 128, 0.2);
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
            color: #9a9a9a;
        }

        .meta-item i {
            color: #ff6b6b;
        }

        /* Conversation/Reply Section */
        .conversation-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(128, 0, 128, 0.2);
        }

        .conversation-header {
            color: #ff6b6b;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #800080;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.05rem;
        }

        .conversation-empty {
            text-align: center;
            padding: 30px 20px;
            color: #7a7a7a;
            font-style: italic;
        }

        .conversation-empty i {
            font-size: 2rem;
            color: rgba(255, 107, 107, 0.2);
            display: block;
            margin-bottom: 10px;
        }

        .message-admin {
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.15), rgba(76, 175, 80, 0.08));
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.2);
            transition: all 0.3s ease;
        }

        .message-admin:hover {
            border-color: #4caf50;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.1);
        }

        .message-sender {
            font-weight: 700;
            color: #4caf50;
            font-size: 0.95rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .message-text {
            color: #d0d0d0;
            line-height: 1.7;
            margin-bottom: 12px;
        }

        .message-time {
            font-size: 0.85rem;
            color: #7a7a7a;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .mark-read-btn {
            background: rgba(255, 107, 107, 0.15) !important;
            color: #ff6b6b !important;
            border: 1px solid #ff6b6b !important;
            border-radius: 6px;
            padding: 8px 16px !important;
            margin-top: 15px !important;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .mark-read-btn:hover {
            background: rgba(255, 107, 107, 0.25) !important;
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.2);
        }

        /* Empty State */
        .no-feedback {
            text-align: center;
            padding: 80px 40px;
        }

        .empty-state-icon {
            font-size: 4.5rem;
            color: rgba(255, 107, 107, 0.2);
            margin-bottom: 25px;
        }

        .no-feedback strong {
            display: block;
            font-size: 1.3rem;
            color: #ffffff;
            margin-bottom: 12px;
        }

        .no-feedback p {
            color: #8a8a8a;
            font-size: 0.95rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn-primary {
            background: linear-gradient(135deg, #800080, #6b0080);
            border: none;
            color: white;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #6b0080, #5a0070);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(128, 0, 128, 0.3);
            color: white;
            text-decoration: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .feedback-header {
                flex-direction: column;
            }

            .page-header {
                padding: 25px;
                margin: 20px;
            }

            .page-header h2 {
                font-size: 1.5rem;
            }

            .feedback-body {
                padding: 18px;
            }

            .stats-container {
                gap: 15px;
            }

            .stat-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid px-4 px-md-5">
            <a class="navbar-brand" href="Student_Dashboard.php">
                <img src="shikshalogo.jpg" alt="Career Portal Logo">
                <span>Career Portal</span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="Contact.html"><i class="fas fa-envelope"></i> Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Student_Dashboard.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header with Title -->
    <div class="page-header">
        <h2><i class="fas fa-comments"></i> My Feedback & Responses</h2>
        <p>Track your submitted feedback and admin responses in one place</p>
    </div>

    <!-- Notification Alert -->
    <?php if ($notification_count > 0): ?>
        <div class="alerts-container">
            <div class="alert alert-info">
                <i class="fas fa-bell"></i>
                <strong>You have <?php echo $notification_count; ?> new <?php echo $notification_count == 1 ? 'reply' : 'replies'; ?>!</strong> 
                Check below to see the admin responses.
            </div>
        </div>
    <?php endif; ?>

    <?php if ($feedback_result->num_rows > 0): ?>
        <!-- Statistics Dashboard -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-comments"></i></div>
                <div class="stat-number"><?php echo $feedback_result->num_rows; ?></div>
                <div class="stat-label">Total Feedback</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-number">
                    <?php
                        $feedback_result->data_seek(0);
                        $pending_count = 0;
                        while ($row = $feedback_result->fetch_assoc()) {
                            if (($row['status'] ?? 'open') === 'open') $pending_count++;
                        }
                        echo $pending_count;
                    ?>
                </div>
                <div class="stat-label">Awaiting Response</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-number">
                    <?php
                        $feedback_result->data_seek(0);
                        $replied_count = 0;
                        while ($row = $feedback_result->fetch_assoc()) {
                            if (($row['status'] ?? 'open') === 'replied') $replied_count++;
                        }
                        echo $replied_count;
                    ?>
                </div>
                <div class="stat-label">Replied</div>
            </div>
        </div>

        <!-- Feedback Cards -->
        <div class="feedback-container">
            <?php
                $feedback_result->data_seek(0);
                while ($feedback = $feedback_result->fetch_assoc()):
            ?>
                <?php
                    $feedback_id = $feedback['id'];
                    $replies_result = $mysqli->query("SELECT fr.*, a.name as admin_name FROM feedback_replies fr LEFT JOIN admin_users a ON fr.admin_id = a.id WHERE fr.feedback_id = $feedback_id ORDER BY fr.created_at ASC");
                    $reply_count = $replies_result->num_rows;
                    
                    // Check if this feedback has unread replies
                    $unread_check = $mysqli->query("SELECT id FROM feedback_notifications WHERE feedback_id = $feedback_id AND user_id = $user_id AND is_read = 0");
                    $has_unread = $unread_check->num_rows > 0;
                    
                    $status = $feedback['status'] ?? 'open';
                ?>
                <div class="feedback-card">
                    <!-- Card Header -->
                    <div class="feedback-header">
                        <div class="feedback-header-info">
                            <div class="feedback-subject">
                                <i class="fas fa-quote-left"></i>
                                <?php echo htmlspecialchars($feedback['subject'] ?? 'Feedback'); ?>
                            </div>
                            <div class="feedback-date">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo date('F j, Y • g:i A', strtotime($feedback['created_at'])); ?>
                            </div>
                            <?php if (!empty($feedback['rating'])): ?>
                                <div class="rating-display">
                                    <i class="fas fa-star"></i>
                                    <?php for ($i = 1; $i < intval($feedback['rating']); $i++) echo '<i class="fas fa-star"></i> '; ?>
                                    <span><?php echo $feedback['rating']; ?>/5 Rating</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <?php if ($has_unread): ?>
                                <span class="status-badge status-replied">
                                    <span class="new-reply-indicator"></span>NEW REPLY!
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-<?php echo $status; ?>">
                                    <i class="fas fa-<?php echo $status === 'replied' ? 'check-circle' : 'clock'; ?>"></i> 
                                    <?php echo strtoupper($status); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="feedback-body">
                        <!-- User's Message -->
                        <div class="feedback-message">
                            <strong><i class="fas fa-user-circle"></i> Your Message</strong>
                            <?php echo nl2br(htmlspecialchars($feedback['message'])); ?>
                        </div>

                        <!-- Admin Response Section -->
                        <?php if ($reply_count > 0): ?>
                            <div class="conversation-section">
                                <div class="conversation-header">
                                    <i class="fas fa-reply"></i>
                                    Admin Response<?php echo $reply_count > 1 ? " ({$reply_count})" : ''; ?>
                                </div>
                                
                                <?php while ($reply = $replies_result->fetch_assoc()): ?>
                                    <div class="message-admin">
                                        <div class="message-sender">
                                            <i class="fas fa-user-tie"></i>
                                            <?php echo htmlspecialchars($reply['admin_name'] ?? 'Administrator'); ?>
                                        </div>
                                        <div class="message-text">
                                            <?php echo nl2br(htmlspecialchars($reply['reply_message'])); ?>
                                        </div>
                                        <div class="message-time">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('F j, Y • g:i A', strtotime($reply['created_at'])); ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>

                                <?php if ($has_unread): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="feedback_id" value="<?php echo $feedback_id; ?>">
                                        <!-- CRITICAL FIX: Add CSRF token -->
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <button type="submit" class="mark-read-btn">
                                            <i class="fas fa-check"></i> Mark as Read
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="conversation-section">
                                <div class="conversation-empty">
                                    <i class="fas fa-inbox"></i>
                                    ⏳ Waiting for admin response...
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

    <?php else: ?>
        <!-- Empty State -->
        <div class="feedback-container">
            <div class="feedback-card">
                <div class="no-feedback">
                    <div class="empty-state-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <strong>No feedback submitted yet</strong>
                    <p>Start by submitting your feedback to get guidance and responses from our admin team.</p>
                    <a href="Feedback.html" class="btn-primary">
                        <i class="fas fa-plus"></i> Submit Your First Feedback
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Database connection closed automatically by PHP
?>
