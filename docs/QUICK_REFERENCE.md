# Quick Reference - New Project Structure

## 🚀 Project Reorganization Complete!

Your Career Counseling Guide Portal has been successfully reorganized into a professional, scalable structure.

## 📁 Folder Layout at a Glance

```
Career-Counseling-Guide-Portal-master/
├── bootstrap.php                 ← Include this in ALL PHP files
├── index.html                    ← Static home page
├── 
├── app/                          ← Application code (hidden from web)
│   └── config/
│       └── db_connection.php     ← Database configuration
│
├── public/                       ← All user-facing pages
│   ├── auth/                     ← Login & signup pages
│   ├── student/                  ← Student-only pages
│   ├── admin/                    ← Admin dashboard & management
│   ├── modules/                  ← Feature modules
│   │   ├── contact/              ← Contact system
│   │   ├── feedback/             ← Feedback system  
│   │   └── resources/            ← Resources & PDFs
│   │       └── pdfs/
│   └── pages/                    ← Static pages (coming soon)
│
├── database/
│   ├── migrations/               ← Migration scripts
│   └── schema/                   ← SQL schema files
│
├── setup/                        ← Installation scripts
├── docs/                         ← Documentation (this folder)
└── (old files in root)           ← Kept for backward compatibility
```

## 🔑 Key Files to Know

| File | Purpose | Access |
|------|---------|--------|
| `bootstrap.php` | Initialize app, set paths, DB connection | Require in every PHP file |
| `app/config/db_connection.php` | Database connection & utilities | Loaded via bootstrap.php |
| `public/auth/Log-in (Student).html` | Student login page | `localhost/.../public/auth/Log-in (Student).html` |
| `public/auth/Log-in (Admin).html` | Admin login page | `localhost/.../public/auth/Log-in (Admin).html` |
| `public/modules/resources/resources.html` | Download PDFs | `localhost/.../public/modules/resources/resources.html` |

## 🛠️ How to Work with PHP Files

### Include Database in Your PHP File
```php
<?php
// At the VERY TOP of any PHP file
require_once __DIR__ . '/../../bootstrap.php';
// OR (if in modules)
require_once __DIR__ . '/../../../bootstrap.php';

// Now use:
// - $conn (database connection)
// - sanitize($input)
// - hash_password($password)
// - verify_password($input, $hash)
// - validate_email($email)
?>
```

### Path Depths (relative to file location)
- `public/auth/*.php` → 2 levels: `__DIR__ . '/../../bootstrap.php'`
- `public/student/*.php` → 2 levels: `__DIR__ . '/../../bootstrap.php'`
- `public/admin/*.php` → 2 levels: `__DIR__ . '/../../bootstrap.php'`
- `public/modules/contact/*.php` → 3 levels: `__DIR__ . '/../../../bootstrap.php'`
- `public/modules/feedback/*.php` → 3 levels: `__DIR__ . '/../../../bootstrap.php'`
- `public/modules/resources/*.php` → 3 levels: `__DIR__ . '/../../../bootstrap.php'`

## 🌐 Accessing Pages in Browser

### Authentication
- Student Login: `http://localhost/Career-Counseling-Guide-Portal-master/public/auth/Log-in (Student).html`
- Admin Login: `http://localhost/Career-Counseling-Guide-Portal-master/public/auth/Log-in (Admin).html`
- Sign Up: `http://localhost/Career-Counseling-Guide-Portal-master/public/auth/Sign-Up.html`

### Student Pages
- Dashboard: `http://localhost/Career-Counseling-Guide-Portal-master/public/student/Student_Dashboard.php`
- Contact Admin: `http://localhost/Career-Counseling-Guide-Portal-master/public/modules/contact/Contact.html`
- Send Feedback: `http://localhost/Career-Counseling-Guide-Portal-master/public/modules/feedback/Feedback.html`
- My Messages: `http://localhost/Career-Counseling-Guide-Portal-master/public/student/my_messages.php`
- My Feedback: `http://localhost/Career-Counseling-Guide-Portal-master/public/student/my_feedback.php`

