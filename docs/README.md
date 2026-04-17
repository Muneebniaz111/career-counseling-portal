# Career & Counseling Portal ✨

A comprehensive web-based platform designed to assist students and professionals in exploring career opportunities, receiving guidance, and accessing counseling resources.

## 🚀 Features

### ✅ Already Implemented
- **User Authentication**
  - Student Registration & Login (Secure password hashing)
  - Admin Dashboard with login
  
- **Student Dashboard**
  - View profile information
  - Book appointments (Coming soon)
  - View appointment history
  - Edit profile details
  - Access to resources

- **Admin Dashboard**
  - User management (view, delete users)
  - Appointment management
  - Resource management
  - Feedback & rating system
  - Contact message management
  - System settings

- **Feedback & Communication**
  - Feedback form with star ratings
  - Contact form
  - View all feedback and messages (admin)

- **Database Integration**
  - MySQL database with proper schema
  - Secure prepared statements (SQL injection protection)
  - Proper data validation

## 🔧 Requirements

- **PHP** >= 7.4
- **MySQL/MariaDB** 5.7 or higher
- **Apache/Nginx** Web Server
- **XAMPP**, **WAMP**, or **LAMP** stack (for local development)
- Modern web browser (Chrome, Firefox, Safari, Edge)

## 📋 Setup Instructions

### 1. Initial Setup
```bash
# Clone the repository
git clone https://github.com/Muneebniaz111/Career-Counseling-Guide-Portal.git

# Navigate to project folder
cd Career-Counseling-Guide-Portal
```

