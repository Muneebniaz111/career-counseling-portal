<?php
require_once __DIR__ . '/bootstrap.php';

// Database connection - MUST be before any database operations
$mysqli = new mysqli("localhost", "root", "", "career_counseling");

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: Log-in (Admin).php");
    exit();
}

// Initialize CSRF token if not present
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    // CRITICAL FIX: Add CSRF token validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die(json_encode(['error' => 'CSRF token validation failed']));
    }
    
    $contact_id = intval($_POST['contact_id']);
    
    // Delete related records first due to foreign key constraints
    $mysqli->query("DELETE FROM contact_replies WHERE contact_id = $contact_id");
    $mysqli->query("DELETE FROM admin_contact_notifications WHERE contact_id = $contact_id");
    
    // Now delete the message itself
    $delete_stmt = $mysqli->prepare("DELETE FROM contact_messages WHERE id = ?");
    $delete_stmt->bind_param("i", $contact_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    echo json_encode(['success' => true]);
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    // CRITICAL FIX: Add CSRF token validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die(json_encode(['error' => 'CSRF token validation failed']));
    }
    
    $contact_id = intval($_POST['contact_id']);
    $status = $_POST['status'] === 'replied' ? 'replied' : 'open';
    $update_stmt = $mysqli->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $status, $contact_id);
    $update_stmt->execute();
    $update_stmt->close();
    echo json_encode(['success' => true]);
    exit();
}

// Get all contacts
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Check if status column exists
try {
    $check_status = $mysqli->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='contact_messages' AND COLUMN_NAME='status'");
    $has_status = $check_status && $check_status->num_rows > 0;
} catch (Exception $e) {
    $has_status = false; // If we can't check, assume column doesn't exist
}

$query = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $search_term = "%{$search}%";
    $query .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $params = [$search_term, $search_term, $search_term, $search_term];
    $types = 'ssss';
}

if (!empty($status_filter) && $has_status) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$query .= " ORDER BY created_at DESC";

if (empty($params)) {
    $result = $mysqli->query($query);
} else {
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $mysqli->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    }
}

$contacts = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Ensure status key exists - default to 'open' if not present
        if (!isset($row['status'])) {
            $row['status'] = 'open';
        }
        $contacts[] = $row;
    }
}

