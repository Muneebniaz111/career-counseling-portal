<?php
session_start();
require_once __DIR__ . '/bootstrap.php';

$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validation
    if (empty($email) || empty($password)) {
        $errorMessage = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Please enter a valid email address.";
    } else {
        // Query admin user
        $stmt = $conn->prepare("SELECT id, name, email, password FROM admin_users WHERE email = ?");
        if (!$stmt) {
            $errorMessage = "Database error. Please try again later.";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();

                // Check password verification
                $passwordVerified = password_verify($password, $admin['password']);
                
                // Verify password
                if ($passwordVerified) {
                    // CRITICAL FIX: Regenerate session ID to prevent session fixation attacks
                    session_regenerate_id(true);
                    
                    // Password is correct, start session and redirect
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['user_type'] = 'admin';
                    
                    $stmt->close();
                    // Redirect to admin dashboard
                    header("Location: Admin_Dashboard.php");
                    exit();
                } else {
                    $errorMessage = "Invalid email or password. Please try again.";
                }
            } else {
                $errorMessage = "Invalid email or password. Please try again.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Career Counseling & Guide Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #4B0082 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .navbar {
            background: rgba(0, 0, 0, 0.95);
            padding: 15px 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
            color: white !important;
        }

        .navbar-brand i {
            margin-right: 8px;
            color: #d4af37;
        }

        .navbar-nav .nav-link {
            color: white !important;
            margin-left: 20px;
            transition: 0.3s;
            font-weight: 500;
        }

        .navbar-nav .nav-link:hover {
            color: #d4af37 !important;
        }

        /* Main Content */
        .login-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 420px;
            animation: fadeInUp 0.6s ease-out;
            border-top: 4px solid #d4af37;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-badge {
            display: inline-block;
            background: linear-gradient(135deg, #d4af37 0%, #aa8c2c 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 15px;
            text-align: center;
            width: 100%;
            letter-spacing: 1px;
        }

        .login-title {
            text-align: center;
            color: #1a1a1a;
            margin-bottom: 10px;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .login-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: white;
            color: #333;
        }

        .form-group input:focus {
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
            outline: none;
        }

        .form-group input.error {
            border-color: #dc3545;
        }

        .form-group input.success {
            border-color: #28a745;
        }

        .password-group {
            position: relative;
            margin-bottom: 20px;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 40px;
            cursor: pointer;
            color: #666;
            transition: 0.3s;
            font-size: 1.1rem;
        }

        .password-toggle:hover {
            color: #d4af37;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 6px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #d4af37;
        }

        .form-check-label {
            margin-left: 8px;
            color: #666;
            cursor: pointer;
            font-weight: 500;
            margin-bottom: 0;
        }

        .login-btn {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #d4af37 0%, #aa8c2c 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }

        .login-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
        }

        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 0.95rem;
        }

        .back-link a {
            color: #d4af37;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .alert {
            margin-bottom: 20px;
            border: none;
            border-radius: 6px;
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-danger {
            background: #ffe6e6;
            color: #dc3545;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-card {
                padding: 30px 20px;
            }

            .navbar {
                padding: 12px 20px;
            }

            .login-title {
                font-size: 1.5rem;
            }

            .login-wrapper {
                padding: 20px 15px;
            }

            .credentials-hint {
                font-size: 0.75rem;
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 20px 15px;
            }

            .login-title {
                font-size: 1.3rem;
            }

            .form-group input {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.html">
                <i class="fas fa-crown"></i>Admin Portal
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="Log-in (Student).php"><i class="fas fa-user-graduate"></i> Student Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Login Container -->
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-badge">
                <i class="fas fa-lock-open"></i> ADMINISTRATOR ACCESS
            </div>
            <h1 class="login-title">Admin Login</h1>
            <p class="login-subtitle">Secure administrative access</p>

            <!-- Error Alert -->
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="Log-in (Admin).php" id="loginForm">
                <!-- Email -->
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Admin Email</label>
                    <input type="email" id="email" name="email" placeholder="admin@example.com" required>
                    <div class="error-message" id="emailError"></div>
                </div>

                <!-- Password -->
                <div class="password-group form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <i class="fas fa-eye password-toggle" id="passwordToggle"></i>
                    <div class="error-message" id="passwordError"></div>
                </div>

                <!-- Remember Me -->
                <div class="remember-me">
                    <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember me</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="login-btn" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Sign In as Admin
                </button>

                <!-- Back Link -->
                <div class="back-link">
                    <p><a href="Log-in (Student).php"><i class="fas fa-arrow-left"></i> Back to Student Login</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const form = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');
        const loginBtn = document.getElementById('loginBtn');

        // Password visibility toggle
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Validation functions
        function validateEmail() {
            const email = emailInput.value.trim();
            const error = document.getElementById('emailError');
            const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            if (!email) {
                error.textContent = 'Email is required';
                error.classList.add('show');
                emailInput.classList.add('error');
                return false;
            }
            if (!emailRegex.test(email)) {
                error.textContent = 'Please enter a valid email address';
                error.classList.add('show');
                emailInput.classList.add('error');
                return false;
            }
            error.classList.remove('show');
            emailInput.classList.remove('error');
            emailInput.classList.add('success');
            return true;
        }

        function validatePassword() {
            const password = passwordInput.value;
            const error = document.getElementById('passwordError');

            if (!password) {
                error.textContent = 'Password is required';
                error.classList.add('show');
                passwordInput.classList.add('error');
                return false;
            }
            if (password.length < 6) {
                error.textContent = 'Password must be at least 6 characters';
                error.classList.add('show');
                passwordInput.classList.add('error');
                return false;
            }
            error.classList.remove('show');
            passwordInput.classList.remove('error');
            passwordInput.classList.add('success');
            return true;
        }

        // Add blur validation listeners
        emailInput.addEventListener('blur', validateEmail);
        passwordInput.addEventListener('blur', validatePassword);

        // Clear errors on input
        emailInput.addEventListener('input', function() {
            document.getElementById('emailError').classList.remove('show');
            this.classList.remove('error');
        });

        passwordInput.addEventListener('input', function() {
            document.getElementById('passwordError').classList.remove('show');
            this.classList.remove('error');
        });

        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const isEmailValid = validateEmail();
            const isPasswordValid = validatePassword();

            if (isEmailValid && isPasswordValid) {
                loginBtn.disabled = true;
                loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
                form.submit();
            }
        });
    </script>
</body>
</html>

