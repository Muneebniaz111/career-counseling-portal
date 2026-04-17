<?php
session_start();
require_once __DIR__ . '/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Contact Us | Career Portal</title>
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
            padding: 3rem 1rem;
        }

        /* Hero Section */
        .hero-section {
            text-align: center;
            margin-bottom: 3rem;
            color: white;
            animation: fadeInDown 0.8s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-section h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-section p {
            font-size: 1.1rem;
            opacity: 0.95;
            max-width: 700px;
            margin: 0 auto;
        }

        /* Container */
        .content-container {
            max-width: 1000px;
            margin: 0 auto;
            width: 100%;
        }

        /* Contact Grid */
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .contact-info-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
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

        .contact-info-card:nth-child(1) { animation-delay: 0.1s; }
        .contact-info-card:nth-child(2) { animation-delay: 0.2s; }
        .contact-info-card:nth-child(3) { animation-delay: 0.3s; }

        .contact-info-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 30px rgba(128, 0, 128, 0.2);
        }

        .contact-info-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .contact-info-card h4 {
            color: #333;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .contact-info-card p {
            color: #666;
            margin-bottom: 0;
            font-size: 0.95rem;
        }

        /* Contact Form */
        .contact-form {
            background: white;
            border-radius: 12px;
            padding: 3rem 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.6s ease-out 0.4s both;
        }

        .contact-form h3 {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 2rem;
            text-align: center;
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
            padding: 0.8rem 1rem;
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
            background-image: none;
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

        textarea.form-control {
            resize: vertical;
            min-height: 130px;
            max-height: 300px;
        }

        .char-counter {
            font-size: 0.85rem;
            color: #999;
            text-align: right;
            margin-top: 0.4rem;
        }

        .char-counter.warning {
            color: #ff9800;
        }

        .char-counter.danger {
            color: var(--danger);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .btn-submit {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.95rem 2.5rem;
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
            gap: 0.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(128, 0, 128, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Toast Notification */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            min-width: 300px;
            animation: slideIn 0.4s ease-out;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(400px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .toast.success {
            border-left: 4px solid var(--success);
        }

        .toast.success .toast-icon {
            color: var(--success);
        }

        .toast.error {
            border-left: 4px solid var(--danger);
        }

        .toast.error .toast-icon {
            color: var(--danger);
        }

        .toast-icon {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 700;
            color: #333;
            margin-bottom: 0.3rem;
        }

        .toast-message {
            color: #666;
            font-size: 0.9rem;
        }

        .toast-close {
            cursor: pointer;
            color: #999;
            font-size: 1.2rem;
            flex-shrink: 0;
            transition: color 0.3s ease;
        }

        .toast-close:hover {
            color: #333;
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

            main {
                padding: 2rem 0.5rem;
            }

            .hero-section h1 {
                font-size: 2rem;
            }

            .hero-section p {
                font-size: 1rem;
            }

            .contact-form {
                padding: 2rem 1.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .contact-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .toast-container {
                right: 10px;
                left: 10px;
            }

            .toast {
                min-width: auto;
                width: 100%;
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

            .hero-section h1 {
                font-size: 1.5rem;
            }

            .hero-section p {
                font-size: 0.95rem;
            }

            .contact-form {
                padding: 1.5rem 1rem;
            }

            .form-control {
                padding: 0.7rem 0.8rem;
                font-size: 0.9rem;
            }

            .btn-submit {
                padding: 0.85rem 1.5rem;
                font-size: 0.95rem;
            }

            .contact-info-card {
                padding: 1.5rem 1rem;
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
                        <a class="nav-link" href="Student.html"><i class="fas fa-arrow-left"></i> Back</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <div class="content-container">
            <!-- Hero Section -->
            <section class="hero-section">
                <h1><i class="fas fa-envelope"></i> Contact Us</h1>
                <p>We're here to help! Get in touch with our team for any questions or support you need</p>
            </section>

            <!-- Contact Information Cards -->
            <section class="contact-grid">
                <div class="contact-info-card">
                    <div class="contact-info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h4>Address</h4>
                    <p>123 Career Street<br>Counseling City, CC 12345<br>Pakistan</p>
                </div>

                <div class="contact-info-card">
                    <div class="contact-info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h4>Phone</h4>
                    <p><a href="tel:+92-21-1234567" style="color: #666; text-decoration: none;">+92-21-1234567</a><br>Available 24/7</p>
                </div>

                <div class="contact-info-card">
                    <div class="contact-info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h4>Email</h4>
                    <p><a href="mailto:careercounseling.portal@gmail.com" style="color: #666; text-decoration: none;">careercounseling.portal@gmail.com</a><br>Response within 24 hours</p>
                </div>
            </section>

            <!-- Contact Form -->
            <section>
                <div class="contact-form">
                    <h3><i class="fas fa-pencil-alt"></i> Send us a Message</h3>
                    <form id="contactFormElement" novalidate>
                        <!-- CRITICAL FIX: Add CSRF token hidden input -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name <span class="required">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Your Full Name" minlength="2" maxlength="50" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address <span class="required">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="your.email@example.com" maxlength="100" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="+92-300-1234567" maxlength="20">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="form-group">
                                <label for="subject">Subject <span class="required">*</span></label>
                                <input type="text" class="form-control" id="subject" name="subject" placeholder="Message Subject" minlength="3" maxlength="100" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="form-group form-row full">
                            <label for="message">Message <span class="required">*</span></label>
                            <textarea class="form-control" id="message" name="message" placeholder="Please share your message here..." minlength="10" maxlength="1000" required></textarea>
                            <div class="char-counter"><span class="char-count">0</span>/1000</div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <button type="submit" class="btn-submit" id="submitBtn">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p><i class="fas fa-envelope"></i> Email: <a href="mailto:careercounseling.portal@gmail.com">careercounseling.portal@gmail.com</a></p>
        <p><i class="fas fa-phone"></i> Phone: +92-21-1234567</p>
        <p style="margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 1rem;">
            &copy; 2026 Career Counseling & Guide Portal. All Rights Reserved.
        </p>
    </footer>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toast Notification Function
        function showToast(type, title, message) {
            const toastContainer = document.getElementById('toastContainer');
            const toastElement = document.createElement('div');
            toastElement.className = `toast ${type}`;
            toastElement.innerHTML = `
                <div class="toast-icon">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                </div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <div class="toast-close" onclick="this.parentElement.remove()">×</div>
            `;
            toastContainer.appendChild(toastElement);

            // Auto-remove toast after 5 seconds
            setTimeout(() => {
                if (toastElement.parentElement) {
                    toastElement.remove();
                }
            }, 5000);
        }

        // Validation Functions
        function validateName(name) {
            return name.trim().length >= 2 && name.trim().length <= 50;
        }

        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function validatePhone(phone) {
            if (!phone) return true; // Optional field
            // Allow any combination of digits, spaces, hyphens, parentheses, and plus sign
            // Must have at least 7 digits total
            const digitsOnly = phone.replace(/\D/g, '');
            return digitsOnly.length >= 7;
        }

        function validateSubject(subject) {
            return subject.trim().length >= 3 && subject.trim().length <= 100;
        }

        function validateMessage(message) {
            return message.trim().length >= 10 && message.trim().length <= 1000;
        }

        // Helper function to get error element for a field
        function getErrorElement(fieldId) {
            const field = document.getElementById(fieldId);
            if (!field) return null;
            
            // If it's the message field, skip char-counter
            if (fieldId === 'message') {
                return field.parentElement.querySelector('.invalid-feedback');
            }
            
            // For other fields, next sibling should be invalid-feedback
            return field.nextElementSibling;
        }

        // Helper function to set field error
        function setFieldError(fieldId, isInvalid, errorMsg = '') {
            const field = document.getElementById(fieldId);
            const errorEl = getErrorElement(fieldId);
            
            if (isInvalid) {
                field.classList.add('is-invalid');
                if (errorEl) errorEl.textContent = errorMsg;
            } else {
                field.classList.remove('is-invalid');
                if (errorEl) errorEl.textContent = '';
            }
        }

        // Real-time validation for each field
        document.getElementById('name').addEventListener('blur', function() {
            const isValid = validateName(this.value);
            setFieldError('name', !isValid, isValid ? '' : 'Name must be between 2 and 50 characters');
        });

        document.getElementById('email').addEventListener('blur', function() {
            const isValid = validateEmail(this.value);
            setFieldError('email', !isValid, isValid ? '' : 'Please enter a valid email address');
        });

        document.getElementById('phone').addEventListener('blur', function() {
            const isValid = !this.value || validatePhone(this.value);
            setFieldError('phone', !isValid, isValid ? '' : 'Please enter a valid phone number');
        });

        document.getElementById('subject').addEventListener('blur', function() {
            const isValid = validateSubject(this.value);
            setFieldError('subject', !isValid, isValid ? '' : 'Subject must be between 3 and 100 characters');
        });

        // Character counter for message
        const messageField = document.getElementById('message');
        const charCounter = document.querySelector('.char-counter');
        messageField.addEventListener('input', function() {
            const count = this.value.length;
            document.querySelector('.char-count').textContent = count;
            charCounter.classList.remove('warning', 'danger');
            if (count > 900) {
                charCounter.classList.add('danger');
            } else if (count > 800) {
                charCounter.classList.add('warning');
            }
        });

        messageField.addEventListener('blur', function() {
            const isValid = validateMessage(this.value);
            setFieldError('message', !isValid, isValid ? '' : 'Message must be between 10 and 1000 characters');
        });

        // Form submission with CSRF token validation
        document.getElementById('contactFormElement').addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate all fields
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value;

            let isFormValid = true;

            // Validate each field and set errors
            if (!validateName(name)) {
                setFieldError('name', true, 'Name must be between 2 and 50 characters');
                isFormValid = false;
            } else {
                setFieldError('name', false);
            }

            if (!validateEmail(email)) {
                setFieldError('email', true, 'Please enter a valid email address');
                isFormValid = false;
            } else {
                setFieldError('email', false);
            }

            if (phone && !validatePhone(phone)) {
                setFieldError('phone', true, 'Please enter a valid phone number');
                isFormValid = false;
            } else {
                setFieldError('phone', false);
            }

            if (!validateSubject(subject)) {
                setFieldError('subject', true, 'Subject must be between 3 and 100 characters');
                isFormValid = false;
            } else {
                setFieldError('subject', false);
            }

            if (!validateMessage(message)) {
                setFieldError('message', true, 'Message must be between 10 and 1000 characters');
                isFormValid = false;
            } else {
                setFieldError('message', false);
            }

            if (!isFormValid) {
                showToast('error', 'Validation Error', 'Please fix the errors in the form');
                return;
            }

            // Disable submit button
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

            // CRITICAL FIX: Include CSRF token in form submission
            const formData = new FormData();
            formData.append('name', name);
            formData.append('email', email);
            formData.append('phone', phone);
            formData.append('subject', subject);
            formData.append('message', message);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

            // Send to backend
            fetch('contact_form.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                // Try to parse as JSON
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON response:', text);
                    throw new Error('Invalid response from server. Please try again.');
                }

                if (data.success) {
                    showToast('success', 'Success!', 'Your message has been sent successfully. We will respond within 24 hours.');
                    
                    // Save to localStorage as backup
                    const contactData = { name, email, phone, subject, message, timestamp: new Date().toISOString() };
                    localStorage.setItem('lastContact', JSON.stringify(contactData));

                    // Reset form
                    document.getElementById('contactFormElement').reset();
                    document.querySelector('.char-count').textContent = '0';
                    
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Message';
                } else {
                    showToast('error', 'Error', data.message || 'Failed to send message. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Message';
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showToast('error', 'Error', error.message || 'An error occurred. Please try again later.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Message';
            });
        });

        // Prevent form submission on Enter in input fields
        document.querySelectorAll('.form-control').forEach(input => {
            if (input.tagName !== 'TEXTAREA') {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
</body>
</html>

