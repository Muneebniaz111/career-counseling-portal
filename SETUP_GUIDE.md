# 🎯 Complete Setup Guide - Career Counseling Portal

## Step 1: Install XAMPP

### Windows
1. Download XAMPP from: https://www.apachefriends.org/index.html
2. Select the version with **PHP 7.4 or higher**
3. Run the installer
4. Choose installation path (default: C:\xampp)
5. Install Apache, MySQL, and PHP
6. Click "Finish"

### Mac
1. Download XAMPP for Mac
2. Drag to Applications folder
3. Open XAMPP and click "Start" for Apache and MySQL

### Linux
```bash
sudo apt-get install xampp-linux-x64
sudo /opt/lampp/start
```

## Step 2: Start XAMPP Services

### Windows
1. Open XAMPP Control Panel
2. Click "Start" next to Apache
3. Click "Start" next to MySQL
4. Both should show "Running" in green

### Mac/Linux
1. Click "Start All" button
2. Wait for all services to become green

## Step 3: Download & Place Project

1. **Download Project**
   - Download or clone: https://github.com/Muneebniaz111/Career-Counseling-Guide-Portal.git
   - Extract the ZIP file

2. **Move to XAMPP**
   - Copy the entire project folder to:
     - **Windows**: `C:\xampp\htdocs\Career-Counseling-Guide-Portal`
     - **Mac/Linux**: `/applications/xampp/htdocs/Career-Counseling-Guide-Portal`

## Step 4: Create Database

1. **Open PhpMyAdmin**
   - Go to: http://localhost/phpmyadmin/
   - Default username: `root` (no password)

2. **Import Database**
   - Click on "Import" tab
   - Click "Choose File"
   - Select `Query.sql` from the project folder
   - Click "Import"
   - ✅ Database created successfully!

   OR **Manual Creation**:
   - Right-click "New" in left panel
   - Create database named: `career_counseling`
   - Click "Create"

## Step 5: Verify Configuration

1. Open `db_connection.php` in any text editor
2. Check these settings match your XAMPP installation:
   ```php
   $server = "localhost";
   $username = "root";
   $password = "";   // Empty password is default
   $database = "career_counseling";
   ```
3. Save if you made changes

## Step 6: Access the Application

Now you can access the application in your browser:

### Main Pages
| Page | URL | Purpose |
|------|-----|---------|
| Home | http://localhost/Career-Counseling-Guide-Portal/index.html | Landing page |
| Student Login | http://localhost/Career-Counseling-Guide-Portal/Log-in (Student).php | Student authentication |
| Admin Login | http://localhost/Career-Counseling-Guide-Portal/Log-in (Admin).php | Admin authentication |
| Sign Up | http://localhost/Career-Counseling-Guide-Portal/Sign-Up.php | Register new account |

## Step 7: Test with Sample Accounts

### Admin Account (Test Login)
- **Email**: muneeb122@gmail.com
- **Password**: password123
- **Access**: http://localhost/Career-Counseling-Guide-Portal/Log-in (Admin).php

### Create Student Account
1. Go to: http://localhost/Career-Counseling-Guide-Portal/Sign-Up.php
2. Fill in the registration form
3. Click "Sign Up"
4. Login with your new account

## Troubleshooting

### Issue: "Connection failed" error

**Solution**:
1. Make sure MySQL is running in XAMPP Control Panel
2. Check `db_connection.php` has correct database name
3. Verify database exists in PhpMyAdmin

### Issue: "Page not found" error

**Solution**:
1. Check if Apache is running
2. Verify file locations are correct
3. Use correct URL: `http://localhost/Career-Counseling-Guide-Portal/filename.php`

### Issue: "404 Not Found"

**Solution**:
1. Ensure project folder is in `htdocs` directory
2. Use exact folder name in URL
3. Restart Apache

### Issue: Blank page

