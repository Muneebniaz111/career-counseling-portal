<?php
session_start();
require_once __DIR__ . '/bootstrap.php';

$successMessage = "";
$errorMessage = "";
$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'student';
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;
$user_name = $isLoggedIn ? $_SESSION['user_name'] : '';
$user_email = '';

// Get user email if logged in
if ($isLoggedIn && $user_id) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $user_email = $row['email'];
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errorMessage = "Invalid request. Please try again.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $rating = intval($_POST['rating'] ?? 0);

        // Validation
        if (empty($name) || empty($email) || empty($subject) || empty($message) || $rating == 0) {
            $errorMessage = "Please fill in all required fields and select a rating.";
        } elseif (strlen($name) < 2) {
            $errorMessage = "Name must be at least 2 characters long.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = "Invalid email format.";
        } elseif (strlen($subject) < 3) {
            $errorMessage = "Subject must be at least 3 characters long.";
        } elseif (strlen($message) < 10) {
            $errorMessage = "Feedback message must be at least 10 characters long.";
        } elseif ($rating < 1 || $rating > 5) {
            $errorMessage = "Rating must be between 1 and 5.";
        } else {
            // Insert into database with prepared statement
            try {
                $stmt = $conn->prepare("INSERT INTO feedback (user_id, name, email, subject, message, rating, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'open', NOW())");
                
                if (!$stmt) {
                    error_log("Prepare failed: " . $conn->error);
                    throw new Exception("An error occurred while processing your feedback. Please try again later.");
                }
                
                // Bind parameters: user_id (integer or NULL), name (string), email (string), subject (string), message (string), rating (integer)
                $stmt->bind_param("issssi", $user_id, $name, $email, $subject, $message, $rating);

                if (!$stmt->execute()) {
                    error_log("Execute failed: " . $stmt->error);
                    throw new Exception("An error occurred while submitting your feedback. Please try again later.");
                }
                
                $successMessage = "Thank you for your feedback! We've received your submission and will review it shortly.";
                
                // Redirect to Student Dashboard after success (with delay for message display)
                header("Refresh: 2; url=Student_Dashboard.php");
                
                // Clear form values
                $name = $email = $subject = $message = "";
                $rating = 0;
                
                $stmt->close();
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
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
    <title>Feedback - Career Counseling</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom, #000, #4b0082);
            color: white;
            min-height: 100vh;
        }

        .container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background-color: #000;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .navbar-brand img {
            margin-right: 10px;
            height: 40px;
        }

        .navbar ul {
            list-style: none;
            display: flex;
            margin: 0;
        }

        .navbar ul li {
            margin-left: 25px;
        }

        .navbar ul li a {
            text-decoration: none;
            color: white;
            font-weight: bold;
            font-size: 16px;
            transition: color 0.3s;
        }

        .navbar ul li a:hover {
            color: #800080;
        }

        .hero {
            text-align: center;
            padding: 50px 20px;
        }

        .hero h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 1.1rem;
            color: #ddd;
            margin-bottom: 10px;
        }

        .feedback-form-container {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 10px;
            padding: 40px;
            max-width: 600px;
            margin: 30px auto;
            border-left: 5px solid #800080;
        }

        .form-group label {
            color: white;
            font-weight: bold;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid #800080;
            color: white;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #800080;
            color: white;
        }

        .star-rating {
            display: flex;
            gap: 10px;
            font-size: 2rem;
            margin: 15px 0;
        }

        .star {
            cursor: pointer;
            color: #555;
            transition: color 0.3s;
        }

        .star:hover,
        .star.selected {
            color: #ffc107;
        }

        .submit-btn {
            background: #800080;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            width: 100%;
            transition: 0.3s;
        }

        .submit-btn:hover {
            background: #4b0082;
        }

        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="navbar-brand">
                <img src="shikshalogo.jpg" alt="Logo">
                <span>Career Portal</span>
            </div>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="About us.html">About Us</a></li>
                <li><a href="contact_form.php">Contact</a></li>
                <li><a href="resources.html">Resources</a></li>
                <li><a href="Log-in (Student).php">Login</a></li>
            </ul>
        </nav>

        <div class="hero">
            <h1>📝 Share Your Feedback</h1>
            <p>Help us improve by sharing your experience with our Career Counseling Portal</p>
        </div>

        <div class="feedback-form-container">
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <form method="POST" action="feedback_form.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="form-group">
                    <label for="name">Your Name *</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter your name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Your Email *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-control" placeholder="Feedback subject" value="<?php echo htmlspecialchars($subject ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="rating">Rate Your Experience *</label>
                    <div class="star-rating" id="starRating">
                        <span class="star" data-value="1">★</span>
                        <span class="star" data-value="2">★</span>
                        <span class="star" data-value="3">★</span>
                        <span class="star" data-value="4">★</span>
                        <span class="star" data-value="5">★</span>
                    </div>
                    <input type="hidden" id="rating" name="rating" value="<?php echo htmlspecialchars($rating ?? '0'); ?>">
                </div>

                <div class="form-group">
                    <label for="message">Your Feedback *</label>
                    <textarea id="message" name="message" class="form-control" rows="5" placeholder="Please share your feedback..." required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                </div>

                <button type="submit" class="submit-btn">Submit Feedback</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script>
        // Star rating functionality
        const stars = document.querySelectorAll('.star');
        const ratingInput = document.getElementById('rating');

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const value = this.dataset.value;
                ratingInput.value = value;

                stars.forEach(s => {
                    if (s.dataset.value <= value) {
                        s.classList.add('selected');
                    } else {
                        s.classList.remove('selected');
                    }
                });
            });

            star.addEventListener('mouseover', function() {
                const value = this.dataset.value;
                stars.forEach(s => {
                    if (s.dataset.value <= value) {
                        s.style.color = '#ffc107';
                    } else {
                        s.style.color = '#555';
                    }
                });
            });
        });

        document.getElementById('starRating').addEventListener('mouseout', function() {
            const selectedValue = ratingInput.value;
            stars.forEach(s => {
                if (s.dataset.value <= selectedValue) {
                    s.style.color = '#ffc107';
                } else {
                    s.style.color = '#555';
                }
            });
        });
    </script>
</body>
</html>

