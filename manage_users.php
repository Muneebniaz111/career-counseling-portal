<?php
require_once __DIR__ . '/bootstrap.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: Log-in (Admin).php");
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "career_counseling");
$message = '';
$message_type = '';

// Handle Add New User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $name = $mysqli->real_escape_string($_POST['name']);
    $email = $mysqli->real_escape_string($_POST['email']);
    $username = $mysqli->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $gender = $mysqli->real_escape_string($_POST['gender']);
    $contact = $mysqli->real_escape_string($_POST['contact']);
    $city = $mysqli->real_escape_string($_POST['city']);

    // Check if email or username already exists
    $check_stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $check_stmt->bind_param("ss", $email, $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message = "Email or Username already exists!";
        $message_type = "error";
    } else {
        $insert_stmt = $mysqli->prepare("INSERT INTO users (name, email, username, password, gender, contact, city, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $insert_stmt->bind_param("sssssss", $name, $email, $username, $password, $gender, $contact, $city);
        
        if ($insert_stmt->execute()) {
            $message = "User added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding user!";
            $message_type = "error";
        }
        $insert_stmt->close();
    }
    $check_stmt->close();
}

// Handle Update User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_user') {
    $user_id = intval($_POST['user_id']);
    $name = $mysqli->real_escape_string($_POST['name']);
    $email = $mysqli->real_escape_string($_POST['email']);
    $username = $mysqli->real_escape_string($_POST['username']);
    $gender = $mysqli->real_escape_string($_POST['gender']);
    $contact = $mysqli->real_escape_string($_POST['contact']);
    $city = $mysqli->real_escape_string($_POST['city']);

    // Check if email or username is already used by another user
    $check_stmt = $mysqli->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
    $check_stmt->bind_param("ssi", $email, $username, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message = "Email or Username already in use by another user!";
        $message_type = "error";
    } else {
        $update_stmt = $mysqli->prepare("UPDATE users SET name = ?, email = ?, username = ?, gender = ?, contact = ?, city = ? WHERE id = ?");
        $update_stmt->bind_param("ssssssi", $name, $email, $username, $gender, $contact, $city, $user_id);
        
        if ($update_stmt->execute()) {
            $message = "User updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating user!";
            $message_type = "error";
        }
        $update_stmt->close();
    }
    $check_stmt->close();
}

// Handle Delete User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $user_id = intval($_POST['user_id']);
    $delete_stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->bind_param("i", $user_id);
    
    if ($delete_stmt->execute()) {
        $message = "User deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting user!";
        $message_type = "error";
    }
    $delete_stmt->close();
}

$result = $mysqli->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom, #000, #4b0082);
            color: white;
            font-family: 'Arial', sans-serif;
        }

        .navbar {
            background-color: black;
            padding: 20px 40px;
            margin-bottom: 40px;
        }

        .container-fluid {
            margin-top: 40px;
            margin-bottom: 40px;
        }

        .card {
            background-color: rgba(0, 0, 0, 0.8);
            border: 1px solid #ff6b6b;
            margin-bottom: 30px;
        }

        .card-header {
            background-color: #ff6b6b;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table {
            background-color: rgba(0, 0, 0, 0.8);
        }

        .table thead th {
            border-color: #666;
            color: #ff6b6b;
        }

        .table td {
            border-color: #666;
            color: white;
            vertical-align: middle;
        }

        .btn-danger {
            background-color: #ff6b6b;
            border-color: #ff6b6b;
            margin: 3px;
        }

        .btn-danger:hover {
            background-color: #ff5252;
        }

        .btn-primary {
            background-color: #800080;
            border-color: #800080;
            margin: 3px;
        }

        .btn-primary:hover {
            background-color: #6b0080;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
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
            margin-bottom: 20px;
        }

        .back-link:hover {
            color: #ff5252;
        }

        .back-link i {
            font-size: 1.1rem;
        }

        .alert {
            margin-bottom: 20px;
        }

        .modal-content {
            background-color: rgba(0, 0, 0, 0.9);
            border: 1px solid #ff6b6b;
            color: white;
        }

        .modal-header {
            border-bottom: 1px solid #ff6b6b;
        }

        .modal-footer {
            border-top: 1px solid #ff6b6b;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid #666;
            color: white;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: #ff6b6b;
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 107, 0.25);
        }

        .form-control::placeholder {
            color: #999;
        }

        .form-group label {
            color: #ff6b6b;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="Admin_Dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </nav>

    <div class="container-fluid" style="max-width: 1200px;">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $message; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-users"></i> Manage Users</h3>
                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addUserModal">
                    <i class="fas fa-plus"></i> Add New User
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Gender</th>
                                <th>Contact</th>
                                <th>City</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['gender'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($user['contact'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($user['city'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editUserModal<?php echo $user['id']; ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Edit User Modal -->
                                        <div class="modal fade" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit User: <?php echo htmlspecialchars($user['name']); ?></h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="update_user">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">

                                                            <div class="form-group">
                                                                <label for="name<?php echo $user['id']; ?>">Full Name</label>
                                                                <input type="text" class="form-control" id="name<?php echo $user['id']; ?>" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="email<?php echo $user['id']; ?>">Email</label>
                                                                <input type="email" class="form-control" id="email<?php echo $user['id']; ?>" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="username<?php echo $user['id']; ?>">Username</label>
                                                                <input type="text" class="form-control" id="username<?php echo $user['id']; ?>" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="gender<?php echo $user['id']; ?>">Gender</label>
                                                                <select class="form-control" id="gender<?php echo $user['id']; ?>" name="gender">
                                                                    <option value="">Select Gender</option>
                                                                    <option value="Male" <?php echo $user['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                                                    <option value="Female" <?php echo $user['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                                                    <option value="Other" <?php echo $user['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                                                </select>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="contact<?php echo $user['id']; ?>">Contact</label>
                                                                <input type="text" class="form-control" id="contact<?php echo $user['id']; ?>" name="contact" value="<?php echo htmlspecialchars($user['contact'] ?? ''); ?>">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="city<?php echo $user['id']; ?>">City</label>
                                                                <input type="text" class="form-control" id="city<?php echo $user['id']; ?>" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="fas fa-save"></i> Save Changes
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add New User Modal -->
        <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-plus"></i> Add New User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="add_user">

                            <div class="form-group">
                                <label for="new_name">Full Name</label>
                                <input type="text" class="form-control" id="new_name" name="name" placeholder="Enter full name" required>
                            </div>

                            <div class="form-group">
                                <label for="new_email">Email</label>
                                <input type="email" class="form-control" id="new_email" name="email" placeholder="Enter email" required>
                            </div>

                            <div class="form-group">
                                <label for="new_username">Username</label>
                                <input type="text" class="form-control" id="new_username" name="username" placeholder="Enter username" required>
                            </div>

                            <div class="form-group">
                                <label for="new_password">Password</label>
                                <input type="password" class="form-control" id="new_password" name="password" placeholder="Enter password" required>
                            </div>

                            <div class="form-group">
                                <label for="new_gender">Gender</label>
                                <select class="form-control" id="new_gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="new_contact">Contact</label>
                                <input type="text" class="form-control" id="new_contact" name="contact" placeholder="Enter contact number">
                            </div>

                            <div class="form-group">
                                <label for="new_city">City</label>
                                <input type="text" class="form-control" id="new_city" name="city" placeholder="Enter city">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

<?php $mysqli->close(); ?>

