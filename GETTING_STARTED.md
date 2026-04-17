# 🚀 Career Counseling Guide Portal - Setup & Access Guide

## ✅ System Status

Your project is now **fully operational** and ready to use!

### ✓ Verified Components
- ✅ **Apache Web Server**: Running (httpd processes active)
- ✅ **MySQL Database**: Running (mysqld process active)  
- ✅ **PHP**: Available at `C:\xampp\php\php.exe`
- ✅ **Project Location**: `C:\xampp\htdocs\Career-Counseling-Guide-Portal-master`
- ✅ **Bootstrap System**: Configured and ready
- ✅ **Database**: `career_counseling` created with all tables
- ✅ **Test Accounts**: Created for immediate testing

---

## 🔐 Test User Accounts

Two test accounts have been created for you:

### Student Account
```
Email: student@test.com
Password: Test123!
Type: Student
```

### Admin Account
```
Email: admin@test.com
Password: Test123!
Type: Admin
```

---

## 🌐 How to Access the Project

### Option 1: Home Page (Recommended)
**URL**: `http://localhost/Career-Counseling-Guide-Portal-master/`

This will:
- Show the home page if not logged in
- Redirect to dashboard if already logged in

### Option 2: Direct Access by Role

**Student Login**:
```
http://localhost/Career-Counseling-Guide-Portal-master/public/html/Log-in%20(Student).html
```

**Admin Login**:
```
http://localhost/Career-Counseling-Guide-Portal-master/public/html/Log-in%20(Admin).html
```

**Resources/Downloads**:
```
http://localhost/Career-Counseling-Guide-Portal-master/public/html/resources.html
```

**Home Page**:
```
http://localhost/Career-Counseling-Guide-Portal-master/public/html/index.html
```

---

## 📋 Step-by-Step: First Run Guide

### Step 1: Open Home Page
1. Open your web browser (Chrome, Firefox, Edge, Safari)
2. Go to: `http://localhost/Career-Counseling-Guide-Portal-master/`
3. You should see the Career Portal home page

### Step 2: Login as Student
1. Click "Login" or go to Student Login page
2. Enter credentials:
   - **Email**: `student@test.com`
   - **Password**: `Test123!`
3. Click "Login"
4. You should see the Student Dashboard

### Step 3: Explore Student Features
From the Student Dashboard, you can:
- ✓ View your profile
- ✓ Access resources and download PDFs
- ✓ Contact admin
- ✓ Submit feedback
- ✓ View your messages
- ✓ View your feedback history

### Step 4: Login as Admin
1. Logout from student account
2. Go to Admin Login page
3. Enter credentials:
   - **Email**: `admin@test.com`
   - **Password**: `Test123!`
4. Click "Login"
5. You should see the Admin Dashboard

### Step 5: Explore Admin Features
From the Admin Dashboard, you can:
- ✓ View and manage all contacts
- ✓ View and respond to feedback
- ✓ Manage user accounts
- ✓ Manage resources
- ✓ View system statistics

### Step 6: Test Core Features

**Test PDF Downloads**:
1. Go to Resources page
2. Try downloading all 5 PDF resources
3. Files should download without errors

**Test Contact Form**:
1. As a student, go to "Contact Admin"
2. Fill in the form with test data
3. Submit the form
4. Login as admin and verify the contact appears

**Test Feedback Form**:
1. As a student, go to "Share Feedback"
2. Fill in the feedback form
3. Submit the form
4. Login as admin and verify the feedback appears

---

## 🗂️ Project File Structure

```
Career-Counseling-Guide-Portal-master/
├── index.php                        ← Main entry point (you are here)
├── bootstrap.php                    ← Application initialization
│
├── public/
│   ├── html/                        ← All HTML pages
│   │   ├── index.html               (Home page)
│   │   ├── Log-in (Student).html    (Student login)
│   │   ├── Log-in (Admin).html      (Admin login)
│   │   ├── resources.html           (Download resources)
│   │   ├── Contact.html             (Contact form)
│   │   ├── Feedback.html            (Feedback form)
│   │   └── ... (other pages)
│   │
│   ├── php/                         ← All PHP logic
│   │   ├── Student_Dashboard.php
│   │   ├── Admin_Dashboard.php
│   │   ├── Log-in (Student).php
│   │   ├── Log-in (Admin).php
│   │   └── ... (other PHP files)
│   │
│   ├── resources/                   ← PDF files (5 resources)
│   │   ├── Courses.pdf
│   │   ├── Explore Universities.pdf
│   │   ├── Insights.pdf
│   │   ├── internships.pdf
│   │   └── Prep.pdf
│   │
│   └── assets/                      ← Images and icons
│
├── app/
│   └── config/
│       └── db_connection.php        ← Database configuration
│
├── database/
│   └── schema/
│       └── Query.sql                ← Database schema
│
└── docs/                            ← Documentation
    ├── PROJECT_STRUCTURE.md
    ├── QUICK_REFERENCE.md
    └── README.md
```

---

## 🔗 Important URLs

