<?php
require_once __DIR__ . '/bootstrap.php';

// Check if either admin or user is accessing
$is_admin = isset($_SESSION['admin_id']) && $_SESSION['user_type'] === 'admin';
$is_user = isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'student';

if (!$is_admin && !$is_user) {
    header("Location: Log-in (Student).php");
    exit();
}

// Get and validate contact_id
$contact_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (empty($contact_id) || $contact_id <= 0) {
    header("Location: " . ($is_admin ? "admin_contacts.php" : "my_messages.php"));
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "career_counseling");
if ($mysqli->connect_error) {
    error_log("Database connection error: " . $mysqli->connect_error);
    die("Database connection failed. Please contact support.");
}

$user_id = $is_user ? $_SESSION['user_id'] : 0;

// AUTHORIZATION CHECK BEFORE QUERY - Build appropriate query based on user role
$contact_stmt = null;
if ($is_admin) {
    // Admins can see all messages
    $contact_stmt = $mysqli->prepare("SELECT * FROM contact_messages WHERE id = ?");
    if ($contact_stmt) {
        $contact_stmt->bind_param("i", $contact_id);
    }
} else if ($is_user) {
    // Users can only see their own messages
    $contact_stmt = $mysqli->prepare("SELECT * FROM contact_messages WHERE id = ? AND user_id = ?");
    if ($contact_stmt) {
        $contact_stmt->bind_param("ii", $contact_id, $user_id);
    }
} else {
    header("Location: Log-in (Student).php");
    exit();
}

if (!$contact_stmt) {
    error_log("Statement preparation failed: " . $mysqli->error);
    die("Database error. Please try again.");
}

$contact_stmt->execute();
$contact_result = $contact_stmt->get_result();
$contact = $contact_result ? $contact_result->fetch_assoc() : null;
$contact_stmt->close();

if (!$contact) {
    header("Location: " . ($is_admin ? "admin_contacts.php" : "my_messages.php"));
    exit();
}

// Get replies using prepared statement
$replies = [];
try {
    $replies_stmt = $mysqli->prepare(
        "SELECT cr.*, au.name as admin_name FROM contact_replies cr 
         LEFT JOIN admin_users au ON cr.admin_id = au.id 
         WHERE cr.contact_id = ? 
         ORDER BY cr.created_at ASC"
    );
    
    if ($replies_stmt) {
        $replies_stmt->bind_param("i", $contact_id);
        $replies_stmt->execute();
        $replies_result = $replies_stmt->get_result();
        
        while ($reply = $replies_result->fetch_assoc()) {
            // Sanitize output to prevent XSS
            $reply['reply_message'] = htmlspecialchars($reply['reply_message'], ENT_QUOTES, 'UTF-8');
            $reply['admin_name'] = htmlspecialchars($reply['admin_name'] ?? 'Administrator', ENT_QUOTES, 'UTF-8');
            $replies[] = $reply;
        }
        $replies_stmt->close();
    }
} catch (Exception $e) {
    error_log("Error fetching replies: " . $e->getMessage());
    $replies = [];
}

// Get is_read status (only for non-admin users viewing their messages)
// NOTE: This is a READ operation only - does NOT auto-mark as read
$is_read = true; // Default to true
if ($is_user && !$is_admin) {
    try {
        // Check if is_read column exists safely
        $check_column = $mysqli->query("SHOW COLUMNS FROM contact_messages LIKE 'is_read'");
        $has_column = ($check_column && $check_column->num_rows > 0);
        
        if ($has_column) {
            // Column exists, fetch the is_read status using prepared statement
            $read_stmt = $mysqli->prepare("SELECT is_read FROM contact_messages WHERE id = ? AND user_id = ?");
            if ($read_stmt) {
                $read_stmt->bind_param("ii", $contact_id, $user_id);
                $read_stmt->execute();
                $read_result = $read_stmt->get_result();
                
                if ($read_result && $read_result->num_rows > 0) {
                    $read_data = $read_result->fetch_assoc();
                    $is_read = (bool)$read_data['is_read'];
                }
                $read_stmt->close();
            }
        }
    } catch (Exception $e) {
        error_log("Error checking is_read status: " . $e->getMessage());
        $is_read = true;
    }
}

