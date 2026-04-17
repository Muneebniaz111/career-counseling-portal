<?php
require_once __DIR__ . '/bootstrap.php';

$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $province = trim($_POST['province'] ?? '');

    // Validation
    if (empty($name) || empty($email) || empty($username) || empty($password) || empty($confirmPassword)) {
        $errorMessage = "Please fill in all required fields.";
    } elseif (strlen($name) < 2) {
        $errorMessage = "Name must be at least 2 characters long.";
    } elseif (strlen($password) < 8) {
        $errorMessage = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirmPassword) {
        $errorMessage = "Passwords do not match. Please try again.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Invalid email format.";
    } elseif (strlen($username) < 3) {
        $errorMessage = "Username must be at least 3 characters long.";
    } elseif (!empty($contact) && !preg_match('/^[\d+\-() ]+$/', $contact)) {
        $errorMessage = "Invalid phone number format.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errorMessage = "Email or username already exists. Please use different credentials.";
        } else {
            // Hash password and insert user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (name, gender, email, username, password, contact, city) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $name, $gender, $email, $username, $hashedPassword, $contact, $province);

            if ($stmt->execute()) {
                $successMessage = "Registration successful! Redirecting to login...";
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'Log-in (Student).php';
                    }, 2000);
                </script>";
            } else {
                $errorMessage = "Registration failed. Please try again later.";
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Career Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #800080;
            --secondary: #4B0082;
            --dark: #1a1a1a;
            --light: #f8f9fa;
            --danger: #dc3545;
            --success: #28a745;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--dark) 0%, var(--secondary) 100%);
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: 60px;
        }

        /* Navbar */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background: linear-gradient(90deg, #000 0%, var(--dark) 100%) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            padding: 1.2rem 0 !important;
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
            transition: all 0.3s ease;
            padding: 0.6rem 1.2rem !important;
            border-radius: 5px;
            margin-left: 0.5rem;
        }

        .nav-link:hover {
            background-color: var(--primary);
            color: white !important;
        }

        /* Main Content */
        main {
            flex: 1;
            padding: 2rem 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Form Container */
        .signup-container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 12px;
            padding: 3rem 2.5rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .signup-container h2 {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .signup-subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            margin-bottom: 0.6rem;
            font-size: 0.95rem;
        }

        .form-group label .required {
            color: var(--danger);
            margin-left: 0.3rem;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 0.85rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(128, 0, 128, 0.15);
            outline: none;
        }

        .form-control.is-invalid {
            border-color: var(--danger);
        }

        .form-control.is-invalid:focus {
            border-color: var(--danger);
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15);
        }

        .invalid-feedback {
            display: block;
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 0.4rem;
        }

        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .password-strength {
            font-size: 0.8rem;
            margin-top: 0.4rem;
            display: none;
        }

        .password-strength.weak {
            color: var(--danger);
            display: block;
        }

        .password-strength.medium {
            color: #ff9800;
            display: block;
        }

        .password-strength.strong {
            color: var(--success);
            display: block;
        }

        .btn-signup {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.95rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
        }

        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(128, 0, 128, 0.3);
        }

        .btn-signup:active {
            transform: translateY(0);
        }

        .btn-signup:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Alert Messages */
        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 2rem;
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-danger {
            background: #ffe6e6;
            color: var(--danger);
            padding: 1rem 1.2rem;
        }

        .alert-success {
            background: #e6ffe6;
            color: var(--success);
            padding: 1rem 1.2rem;
        }

        /* Login Link */
        .login-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f0f0f0;
        }

        .login-link p {
            color: #666;
            margin-bottom: 0;
            font-size: 0.95rem;
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding-top: 50px;
            }

            nav {
                padding: 1rem 0 !important;
            }

            .navbar-brand {
                font-size: 1.1rem;
            }

            .navbar-brand img {
                height: 40px;
                width: 40px;
                margin-right: 8px;
            }

            main {
                padding: 1.5rem 0.5rem;
            }

            .signup-container {
                padding: 2rem 1.5rem;
            }

            .signup-container h2 {
                font-size: 1.5rem;
            }

            .form-row-2 {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }

        @media (max-width: 480px) {
            .navbar-brand {
                font-size: 1rem;
            }

            .navbar-brand img {
                height: 35px;
                width: 35px;
                margin-right: 10px;
            }

            .signup-container {
                padding: 1.5rem 1rem;
            }

            .signup-container h2 {
                font-size: 1.3rem;
            }

            .signup-subtitle {
                font-size: 0.85rem;
            }

            .form-control {
                padding: 0.75rem 0.8rem;
                font-size: 0.9rem;
            }

            .btn-signup {
                padding: 0.85rem 1rem;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid px-4 px-md-5">
            <a class="navbar-brand" href="index.html">
                <img src="shikshalogo.jpg" alt="Career Portal Logo">
                <span>Career Portal</span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="Log-in (Student).php"><i class="fas fa-sign-in-alt"></i> Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <div class="signup-container">
            <h2><i class="fas fa-user-plus"></i> Create Your Account</h2>
            <p class="signup-subtitle">Join our community and start your career counseling journey</p>

            <!-- Error Alert -->
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <!-- Success Alert -->
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <!-- Sign Up Form -->
            <form method="POST" action="Sign-Up.php" id="signupForm" novalidate>
                <!-- Full Name -->
                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" minlength="2" maxlength="50" required>
                    <div class="invalid-feedback"></div>
                </div>

                <!-- Gender and Email Row -->
                <div class="form-row-2">
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-control">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                            <option value="Prefer not to say">Prefer not to say</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="your.email@example.com" maxlength="100" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Username -->
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Choose a unique username" minlength="3" maxlength="30" required>
                    <div class="invalid-feedback"></div>
                </div>

                <!-- Password Row -->
                <div class="form-row-2">
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Min. 8 characters" minlength="8" maxlength="50" required>
                        <div class="password-strength" id="passwordStrength"></div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password <span class="required">*</span></label>
                        <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" placeholder="Re-enter password" minlength="8" maxlength="50" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Contact and Province Row -->
                <div class="form-row-2">
                    <div class="form-group">
                        <label for="contact">Phone Number</label>
                        <input type="tel" id="contact" name="contact" class="form-control" placeholder="+92-300-1234567" maxlength="20">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="province">Province</label>
                        <input type="text" id="province" name="province" class="form-control" placeholder="Your province" maxlength="50">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-signup" id="submitBtn">
                    <i class="fas fa-check"></i> Create Account
                </button>

                <!-- Login Link -->
                <div class="login-link">
                    <p>Already have an account? <a href="Log-in (Student).php">Login here</a></p>
                </div>
            </form>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Password Strength Indicator
        const passwordField = document.getElementById('password');
        const strengthIndicator = document.getElementById('passwordStrength');

        passwordField.addEventListener('input', function() {
            const password = this.value;
            let strength = 'weak';
            let message = 'Weak password';

            if (password.length >= 8) {
                if (/[a-z]/.test(password) && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[^a-zA-Z0-9]/.test(password)) {
                    strength = 'strong';
                    message = 'Strong password';
                } else if (/[a-z]/.test(password) && /[A-Z]/.test(password) && /[0-9]/.test(password)) {
                    strength = 'medium';
                    message = 'Medium password (add symbols for strength)';
                }
            }

            strengthIndicator.className = `password-strength ${strength}`;
            strengthIndicator.textContent = message;
        });

        // Real-time Form Validation
        const form = document.getElementById('signupForm');
        const nameField = document.getElementById('name');
        const emailField = document.getElementById('email');
        const usernameField = document.getElementById('username');
        const passwordField2 = document.getElementById('password');
        const confirmPasswordField = document.getElementById('confirmPassword');
        const contactField = document.getElementById('contact');

        function validateField(field) {
            let isValid = true;
            const feedbackElement = field.nextElementSibling;

            switch(field.id) {
                case 'name':
                    isValid = field.value.trim().length >= 2;
                    if (!isValid) feedbackElement.textContent = 'Name must be at least 2 characters long';
                    break;
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    isValid = emailRegex.test(field.value);
                    if (!isValid) feedbackElement.textContent = 'Please enter a valid email address';
                    break;
                case 'username':
                    isValid = field.value.trim().length >= 3;
                    if (!isValid) feedbackElement.textContent = 'Username must be at least 3 characters long';
                    break;
                case 'password':
                    isValid = field.value.length >= 8;
                    if (!isValid) feedbackElement.textContent = 'Password must be at least 8 characters long';
                    break;
                case 'confirmPassword':
                    isValid = field.value === passwordField2.value && field.value.length > 0;
                    if (!isValid) feedbackElement.textContent = 'Passwords do not match';
                    break;
                case 'contact':
                    if (field.value) {
                        const phoneRegex = /^[\d+\-() ]+$/;
                        isValid = phoneRegex.test(field.value);
                        if (!isValid) feedbackElement.textContent = 'Please enter a valid phone number';
                    }
                    break;
            }

            field.classList.toggle('is-invalid', !isValid);
            return isValid;
        }

        [nameField, emailField, usernameField, passwordField2, confirmPasswordField, contactField].forEach(field => {
            field.addEventListener('blur', function() {
                validateField(this);
            });
            field.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });

        // Form Submit Validation
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            let isFormValid = true;
            [nameField, emailField, usernameField, passwordField2, confirmPasswordField].forEach(field => {
                if (!validateField(field)) {
                    isFormValid = false;
                }
            });

            if (contactField.value && !validateField(contactField)) {
                isFormValid = false;
            }

            if (isFormValid) {
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
                form.submit();
            }
        });

        // Prevent form submission on Enter in input fields (except textarea)
        form.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>


