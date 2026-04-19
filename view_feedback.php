<?php
require_once __DIR__ . '/bootstrap.php';

// CRITICAL FIX: Create database connection BEFORE using it
$mysqli = new mysqli("localhost", "root", "", "career_counseling");

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: Log-in (Admin).php");
    exit();
}

// CRITICAL FIX: Verify admin exists in database to prevent privilege escalation
$admin_verify = $mysqli->prepare("SELECT id FROM admin_users WHERE id = ?");
if (!$admin_verify) {
    die("Database error");
}
$admin_verify->bind_param("i", $_SESSION['admin_id']);
$admin_verify->execute();
if ($admin_verify->get_result()->num_rows === 0) {
    session_destroy();
    header("Location: Log-in (Admin).php");
    exit();
}
$admin_verify->close();

$message = '';
$message_type = '';

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_reply') {
    // CSRF Token Validation - CRITICAL FIX: Use hash_equals() to prevent timing attacks
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = "Invalid request. Please try again.";
        $message_type = "error";
    } else {
        $feedback_id = intval($_POST['feedback_id']);
        $reply_message = trim($_POST['reply_message'] ?? '');
        $admin_id = $_SESSION['admin_id'];
        
        if (empty($reply_message)) {
            $message = "Reply message cannot be empty!";
            $message_type = "error";
        } else if (strlen($reply_message) > 5000) {
            $message = "Reply message is too long (maximum 5000 characters)!";
            $message_type = "error";
        } else {
            try {
                $reply_stmt = $mysqli->prepare("INSERT INTO feedback_replies (feedback_id, admin_id, reply_message, created_at) VALUES (?, ?, ?, NOW())");
                
                if (!$reply_stmt) {
                    error_log("Prepare failed: " . $mysqli->error);
                    throw new Exception("An error occurred while processing your reply. Please try again later.");
                }
                
                $reply_stmt->bind_param("iis", $feedback_id, $admin_id, $reply_message);
                
                if (!$reply_stmt->execute()) {
                    error_log("Execute failed: " . $reply_stmt->error);
                    throw new Exception("An error occurred while submitting your reply. Please try again later.");
                }
                
                // Get the user_id from feedback
                $feedback_user_stmt = $mysqli->prepare("SELECT user_id FROM feedback WHERE id = ?");
                if (!$feedback_user_stmt) {
                    error_log("Prepare failed: " . $mysqli->error);
                    throw new Exception("Cannot retrieve user information. Please try again later.");
                }
                
                $feedback_user_stmt->bind_param("i", $feedback_id);
                $feedback_user_stmt->execute();
                $feedback_user_result = $feedback_user_stmt->get_result();
                $feedback_user = $feedback_user_result->fetch_assoc();
                
                if ($feedback_user && $feedback_user['user_id']) {
                    // Create notification for user
                    $notification_stmt = $mysqli->prepare("INSERT INTO feedback_notifications (feedback_id, user_id, is_read, created_at) VALUES (?, ?, 0, NOW())");
                    if (!$notification_stmt) {
                        error_log("Prepare failed: " . $mysqli->error);
                        throw new Exception("Cannot create notification. Please try again later.");
                    }
                    
                    $notification_stmt->bind_param("ii", $feedback_id, $feedback_user['user_id']);
                    if (!$notification_stmt->execute()) {
                        error_log("Execute failed: " . $notification_stmt->error);
                        // Non-critical error - continue
                    }
                    $notification_stmt->close();
                }
                
                // Update feedback status using prepared statement
                $status_stmt = $mysqli->prepare("UPDATE feedback SET status = 'replied' WHERE id = ?");
                if (!$status_stmt) {
                    error_log("Prepare failed: " . $mysqli->error);
                    throw new Exception("Cannot update feedback status. Please try again later.");
                }
                
                $status_stmt->bind_param("i", $feedback_id);
                if (!$status_stmt->execute()) {
                    error_log("Execute failed: " . $status_stmt->error);
                    throw new Exception("Cannot update feedback status. Please try again later.");
                }
                $status_stmt->close();
                
                $message = "Reply added successfully! User will be notified.";
                $message_type = "success";
                $feedback_user_stmt->close();
                $reply_stmt->close();
            } catch (Exception $e) {
                $message = $e->getMessage();
                $message_type = "error";
            }
        }
    }
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Invalid request. Please try again.";
        $message_type = "error";
    } else {
        try {
            $feedback_id = intval($_POST['feedback_id']);
            $delete_stmt = $mysqli->prepare("DELETE FROM feedback WHERE id = ?");
            
            if (!$delete_stmt) {
                error_log("Prepare failed: " . $mysqli->error);
                throw new Exception("An error occurred while processing your request. Please try again later.");
            }
            
            $delete_stmt->bind_param("i", $feedback_id);
            if (!$delete_stmt->execute()) {
                error_log("Execute failed: " . $delete_stmt->error);
                throw new Exception("An error occurred while deleting feedback. Please try again later.");
            }
            
            $message = "Feedback deleted successfully!";
            $message_type = "success";
            $delete_stmt->close();
        } catch (Exception $e) {
            $message = $e->getMessage();
            $message_type = "error";
        }
    }
}