### Admin Pages
- Dashboard: `http://localhost/Career-Counseling-Guide-Portal-master/public/admin/Admin_Dashboard.php`
- Manage Contacts: `http://localhost/Career-Counseling-Guide-Portal-master/public/admin/admin_contacts.php`

### Resources
- Resources & PDFs: `http://localhost/Career-Counseling-Guide-Portal-master/public/modules/resources/resources.html`

## 🔗 Linking Between HTML Pages

### From the same directory
```html
<a href="other-file.html">Go to other file</a>
```

### Going up directories
```html
<!-- From: public/student/student.html -->
<a href="../modules/contact/Contact.html">Contact Admin</a>
<a href="../modules/feedback/Feedback.html">Send Feedback</a>
<a href="../modules/resources/resources.html">Download Resources</a>
<a href="../auth/logout.php">Logout</a>
```

### Navigation Pattern
```
public/
├── auth/              ← depth 2
├── student/           ← depth 2
├── admin/             ← depth 2
└── modules/
    ├── contact/       ← depth 3 (need ../../../ to root)
    ├── feedback/      ← depth 3
    └── resources/     ← depth 3
```

## 📝 Important Notes

✅ **All original files preserved** in root directory for backward compatibility
✅ **All database includes updated** to use new bootstrap.php paths
✅ **Session management** works as before (bootstrap.php initializes sessions)
✅ **Database connection** automatically available after bootstrap.php include
✅ **Relative paths maintained** - links between files still work

## 🧪 Testing Your Changes

### Test Student Login
1. Open: `http://localhost/.../public/auth/Log-in (Student).html`
2. Enter test credentials
3. Should redirect to: `http://localhost/.../public/student/Student_Dashboard.php`

### Test Admin Login
1. Open: `http://localhost/.../public/auth/Log-in (Admin).html`
2. Enter admin credentials
3. Should redirect to: `http://localhost/.../public/admin/Admin_Dashboard.php`

### Test PDF Downloads
1. Open: `http://localhost/.../public/modules/resources/resources.html`
2. Click any PDF download button
3. PDF should download to your computer

### Test Contact Form
1. Open: `http://localhost/.../public/modules/contact/Contact.html`
2. Fill form and submit
3. Should see success message (and database should be updated)

## ⚠️ Troubleshooting

### "File not found" errors
- Check file is in the expected directory
- Verify relative path counts directory levels correctly
- Use `__DIR__` for absolute paths in PHP

### Database connection failing
- Ensure `bootstrap.php` is included at TOP of file
- Verify MySQL/MariaDB is running
- Check credentials in `app/config/db_connection.php`

### Links not working
- Count directory levels carefully
- Test in browser to see actual path
- Verify file names match exactly (case-sensitive on some servers)

## 📚 Documentation Structure

| File | Purpose |
|------|---------|
| `PROJECT_STRUCTURE.md` | Detailed folder organization |
| `RESTRUCTURING_COMPLETE.md` | Migration summary & what changed |
| `QUICK_REFERENCE.md` | This file - quick lookup |
| `QUICK_START.md` | Getting started guide |
| `SETUP_GUIDE.md` | Installation instructions |

## 🎯 Next Steps

1. **Test everything** - verify all pages load and work
2. **Review links** - check that all HTML navigation works
3. **Test forms** - ensure contact & feedback forms still submit to DB
4. **Test downloads** - verify PDF downloads work
5. **Monitor logs** - check error logs if anything fails

## 💾 Cleanup (Optional)

Once verified everything works, you can optionally:
- Move old files to `_archive/` folder
- Or simply delete the old files from root (keep backups first!)
- Update any external documentation that references old paths

---

**Need more help?** Check the `docs/` folder for detailed guides on specific topics.