// Close database connection
$mysqli->close();

// Ensure CSRF token exists in session for form protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Contact Message</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #333;
            min-height: 100vh;
        }

        .navbar {
            background-color: black;
            padding: 20px 40px;
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            color: white;
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
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

        .container-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 40px 40px 40px;
        }

        .message-card {
            background: linear-gradient(135deg, #800080 0%, #4B0082 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            margin-bottom: 2rem;
        }

        .message-card h3 {
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .message-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .info-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 8px;
            border-left: 3px solid #ff6b6b;
        }

        .info-label {
            font-size: 0.85rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 500;
            word-break: break-word;
        }

        .message-content {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            line-height: 1.6;
        }

        .status-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-open {
            background-color: #ff6b6b;
            color: white;
        }

        .status-replied {
            background-color: #51cf66;
            color: white;
        }

        .replies-section {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid rgba(255, 255, 255, 0.1);
        }

        .replies-section h4 {
            color: white;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .reply-item {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .reply-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .reply-from {
            font-weight: 600;
            color: #800080;
            font-size: 1rem;
        }

        .reply-date {
            font-size: 0.85rem;
            color: #757575;
        }

        .reply-content {
            color: #424242;
            line-height: 1.6;
            font-size: 0.95rem;
            word-break: break-word;
        }

        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
        }

        .form-section h4 {
            margin-bottom: 1.5rem;
            color: #800080;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-weight: 500;
            color: #424242;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #800080;
            box-shadow: 0 0 0 3px rgba(128, 0, 128, 0.1);
            outline: none;
        }

        .textarea {
            min-height: 150px;
            resize: vertical;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .btn-submit {
            background: linear-gradient(135deg, #800080 0%, #4B0082 100%);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(128, 0, 128, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }

        .alert-success {
            background-color: #e8f5e9;
            color: #1b5e20;
            border-color: #51cf66;
        }

        .alert-error {
            background-color: #ffebee;
            color: #c62828;
            border-color: #ff6b6b;
        }

        .alert-info {
            background-color: #e3f2fd;
            color: #0d47a1;
            border-color: #42a5f5;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            margin: 2rem 0;
        }

        .spinner {
            border: 4px solid rgba(128, 0, 128, 0.1);
            border-top: 4px solid #800080;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #999;
        }

        .empty-state i {
            font-size: 3rem;
            color: rgba(255, 255, 255, 0.3);
            margin-bottom: 1rem;
        }

        .read-only-notice {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .read-only-notice i {
            font-size: 1.5rem;
        }

        .mark-as-read-btn {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .mark-as-read-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(81, 207, 102, 0.3);
        }

        .mark-as-read-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .mark-as-read-btn.read {
            background: linear-gradient(135deg, #a0aec0 0%, #8899aa 100%);
        }

        .replies-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
                flex-direction: column;
                gap: 1rem;
            }

            .navbar h2 {
                font-size: 1.2rem;
            }

            .container-main {
                padding: 0 20px 40px 20px;
            }

            .message-card {
                padding: 1.5rem;
            }

            .message-card h3 {
                font-size: 1.4rem;
            }

            .message-info {
                grid-template-columns: 1fr;
            }

            .form-section {
                padding: 1.5rem;
            }

            .reply-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2><i class="fas fa-envelope" style="margin-right: 0.5rem;"></i>Contact Message</h2>
        <a href="<?php echo $is_admin ? 'admin_contacts.php' : 'my_messages.php'; ?>" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back
        </a>
    </div>

    <div class="container-main">
        <?php if (isset($_GET['reply_success'])): ?>
            <div class="alert alert-success">
                <strong>Success!</strong> Your reply has been sent.
            </div>
        <?php endif; ?>

        <!-- Contact Message Card -->
        <div class="message-card">
            <h3>
                <?php echo htmlspecialchars($contact['subject']); ?>
                <span class="status-badge status-<?php echo $contact['status'] === 'replied' ? 'replied' : 'open'; ?>">
                    <?php echo ucfirst($contact['status']); ?>
                </span>
            </h3>

            <div class="message-info">
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-user"></i> From</div>
                    <div class="info-value"><?php echo htmlspecialchars($contact['name']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-envelope"></i> Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($contact['email']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-phone"></i> Phone</div>
                    <div class="info-value"><?php echo htmlspecialchars($contact['phone'] ?? 'Not provided'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-calendar"></i> Received</div>
                    <div class="info-value"><?php echo date('M d, Y @ H:i', strtotime($contact['created_at'])); ?></div>
                </div>
            </div>

            <div class="message-content">
                <p><?php echo nl2br(htmlspecialchars($contact['message'])); ?></p>
            </div>

            <!-- Replies Section -->
            <div class="replies-section" id="replies-message-card">
                <div class="replies-header">
                    <h4 style="margin: 0;"><i class="fas fa-comments" style="margin-right: 0.5rem;"></i>Replies (<span id="reply-count"><?php echo count($replies); ?></span>)</h4>
                    <?php if ($is_user && !$is_admin && count($replies) > 0 && !$is_read): ?>
                        <button class="mark-as-read-btn" id="markAsReadBtn">
                            <i class="fas fa-check-circle"></i>
                            Mark as Read
                        </button>
                    <?php elseif ($is_user && !$is_admin && count($replies) > 0 && $is_read): ?>
                        <button class="mark-as-read-btn read" disabled>
                            <i class="fas fa-check"></i>
                            Marked as Read
                        </button>
                    <?php endif; ?>
                </div>

                <div id="replies-container">
                    <?php if (count($replies) > 0): ?>
                        <div class="alert alert-info" id="read-status-alert" style="<?php echo $is_read ? 'display: none;' : ''; ?>">
                            <strong>New Reply!</strong> You have unread replies from the admin team.
                        </div>
                        <?php foreach ($replies as $reply): ?>
                            <div class="reply-item">
                                <div class="reply-header">
                                    <span class="reply-from">
                                        <i class="fas fa-user-circle" style="margin-right: 0.5rem;"></i>
                                        <?php echo htmlspecialchars($reply['admin_name'] ?? 'System'); ?>
                                    </span>
                                    <span class="reply-date"><?php echo date('M d, Y @ H:i', strtotime($reply['created_at'])); ?></span>
                                </div>
                                <div class="reply-content">
                                    <?php echo nl2br(htmlspecialchars($reply['reply_message'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-comment-slash"></i>
                            <p>No replies yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Reply Form (Admin Only) -->
        <?php if ($is_admin): ?>
            <div class="form-section">
                <h4><i class="fas fa-reply" style="margin-right: 0.5rem;"></i>Add Your Reply</h4>
                
                <div id="alert-container"></div>

                <form id="replyForm">
                    <input type="hidden" name="action" value="add_reply">
                    <input type="hidden" name="contact_id" value="<?php echo htmlspecialchars($contact_id, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="form-group">
                        <label for="replyMessage">Your Reply <span style="color: #ff6b6b;">*</span></label>
                        <textarea 
                            id="replyMessage" 
                            name="reply_message" 
                            class="form-control textarea" 
                            placeholder="Type your reply here... (minimum 10 characters)" 
                            required
                        ></textarea>
                        <small style="color: #757575; margin-top: 0.5rem; display: block;">
                            <span id="char-count">0</span> / 5000 characters
                        </small>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i>
                        Send Reply
                    </button>

                    <div class="loading-spinner" id="loadingSpinner">
                        <div class="spinner"></div>
                        <p style="color: #800080; margin-top: 1rem;">Sending reply...</p>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="read-only-notice">
                <i class="fas fa-lock"></i>
                <span>This is a read-only view. Only administrators can reply to messages.</span>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Mark as Read button - USER MUST CLICK MANUALLY TO UPDATE STATUS
        // Status ONLY changes from Unread to Read when this button is explicitly clicked
        // Viewing the message does NOT automatically mark it as read
        const markAsReadBtn = document.getElementById('markAsReadBtn');
        if (markAsReadBtn) {
            markAsReadBtn.addEventListener('click', function() {
                const contactId = new URLSearchParams(window.location.search).get('id');
                
                markAsReadBtn.disabled = true;
                const originalHTML = markAsReadBtn.innerHTML;
                markAsReadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Marking...';

                fetch('mark_contact_as_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        contact_id: contactId,
                        csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update button state to indicate it's been marked
                        markAsReadBtn.classList.add('read');
                        markAsReadBtn.innerHTML = '<i class="fas fa-check"></i> Marked as Read';
                        markAsReadBtn.disabled = true;
                        
                        // Hide the unread notification alert
                        const alert = document.getElementById('read-status-alert');
                        if (alert) {
                            alert.style.display = 'none';
                        }
                        
                        // Show success message
                        showAlert('Message marked as read. Dashboard notification will be updated.', 'success');
                        
                        // Redirect to dashboard after 3 seconds to show refreshed notification counts
                        setTimeout(() => {
                            window.location.href = 'Student_Dashboard.php';
                        }, 3000);
                    } else {
                        markAsReadBtn.disabled = false;
                        markAsReadBtn.innerHTML = originalHTML;
                        showAlert(data.message || 'Failed to mark as read', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    markAsReadBtn.disabled = false;
                    markAsReadBtn.innerHTML = originalHTML;
                    showAlert('An error occurred while marking as read', 'error');
                });
            });
        }
        // Character counter
        const replyMessage = document.getElementById('replyMessage');
        const charCount = document.getElementById('char-count');
        
        if (replyMessage) {
            replyMessage.addEventListener('input', function() {
                charCount.textContent = this.value.length;
                if (this.value.length > 5000) {
                    this.value = this.value.substring(0, 5000);
                    charCount.textContent = '5000';
                }
            });
        }

        // Form submission
        document.getElementById('replyForm')?.addEventListener('submit', function(e) {
            e.preventDefault();

            const replyMessage = document.getElementById('replyMessage').value.trim();
            
            if (replyMessage.length < 10) {
                showAlert('Please enter at least 10 characters in your reply.', 'error');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            const loadingSpinner = document.getElementById('loadingSpinner');
            
            submitBtn.disabled = true;
            loadingSpinner.style.display = 'block';

            // Send AJAX request
            fetch('reply_contact.php', {
                method: 'POST',
                body: new FormData(this),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Reply sent successfully!', 'success');
                    
                    // Add new reply to the page
                    const newReply = document.createElement('div');
                    newReply.className = 'reply-item';
                    const now = new Date();
                    const dateStr = now.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: '2-digit'
                    }) + ' @ ' + now.toLocaleTimeString('en-US', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });
                    
                    newReply.innerHTML = `
                        <div class="reply-header">
                            <span class="reply-from">
                                <i class="fas fa-user-circle" style="margin-right: 0.5rem;"></i>
                                ${data.admin_name || 'Admin'}
                            </span>
                            <span class="reply-date">${dateStr}</span>
                        </div>
                        <div class="reply-content">
                            ${data.reply_message.replace(/\n/g, '<br>')}
                        </div>
                    `;
                    
                    const repliesContainer = document.getElementById('replies-container');
                    if (repliesContainer) {
                        // Remove empty state if it exists
                        const emptyState = repliesContainer.querySelector('.empty-state');
                        if (emptyState) {
                            emptyState.remove();
                        }
                        repliesContainer.appendChild(newReply);
                    }
                    
                    // Update reply count
                    const replyCount = document.getElementById('reply-count');
                    if (replyCount) {
                        replyCount.textContent = parseInt(replyCount.textContent) + 1;
                    }

                    // Clear form
                    document.getElementById('replyForm').reset();
                    charCount.textContent = '0';
                    
                    // Scroll to new reply
                    setTimeout(() => {
                        newReply.scrollIntoView({ behavior: 'smooth' });
                    }, 300);
                } else {
                    showAlert(data.message || 'Failed to send reply. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while sending your reply. Please try again.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                loadingSpinner.style.display = 'none';
            });
        });

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alert-container');
            if (!alertContainer) return;

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `<strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}`;
            
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alertDiv);

            // Auto-remove alert after 6 seconds
            setTimeout(() => {
                alertDiv.style.opacity = '0';
                alertDiv.style.transition = 'opacity 0.3s ease';
                setTimeout(() => alertDiv.remove(), 300);
            }, 6000);
        }
    </script>
</body>
</html>

