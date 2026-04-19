<?php
require_once __DIR__ . '/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Log-in (Student).php");
    exit();
}

// Initialize CSRF token if not present
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'User';

$mysqli = new mysqli("localhost", "root", "", "career_counseling");

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    // CRITICAL FIX: Add CSRF token validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die(json_encode(['error' => 'CSRF token validation failed']));
    }
    
    $contact_id = intval($_POST['contact_id']);
    
    // CRITICAL FIX: Use prepared statement instead of direct interpolation for SQL injection prevention
    $verify_stmt = $mysqli->prepare("SELECT user_id FROM contact_messages WHERE id = ?");
    if (!$verify_stmt) {
        die(json_encode(['error' => 'Database error']));
    }
    $verify_stmt->bind_param("i", $contact_id);
    $verify_stmt->execute();
    $verify = $verify_stmt->get_result();
    $message = $verify->fetch_assoc();
    
    if($message && $message['user_id'] == $user_id) {
        // Delete related records first due to foreign key constraints
        $mysqli->query("DELETE FROM contact_replies WHERE contact_id = $contact_id");
        $mysqli->query("DELETE FROM admin_contact_notifications WHERE contact_id = $contact_id");
        
        // Now delete the message itself
        $delete_stmt = $mysqli->prepare("DELETE FROM contact_messages WHERE id = ?");
        $delete_stmt->bind_param("i", $contact_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        echo json_encode(['success' => true]);
    } else {
        http_response_code(403);
        die(json_encode(['error' => 'Unauthorized']));
    }
    $verify_stmt->close();
    exit();
}

// Get user's messages
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];
$types = '';

// Only add user_id filter if the column exists
try {
    $check_column = $mysqli->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='contact_messages' AND COLUMN_NAME='user_id'");
    $has_user_id = $check_column && $check_column->num_rows > 0;
} catch (Exception $e) {
    $has_user_id = true; // Assume it exists
}

// Check if status column exists
try {
    $check_status = $mysqli->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='contact_messages' AND COLUMN_NAME='status'");
    $has_status = $check_status && $check_status->num_rows > 0;
} catch (Exception $e) {
    $has_status = false; // Assume it doesn't exist
}

if ($has_user_id) {
    $query = "SELECT * FROM contact_messages WHERE user_id = ?";
    $params = [$user_id];
    $types = 'i';
} else {
    // Fallback: get all messages (without user_id filter)
    $query = "SELECT * FROM contact_messages WHERE email = ?";
    $params = [$_SESSION['email'] ?? ''];
    $types = 's';
}

if (!empty($search)) {
    $search_term = "%{$search}%";
    $query .= " AND (subject LIKE ? OR message LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ss';
}

if (!empty($status_filter) && $has_status) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$query .= " ORDER BY created_at DESC";

$stmt = $mysqli->prepare($query);
if ($stmt) {
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        // Ensure status key exists - default to 'open' if not present
        if (!isset($row['status'])) {
            $row['status'] = 'open';
        }
        $messages[] = $row;
    }
    $stmt->close();
} else {
    $messages = [];
}

