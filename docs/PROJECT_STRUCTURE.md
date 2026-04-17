# Project Structure - Career Counseling Guide Portal

## New Organized File Structure

```
Career-Counseling-Guide-Portal-master/
│
├── bootstrap.php              [Application initialization & path setup]
├── index.php                  [Main entry point for web access]
│
├── app/                       [Application logic and configuration]
│   ├── config/
│   │   └── db_connection.php [Database configuration & utility functions]
│   └── helpers/
│       └── functions.php      [Common helper functions]
│
├── public/                    [All user-facing files (WWW root)]
│   ├── index.html            [Homepage]
│   ├── about-us.html         [About page]
│   │
│   ├── auth/                 [Authentication pages & logic]
│   │   ├── login-student.html
│   │   ├── login-student.php
│   │   ├── login-admin.html
│   │   ├── login-admin.php
│   │   ├── signup.html
│   │   ├── signup.php
│   │   └── logout.php
│   │
│   ├── student/              [Student-side pages]
│   │   ├── dashboard.php     [Student dashboard]
│   │   ├── student.html      [Student home]
│   │   ├── my-messages.php   [Student messages]
│   │   ├── my-feedback.php   [Student feedback view]
│   │   └── edit-profile.php  [Profile editing]
│   │
│   ├── admin/                [Admin-side pages]
│   │   ├── dashboard.php     [Admin dashboard]
│   │   ├── admin.html        [Admin home]
│   │   ├── manage-users.php  [User management]
│   │   ├── manage-resources.php [Resource management]
│   │   ├── admin-contacts.php [Contact management]
│   │   ├── admin-settings.php [Settings]
│   │   └── admin-settings-api.php [Settings API]
│   │
│   ├── modules/              [Feature modules]
│   │   │
│   │   ├── contact/          [Contact/Messages system]
│   │   │   ├── contact.html
│   │   │   ├── contact-form.php
│   │   │   ├── view-contact.php
│   │   │   ├── display-contact.html
│   │   │   ├── reply-contact.php
│   │   │   ├── mark-as-read.php
│   │   │   └── get-notification-counts.php
│   │   │
│   │   ├── feedback/         [Feedback system]
│   │   │   ├── feedback.html
│   │   │   ├── feedback-form.php
│   │   │   ├── view-feedback.php
│   │   │   ├── display-feedback.html
│   │   │   └── my-feedback.php
│   │   │
│   │   ├── resources/        [Resources/Materials]
│   │   │   ├── resources.html
│   │   │   ├── manage-resources.php
│   │   │   └── pdfs/         [PDF files stored here]
│   │   │       ├── Explore Universities.pdf
│   │   │       ├── Courses.pdf
│   │   │       ├── Prep.pdf
│   │   │       ├── internships.pdf
│   │   │       └── Insights.pdf
│   │   │
│   │   ├── appointments/     [Appointments system]
│   │   │   └── manage-appointments.php
│   │   │
│   │   └── quizzes/          [Quizzes system]
│   │       └── manage-quizzes.html
│   │
│   └── pages/                [Static pages]
│       ├── test.html
│       └── log-in.html
│
├── database/                 [Database files & migrations]
│   ├── schema/
│   │   ├── queries.sql       [SQL queries]
│   │   └── migrate-contact-system.sql [Contact system schema]
│   │
│   └── migrations/           [Database migration scripts]
│       ├── setup-database.php
│       ├── migrate-database.php
│       ├── migrate-admin-settings.php
│       ├── migrate-add-status.php
│       ├── add-is-read-column.php
│       ├── ensure-is-read-column.php
│       ├── check-db-schema.php
│       ├── diagnose-db.php
│       ├── diagnose-replies.php
│       ├── diagnose-contact-read-status.php
│       └── verify-submissions.php
│
├── setup/                    [Installation & configuration]
│   ├── install.bat          [Windows installation script]
│   ├── install.sh           [Linux/Mac installation script]
│   └── system-test.php      [System diagnostics]
│
├── docs/                     [Documentation]
│   ├── README.md
│   ├── QUICK_START.md
│   ├── SETUP_GUIDE.md
│   ├── FEATURES.md
│   ├── TESTING_GUIDE.md
│   ├── RESOURCE_MANAGEMENT_GUIDE.md
│   ├── MODULAR_STRUCTURE_GUIDE.md
│   ├── CONTACT_SYSTEM_STRUCTURE.md
│   ├── SECURITY_AUDIT_REPORT.md
│   ├── COMPREHENSIVE_SECURITY_AUDIT_REPORT.md
│   ├── SECURITY_FIXES_COMPLETE.md
│   ├── SECURITY_FIXES_IMPLEMENTATION.md
│   ├── CRITICAL_FIXES_IMPLEMENTATION.md
│   ├── CONTACT_FORM_TROUBLESHOOTING.md
│   ├── ADMIN_FEEDBACK_FIX.md
│   ├── ADMIN_SETTINGS_INTEGRATION.md
│   ├── IMPLEMENTATION_REPORT_FINAL.md
│   ├── DEPLOYMENT_CHECKLIST.md
│   ├── PHASE2_COMPLETION_CHECKLIST.md
│   ├── PHASE2_SECURITY_COMPLETION.md
│   ├── AUDIT_FINDINGS_SUMMARY.md
│   ├── QUICK_FIX_REFERENCE.md
│   └── DOWNLOAD_HANDLER_DOCUMENTATION.md
│
├── _archive/                 [Backup of old files]
│   └── (Original files preserved here for reference)
│
└── .htaccess                 [Apache routing configuration]
```