// Get reply count for each contact using prepared statements
$reply_counts = [];
foreach ($contacts as $contact) {
    try {
        $reply_stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM contact_replies WHERE contact_id = ?");
        if ($reply_stmt) {
            $contact_id_val = intval($contact['id']);
            $reply_stmt->bind_param("i", $contact_id_val);
            $reply_stmt->execute();
            $reply_result = $reply_stmt->get_result();
            
            if ($reply_result) {
                $reply_counts[$contact_id_val] = intval($reply_result->fetch_assoc()['count'] ?? 0);
            } else {
                $reply_counts[$contact_id_val] = 0;
            }
            $reply_stmt->close();
        }
    } catch (Exception $e) {
        error_log("Error getting reply count: " . $e->getMessage());
        $reply_counts[intval($contact['id'])] = 0;
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin Dashboard</title>
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
        }

        .navbar {
            background-color: black;
            padding: 20px 40px;
            margin-bottom: 40px;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
            color: white !important;
        }

        .nav-link {
            color: white !important;
            margin-left: 1.5rem;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: var(--primary) !important;
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

        .container {
            margin-top: 2rem;
        }

        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .page-header h1 {
            color: var(--primary);
            font-weight: 700;
            margin: 0;
        }

        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .contact-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .contact-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .contact-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .contact-title {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.1rem;
            margin: 0;
        }

        .contact-meta {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0.5rem 0 0 0;
        }

        .contact-body {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
        }

        .contact-message {
            color: #333;
            line-height: 1.6;
            margin: 0;
        }

        .badge-open {
            background-color: #ffc107;
            color: #000;
        }

        .badge-replied {
            background-color: #28a745;
        }

        .replies-count {
            background: #e7f3ff;
            color: var(--primary);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .btn-sm {
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
        }

        .btn-primary {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
        }

        .btn-primary:hover {
            background-color: #6a006a !important;
            border-color: #6a006a !important;
        }

        .btn-info {
            background-color: #17a2b8 !important;
            border-color: #17a2b8 !important;
        }

        .btn-info:hover {
            background-color: #138496 !important;
            border-color: #138496 !important;
        }

        .btn-danger:hover {
            background-color: #c82333 !important;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(128, 0, 128, 0.25);
        }

        .modal-header {
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }

        .modal-header .close {
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <a href="Admin_Dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </nav>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-comments"></i> Contact Messages Management</h1>
            <p class="text-muted mt-2">Manage all contact messages from users and guests</p>
        </div>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-box">
                <div class="stat-number"><?php echo count($contacts); ?></div>
                <div class="stat-label">Total Messages</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo count(array_filter($contacts, fn($c) => ($c['status'] ?? 'open') === 'open')); ?></div>
                <div class="stat-label">Open Messages</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo count(array_filter($contacts, fn($c) => ($c['status'] ?? 'open') === 'replied')); ?></div>
                <div class="stat-label">Replied Messages</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <form method="get" class="form-inline" style="gap: 1rem; flex-wrap: wrap;">
                <div class="form-group flex-grow-1" style="min-width: 250px;">
                    <input type="text" name="search" class="form-control w-100" placeholder="Search by name, email, subject..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <select name="status" class="form-control" style="min-width: 150px;">
                    <option value="">All Status</option>
                    <option value="open" <?php if($status_filter === 'open') echo 'selected'; ?>>Open</option>
                    <option value="replied" <?php if($status_filter === 'replied') echo 'selected'; ?>>Replied</option>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                <a href="admin_contacts.php" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
            </form>
        </div>

        <!-- Contact Messages List -->
        <div>
            <?php if(empty($contacts)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Messages Found</h3>
                    <p>There are no contact messages matching your criteria.</p>
                </div>
            <?php else: ?>
                <?php foreach($contacts as $contact): ?>
                    <div class="contact-card">
                        <div class="contact-header">
                            <div>
                                <h5 class="contact-title"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($contact['name']); ?></h5>
                                <p class="contact-meta">
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contact['email']); ?>
                                    <?php if(!empty($contact['phone'])): ?>
                                        | <i class="fas fa-phone"></i> <?php echo htmlspecialchars($contact['phone']); ?>
                                    <?php endif; ?>
                                </p>
                                <p class="contact-meta">
                                    <i class="fas fa-clock"></i> <?php echo date('M d, Y H:i', strtotime($contact['created_at'])); ?>
                                </p>
                            </div>
                            <div style="text-align: right;">
                                <?php $status = $contact['status'] ?? 'open'; ?>
                                <span class="badge <?php echo $status === 'open' ? 'badge-open' : 'badge-replied'; ?> badge-lg">
                                    <?php echo ucfirst($status); ?>
                                </span>
                                <div class="replies-count mt-2">
                                    <i class="fas fa-reply"></i> <?php echo $reply_counts[$contact['id']]; ?> Reply/Replies
                                </div>
                            </div>
                        </div>

                        <div>
                            <strong>Subject:</strong> <?php echo htmlspecialchars($contact['subject']); ?>
                        </div>

                        <div class="contact-body">
                            <p class="contact-message"><?php echo nl2br(htmlspecialchars($contact['message'])); ?></p>
                        </div>

                        <div class="action-buttons">
                            <a href="view_contact.php?id=<?php echo $contact['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> View Details & Reply
                            </a>
                            <?php if(($contact['status'] ?? 'open') === 'open'): ?>
                                <button class="btn btn-success btn-sm" onclick="updateStatus(<?php echo $contact['id']; ?>, 'replied')">
                                    <i class="fas fa-check"></i> Mark as Replied
                                </button>
                            <?php else: ?>
                                <button class="btn btn-warning btn-sm" onclick="updateStatus(<?php echo $contact['id']; ?>, 'open')">
                                    <i class="fas fa-history"></i> Mark as Open
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-danger btn-sm" onclick="deleteContact(<?php echo $contact['id']; ?>)">
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
        function deleteContact(contactId) {
            if(confirm('Are you sure you want to delete this contact message? This action cannot be undone.')) {
                $.ajax({
                    url: 'admin_contacts.php',
                    method: 'POST',
                    data: {
                        action: 'delete',
                        contact_id: contactId,
                        csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                    },
                    success: function(response) {
                        location.reload();
                    },
                    error: function() {
                        alert('Error deleting contact message');
                    }
                });
            }
        }

        function updateStatus(contactId, status) {
            $.ajax({
                url: 'admin_contacts.php',
                method: 'POST',
                data: {
                    action: 'update_status',
                    contact_id: contactId,
                    status: status,
                    csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                },
                success: function(response) {
                    location.reload();
                },
                error: function() {
                    alert('Error updating status');
                }
            });
        }
    </script>
</body>
</html>