### Core Pages

| Page | URL |
|------|-----|
| **Home** | `http://localhost/.../public/html/index.html` |
| **Student Login** | `http://localhost/.../public/html/Log-in (Student).html` |
| **Admin Login** | `http://localhost/.../public/html/Log-in (Admin).html` |
| **Resources** | `http://localhost/.../public/html/resources.html` |
| **Contact** | `http://localhost/.../public/html/Contact.html` |
| **Feedback** | `http://localhost/.../public/html/Feedback.html` |

### PHP Backend

| Feature | File |
|---------|------|
| **Student Dashboard** | `public/php/Student_Dashboard.php` |
| **Admin Dashboard** | `public/php/Admin_Dashboard.php` |
| **Student Login** | `public/php/Log-in (Student).php` |
| **Admin Login** | `public/php/Log-in (Admin).php` |

---

## 🐛 Troubleshooting

### Issue: Page shows blank or 404 error
**Solution**: 
- Verify XAMPP is running (Apache and MySQL)
- Check URL spelling (URLs are case-sensitive)
- Clear browser cache (Ctrl+Shift+Delete)
- Try different browser

### Issue: Login fails with "Invalid email or password"
**Solution**:
- Verify credentials are correct (check test accounts above)
- Check database is running (MySQL should be active)
- Review browser console for errors (F12 → Console)

### Issue: PDFs won't download
**Solution**:
- Verify PDF files exist in `public/resources/`
- Check file names match exactly (case-sensitive)
- Clear browser cache
- Try different browser or download method

### Issue: Forms don't submit
**Solution**:
- Check all required fields are filled
- Verify database connection is working
- Check browser console for JavaScript errors
- Ensure MySQL is running

### Issue: Database connection error
**Solution**:
- Verify MySQL is running (check XAMPP)
- Check credentials in `app/config/db_connection.php`
- Verify database `career_counseling` exists
- Check database has required tables

---

## 📊 Database Information

**Database Name**: `career_counseling`

**Tables Created**:
- `users` - Student accounts
- `admin_users` - Admin accounts
- `contacts` - Contact form submissions
- `feedback` - Feedback submissions
- `resources` - Available resources
- `appointments` - Appointment bookings
- `counselors` - Counselor profiles
- And more...

---

## ⚙️ System Configuration

**Web Server**: Apache 2.4 (XAMPP)  
**PHP Version**: 7.4+  
**Database**: MySQL/MariaDB  
**Framework**: Bootstrap 4.6.0  
**Frontend**: jQuery 3.6.0  

---

## 💾 Important Files to Know

| File | Purpose |
|------|---------|
| `index.php` | Main entry point (routes to appropriate page) |
| `bootstrap.php` | Application initialization (include in all PHP files) |
| `app/config/db_connection.php` | Database connection and utilities |
| `public/html/index.html` | Home page |
| `public/php/*.php` | All backend logic files |
| `public/resources/*.pdf` | Downloadable resources |
| `database/schema/Query.sql` | Database schema |

---

## 🆘 Need Help?

### Check These Files for More Info:
- `README.md` - Project overview
- `docs/PROJECT_STRUCTURE.md` - Detailed folder structure
- `docs/QUICK_REFERENCE.md` - Developer quick lookup
- `SETUP_GUIDE.md` - Detailed setup instructions

### Common Questions:

**Q: How do I change the database password?**  
A: Edit `app/config/db_connection.php` and update the credentials.

**Q: How do I create a new user account?**  
A: Use the Sign-Up page at `public/html/Sign-Up.html` or add directly to database.

**Q: How do I add new PDF resources?**  
A: Place PDF in `public/resources/` and add link in `public/html/resources.html`.

**Q: How do I backup the database?**  
A: Use phpMyAdmin in XAMPP or MySQL command-line backup tools.

**Q: How do I deploy to a live server?**  
A: See `DEPLOYMENT_TESTING_CHECKLIST.md` for detailed instructions.

---

## ✅ Quick Verification Checklist

Run through these to verify everything is working:

- [ ] XAMPP Apache is running
- [ ] XAMPP MySQL is running
- [ ] Home page loads at `http://localhost/Career-Counseling-Guide-Portal-master/`
- [ ] Student login works with test account
- [ ] Student Dashboard displays correctly
- [ ] Admin login works with test account
- [ ] Admin Dashboard displays correctly
- [ ] PDF downloads work from resources page
- [ ] Contact form submits successfully
- [ ] Feedback form submits successfully
- [ ] Navigation between pages works
- [ ] Logout functionality works

---

## 🎉 You're All Set!

Your Career Counseling Guide Portal is **fully operational** and ready for use!

**Next Steps:**
1. Test all core features using the test accounts
2. Create additional user accounts via Sign-Up page
3. Add your own content and resources
4. Customize styling and branding as needed
5. When ready, deploy to a live server

---

**Setup Completed**: April 18, 2026  
**Status**: ✅ **READY FOR USE**

For detailed information, see the documentation in the `/docs/` folder.