**Solution**:
1. Check browser console for errors (F12)
2. Verify PHP is enabled
3. Check `display_errors` in XAMPP PHP settings

### Issue: Can't upload files

**Solution**:
1. Create `uploads` folder in project directory:
   ```
   Career-Counseling-Guide-Portal/uploads/
   ```
2. Give it write permissions (777)
3. Check file size limits in php.ini

## Port Usage

If ports are in use, change them in XAMPP:

### Apache Port
1. Tools > Port Check
2. If 80 is occupied, Settings > Configure > Apache
3. Change port to 81: `Listen 81`
4. Access: `http://localhost:81/`

### MySQL Port
1. If 3306 is occupied, MySQL Config
2. Change port to 3307
3. Update `db_connection.php`:
   ```php
   $server = "localhost:3307";
   ```

## File Permissions (Linux/Mac)

If getting permission denied errors:

```bash
# Give write permissions to uploads folder
chmod 777 uploads/

# Give write permissions to project folder
chmod 755 Career-Counseling-Guide-Portal/
```

## Database Backup & Restore

### Backup your database
```bash
# Navigate to XAMPP folder
cd C:\xampp\mysql\bin

# Create backup
mysqldump -u root career_counseling > career_backup.sql
```

### Restore database
```bash
mysql -u root career_counseling < career_backup.sql
```

## Project Structure

```
Career-Counseling-Guide-Portal/
├── 📄 index.html                 # Landing page
├── 📄 About us.html              # About page
├── 📄 Contact.html               # Contact page
├── 📄 Feedback.html              # Feedback page
├── 📄 resources.html             # Resources page
│
├── 🔐 AUTHENTICATION
├── 📄 Sign-Up.php                # Student registration
├── 📄 Log-in (Student).php       # Student login
├── 📄 Log-in (Admin).php         # Admin login
├── 📄 logout.php                 # Logout handler
│
├── 👥 STUDENT PAGES
├── 📄 Student_Dashboard.php      # Student main dashboard
├── 📄 edit_profile.php           # Edit profile
│
├── ⚙️ ADMIN PAGES
├── 📄 Admin_Dashboard.php        # Admin main dashboard
├── 📄 manage_users.php           # User management
├── 📄 manage_appointments.php    # Appointment management
├── 📄 manage_resources.php       # Resource management
├── 📄 view_feedback.php          # View feedback
├── 📄 view_contact.php           # View messages
├── 📄 admin_settings.php         # Settings
│
├── 📝 FORMS
├── 📄 contact_form.php           # Contact form handler
├── 📄 feedback_form.php          # Feedback form handler
├── 📄 delete_file.php            # Delete resources
├── 📄 update_file.php            # Update resources
│
├── 🗄️ DATABASE
├── 📄 db_connection.php          # Database connection
├── 📄 Query.sql                  # Database schema
│
├── 📁 uploads/                   # File uploads folder
└── 📄 README.md                  # Project documentation
```

## Next Steps

After successful setup:

1. **Explore Admin Dashboard**
   - Login as admin
   - Check user management
   - View statistics

2. **Create Test Student Account**
   - Click "Sign Up"
   - Fill registration form
   - Login to student dashboard

3. **Customize Settings**
   - Edit admin_settings.php
   - Add your site information
   - Configure email addresses

4. **Add Resources**
   - Upload PDFs in admin dashboard
   - Create career guides
   - Manage appointments

## Getting Help

### Common Resources
- PHP Documentation: https://php.net/manual
- MySQL Documentation: https://dev.mysql.com/doc
- Bootstrap Docs: https://getbootstrap.com/docs

### Community Support
- GitHub Issues: [Project Issues](https://github.com/Muneebniaz111/Career-Counseling-Guide-Portal/issues)
- Stack Overflow: Tag questions with `php`, `mysql`, `xampp`

## ✨ Congratulations!

Your Career Counseling Portal is now ready to use! 🎉

For questions or issues, please check the README.md file or open an issue on GitHub.
