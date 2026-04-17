# 🎓 Career Counseling & Guide Portal

![Status: Production Ready](https://img.shields.io/badge/Status-Production%20Ready-brightgreen)
![Version: 2.0.0](https://img.shields.io/badge/Version-2.0.0-blue)
![License: MIT](https://img.shields.io/badge/License-MIT-green)

## 📌 Project Overview

**Career Counseling & Guide Portal** is a comprehensive, full-featured web-based platform designed to bridge the gap between students and career guidance services. It provides an integrated ecosystem where students can explore career paths, receive personalized counseling, access learning resources, and track their professional development journey. The platform features secure authentication, real-time feedback systems, and an intuitive admin dashboard for comprehensive resource and user management.

**Live Platform**: http://localhost/Career-Counseling-Guide-Portal/

---

## ✨ Key Features

### 🎯 For Students
| Feature | Description |
|---------|-------------|
| **User Authentication** | Secure registration and login with password hashing (BCrypt) |
| **Profile Management** | Create and update personal profile with all relevant information |
| **Career Assessments** | Take psychometric and skills-based assessments via integrated Google Forms |
| **Career Path Exploration** | Access detailed information about IT, Software Engineering, and other career paths with downloadable PDFs |
| **Resource Library** | Download comprehensive guides for exam prep, course selection, and internships |
| **Appointment Booking** | Schedule counseling sessions with available counselors |
| **Feedback System** | Submit ratings and feedback with 5-star evaluation |
| **Contact Support** | Send messages and inquiries to the counseling team |
| **Dashboard** | Personal dashboard showing profile, appointments, and quick actions |

### 👨‍💼 For Administrators
| Feature | Description |
|---------|-------------|
| **User Management** | View, manage, and delete student accounts with full control |
| **Admin Dashboard** | Comprehensive analytics and system overview |
| **Resource Management** | Upload, update, and manage PDF career resources and guides |
| **Appointment Control** | Monitor and manage all student appointments |
| **Feedback Monitoring** | Review all student feedback and ratings |
| **Message Management** | View and respond to contact form submissions |
| **System Settings** | Configure application settings and preferences |
| **Notification System** | Real-time notification counts for messages and feedback |

---

## 🔧 Technology Stack

### Frontend
- **HTML5** - Semantic markup and structure
- **CSS3** - Modern styling with CSS variables and gradients
- **Bootstrap 4.6.0** - Responsive grid system and pre-built components
- **jQuery 3.6.0** - DOM manipulation and AJAX requests
- **Font Awesome 6.0.0** - Icon library for UI elements
- **JavaScript (Vanilla)** - Interactive features and form validation

### Backend
- **PHP 8.2.12** - Server-side logic and request handling
- **Apache 2.4.58** - Web server with OpenSSL 3.1.3
- **MySQL/MariaDB 10.4.32** - Relational database management

### Development Tools
- **Git & GitHub** - Version control and collaboration
- **XAMPP** - Local development environment
- **PhpMyAdmin** - Database administration interface

### Security
- **BCrypt Password Hashing** - Secure password storage
- **Prepared Statements** - SQL injection prevention
- **Session Management** - Server-side session security
- **Input Sanitization** - XSS protection via `htmlspecialchars()`
- **CSRF Tokens** - Cross-site request forgery protection

---

## 💡 Key Benefits

### For Students
✅ **Personalized Guidance** - Tailored career advice based on assessments and preferences  
✅ **Easy Resource Access** - One-stop library for all career-related documents and guides  
✅ **Professional Development** - Track progress and book counseling sessions  
✅ **Secure Data** - All personal information protected with industry-standard encryption  
✅ **24/7 Availability** - Access resources anytime, anywhere  

### For Institutions
✅ **Centralized Management** - Manage all student counseling from one dashboard  
✅ **Analytics & Insights** - Track engagement and student progress metrics  
✅ **Resource Efficiency** - Automate document distribution and appointment scheduling  
✅ **Scalability** - Support unlimited students and resources  
✅ **Professional Image** - Modern, well-designed platform reflects institutional quality  

### For Counselors
✅ **Organized Scheduling** - Clear view of all appointments  
✅ **Easy Communication** - Direct messaging and feedback channels  
✅ **Resource Library** - Quick access to career information and guides  
✅ **Time-Saving** - Automated administrative tasks  

---

## 🏗️ System Architecture & Working Flow

```
┌─────────────────────────────────────────────────────────────┐
│                   USER ACCESS LAYER                         │
├─────────────────────────────────────────────────────────────┤
│  Student Portal  │  Admin Portal  │  Public Pages           │
│  (Dashboard)     │  (Dashboard)   │  (About, Contact, etc.) │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│              ROUTING & AUTHENTICATION                        │
├─────────────────────────────────────────────────────────────┤
│ index.php (Router) → bootstrap.php → Session Validation     │
│                                                              │
│ User Type Detection:                                        │
│  • $_SESSION['user_type'] == 'student' → Student Flow      │
│  • $_SESSION['user_type'] == 'admin' → Admin Flow          │
│  • Not logged in → Home Page                               │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│           CORE APPLICATION MODULES                          │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌─ Authentication Module                                  │
│  │  ├─ Sign-Up.php (Registration validation)              │
│  │  ├─ Log-in (Student).php (Student authentication)      │
│  │  ├─ Log-in (Admin).php (Admin authentication)          │
│  │  └─ logout.php (Session termination)                   │
│  │                                                         │
│  ┌─ Student Module                                        │
│  │  ├─ Student_Dashboard.php (Profile & overview)         │
│  │  ├─ edit_profile.php (Profile updates)                 │
│  │  ├─ my_feedback.php (Submitted feedback)               │
│  │  ├─ my_messages.php (Contact submissions)              │
│  │  └─ view_contact.php (Message detail)                  │
│  │                                                         │
│  ┌─ Feedback & Communication Module                       │
│  │  ├─ feedback_form.php (Feedback submission)            │
│  │  ├─ contact_form.php (Contact inquiry)                 │
│  │  ├─ view_feedback.php (Admin feedback list)            │
│  │  └─ admin_contacts.php (Admin message list)            │
│  │                                                         │
│  ┌─ Resource Management Module                            │
│  │  ├─ manage_resources.php (Upload & manage)             │
│  │  ├─ download_resource.php (Secure downloads)           │
│  │  ├─ delete_file.php (Resource deletion)                │
│  │  └─ update_file.php (Resource updates)                 │
│  │                                                         │
│  ┌─ Admin Module                                          │
│  │  ├─ Admin_Dashboard.php (Overview & analytics)         │
│  │  ├─ manage_users.php (User management)                 │
│  │  ├─ manage_appointments.php (Appointment control)      │
│  │  ├─ admin_settings.php (System configuration)          │
│  │  └─ admin_settings_api.php (Settings API)              │
│  │                                                         │
│  └─ Assessment & Career Paths Module                      │
│     ├─ Test.html (Assessment page with Google Forms)      │
│     └─ Career paths with downloadable PDFs                │
│                                                              │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│         HELPER FUNCTIONS & UTILITIES LAYER                  │
├─────────────────────────────────────────────────────────────┤
│ app/helpers/functions.php (25+ reusable functions)          │
│  ├─ Authentication: is_logged_in(), is_admin(), is_student()│
│  ├─ Security: generate_csrf_token(), sanitize_output()      │
│  ├─ Utilities: redirect(), format_date(), validate_password()│
│  ├─ AJAX: is_ajax(), send_json()                            │
│  └─ Logging: log_activity()                                 │
│                                                              │
│ bootstrap.php (Central initialization)                      │
│  ├─ Constant definitions (APP_ROOT, PUBLIC_DIR, etc.)      │
│  ├─ Include database connection                             │
│  └─ Include helper functions                                │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│           DATABASE ABSTRACTION LAYER                        │
├─────────────────────────────────────────────────────────────┤
│ app/config/db_connection.php                                │
│  ├─ MySQLi connection initialization                        │
│  ├─ Charset: UTF-8 (utf8mb4)                                │
│  ├─ Error handling & reporting                              │
│  └─ Prepared statement support                              │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│          DATABASE LAYER (MariaDB 10.4.32)                   │
├─────────────────────────────────────────────────────────────┤
│ Tables:                                                      │
│  ├─ admin_users (Administrator accounts)                   │
│  ├─ users (Student accounts & profiles)                    │
│  ├─ counselors (Counselor information)                     │
│  ├─ appointments (Booking records)                         │
│  ├─ resources (Career documents & guides)                  │
│  ├─ feedback (Ratings & user feedback)                     │
│  └─ contact_messages (Inquiry submissions)                 │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow Example (User Feedback Submission)
```
1. User fills feedback form (5-star rating + message)
2. Form submits to feedback_form.php via POST
3. PHP validates input, sanitizes data
4. Prepared statement prevents SQL injection
5. Data inserted into 'feedback' table
6. Success message returned to user
7. Admin notified via dashboard
8. Admin can view/respond via view_feedback.php
```

---

## 📦 Complete Project Structure

```
Career-Counseling-Guide-Portal/
│
├── 📄 Core Configuration
│   ├── bootstrap.php                 # Central initialization & constants
│   ├── index.php                     # Main router/entry point
│   └── .gitignore                    # Git ignore patterns
│
├── 📁 app/                           # Application code
│   ├── config/
│   │   └── db_connection.php         # Database connection setup
│   └── helpers/
│       └── functions.php             # 25+ utility functions
│
├── 📁 database/                      # Database files
│   ├── schema/
│   │   ├── Query.sql                 # Main database schema
│   │   └── migrate_contact_system.sql# Contact system schema
│   └── migrations/                   # Database migration scripts
│       ├── setup_database.php
│       ├── migrate_database.php
│       ├── add_is_read_column.php
│       ├── ensure_is_read_column.php
│       ├── migrate_add_status.php
│       └── migrate_admin_settings.php
│
├── 🎨 Frontend - Public Pages
│   ├── index.html                    # Home landing page
│   ├── About us.html                 # About the portal
│   ├── Contact.html                  # Contact page
│   ├── Feedback.html                 # Feedback page
│   ├── resources.html                # Resources listing
│   ├── Student.html                  # Student info page
│   ├── Admin.html                    # Admin info page
│   └── Log-in.html                   # Login selection page
│
├── 🔐 Authentication Pages
│   ├── Sign-Up.html / Sign-Up.php    # Student registration
│   ├── Log-in (Student).html/.php    # Student login
│   └── Log-in (Admin).html/.php      # Admin login
│
├── 👤 Student Dashboard Pages
│   ├── Student_Dashboard.php         # Main student dashboard
│   ├── edit_profile.php              # Profile editing
│   ├── my_feedback.php               # My submitted feedback
│   ├── my_messages.php               # My contact submissions
│   └── view_contact.php              # Message details
│
├── 📋 Feedback & Communication
│   ├── feedback_form.php             # Feedback form handler
│   ├── Feedback.html                 # Feedback page
│   ├── contact_form.php              # Contact form handler
│   ├── Contact.html                  # Contact page
│   └── reply_contact.php             # Reply handler
│
├── 📚 Resource Management
│   ├── manage_resources.php          # Resource management interface
│   ├── download_resource.php         # Secure download handler
│   ├── delete_file.php               # File deletion
│   ├── update_file.php               # File update
│   └── resources.html                # Public resources page
│
├── 👨‍💼 Admin Dashboard Pages
│   ├── Admin_Dashboard.php           # Admin main dashboard
│   ├── manage_users.php              # User management
│   ├── manage_appointments.php       # Appointment management
│   ├── admin_settings.php            # Settings interface
│   ├── admin_settings_api.php        # Settings API
│   ├── admin_contacts.php            # Message management
│   ├── view_feedback.php             # Feedback management
│   └── get_notification_counts.php   # Notification API
│
├── 🎓 Career Assessments & Resources
│   ├── Test.html                     # Assessments & Career Paths page
│   ├── Explore Universities.pdf      # University guide
│   ├── Courses.pdf                   # Course selection guide
│   ├── Insights.pdf                  # Industry insights
│   ├── internships.pdf               # Internship guide
│   ├── Prep.pdf                      # Exam preparation
│   ├── IT.pdf                        # IT career paths (embedded)
│   ├── SE.pdf                        # Software engineering paths (embedded)
│   └── broch.pdf                     # Career pathway overview (embedded)
│
├── 📊 Display Pages
│   ├── Display_Contact.html          # Contact message display
│   └── Display_Feedback.html         # Feedback display
│
├── 🔌 Utilities & Handlers
│   ├── logout.php                    # Session logout
│   ├── mark_contact_as_read.php      # Read status updater
│   └── get_notification_counts.php   # Notification counter
│
├── 📁 public/
│   └── resources/                    # Resource files directory
│       └── *.pdf                     # Career & learning resources
│
├── 📁 docs/                          # Documentation
│   ├── README.md                     # Documentation index
│   ├── PROJECT_STRUCTURE.md          # Detailed structure
│   └── QUICK_REFERENCE.md            # Quick reference guide
│
├── 🖼️ Media Assets
│   ├── shikshalogo.jpg               # Application logo
│   ├── student.jpg                   # Student image
│   ├── student_avatar.jpg            # Student avatar
│   └── admin_avatar.jpg              # Admin avatar
│
└── 📄 Documentation
    ├── README.md                     # This file
    ├── SETUP_GUIDE.md                # Detailed setup
    └── GETTING_STARTED.md            # Quick start guide
```

---

## 🚀 Quick Start Guide

### Prerequisites
- Windows, macOS, or Linux OS
- XAMPP / WAMP / LAMP installed
- Git installed (for cloning)
- Modern web browser (Chrome, Firefox, Safari, Edge)

### Step 1: Clone the Repository
```bash
git clone https://github.com/Muneebniaz111/Career-Counseling-Guide-Portal.git
cd Career-Counseling-Guide-Portal
```

### Step 2: Place Project in Web Root
```bash
# For XAMPP (Windows)
Copy project to: C:\xampp\htdocs\Career-Counseling-Guide-Portal

# For XAMPP (Mac/Linux)
Copy project to: /Applications/XAMPP/xamppfiles/htdocs/Career-Counseling-Guide-Portal

# For WAMP (Windows)
Copy project to: C:\wamp\www\Career-Counseling-Guide-Portal
```

### Step 3: Start Services
1. Open XAMPP/WAMP Control Panel
2. Click **Start** for Apache
3. Click **Start** for MySQL

### Step 4: Import Database
```
1. Open http://localhost/phpmyadmin/
2. Create new database named: career_counseling
3. Select the database
4. Click "Import" tab
5. Choose database/schema/Query.sql
6. Click "Import"
```

### Step 5: Configure Database (if needed)
Edit `app/config/db_connection.php`:
```php
$server = "localhost";       // Database host
$username = "root";          // MySQL username
$password = "";              // MySQL password (usually empty for XAMPP)
$database = "career_counseling";
```

### Step 6: Access the Application
```
Home: http://localhost/Career-Counseling-Guide-Portal/
Student Login: http://localhost/Career-Counseling-Guide-Portal/Log-in%20(Student).php
Admin Login: http://localhost/Career-Counseling-Guide-Portal/Log-in%20(Admin).php
```

---

## 🔑 Default Credentials

### Test Student Account
| Field | Value |
|-------|-------|
| Email | student@test.com |
| Password | Test123! |

### Test Admin Accounts
| Email | Password |
|-------|----------|
| muneeb122@gmail.com | password123 |
| muzzamil012@gmail.com | password123 |
| zaeem028@gmail.com | password123 |
| mohsin005@gmail.com | password123 |

⚠️ **IMPORTANT**: Change all passwords immediately after first login in production environments!

---

## 📊 Database Schema

### Main Tables

**admin_users** - Administrator accounts
```sql
CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**users** - Student accounts
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    gender VARCHAR(50),
    email VARCHAR(255) UNIQUE NOT NULL,
    username VARCHAR(255) UNIQUE,
    password VARCHAR(255) NOT NULL,
    contact VARCHAR(20),
    city VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**appointments** - Booking records
```sql
CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    counselor_id INT,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status VARCHAR(50) DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id)
);
```

**feedback** - User ratings & feedback
```sql
CREATE TABLE feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    rating INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**resources** - Career documents
```sql
CREATE TABLE resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**contact_messages** - Contact inquiries
```sql
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    phone VARCHAR(20),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🔐 Security Implementation

### Authentication & Authorization
✅ **Password Hashing** - Uses BCrypt (`password_hash()` / `password_verify()`)  
✅ **Session Management** - Server-side session storage with secure cookies  
✅ **Role-Based Access** - Different permissions for student/admin roles  
✅ **User Type Checking** - All protected pages verify `$_SESSION['user_type']`  

### Data Protection
✅ **SQL Injection Prevention** - MySQLi prepared statements on all queries  
✅ **XSS Prevention** - `htmlspecialchars()` for all user output  
✅ **Input Validation** - Server-side validation on all forms  
✅ **Input Sanitization** - `trim()`, `stripslashes()` on all inputs  

### Files & Downloads
✅ **Secure Downloads** - `download_resource.php` validates user authentication  
✅ **Path Traversal Prevention** - Uses `basename()` to prevent directory traversal  
✅ **File Type Checking** - Validates file extensions before processing  

---

## 🛠️ Maintenance & Administration

### Add New Admin User
```bash
1. Open http://localhost/phpmyadmin/
2. Select career_counseling database
3. Click admin_users table
4. Click "Insert"
5. Enter:
   - email: your_email@example.com
   - password: [hash using PHP or online tool]
   - name: Your Name
6. Click "Go"
```

Or use PHP to hash password:
```php
<?php
echo password_hash('your_password', PASSWORD_DEFAULT);
?>
```

### Backup Database
```bash
mysqldump -u root -p career_counseling > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Restore Database
```bash
mysql -u root -p career_counseling < backup_YYYYMMDD_HHMMSS.sql
```

### Clear User Sessions
```sql
-- Delete all active sessions (forces logout)
TRUNCATE TABLE ci_sessions;  -- if using CodeIgniter sessions

-- Or simply restart Apache/MySQL
```

---

## ⚡ Performance Tips

1. **Enable Database Indexing** - Indexes on frequently queried columns
2. **Use Query Caching** - MySQL query cache for repeated queries
3. **Optimize Images** - Compress images before uploading
4. **Minify CSS/JS** - Reduce file sizes for faster loading
5. **Enable Gzip** - Compress HTTP responses
6. **Use CDN** - Bootstrap, jQuery, Font Awesome via CDN
7. **Lazy Loading** - Load resources on demand

---

## 🐛 Troubleshooting

### Issue: "Database Connection Failed"
**Solution:**
```php
// Check app/config/db_connection.php credentials
// Ensure MySQL is running
// Verify database name is: career_counseling
```

### Issue: "Blank Page / White Screen"
**Solution:**
```php
// Enable error reporting in bootstrap.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Check PHP error logs
```

### Issue: "Login Not Working"
**Solution:**
1. Clear browser cookies and cache
2. Check if session.save_path is writable
3. Verify database has admin_users/users tables
4. Check password is hashed correctly

### Issue: "PDF Files Not Downloading"
**Solution:**
```php
// Check file permissions
// Ensure files exist in public/resources/
// Verify file paths in HTML match actual filenames
// Check for special characters in filenames
```

### Issue: "404 Not Found Errors"
**Solution:**
1. Verify project path in browser matches file location
2. Check .htaccess file exists (if using URL rewriting)
3. Clear browser cache (Ctrl+Shift+Delete)
4. Check spelling of filenames (case-sensitive on Linux)

---

## 📈 Future Roadmap

### Phase 2 Features (Q2 2026)
- [ ] Email notifications for appointments
- [ ] SMS integration for alerts
- [ ] Video conferencing for counseling sessions
- [ ] Mobile-responsive admin panel improvements
- [ ] Advanced reporting & analytics dashboard

### Phase 3 Features (Q3 2026)
- [ ] Two-factor authentication (2FA)
- [ ] API development (REST API)
- [ ] Mobile app (iOS/Android)
- [ ] Real-time chat/messaging
- [ ] Payment gateway integration

### Phase 4 Features (Q4 2026)
- [ ] AI-powered career recommendations
- [ ] Machine learning for assessment analysis
- [ ] Advanced data visualization
- [ ] Multi-language support
- [ ] Accessibility improvements (WCAG 2.1)

---

## 🤝 Contributing

We welcome contributions! To contribute:

1. **Fork** the repository on GitHub
2. **Create** a feature branch: `git checkout -b feature/YourFeature`
3. **Make** your changes with clear, descriptive commits
4. **Push** to your fork: `git push origin feature/YourFeature`
5. **Submit** a Pull Request with detailed description

### Coding Standards
- Use PSR-12 PHP coding standards
- Add comments for complex logic
- Test all changes before submitting
- Update documentation as needed

---

## 📄 License

This project is licensed under the **MIT License** - see LICENSE file for details.

You are free to use this project for:
- ✅ Personal projects
- ✅ Educational purposes
- ✅ Commercial applications
- ✅ Modification and distribution

---

## 👥 Team & Contributors

| Name | Role | Email |
|------|------|-------|
| Muneeb Niaz | Lead Developer | muneeb122@gmail.com |
| Muzzamil | Developer | muzzamil012@gmail.com |
| Zaeem | Developer | zaeem028@gmail.com |
| Mohsin | Developer | mohsin005@gmail.com |

---

## 📞 Support & Contact

### Report Issues
- **GitHub Issues**: https://github.com/Muneebniaz111/Career-Counseling-Guide-Portal/issues
- **Email**: muneeb122@gmail.com
- **Contact Form**: http://localhost/Career-Counseling-Guide-Portal/Contact.html

### Get Help
1. Check [SETUP_GUIDE.md](SETUP_GUIDE.md) for detailed setup instructions
2. Review [GETTING_STARTED.md](GETTING_STARTED.md) for quick start
3. Read [docs/QUICK_REFERENCE.md](docs/QUICK_REFERENCE.md) for common tasks
4. Search existing GitHub issues for solutions
5. Create a new issue with detailed information

### Documentation
- 📖 [Setup Guide](SETUP_GUIDE.md) - Detailed installation steps
- 🚀 [Getting Started](GETTING_STARTED.md) - Quick start guide
- 📚 [Project Documentation](docs/) - Additional resources

---

## 🔗 Links & Resources

- **GitHub Repository**: https://github.com/Muneebniaz111/Career-Counseling-Guide-Portal
- **XAMPP Download**: https://www.apachefriends.org/
- **PHP Documentation**: https://www.php.net/docs.php
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **Bootstrap Documentation**: https://getbootstrap.com/docs/4.6/

---

## 📊 Project Statistics

| Metric | Value |
|--------|-------|
| **Total Files** | 67+ |
| **Database Tables** | 7 |
| **PHP Files** | 26+ |
| **HTML Files** | 13+ |
| **CSS Framework** | Bootstrap 4.6.0 |
| **Lines of Code** | 15,000+ |
| **Security Features** | 8+ |
| **Test Accounts** | 5+ |

---

## ✅ Current Status

### ✓ Completed Features
- ✅ User authentication system
- ✅ Student & admin dashboards
- ✅ Profile management
- ✅ Feedback system with ratings
- ✅ Contact form & messaging
- ✅ Resource management
- ✅ Appointment booking UI
- ✅ Database integration
- ✅ Security implementation
- ✅ Career assessments with Google Forms
- ✅ PDF download functionality
- ✅ Admin notification system

### 🚀 Ready for Production
✅ All core features operational  
✅ Security best practices implemented  
✅ Database properly structured  
✅ Error handling in place  
✅ Comprehensive documentation  

---

## 📝 Version History

### v2.0.0 (Current)
- ✨ Modernized Test.html with project styling
- 📥 Implemented download-only PDF functionality
- 🔧 Fixed bootstrap.php path calculation
- 🎨 Standardized color scheme (#800080, #4B0082)
- 📚 Integrated Bootstrap 4.6.0 and Font Awesome 6.0.0
- 📝 Comprehensive README documentation

### v1.0.0 (Initial Release)
- Core authentication system
- Student & admin dashboards
- Basic resource management
- Feedback & contact forms
- Database integration

---

## 🙏 Acknowledgments

- **Bootstrap** for responsive UI components
- **jQuery** for dynamic functionality
- **Font Awesome** for beautiful icons
- **MySQL/MariaDB** for reliable data storage
- **Apache** for web server infrastructure

---

## 📅 Last Updated

**Date**: April 18, 2026  
**Version**: 2.0.0  
**Status**: ✅ Production Ready  

---

**Made with ❤️ by the Career Counseling Portal Team**

For questions, feedback, or support, please reach out to: **muneeb122@gmail.com**

 Connect to database and implement login functionality

 Develop counselor dashboard and appointment system

 Improve UI design and responsiveness

 Add user role permissions (admin, student, counselor)

Contributing
Pull requests are welcome. Please open an issue first to discuss proposed changes or bug reports.