// Get reply count for each message using prepared statements
$reply_counts = [];
foreach ($messages as $message) {
    try {
        $reply_stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM contact_replies WHERE contact_id = ?");
        if ($reply_stmt) {
            $message_id = intval($message['id']);
            $reply_stmt->bind_param("i", $message_id);
            $reply_stmt->execute();
            $reply_result = $reply_stmt->get_result();
            
            if ($reply_result) {
                $reply_counts[$message_id] = intval($reply_result->fetch_assoc()['count'] ?? 0);
            } else {
                $reply_counts[$message_id] = 0;
            }
            $reply_stmt->close();
        }
    } catch (Exception $e) {
        error_log("Error getting reply count: " . $e->getMessage());
        $reply_counts[intval($message['id'])] = 0;
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Messages - Career Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #800080;
            --secondary: #4B0082;
            --dark: #1a1a1a;
            --light: #f8f9fa;
            --accent: #ff6b6b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--dark) 0%, var(--secondary) 100%);
            color: #e0e0e0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding-top: 80px;
        }

        .navbar {
            background: linear-gradient(90deg, #000 0%, var(--dark) 100%) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            padding: 1rem 0 !important;
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
            color: var(--accent) !important;
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
            margin: 40px auto;
            padding: 40px;
            background: linear-gradient(135deg, rgba(128, 0, 128, 0.15), rgba(255, 107, 107, 0.1));
            border-radius: 12px;
            max-width: 900px;
            border: 1px solid rgba(128, 0, 128, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .page-header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 12px;
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
            border-color: var(--accent);
            box-shadow: 0 12px 24px rgba(128, 0, 128, 0.2);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 12px;
            color: var(--accent);
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
            letter-spacing: 0.5px;
        }

        /* Filter Section */
        .filters-container {
            max-width: 900px;
            margin: 0 auto 40px;
            padding: 0 20px;
        }

        .filter-section {
            background: linear-gradient(135deg, rgba(128, 0, 128, 0.1), rgba(75, 0, 130, 0.05));
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(128, 0, 128, 0.2);
        }

        .filter-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            color: #b0b0b0;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(128, 0, 128, 0.3);
            color: #e0e0e0;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-control::placeholder {
            color: rgba(224, 224, 224, 0.5);
        }

        .form-control:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 0.2rem rgba(128, 0, 128, 0.25) !important;
            background: rgba(255, 255, 255, 0.1) !important;
            color: #e0e0e0 !important;
            outline: none;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #6a006a 100%);
            color: white !important;
            box-shadow: 0 4px 15px rgba(128, 0, 128, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(128, 0, 128, 0.4);
            color: white !important;
            text-decoration: none;
        }

        .btn-secondary {
            background: rgba(128, 0, 128, 0.15);
            color: #b0b0b0;
            border: 1px solid rgba(128, 0, 128, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(128, 0, 128, 0.25);
            color: white;
            text-decoration: none;
        }

        .btn-danger {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        .btn-danger:hover {
            background: rgba(244, 67, 54, 0.3);
            border-color: #f44336;
        }

        .btn-sm {
            padding: 8px 12px;
            font-size: 0.85rem;
        }

        /* Messages Container */
        .messages-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Message Cards */
        .message-card {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgba(128, 0, 128, 0.1));
            border: 1px solid rgba(128, 0, 128, 0.3);
            margin-bottom: 30px;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }

        .message-card:hover {
            border-color: var(--accent);
            box-shadow: 0 12px 40px rgba(128, 0, 128, 0.2);
            transform: translateY(-3px);
        }

        .message-header {
            background: linear-gradient(90deg, rgba(128, 0, 128, 0.2), rgba(255, 107, 107, 0.15));
            padding: 25px;
            border-bottom: 1px solid rgba(128, 0, 128, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }

        .message-header-info {
            flex: 1;
            min-width: 250px;
        }

        .message-subject {
            font-weight: 700;
            color: var(--accent);
            font-size: 1.2rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message-date {
            color: #9a9a9a;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
        }

        .message-sender {
            color: #b0b0b0;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
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
        }

        .replies-count {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(128, 0, 128, 0.2);
            color: var(--accent);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid rgba(255, 107, 107, 0.3);
            margin-top: 10px;
        }

        /* Message Body */
        .message-body {
            padding: 25px;
        }

        .message-text {
            background: rgba(128, 0, 128, 0.08);
            padding: 18px;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
            color: #d0d0d0;
            line-height: 1.8;
            word-wrap: break-word;
        }

        .message-contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(128, 0, 128, 0.2);
        }

        .contact-info-item {
            display: flex;
            flex-direction: column;
        }

        .contact-info-label {
            color: #9a9a9a;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .contact-info-value {
            color: #e0e0e0;
            font-weight: 600;
        }

        /* Action Buttons */
        .message-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(128, 0, 128, 0.2);
            flex-wrap: wrap;
        }

        .message-actions .btn {
            flex: 1;
            min-width: 140px;
            justify-content: center;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 30px;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.4), rgba(128, 0, 128, 0.08));
            border-radius: 12px;
            border: 1px solid rgba(128, 0, 128, 0.2);
            max-width: 600px;
            margin: 40px auto;
        }

        .empty-state-icon {
            font-size: 4rem;
            color: rgba(255, 107, 107, 0.2);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #e0e0e0;
            font-size: 1.5rem;
            margin-bottom: 12px;
        }

        .empty-state p {
            color: #9a9a9a;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                padding: 30px 20px;
                margin: 30px 15px;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .filter-form {
                flex-direction: column;
            }

            .filter-form > div {
                width: 100%;
            }

            .message-header {
                flex-direction: column;
                padding: 20px;
            }

            .message-actions .btn {
                min-width: 100%;
            }

            .stats-container {
                padding: 0 15px;
                grid-template-columns: 1fr;
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

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-envelope"></i> My Messages</h1>
            <p>Manage your contact messages and admin replies in one place</p>
        </div>

        <!-- Statistics -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-inbox"></i></div>
                <div class="stat-number"><?php echo count($messages); ?></div>
                <div class="stat-label">Total Messages</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-number"><?php echo count(array_filter($messages, fn($m) => $m['status'] === 'open')); ?></div>
                <div class="stat-label">Pending Replies</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check"></i></div>
                <div class="stat-number"><?php echo count(array_filter($messages, fn($m) => $m['status'] === 'replied')); ?></div>
                <div class="stat-label">Replied</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-container">
            <div class="filter-section">
                <form method="get" class="filter-form">
                    <div class="form-group" style="flex: 2;">
                        <label for="search">Search Messages</label>
                        <input type="text" id="search" name="search" class="form-control" placeholder="Search by subject or message..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group">
                        <label for="status">Filter by Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="open" <?php if($status_filter === 'open') echo 'selected'; ?>>Pending</option>
                            <option value="replied" <?php if($status_filter === 'replied') echo 'selected'; ?>>Replied</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 10px; align-self: flex-end;">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                        <a href="my_messages.php" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Messages List -->
        <div class="messages-container">
            <?php if(empty($messages)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                    <h3>No Messages Yet</h3>
                    <p>You haven't sent any contact messages yet. Start a conversation with us to get guidance and support.</p>
                    <a href="Contact.html" class="btn btn-primary" style="justify-content: center;">
                        <i class="fas fa-envelope"></i> Send Your First Message
                    </a>
                </div>
            <?php else: ?>
                <?php foreach($messages as $message): ?>
                    <div class="message-card">
                        <div class="message-header">
                            <div class="message-header-info">
                                <div class="message-subject">
                                    <i class="fas fa-comment-dots"></i>
                                    <?php echo htmlspecialchars($message['subject']); ?>
                                </div>
                                <div class="message-date">
                                    <i class="fas fa-clock"></i> <?php echo date('M d, Y • H:i', strtotime($message['created_at'])); ?>
                                </div>
                                <div class="message-sender">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($message['name']); ?>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div class="status-badge <?php echo $message['status'] === 'open' ? 'status-open' : 'status-replied'; ?>">
                                    <i class="fas <?php echo $message['status'] === 'open' ? 'fa-hourglass-end' : 'fa-check-circle'; ?>"></i>
                                    <?php echo $message['status'] === 'open' ? 'Pending Reply' : 'Replied'; ?>
                                </div>
                                <?php if($reply_counts[$message['id']] > 0): ?>
                                    <div class="replies-count">
                                        <i class="fas fa-reply"></i> <?php echo $reply_counts[$message['id']]; ?> <?php echo $reply_counts[$message['id']] === 1 ? 'Reply' : 'Replies'; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="message-body">
                            <div class="message-text">
                                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                            </div>

                            <div class="message-contact-info">
                                <div class="contact-info-item">
                                    <span class="contact-info-label"><i class="fas fa-envelope"></i> Email</span>
                                    <span class="contact-info-value"><?php echo htmlspecialchars($message['email']); ?></span>
                                </div>
                                <?php if(!empty($message['phone'])): ?>
                                <div class="contact-info-item">
                                    <span class="contact-info-label"><i class="fas fa-phone"></i> Phone</span>
                                    <span class="contact-info-value"><?php echo htmlspecialchars($message['phone']); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="contact-info-item">
                                    <span class="contact-info-label"><i class="fas fa-calendar"></i> Sent</span>
                                    <span class="contact-info-value"><?php echo date('M d, Y', strtotime($message['created_at'])); ?></span>
                                </div>
                            </div>

                            <div class="message-actions">
                                <a href="view_contact.php?id=<?php echo $message['id']; ?>&user=1" 
                                   class="btn btn-primary btn-sm"
                                   data-message-id="<?php echo $message['id']; ?>"
                                   data-status="<?php echo $message['status']; ?>">
                                    <i class="fas fa-eye"></i> View Details & Replies
                                </a>
                                <button class="btn btn-danger btn-sm" onclick="deleteMessage(<?php echo $message['id']; ?>)">
                                    <i class="fas fa-trash-alt"></i> Delete Message
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>

    <script>
        function deleteMessage(messageId) {
            if(confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
                $.ajax({
                    url: 'my_messages.php',
                    method: 'POST',
                    data: {
                        action: 'delete',
                        contact_id: messageId,
                        csrf_token: '<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>'
                    },
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if(result.success) {
                                // Remove the card with smooth animation
                                const card = document.querySelector('[data-message-id="' + messageId + '"]')?.closest('.message-card');
                                if(card) {
                                    card.style.transition = 'all 0.3s ease';
                                    card.style.opacity = '0';
                                    card.style.transform = 'translateY(-10px)';
                                    setTimeout(() => location.reload(), 300);
                                } else {
                                    location.reload();
                                }
                            }
                        } catch(e) {
                            location.reload();
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error deleting message: ' + (xhr.responseJSON?.error || 'Unknown error'));
                    }
                });
            }
        }
    </script>
</body>
</html>