## Directory Guide

### `/app/`
**Application logic and configuration**
- `config/` - Database and application configuration
- `helpers/` - Reusable utility functions

### `/public/`
**All user-facing files (main website content)**
- `auth/` - Login, signup, and logout functionality
- `student/` - Student dashboard and pages
- `admin/` - Admin dashboard and management pages
- `modules/` - Feature modules (contact, feedback, resources, etc.)
- `pages/` - Static pages

### `/database/`
**Database schema and migrations**
- `schema/` - SQL schema definitions
- `migrations/` - Scripts to update database structure

### `/setup/`
**Installation and configuration scripts**

### `/docs/`
**All documentation and guides**

### `/bootstrap.php`
**Application initialization**
- Sets up paths and includes
- Initializes configuration
- Include at the top of every PHP file

## How to Use the New Structure

### For PHP Files
Include the bootstrap at the top of every PHP file:

```php
<?php
require_once __DIR__ . '/../../bootstrap.php';
// or adjust path based on file location

// Now you can use:
// APP_ROOT - root directory
// APP_DIR - app directory  
// PUBLIC_DIR - public directory
// DATABASE_DIR - database directory
?>
```

### For HTML Files
Link to resources using relative paths from public/:

```html
<!-- If in public/auth/login-student.html, link to student page: -->
<a href="../student/student.html">Student Home</a>

<!-- Link to modules: -->
<a href="../modules/contact/contact.html">Contact Us</a>
<a href="../modules/feedback/feedback.html">Feedback</a>
<a href="../modules/resources/resources.html">Resources</a>
```

## Benefits of This Structure

✅ **Clear Organization** - Grouped by functionality and role (student/admin)
✅ **Scalability** - Easy to add new modules and pages
✅ **Maintainability** - Files grouped logically make changes easier
✅ **Security** - Sensitive config files in `/app/` not directly accessible
✅ **Separation of Concerns** - HTML, CSS, PHP organized clearly
✅ **Professional** - Industry-standard structure
✅ **No Functionality Lost** - All code remains unchanged, just reorganized

## Migration Status

- [x] Create folder structure
- [x] Create bootstrap file
- [ ] Move database configuration
- [ ] Move authentication files
- [ ] Move student pages
- [ ] Move admin pages
- [ ] Move modules
- [ ] Update all file references
- [ ] Test all functionality
- [ ] Archive old files

## Notes

1. **Backward Compatibility**: Original files remain in root directory initially for safety
2. **Gradual Migration**: Files can be migrated gradually without breaking the application
3. **Include Path**: Use `bootstrap.php` to ensure paths work regardless of file location
4. **Testing**: Always test after moving files to ensure all links and references work