### 2. XAMPP/WAMP Configuration
1. **Start Apache & MySQL**: Open XAMPP/WAMP control panel and start both services
2. **Place Project**: Copy the project folder to `C:\xampp\htdocs\` (XAMPP) or `C:\wamp\www\` (WAMP)
3. **Access Admin**: Open http://localhost/phpmyadmin/ in your browser

### 3. Database Setup
1. **Open PhpMyAdmin**: http://localhost/phpmyadmin/
2. **Import Database**: 
   - Click on "Import" tab
   - Choose `Query.sql` file from the project folder
   - Click "Import"
3. **Database Created**: The `career_counseling` database is now ready

### 4. Configuration
1. Edit `db_connection.php` if your database credentials differ:
   ```php
   $server = "localhost";
   $username = "root";      // Your MySQL username
   $password = "";          // Your MySQL password
   $database = "career_counseling";
   ```

### 5. Access the Application
- **Home Page**: http://localhost/Career-Counseling-Guide-Portal/index.html
- **Student Login**: http://localhost/Career-Counseling-Guide-Portal/Log-in (Student).php
- **Admin Login**: http://localhost/Career-Counseling-Guide-Portal/Log-in (Admin).php

## 👥 Default Admin Credentials

All default admin passwords are: `password123`

| Email | Password |
|-------|----------|
| muneeb122@gmail.com | password123 |
| muzzamil012@gmail.com | password123 |
| zaeem028@gmail.com | password123 |
| mohsin005@gmail.com | password123 |

**⚠️ Change these credentials immediately in production!**

## 📁 Project Structure

```
Career-Counseling-Guide-Portal/
├── index.html                    # Home page
├── Sign-Up.php                   # Student registration
├── Log-in (Student).php          # Student login
├── Log-in (Admin).php            # Admin login
├── Student_Dashboard.php         # Student dashboard
├── Admin_Dashboard.php           # Admin dashboard
├── edit_profile.php              # Edit student profile
├── manage_users.php              # Manage users (admin)
├── manage_appointments.php       # Manage appointments (admin)
├── manage_resources.php          # Manage resources (admin)
├── view_feedback.php             # View feedback (admin)
├── view_contact.php              # View messages (admin)
├── admin_settings.php            # Admin settings
├── contact_form.php              # Contact form
├── feedback_form.php             # Feedback form
├── logout.php                    # Logout handler
├── db_connection.php             # Database connection
├── delete_file.php               # Delete resources
├── update_file.php               # Update resources
├── Query.sql                     # Database schema
├── About us.html                 # About page
├── Contact.html                  # Contact page
├── Feedback.html                 # Feedback page
├── resources.html                # Resources page
└── [Images & PDFs]              # Static assets
```

## 🗄️ Database Schema

### Tables Created

1. **admin_users** - Administrator accounts
   - id, email, password, name, created_at

2. **users** - Student accounts
   - id, name, gender, email, username, password, contact, city, created_at

3. **counselors** - Counselor profiles
   - id, name, email, password, specialization, phone, available_hours, created_at

4. **appointments** - Booking appointments
   - id, student_id, counselor_id, appointment_date, appointment_time, status, notes, created_at

5. **resources** - Career resources
   - id, title, category, description, file_path, created_by, created_at

6. **feedback** - User ratings & feedback
   - id, name, email, subject, message, rating, created_at

7. **contact_messages** - Contact form submissions
   - id, name, email, subject, message, phone, created_at

## 🔐 Security Features

- ✅ Password hashing using PHP's `password_hash()` function
- ✅ SQL injection prevention using prepared statements
- ✅ Session-based authentication
- ✅ Input validation and sanitization
- ✅ XSS protection with `htmlspecialchars()`
- ✅ CSRF protection (ready for implementation)

## 📝 Features in Detail

### Student Features
1. **Registration**: Create a new account with validation
2. **Login**: Secure login with session management
3. **Profile Management**: Update personal information
4. **Dashboard**: View appointments and profile summary
5. **Appointments**: Book counseling appointments (UI ready)
6. **Feedback**: Submit feedback with star ratings
7. **Contact**: Send inquiries and messages

### Admin Features
1. **User Management**: View, delete, manage student accounts
2. **Appointment Management**: View all appointments and status
3. **Resource Management**: Upload and manage career resources
4. **Feedback Monitoring**: View and respond to user feedback
5. **Message Management**: View contact form submissions
6. **Dashboard Analytics**: View system statistics

## 🛠️ Maintenance

### Add Admin User
1. Open http://localhost/phpmyadmin/
2. Go to `admin_users` table
3. Insert new record with:
   - Email: your_email@example.com
   - Password: `password_hash('your_password', PASSWORD_DEFAULT)` (Use PHP to hash)
   - Name: Your Name

### Backup Database
```bash
# Using XAMPP's mysqldump
mysqldump -u root -p career_counseling > backup.sql
```

### Restore Database
```bash
mysql -u root -p career_counseling < backup.sql
```

## 🚧 Future Enhancements

- [ ] Live chat/messaging system
- [ ] Email notifications
- [ ] Video counseling integration
- [ ] Mobile app
- [ ] Advanced analytics
- [ ] Payment integration for premium features
- [ ] API development (REST/GraphQL)
- [ ] Two-factor authentication
- [ ] User role permissions (student, counselor, admin)
- [ ] Real-time appointment scheduling

## 📞 Support & Issues

For issues, bugs, or feature requests:
1. Open an issue on GitHub
2. Provide detailed description
3. Include screenshots if possible

## 📄 License

This project is open source and available under the MIT License.

## ✨ Contributors

- Muneeb Niaz
- Muzzamil
- Zaeem
- Mohsin

## 📞 Contact Information

**Email**: muneeb122@gmail.com
**Website**: [Coming Soon]
**GitHub**: https://github.com/Muneebniaz111/Career-Counseling-Guide-Portal

---

**Last Updated**: April 11, 2026
**Version**: 1.0.0


Access the project at http://localhost/career-counseling-portal/.

🚧 Warning: The site may not function as expected until PHP issues are resolved.

To-Do
 Fix PHP backend errors

 Connect to database and implement login functionality

 Develop counselor dashboard and appointment system

 Improve UI design and responsiveness

 Add user role permissions (admin, student, counselor)

Contributing
Pull requests are welcome. Please open an issue first to discuss proposed changes or bug reports.
