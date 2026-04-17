<?php
require_once __DIR__ . '/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Log-in (Student).php");
    exit();
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
        $delete_stmt = $mysqli->prepare("DELETE FROM contact_messages WHERE id = ?");
        $delete_stmt->bind_param("i", $contact_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        echo json_encode(['success' => true]);
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
        }

        body {
            background: linear-gradient(135deg, var(--dark) 0%, var(--secondary) 100%);
            color: #333;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            padding-top: 80px;
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

        .container {
            margin-top: 2rem;
        }

        .page-header {
            background: linear-gradient(135deg, rgba(128, 0, 128, 0.15), rgba(255, 107, 107, 0.1));
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(128, 0, 128, 0.1);
            margin-bottom: 2rem;
            border: 1px solid rgba(128, 0, 128, 0.2);
        }

        .page-header h1 {
            color: #ff6b6b;
            font-weight: 700;
            margin: 0;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
            padding: 1.5rem;
            padding-top: 0;
            border-top: 1px solid rgba(128, 0, 128, 0.2);
        }

        .btn-primary {
            background-color: #800080 !important;
            border-color: #800080 !important;
            color: white !important;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background-color: #6a006a !important;
            box-shadow: 0 4px 12px rgba(128, 0, 128, 0.3);
            text-decoration: none;
        }

        .filter-section {
            background: linear-gradient(135deg, rgba(128, 0, 128, 0.08), rgba(75, 0, 130, 0.05));
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(128, 0, 128, 0.1);
            margin-bottom: 2rem;
            border: 1px solid rgba(128, 0, 128, 0.2);
        }

        .message-card {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgba(128, 0, 128, 0.1));
            border: 1px solid rgba(128, 0, 128, 0.3);
            border-radius: 12px;
            padding: 0;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .message-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(128, 0, 128, 0.3);
            border-color: rgba(128, 0, 128, 0.5);
        }

        .message-title {
            font-weight: 700;
            color: #ff6b6b;
            font-size: 1.1rem;
            margin: 0;
        }

        .message-text {
            color: #ccc;
            line-height: 1.6;
            margin: 1rem 0;
            padding: 0;
            background: transparent;
            border-radius: 0;
        }

        .message-meta {
            color: #9a9a9a;
            font-size: 0.9rem;
            margin: 0.5rem 0;
        }

        .badge-open {
            background: rgba(255, 152, 0, 0.2);
            color: #ff9800;
            border: 1px solid rgba(255, 152, 0, 0.4);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-replied {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.4);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .replies-badge {
            background: rgba(128, 0, 128, 0.2);
            color: #ff6b6b;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            border: 1px solid rgba(255, 107, 107, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #9a9a9a;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.4), rgba(128, 0, 128, 0.08));
            border-radius: 12px;
            border: 1px solid rgba(128, 0, 128, 0.2);
        }

        .empty-state i {
            font-size: 3rem;
            color: rgba(255, 107, 107, 0.3);
            margin-bottom: 1rem;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: linear-gradient(135deg, rgba(128, 0, 128, 0.1), rgba(255, 107, 107, 0.05));
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(128, 0, 128, 0.15);
            border: 1px solid rgba(128, 0, 128, 0.2);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #ff6b6b;
        }

        .stat-label {
            color: #9a9a9a;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .form-control:focus {
            border-color: #800080 !important;
            box-shadow: 0 0 0 0.2rem rgba(128, 0, 128, 0.25) !important;
            background: rgba(255, 255, 255, 0.95) !important;
            color: #333 !important;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(128, 0, 128, 0.3);
            color: #333;
        }

        .btn-sm {
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
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
            <h1><i class="fas fa-inbox"></i> My Messages</h1>
            <p class="text-muted mt-2">View your contact messages and admin replies</p>
        </div>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-box">
                <div class="stat-number"><?php echo count($messages); ?></div>
                <div class="stat-label">Total Messages</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo count(array_filter($messages, fn($m) => $m['status'] === 'open')); ?></div>
                <div class="stat-label">Pending Replies</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo count(array_filter($messages, fn($m) => $m['status'] === 'replied')); ?></div>
                <div class="stat-label">Replied</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <form method="get" class="form-inline" style="gap: 1rem; flex-wrap: wrap;">
                <div class="form-group flex-grow-1" style="min-width: 250px;">
                    <input type="text" name="search" class="form-control w-100" placeholder="Search by subject or message..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <select name="status" class="form-control" style="min-width: 150px;">
                    <option value="">All Status</option>
                    <option value="open" <?php if($status_filter === 'open') echo 'selected'; ?>>Pending</option>
                    <option value="replied" <?php if($status_filter === 'replied') echo 'selected'; ?>>Replied</option>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                <a href="my_messages.php" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
            </form>
        </div>

        <!-- Messages List -->
        <div>
            <?php if(empty($messages)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Messages</h3>
                    <p>You haven't sent any contact messages yet.</p>
                    <a href="Contact.html" class="btn btn-primary mt-3">
                        <i class="fas fa-plus"></i> Send a Message
                    </a>
                </div>
            <?php else: ?>
                <?php foreach($messages as $message): ?>
                    <div class="message-card">
                        <div style="padding: 1.5rem 1.5rem 0 1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 1rem; margin-bottom: 0;">
                                <div style="flex: 1; min-width: 250px;">
                                    <h5 class="message-title"><?php echo htmlspecialchars($message['subject']); ?></h5>
                                    <p class="message-meta">
                                        <i class="fas fa-clock"></i> <?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <span class="badge <?php echo $message['status'] === 'open' ? 'badge-open' : 'badge-replied'; ?>">
                                        <?php echo $message['status'] === 'open' ? 'Pending Reply' : 'Replied'; ?>
                                    </span>
                                    <?php if($reply_counts[$message['id']] > 0): ?>
                                        <div class="replies-badge mt-2">
                                            <i class="fas fa-reply"></i> <?php echo $reply_counts[$message['id']]; ?> Reply/Replies
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="message-text">
                                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <a href="view_contact.php?id=<?php echo $message['id']; ?>&user=1" 
                               class="btn btn-primary btn-sm view-message-btn"
                               data-message-id="<?php echo $message['id']; ?>"
                               data-status="<?php echo $message['status']; ?>">
                                <i class="fas fa-eye"></i> View Details & Replies
                            </a>
                            <button class="btn btn-danger btn-sm" onclick="deleteMessage(<?php echo $message['id']; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
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
                        csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                    },
                    success: function(response) {
                        location.reload();
                    },
                    error: function() {
                        alert('Error deleting message');
                    }
                });
            }
        }
    </script>
</body>
</html>

