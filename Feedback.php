<?php
session_start();
require_once __DIR__ . '/bootstrap.php';

// Initialize CSRF token if not present
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Your Feedback | Career Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #800080;
            --secondary: #4B0082;
            --dark: #1a1a1a;
            --light: #f8f9fa;
            --success: #28a745;
            --danger: #dc3545;
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
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
            border-radius: 5px;
            margin: 0 0.5rem;
        }

        .nav-link:hover {
            background-color: var(--primary);
            color: white !important;
        }

        .nav-link.btn-login {
            background: linear-gradient(90deg, #000 0%, var(--dark) 100%) !important;
            color: white !important;
        }

        .nav-link.btn-login:hover {
            background-color: var(--primary) !important;
            color: white !important;
            transform: translateX(-3px);
        }

        /* Main Content */
        main {
            flex: 1;
            padding: 3rem 1rem;
        }

        /* Hero Section */
        .hero-section {
            text-align: center;
            padding: 2rem 1rem;
            animation: fadeInDown 0.5s ease-out;
        }

        .hero-section h1 {
            font-size: 2.8rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-section p {
            font-size: 1.2rem;
            color: #ddd;
            margin: 0;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form Container */
        .feedback-form-container {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 12px;
            padding: 2.5rem;
            max-width: 700px;
            margin: 2rem auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 0.5s ease-out;
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

        .feedback-form-container h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }

        .form-subtitle {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 2rem;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(128, 0, 128, 0.1);
            background-color: #fafafa;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }

        /* Star Rating */
        .rating-section {
            margin-bottom: 2rem;
        }

        .rating-label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .star-rating {
            display: flex;
            gap: 0.75rem;
            font-size: 2rem;
        }

        .star {
            cursor: pointer;
            color: #ddd;
            transition: all 0.2s ease;
        }

        .star:hover,
        .star.active {
            color: #ffc107;
            transform: scale(1.2);
        }

        .rating-value {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: #666;
        }

        /* Submit Button */
        .form-group button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(128, 0, 128, 0.3);
            margin-top: 0.5rem;
        }

        .form-group button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(128, 0, 128, 0.4);
        }

        .form-group button:active {
            transform: translateY(0);
        }

        .form-group button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Character Count */
        .char-count {
            font-size: 0.8rem;
            color: #999;
            margin-top: 0.3rem;
        }

        /* Error Message */
        .form-error {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: none;
        }

        .form-group.error input,
        .form-group.error textarea,
        .form-group.error select {
            border-color: var(--danger);
        }

        .form-group.error .form-error {
            display: block;
        }

        /* Success Message */
        .success-message {
            position: fixed;
            top: 80px;
            right: 20px;
            background: var(--success);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            animation: slideInRight 0.3s ease-out;
            z-index: 9999;
            max-width: 400px;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOutRight {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100px);
            }
        }

        .success-message.remove {
            animation: slideOutRight 0.3s ease-out;
        }

        /* Footer */
        footer {
            background: linear-gradient(90deg, #000 0%, var(--dark) 100%);
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: auto;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.2);
        }

        footer p {
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        footer a {
            color: var(--primary);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        footer a:hover {
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

            .nav-link {
                padding: 0.5rem 0.8rem !important;
                font-size: 0.9rem;
                margin-left: 0.25rem !important;
            }

            main {
                padding: 2rem 0.5rem;
            }

            .hero-section h1 {
                font-size: 2rem;
            }

            .hero-section p {
                font-size: 1rem;
            }

            .feedback-form-container {
                padding: 2rem 1.5rem;
            }

            .feedback-form-container h2 {
                font-size: 1.5rem;
            }

            .star-rating {
                font-size: 1.5rem;
            }

            .success-message {
                right: 10px;
                left: 10px;
                max-width: none;
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

            .nav-link {
                padding: 0.4rem 0.6rem !important;
                font-size: 0.8rem;
            }

            .feedback-form-container {
                padding: 1.5rem 1rem;
            }

            .hero-section h1 {
                font-size: 1.5rem;
            }

            footer {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid px-2 px-md-4">
            <a class="navbar-brand" href="Student.html">
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
                        <a class="nav-link" href="Student.html"><i class="fas fa-arrow-left"></i> Back</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero-section">
            <h1><i class="fas fa-comments"></i> Share Your Feedback</h1>
            <p>Help us improve by sharing your thoughts and suggestions</p>
        </section>

        <div class="feedback-form-container">
            <h2>We'd Love to Hear From You!</h2>
            <p class="form-subtitle">Your feedback helps us serve you better</p>

            <form id="feedbackForm" action="feedback_form.php" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" id="name" name="name" 
                        placeholder="Enter your full name" 
                        required>
                    <span class="form-error">Please enter your full name</span>
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" id="email" name="email" 
                        placeholder="your.email@example.com" 
                        required>
                    <span class="form-error">Please enter a valid email address</span>
                </div>

                <div class="form-group">
                    <label for="subject"><i class="fas fa-heading"></i> Subject</label>
                    <input type="text" id="subject" name="subject" 
                        placeholder="Brief subject of your feedback" 
                        required>
                    <span class="form-error">Please enter a subject</span>
                </div>

                <div class="rating-section">
                    <label class="rating-label"><i class="fas fa-star"></i> Rate Your Experience</label>
                    <div class="star-rating" id="starRating">
                        <span class="star" data-value="1">★</span>
                        <span class="star" data-value="2">★</span>
                        <span class="star" data-value="3">★</span>
                        <span class="star" data-value="4">★</span>
                        <span class="star" data-value="5">★</span>
                    </div>
                    <div class="rating-value">Selected Rating: <span id="ratingValue">0</span>/5</div>
                    <input type="hidden" id="rating" name="rating" value="0">
                </div>

                <div class="form-group">
                    <label for="message"><i class="fas fa-message"></i> Your Feedback</label>
                    <textarea id="message" name="message" 
                        placeholder="Share your detailed feedback, suggestions, or concerns..." 
                        required 
                        maxlength="5000"></textarea>
                    <div class="char-count"><span id="charCount">0</span>/5000 characters</div>
                    <span class="form-error">Please enter your feedback (minimum 10 characters)</span>
                </div>

                <div class="form-group">
                    <button type="submit" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> Submit Feedback
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p><i class="fas fa-envelope"></i> Email: careercounseling.portal@gmail.com</p>
        <p><i class="fas fa-phone"></i> Phone: +92-21-1234567</p>
        <p style="margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 1rem;">
            &copy; 2026 Career Counseling & Guide Portal. All rights reserved.
        </p>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("feedbackForm");
            const starRating = document.getElementById("starRating");
            const ratingInput = document.getElementById("rating");
            const ratingValue = document.getElementById("ratingValue");
            const feedbackTextarea = document.getElementById("message");
            const charCount = document.getElementById("charCount");
            const submitBtn = document.getElementById("submitBtn");

            // Star Rating Functionality
            const stars = starRating.querySelectorAll(".star");
            stars.forEach(star => {
                star.addEventListener("click", function() {
                    const value = this.getAttribute("data-value");
                    ratingInput.value = value;
                    ratingValue.textContent = value;
                    
                    // Update visual representation
                    stars.forEach((s, index) => {
                        if (index < value) {
                            s.classList.add("active");
                        } else {
                            s.classList.remove("active");
                        }
                    });
                });

                // Hover effect
                star.addEventListener("mouseenter", function() {
                    const value = this.getAttribute("data-value");
                    stars.forEach((s, index) => {
                        if (index < value) {
                            s.style.color = "#ffc107";
                        } else {
                            s.style.color = "#ddd";
                        }
                    });
                });
            });

            starRating.addEventListener("mouseleave", function() {
                stars.forEach(s => {
                    if (s.classList.contains("active")) {
                        s.style.color = "#ffc107";
                    } else {
                        s.style.color = "#ddd";
                    }
                });
            });

            // Character Count
            feedbackTextarea.addEventListener("input", function() {
                charCount.textContent = this.value.length;
            });

            // Form Validation and Submission
            form.addEventListener("submit", function (e) {
                e.preventDefault();

                // Clear previous errors
                document.querySelectorAll(".form-group").forEach(group => {
                    group.classList.remove("error");
                });

                // Get form values
                const name = document.getElementById("name").value.trim();
                const email = document.getElementById("email").value.trim();
                const subject = document.getElementById("subject").value.trim();
                const message = document.getElementById("message").value.trim();
                const rating = document.getElementById("rating").value;

                let isValid = true;

                // Validate name
                if (!name || name.length < 2) {
                    document.getElementById("name").parentElement.classList.add("error");
                    isValid = false;
                }

                // Validate email
                if (!validateEmail(email)) {
                    document.getElementById("email").parentElement.classList.add("error");
                    isValid = false;
                }

                // Validate subject
                if (!subject || subject.length < 3) {
                    document.getElementById("subject").parentElement.classList.add("error");
                    isValid = false;
                }

                // Validate message (feedback)
                if (!message || message.length < 10) {
                    document.getElementById("message").parentElement.classList.add("error");
                    isValid = false;
                }

                // Validate rating
                if (rating == 0) {
                    alert("Please select a rating (1-5 stars)");
                    isValid = false;
                }

                if (!isValid) {
                    return;
                }

                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

                // Submit the form to the backend
                this.submit();
            });

            function validateEmail(email) {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailPattern.test(email);
            }

            function showSuccessMessage(message) {
                const msgDiv = document.createElement("div");
                msgDiv.className = "success-message";
                msgDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
                document.body.appendChild(msgDiv);

                setTimeout(() => {
                    msgDiv.classList.add("remove");
                    setTimeout(() => msgDiv.remove(), 300);
                }, 3000);
            }
        });
    </script>
</body>
</html>





