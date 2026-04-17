<?php
require_once __DIR__ . '/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Log-in (Student).php");
    exit();
}

$user_id = $_SESSION['user_id'];
$successMessage = "";
$errorMessage = "";

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $city = trim($_POST['city'] ?? '');

    if (empty($name) || empty($email)) {
        $errorMessage = "Name and email are required.";
    } else {
        // Check if email already exists for another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $errorMessage = "Email already exists.";
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, gender = ?, email = ?, username = ?, contact = ?, city = ? WHERE id = ?");
            $stmt->bind_param("ssssssi", $name, $gender, $email, $username, $contact, $city, $user_id);

            if ($stmt->execute()) {
                // Update session variables
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                $successMessage = "Profile updated successfully!";
                
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user_result = $stmt->get_result();
                $user_data = $user_result->fetch_assoc();
            } else {
                $errorMessage = "Error updating profile. Please try again.";
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Career Counseling Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom, #000, #4b0082);
            color: white;
            font-family: 'Arial', sans-serif;
        }

        .container {
            margin-top: 40px;
            max-width: 600px;
        }

        .form-container {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 30px;
            border-radius: 10px;
            border-left: 5px solid #800080;
        }

        .form-group label {
            color: white;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid #800080;
            color: white;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: #800080;
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

        .btn-secondary {
            background-color: #555;
            border-color: #555;
        }

        .btn-secondary:hover {
            background-color: #333;
            border-color: #333;
        }

        .alert {
            margin-bottom: 20px;
        }

        .back-link {
            color: #800080;
            text-decoration: none;
        }

        .back-link:hover {
            color: #4b0082;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <a href="Student_Dashboard.php" class="back-link">← Back to Dashboard</a>
            
            <h2 class="mb-4 mt-3">Edit Your Profile</h2>

            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <form method="POST" action="edit_profile.php">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo ($user_data['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($user_data['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($user_data['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?php echo htmlspecialchars($user_data['username']); ?>">
                </div>

                <div class="form-group">
                    <label for="contact">Contact Number</label>
                    <input type="tel" id="contact" name="contact" class="form-control" 
                           value="<?php echo htmlspecialchars($user_data['contact'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" class="form-control" 
                           value="<?php echo htmlspecialchars($user_data['city'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
                </div>
            </form>

            <small class="text-muted">* Required fields</small>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

