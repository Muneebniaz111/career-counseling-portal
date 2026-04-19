# 🔐 Login System - Complete Setup Guide

## Problem
Student login (and admin login) not working.

## Root Cause
**The database tables were not created.** The code is correct, but the tables don't exist in MySQL.

---

## ✅ Solution - 3 Simple Steps

### Step 1: Initialize Database
Visit this URL in your browser:
```
http://localhost/Career-Counseling-Guide-Portal-master/setup_complete.php
```

This will automatically:
- ✅ Create database `career_counseling`
- ✅ Create all 7 tables
- ✅ Insert test admin account
- ✅ Insert test student account

**Wait for the page to load completely and show "Setup Complete!"**

### Step 2: Verify Setup
Visit this URL to verify everything is ready:
```
http://localhost/Career-Counseling-Guide-Portal-master/verify_setup.php
```

You should see:
- ✅ All tables listed
- ✅ Admin users listed
- ✅ Student users listed

### Step 3: Login Now!
Now you can login with these credentials:

**Admin Login:**
- URL: http://localhost/Career-Counseling-Guide-Portal-master/Log-in%20(Admin).php
- Email: `muneeb122@gmail.com`
- Password: `password123`

**Student Login:**
- URL: http://localhost/Career-Counseling-Guide-Portal-master/Log-in%20(Student).php
- Email: `student@test.com`
- Password: `Test123!`

---

## 🛠️ What Files Are Ready

✅ **bootstrap.php** - Central initialization  
✅ **app/config/db_connection.php** - Database connection  
✅ **app/helpers/functions.php** - Utility functions  
✅ **Log-in (Student).php** - Student login logic  
✅ **Log-in (Admin).php** - Admin login logic  
✅ **Sign-Up.php** - Student registration  
✅ **Student_Dashboard.php** - Student panel  
✅ **Admin_Dashboard.php** - Admin panel  

---

## ⚡ Quick Troubleshooting

### If setup_complete.php shows error:
1. Make sure Apache is running (green in XAMPP)
2. Make sure MySQL is running (green in XAMPP)
3. Refresh the page and try again

### If login still doesn't work after setup:
1. Visit verify_setup.php to check tables exist
2. Clear browser cache (Ctrl+Shift+Delete)
3. Try logging in with exact credentials

### If database already exists:
- The setup script will NOT overwrite existing data
- It only creates missing tables
- You can safely run it multiple times

---

## 📚 How to Create Student Accounts

After setup, students can create their own accounts:

1. Go to: http://localhost/Career-Counseling-Guide-Portal-master/Sign-Up.php
2. Fill in the registration form:
   - Name
   - Gender
   - Email (must be unique)
   - Username (must be unique)
   - Password
   - Contact
   - City
3. Click "Sign Up"
4. Login with the new credentials

---

## 🔒 Security Notes

- ✅ Passwords are hashed with BCrypt
- ✅ Email must be unique per user
- ✅ Username must be unique per student
- ✅ All inputs are validated
- ✅ Database uses prepared statements

---

## 📋 Credentials Reference

**Test Admin Account:**
- Email: `muneeb122@gmail.com`
- Password: `password123`
- Created by: setup_complete.php

**Test Student Account:**
- Email: `student@test.com`
- Password: `Test123!`
- Created by: setup_complete.php

---

## 🎯 Next Steps After Login

### Admin Can:
- Manage students
- View feedback
- Manage resources
- See contact messages
- Access dashboard

### Students Can:
- View profile
- Take assessments
- Download resources
- Submit feedback
- Contact support

---

## 📞 Need Help?

If you're still having issues:

1. **Check MySQL is running** in XAMPP Control Panel
2. **Check Apache is running** in XAMPP Control Panel
3. **Visit verify_setup.php** to see database status
4. **Check browser console** for JavaScript errors
5. **Clear browser cache** and try again

---

**All setup files are ready! Follow the 3 steps above to get started.** 🚀
