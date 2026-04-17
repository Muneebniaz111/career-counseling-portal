<?php
require_once __DIR__ . '/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: Log-in (Student).php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get user details from database
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$stmt->close();

// Get appointments
$stmt = $conn->prepare("SELECT a.*, c.name as counselor_name, c.specialization FROM appointments a 
                        LEFT JOIN counselors c ON a.counselor_id = c.id 
                        WHERE a.student_id = ? 
                        ORDER BY a.appointment_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$appointments_result = $stmt->get_result();
$stmt->close();

$conn->close();

// Get resource categories and items
$mysqli = new mysqli("localhost", "root", "", "career_counseling");

// Initialize counters
$notification_count = 0;
$contact_count = 0;
$new_replies_count = 0;

try {
    // Get feedback notification count using prepared statement
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
    error_log("Error getting feedback notifications: " . $e->getMessage());
    $notification_count = 0;
}

try {
    // Get contact message count using prepared statement
    $contact_stmt = $mysqli->prepare(
        "SELECT cm.id, cm.user_id, cm.status FROM contact_messages cm 
         WHERE cm.user_id = ? 
         ORDER BY cm.created_at DESC"
    );
    if ($contact_stmt) {
        $contact_stmt->bind_param("i", $user_id);
        $contact_stmt->execute();
        $contact_messages = $contact_stmt->get_result();
        if ($contact_messages) {
            $contact_count = $contact_messages->num_rows;
        }
        $contact_stmt->close();
    }
    
    // Count unread/new replies using prepared statements
    $has_is_read_column = false;
    $new_replies_count = 0;
    
    try {
        // Check if is_read column exists
        $check_is_read = $mysqli->query("SHOW COLUMNS FROM contact_messages LIKE 'is_read'");
        $has_is_read_column = ($check_is_read && $check_is_read->num_rows > 0);
        
        // Query based on column existence
        if ($has_is_read_column) {
            // Column exists - count unread messages (status='replied' AND is_read=0)
            $new_replies_stmt = $mysqli->prepare(
                "SELECT COUNT(*) as count FROM contact_messages WHERE user_id = ? AND status = 'replied' AND is_read = 0"
            );
        } else {
            // Column doesn't exist - use status only (backward compatibility)
            error_log("is_read column does not exist - using fallback query");
            $new_replies_stmt = $mysqli->prepare(
                "SELECT COUNT(*) as count FROM contact_messages WHERE user_id = ? AND status = 'replied'"
            );
        }
        
        if ($new_replies_stmt) {
            $new_replies_stmt->bind_param("i", $user_id);
            $new_replies_stmt->execute();
            $new_replies = $new_replies_stmt->get_result();
            
            if ($new_replies) {
                $new_replies_count = intval($new_replies->fetch_assoc()['count'] ?? 0);
            }
            $new_replies_stmt->close();
        }
        
    } catch (Exception $e) {
        error_log("Exception getting new replies count: " . $e->getMessage());
        $new_replies_count = 0;
    }
} catch (Exception $e) {
    error_log("Exception in contact message query: " . $e->getMessage());
    $contact_count = 0;
    $new_replies_count = 0;
}

// Try to get resource categories (optional)
$categories_result = null;
try {
    $categories_result = $mysqli->query("SELECT * FROM resource_categories ORDER BY created_at DESC");
} catch (Exception $e) {
    $categories_result = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Career Counseling Portal</title>
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
            color: #800080 !important;
        }

        .container {
            margin-top: 40px;
        }

        .dashboard-header {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 5px solid #800080;
        }

        .dashboard-header h1 {
            margin-bottom: 10px;
        }

        .card {
            background-color: rgba(0, 0, 0, 0.8);
            border: 1px solid #800080;
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #800080;
            border: none;
            color: white;
        }

        .card-body {
            color: white;
        }

        .btn-primary {
            background-color: #800080;
            border-color: #800080;
        }

        .btn-primary:hover {
            background-color: #4b0082;
            border-color: #4b0082;
        }

        .btn-danger {
            background-color: #ff6b6b;
        }

        .appointment-item {
            background-color: rgba(128, 0, 128, 0.1);
            padding: 15px;
            border-left: 3px solid #800080;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85rem;
        }

        .status-pending {
            background-color: #ffc107;
            color: black;
        }

        .status-confirmed {
            background-color: #28a745;
        }

        .status-completed {
            background-color: #17a2b8;
        }

        .logout-btn {
            float: right;
        }

        /* Resource Categories CSS */
        .resources-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .resource-category-card {
            background: linear-gradient(135deg, rgba(128, 0, 128, 0.1), rgba(255, 107, 107, 0.1));
            border: 2px solid #800080;
            border-radius: 10px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .resource-category-card:hover {
            transform: translateY(-5px);
            border-color: #ff6b6b;
            box-shadow: 0 10px 30px rgba(255, 107, 107, 0.2);
        }

        .resource-category-card h5 {
            color: #ff6b6b;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .resource-category-card .item-count {
            font-size: 0.9rem;
            color: #aaa;
            margin-top: 10px;
        }

        /* Resource Items Display */
        .resource-items-section {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .resource-items-section.show {
            max-height: 1000px;
        }

        .resource-items-container {
            background: rgba(128, 0, 128, 0.1);
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid #ff6b6b;
        }

        .resource-items-container h5 {
            color: #ff6b6b;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ff6b6b;
        }

        .resource-item {
            background: rgba(255, 107, 107, 0.05);
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            border-left: 3px solid #800080;
        }

        .resource-item h6 {
            color: #ff6b6b;
            margin-bottom: 8px;
        }

        .resource-item-description {
            color: #ddd;
            font-size: 0.95rem;
            margin: 8px 0;
        }

        .resource-item-url {
            margin-top: 10px;
        }

        .resource-item-url a {
            color: #ff6b6b;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .resource-item-url a:hover {
            text-decoration: underline;
        }

        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #ff6b6b;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .feedback-notification-card {
            background: linear-gradient(135deg, rgba(128, 0, 128, 0.08), rgba(75, 0, 130, 0.05));
            border: 1px solid rgba(128, 0, 128, 0.3);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
            transition: all 0.3s ease;
        }

        .feedback-notification-card.has-notification {
            background: linear-gradient(135deg, rgba(128, 0, 128, 0.12), rgba(75, 0, 130, 0.08));
            border-color: rgba(128, 0, 128, 0.5);
            box-shadow: 0 0 15px rgba(128, 0, 128, 0.2);
        }

        .feedback-notification-icon {
            font-size: 2rem;
            color: #800080;
            margin-bottom: 10px;
            transition: color 0.3s ease;
        }

        .feedback-notification-card.has-notification .feedback-notification-icon {
            color: #800080;
        }

        .feedback-notification-title {
            font-weight: 700;
            color: #800080;
            margin-bottom: 8px;
            transition: color 0.3s ease;
        }

        .feedback-notification-card.has-notification .feedback-notification-title {
            color: #800080;
        }

        .feedback-notification-count {
            display: inline-block;
            background-color: #ff6b6b;
            color: white;
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><img src="shikshalogo.jpg" alt="Logo" height="40"> Career Portal</a>
            <div class="ml-auto">
                <span style="margin-right: 20px;">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header">
            <h1>👨‍🎓 Student Dashboard</h1>
            <p>Manage your appointments and explore career resources</p>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5>Appointments</h5>
                        <h2><?php echo $appointments_result->num_rows; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5>Profile Status</h5>
                        <h2>✓ Complete</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5>Quick Actions</h5>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#appointmentModal">Book Appointment</button>
                        <a href="Student.html" class="btn btn-primary btn-sm">View Resources</a>
                        <a href="Contact.html" class="btn btn-primary btn-sm">Contact Us</a>
                        <a href="edit_profile.php" class="btn btn-primary btn-sm">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($notification_count > 0): ?>
            <div class="feedback-notification-card has-notification" data-notification-type="feedback">
                <div class="feedback-notification-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="feedback-notification-title">
                    You have new admin replies!
                    <span class="feedback-notification-count"><?php echo $notification_count; ?></span>
                </div>
                <p style="color: #aaa; margin-bottom: 15px;">Click below to view your feedback and responses from the admin team.</p>
                <a href="my_feedback.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-comments"></i> View My Feedback
                </a>
            </div>
        <?php else: ?>
            <div class="feedback-notification-card" data-notification-type="feedback">
                <div class="feedback-notification-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="feedback-notification-title">Feedback & Responses</div>
                <p style="color: #aaa; margin-bottom: 15px;">View your submitted feedback and admin responses in one place.</p>
                <a href="my_feedback.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-eye"></i> View My Feedback
                </a>
            </div>
        <?php endif; ?>

        <?php if ($new_replies_count > 0): ?>
            <div class="feedback-notification-card has-notification" data-notification-type="contact">
                <div class="feedback-notification-icon">
                    <i class="fas fa-envelope-open"></i>
                </div>
                <div class="feedback-notification-title">
                    Contact Reply Notification
                    <span class="feedback-notification-count"><?php echo $new_replies_count; ?></span>
                </div>
                <p style="color: #aaa; margin-bottom: 15px;">You have <?php echo $new_replies_count; ?> unread contact reply/replies waiting for you!</p>
                <a href="my_messages.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-envelope"></i> View My Messages
                </a>
            </div>
        <?php else: ?>
            <div class="feedback-notification-card" data-notification-type="contact">
                <div class="feedback-notification-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="feedback-notification-title">Review Your Messages</div>
                <p style="color: #aaa; margin-bottom: 15px;">Check your contact messages and stay updated with admin responses.</p>
                <a href="my_messages.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-inbox"></i> View My Messages
                </a>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Your Appointments</h5>
            </div>
            <div class="card-body">
                <?php if ($appointments_result->num_rows > 0): ?>
                    <?php while ($appt = $appointments_result->fetch_assoc()): ?>
                        <div class="appointment-item">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Counselor:</strong> <?php echo htmlspecialchars($appt['counselor_name'] ?? 'Not Assigned'); ?><br>
                                    <strong>Date:</strong> <?php echo date('Y-m-d', strtotime($appt['appointment_date'])); ?><br>
                                    <strong>Time:</strong> <?php echo htmlspecialchars($appt['appointment_time']); ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Status:</strong> 
                                    <span class="status-badge status-<?php echo strtolower($appt['status']); ?>">
                                        <?php echo htmlspecialchars($appt['status']); ?>
                                    </span><br>
                                    <strong>Specialization:</strong> <?php echo htmlspecialchars($appt['specialization'] ?? 'General'); ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No appointments scheduled. <a href="#" data-toggle="modal" data-target="#appointmentModal">Book one now</a></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Your Profile</h5>
            </div>
            <div class="card-body">
                <table class="table table-dark">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td><?php echo htmlspecialchars($user_data['name']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?php echo htmlspecialchars($user_data['email']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Username:</strong></td>
                        <td><?php echo htmlspecialchars($user_data['username']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Gender:</strong></td>
                        <td><?php echo htmlspecialchars($user_data['gender'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Contact:</strong></td>
                        <td><?php echo htmlspecialchars($user_data['contact'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>City:</strong></td>
                        <td><?php echo htmlspecialchars($user_data['city'] ?? 'N/A'); ?></td>
                    </tr>
                </table>
                <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-book"></i> Career Resources</h5>
            </div>
            <div class="card-body">
                <p>Explore our comprehensive resource categories below:</p>
                <div class="resources-container">
                    <?php if ($categories_result && $categories_result->num_rows > 0): ?>
                        <?php while ($category = $categories_result->fetch_assoc()): ?>
                            <?php 
                                $cat_id = intval($category['id']);
                                $items = null;
                                try {
                                    // Use prepared statement to prevent SQL injection
                                    $items_stmt = $mysqli->prepare("SELECT * FROM resource_items WHERE category_id = ? ORDER BY created_at DESC");
                                    if ($items_stmt) {
                                        $items_stmt->bind_param("i", $cat_id);
                                        $items_stmt->execute();
                                        $items = $items_stmt->get_result();
                                        $items_stmt->close();
                                    }
                                } catch (Exception $e) {
                                    error_log("Error fetching resource items: " . $e->getMessage());
                                    $items = null;
                                }
                                $item_count = $items ? $items->num_rows : 0;
                            ?>
                            <div class="resource-category-card" onclick="toggleResourceItems(<?php echo $cat_id; ?>)">
                                <h5><i class="fas fa-folder"></i> <?php echo htmlspecialchars($category['name']); ?></h5>
                                <small style="color: #999;">Click to expand</small>
                                <div class="item-count"><i class="fas fa-list"></i> <?php echo $item_count; ?> items</div>
                            </div>

                            <!-- Resource Items Section -->
                            <div class="resource-items-section" id="resource-items-<?php echo $cat_id; ?>" style="grid-column: 1 / -1;">
                                <div class="resource-items-container">
                                    <h5><i class="fas fa-tasks"></i> <?php echo htmlspecialchars($category['name']); ?></h5>
                                    
                                    <?php if ($item_count > 0): ?>
                                    <?php $items->data_seek(0); while ($item = $items->fetch_assoc()): ?>
                                        <div class="resource-item">
                                            <h6><?php echo htmlspecialchars($item['title']); ?></h6>
                                            <?php if (!empty($item['description'])): ?>
                                                <div class="resource-item-description"><?php echo htmlspecialchars($item['description']); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['url'])): ?>
                                                <div class="resource-item-url">
                                                    <a href="<?php echo htmlspecialchars($item['url']); ?>" target="_blank">
                                                        <i class="fas fa-external-link-alt"></i> Open Resource
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p style="color: #aaa;">No items available in this category yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align: center; color: #aaa; padding: 2rem;">
                            <p><i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i></p>
                            <p>No resource categories available at the moment.</p>
                            <p style="font-size: 0.9rem;">Check back later for career resources and guidance materials.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointment Modal -->
    <div class="modal fade" id="appointmentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="background-color: rgba(0, 0, 0, 0.9);">
                <div class="modal-header">
                    <h5 class="modal-title">Book an Appointment</h5>
                    <button type="button" class="close" data-dismiss="modal" style="color: white;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Appointment booking feature coming soon. Please check back later.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
    <script>
        function toggleResourceItems(categoryId) {
            const itemsSection = document.getElementById('resource-items-' + categoryId);
            itemsSection.classList.toggle('show');
        }

        // Auto-refresh notification counts every 5 seconds or when returning from message view
        function refreshNotificationCounts() {
            fetch('get_notification_counts.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateNotificationUI(data.notification_count, data.new_replies_count);
                    }
                })
                .catch(error => {
                    console.error('Error refreshing notification counts:', error);
                });
        }

        // Update the notification UI elements
        function updateNotificationUI(feedbackCount, contactCount) {
            // Get all notification cards
            const feedbackCard = document.querySelector('[data-notification-type="feedback"]');
            const contactCard = document.querySelector('[data-notification-type="contact"]');

            if (feedbackCard) {
                if (feedbackCount > 0) {
                    feedbackCard.classList.add('has-notification');
                    const countBadge = feedbackCard.querySelector('.feedback-notification-count');
                    if (countBadge) {
                        countBadge.textContent = feedbackCount;
                    }
                } else {
                    feedbackCard.classList.remove('has-notification');
                    const countBadge = feedbackCard.querySelector('.feedback-notification-count');
                    if (countBadge) {
                        countBadge.remove();
                    }
                }
            }

            if (contactCard) {
                if (contactCount > 0) {
                    contactCard.classList.add('has-notification');
                    const countBadge = contactCard.querySelector('.feedback-notification-count');
                    if (countBadge) {
                        countBadge.textContent = contactCount;
                    }
                } else {
                    contactCard.classList.remove('has-notification');
                    const countBadge = contactCard.querySelector('.feedback-notification-count');
                    if (countBadge) {
                        countBadge.remove();
                    }
                }
            }
        }

        // Listen for page visibility changes to refresh when user returns to dashboard
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                refreshNotificationCounts();
            }
        });

        // Listen for custom events from other pages
        window.addEventListener('messageRead', function(event) {
            console.log('Message read event received:', event.detail);
            refreshNotificationCounts();
        });

        // Periodically refresh notification counts every 30 seconds
        setInterval(refreshNotificationCounts, 30000);

        // Fix event binding for message card areas - ensure clicks work properly
        document.querySelectorAll('[data-notification-type]').forEach(card => {
            card.style.cursor = 'pointer';
            card.addEventListener('click', function(e) {
                // Don't trigger if clicking on a button or link
                if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.closest('a') || e.target.closest('button')) {
                    return;
                }
                
                // Trigger the link within the card
                const link = this.querySelector('a');
                if (link) {
                    window.location.href = link.href;
                }
            });
        });
    </script>
</body>
</html>