// CRITICAL FIX: Use prepared statement instead of direct query to prevent SQL injection
$stmt = $mysqli->prepare("SELECT * FROM feedback ORDER BY created_at DESC");
if (!$stmt) {
    die("Database error: " . $mysqli->error);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Feedback - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom, #000, #4b0082);
            color: white;
            font-family: 'Arial', sans-serif;
            padding-bottom: 40px;
        }

        .navbar {
            background-color: black;
            padding: 20px 40px;
            margin-bottom: 40px;
        }

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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 0 20px;
        }

        .page-header h2 {
            margin: 0;
            color: white;
        }

        .container-fluid {
            padding: 0 20px;
        }

        .card {
            background-color: rgba(0, 0, 0, 0.8);
            border: 1px solid #ff6b6b;
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
        }

        .card-header {
            background-color: #ff6b6b;
            color: white;
            font-weight: 600;
            padding: 15px 20px;
        }

        .feedback-item {
            background-color: rgba(255, 107, 107, 0.05);
            padding: 20px;
            margin: 15px;
            border-radius: 8px;
            border-left: 4px solid #ff6b6b;
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .feedback-info {
            flex: 1;
        }

        .feedback-name {
            font-weight: 700;
            color: #ff6b6b;
            font-size: 1.1rem;
        }

        .feedback-meta {
            color: #aaa;
            font-size: 0.95rem;
            margin-top: 5px;
        }

        .rating-stars {
            color: #ffc107;
            font-size: 1.1rem;
            margin-top: 8px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-open {
            background-color: #ff9800;
            color: white;
        }

        .status-replied {
            background-color: #4caf50;
            color: white;
        }

        .feedback-message {
            background-color: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            color: #ddd;
            line-height: 1.6;
        }

        .replies-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ff6b6b;
        }

        .reply-item {
            background-color: rgba(128, 0, 128, 0.1);
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 3px solid #800080;
        }

        .reply-header {
            font-weight: 600;
            color: #800080;
            font-size: 0.95rem;
        }

        .reply-time {
            font-size: 0.85rem;
            color: #aaa;
        }

        .reply-message {
            margin-top: 8px;
            color: #ddd;
        }

        .reply-form {
            background-color: rgba(128, 0, 128, 0.05);
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.08);
            border: 1px solid #666;
            color: white;
            font-size: 0.95rem;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.12);
            border-color: #ff6b6b;
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 107, 0.25);
        }

        .form-control::placeholder {
            color: #999;
        }

        .form-control:disabled {
            background-color: rgba(255, 255, 255, 0.05);
            color: #999;
        }

        .feedback-subject {
            font-size: 1rem;
            color: #fff;
            margin-top: 10px;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            border-left: 4px solid #ff6b6b;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .btn-view {
            background-color: #17a2b8;
            color: white;
        }

        .btn-view:hover {
            background-color: #138496;
            transform: translateY(-2px);
        }

        .btn-reply {
            background-color: #800080;
            color: white;
        }

        .btn-reply:hover {
            background-color: #6b0080;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: #ff6b6b;
            color: white;
        }

        .btn-danger:hover {
            background-color: #ff5252;
            transform: translateY(-2px);
        }

        /* Modal Styles */
        .modal {
            background-color: rgba(0, 0, 0, 0.7);
        }

        .modal-content {
            background-color: rgba(0, 0, 0, 0.95);
            border: 1px solid #ff6b6b;
            border-radius: 10px;
            color: white;
        }

        .modal-header {
            border-bottom: 1px solid #ff6b6b;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-title {
            color: #ff6b6b;
            font-weight: bold;
        }

        .modal-body {
            padding: 20px;
        }

        .detail-field {
            margin-bottom: 20px;
        }

        .detail-label {
            color: #ff6b6b;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 5px;
        }

        .detail-content {
            background-color: rgba(255, 255, 255, 0.05);
            padding: 12px 15px;
            border-radius: 5px;
            color: #ddd;
            border-left: 3px solid #ff6b6b;
            word-break: break-word;
        }

        .stars-display {
            font-size: 1.3rem;
            color: #ffc107;
        }

        .feedback-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
            padding: 0 20px;
        }

        .stat-card {
            background-color: rgba(255, 107, 107, 0.1);
            border: 1px solid #ff6b6b;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #ff6b6b;
        }

        .stat-label {
            color: #aaa;
            font-size: 0.9rem;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="Admin_Dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </nav>

    <div class="page-header">
        <h2><i class="fas fa-comments"></i> Feedback Management</h2>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert" style="margin: 20px;">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo htmlspecialchars($message); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Statistics Section -->
    <?php 
    $total_feedback = $mysqli->query("SELECT COUNT(*) as count FROM feedback")->fetch_assoc()['count'];
    $open_feedback = $mysqli->query("SELECT COUNT(*) as count FROM feedback WHERE status = 'open'")->fetch_assoc()['count'];
    $replied_feedback = $total_feedback - $open_feedback;
    ?>
    <div class="feedback-stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_feedback; ?></div>
            <div class="stat-label">Total Feedback</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $open_feedback; ?></div>
            <div class="stat-label">Pending Response</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $replied_feedback; ?></div>
            <div class="stat-label">Replied</div>
        </div>
    </div>

    <div class="container-fluid" style="max-width: 1100px;">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($feedback = $result->fetch_assoc()): ?>
                <?php 
                    $feedback_id = intval($feedback['id']);
                    $replies_stmt = $mysqli->prepare("SELECT fr.*, a.name as admin_name FROM feedback_replies fr LEFT JOIN admin_users a ON fr.admin_id = a.id WHERE fr.feedback_id = ? ORDER BY fr.created_at ASC");
                    $replies_stmt->bind_param("i", $feedback_id);
                    $replies_stmt->execute();
                    $replies_result = $replies_stmt->get_result();
                    $reply_count = $replies_result->num_rows;
                    $status = $feedback['status'] ?? 'open';
                    $rating = intval($feedback['rating'] ?? 0);
                ?>
                <div class="card">
                    <div class="feedback-item">
                        <div class="feedback-header">
                            <div class="feedback-info">
                                <div class="feedback-name">
                                    <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($feedback['name']); ?>
                                </div>
                                <div class="feedback-meta">
                                    <strong>📧 Email:</strong> <?php echo htmlspecialchars($feedback['email']); ?><br>
                                    <strong>📅 Date:</strong> <?php echo date('F d, Y - H:i', strtotime($feedback['created_at'])); ?><br>
                                    <strong>⭐ Rating:</strong> 
                                    <span class="rating-stars">
                                        <?php for ($i = 0; $i < $rating; $i++) echo '<i class="fas fa-star"></i> '; ?>
                                    </span>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <span class="status-badge status-<?php echo $status; ?>">
                                    <i class="fas fa-<?php echo $status === 'replied' ? 'check-circle' : 'clock'; ?>"></i> 
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($feedback['subject'])): ?>
                        <div class="feedback-subject">
                            <strong>📝 Subject:</strong> <?php echo htmlspecialchars($feedback['subject']); ?>
                        </div>
                        <?php endif; ?>

                        <div class="feedback-message">
                            <strong>Message:</strong><br>
                            <div style="margin-top: 8px;">
                                <?php echo htmlspecialchars(substr($feedback['message'], 0, 200)); ?>
                                <?php if (strlen($feedback['message']) > 200): ?>
                                    <span style="color: #999;">...</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($reply_count > 0): ?>
                            <div class="replies-section">
                                <h5 style="margin-bottom: 15px; color: #ff6b6b;">
                                    <i class="fas fa-reply"></i> Replies (<?php echo $reply_count; ?>)
                                </h5>
                                
                                <?php while ($reply = $replies_result->fetch_assoc()): ?>
                                    <div class="reply-item">
                                        <div class="reply-header">
                                            <i class="fas fa-user-tie"></i> Admin: <?php echo htmlspecialchars($reply['admin_name'] ?? 'System'); ?>
                                        </div>
                                        <div class="reply-time">
                                            <i class="fas fa-clock"></i> <?php echo date('Y-m-d H:i', strtotime($reply['created_at'])); ?>
                                        </div>
                                        <div class="reply-message">
                                            <?php echo htmlspecialchars(substr($reply['reply_message'], 0, 150)); ?>
                                            <?php if (strlen($reply['reply_message']) > 150): ?>
                                                <span style="color: #999;">...</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <!-- View Details Button -->
                            <button type="button" class="btn-action btn-view" data-toggle="modal" data-target="#detailModal<?php echo $feedback_id; ?>">
                                <i class="fas fa-eye"></i> View Details
                            </button>

                            <!-- Reply Button -->
                            <button type="button" class="btn-action btn-reply" data-toggle="modal" data-target="#replyModal<?php echo $feedback_id; ?>">
                                <i class="fas fa-reply"></i> Reply
                            </button>

                            <!-- Delete Button -->
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this feedback? This action cannot be undone.');">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="feedback_id" value="<?php echo $feedback_id; ?>">
                                <button type="submit" class="btn-action btn-danger">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- View Details Modal -->
                <div class="modal fade" id="detailModal<?php echo $feedback_id; ?>" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel<?php echo $feedback_id; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailModalLabel<?php echo $feedback_id; ?>">
                                    <i class="fas fa-comments"></i> Feedback Details
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="detail-field">
                                    <div class="detail-label"><i class="fas fa-user"></i> User Name</div>
                                    <div class="detail-content"><?php echo htmlspecialchars($feedback['name']); ?></div>
                                </div>

                                <div class="detail-field">
                                    <div class="detail-label"><i class="fas fa-envelope"></i> Email Address</div>
                                    <div class="detail-content"><?php echo htmlspecialchars($feedback['email']); ?></div>
                                </div>

                                <?php if (!empty($feedback['subject'])): ?>
                                <div class="detail-field">
                                    <div class="detail-label"><i class="fas fa-heading"></i> Subject</div>
                                    <div class="detail-content"><?php echo htmlspecialchars($feedback['subject']); ?></div>
                                </div>
                                <?php endif; ?>

                                <div class="detail-field">
                                    <div class="detail-label"><i class="fas fa-comment"></i> Message</div>
                                    <div class="detail-content"><?php echo htmlspecialchars($feedback['message']); ?></div>
                                </div>

                                <div class="detail-field">
                                    <div class="detail-label"><i class="fas fa-star"></i> Rating</div>
                                    <div class="detail-content">
                                        <span class="stars-display">
                                            <?php for ($i = 0; $i < $rating; $i++) echo '<i class="fas fa-star"></i> '; ?>
                                        </span>
                                        (<?php echo $rating; ?>/5)
                                    </div>
                                </div>

                                <div class="detail-field">
                                    <div class="detail-label"><i class="fas fa-calendar"></i> Submitted On</div>
                                    <div class="detail-content"><?php echo date('F d, Y - H:i:s', strtotime($feedback['created_at'])); ?></div>
                                </div>

                                <div class="detail-field">
                                    <div class="detail-label"><i class="fas fa-info-circle"></i> Status</div>
                                    <div class="detail-content">
                                        <span class="status-badge status-<?php echo $status; ?>">
                                            <i class="fas fa-<?php echo $status === 'replied' ? 'check-circle' : 'clock'; ?>"></i> 
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </div>
                                </div>

                                <?php if ($reply_count > 0): ?>
                                <div class="detail-field">
                                    <div class="detail-label"><i class="fas fa-reply"></i> Admin Replies (<?php echo $reply_count; ?>)</div>
                                    <?php $replies_stmt->execute(); $replies_result = $replies_stmt->get_result(); ?>
                                    <?php while ($reply = $replies_result->fetch_assoc()): ?>
                                    <div style="background-color: rgba(128, 0, 128, 0.1); padding: 12px 15px; border-radius: 5px; border-left: 3px solid #800080; margin-bottom: 10px;">
                                        <div style="color: #ff6b6b; font-weight: 600; margin-bottom: 5px;">
                                            <i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($reply['admin_name'] ?? 'System'); ?>
                                        </div>
                                        <div style="font-size: 0.85rem; color: #aaa; margin-bottom: 8px;">
                                            <i class="fas fa-clock"></i> <?php echo date('F d, Y - H:i:s', strtotime($reply['created_at'])); ?>
                                        </div>
                                        <div style="color: #ddd;"><?php echo htmlspecialchars($reply['reply_message']); ?></div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reply Modal -->
                <div class="modal fade" id="replyModal<?php echo $feedback_id; ?>" tabindex="-1" role="dialog" aria-labelledby="replyModalLabel<?php echo $feedback_id; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="replyModalLabel<?php echo $feedback_id; ?>">
                                    <i class="fas fa-reply"></i> Reply to Feedback
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <div class="modal-body">
                                    <div class="detail-field">
                                        <div class="detail-label">From: <?php echo htmlspecialchars($feedback['name']); ?></div>
                                        <div class="detail-content"><?php echo htmlspecialchars($feedback['message']); ?></div>
                                    </div>

                                    <div class="detail-field" style="margin-top: 20px;">
                                        <div class="detail-label"><i class="fas fa-pen-fancy"></i> Your Reply</div>
                                        <textarea class="form-control" name="reply_message" rows="5" placeholder="Type your reply here. Be helpful and professional..." required style="resize: vertical;"></textarea>
                                        <small style="color: #aaa; margin-top: 5px;">Maximum 5000 characters</small>
                                    </div>
                                </div>
                                <div class="modal-footer" style="background-color: rgba(0, 0, 0, 0.3); border-top: 1px solid #ff6b6b;">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="background-color: #666;">Cancel</button>
                                    <input type="hidden" name="action" value="add_reply">
                                    <input type="hidden" name="feedback_id" value="<?php echo $feedback_id; ?>">
                                    <button type="submit" class="btn btn-reply" style="background-color: #800080; border-color: #800080;">
                                        <i class="fas fa-paper-plane"></i> Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            <?php $stmt->close(); ?>
        <?php else: ?>
            <div class="card">
                <div class="no-feedback">
                    <i class="fas fa-inbox"></i>
                    <p><strong>No feedback received yet.</strong></p>
                    <small style="color: #888;">When users submit feedback, it will appear here.</small>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

