
A job portal built with *Core PHP (OOP + PDO), **MySQL, **HTML5, CSS3, JavaScript* and *Bootstrap 5. 

Employers post jobs, candidates search and apply, and admins approve listings. The look is modelled on Indeed and LinkedIn.

## Status

| Module | Status |
|---|---|
| Auth: separate employer / candidate registration, login, logout (PHP sessions, role column) | Done |
| Job listing page: CSS Grid, sticky filter sidebar, premium job cards | Done |
| Filters (keyword, location, category, salary range, job type) via JS fetch + PHP GET params | Done |
| Saved jobs: MySQL table + heart toggle | Done |
| Clean URLs via .htaccess | Done |
| Employer dashboard: company profile, logo upload, post job, applications table with AJAX status update | Planned |
| Candidate apply page: PDF-only CV upload, max 2MB, JS + PHP validation | Planned |
| Admin job approval | Planned |

## Tech stack

- PHP 8.0+ (OOP, PDO with prepared statements)
- MySQL 5.7+ / 8
- Bootstrap 5.3 and Bootstrap Icons (CDN) plus custom CSS
- Vanilla JavaScript (fetch, no jQuery)

## Requirements

- WAMP Server with Apache, PHP 8.0 or newer, and MySQL
- Apache rewrite_module enabled
- AllowOverride All for the www directory

## Installation

1. Place the project at:
   
   C:\wamp64\www\jobportal\
   
2. Enable rewrite_module: WAMP tray icon, Apache, Apache modules, rewrite_module.
3. Create the database: open phpMyAdmin and import database/schema.sql (it creates the jobportal database).
4. Check config/config.php. WAMP defaults are already set:
   php
   define('BASE_URL', '/jobportal');
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'jobportal');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   
5. Load demo data by visiting http://localhost/jobportal/install.php once, then *delete install.php*.
6. Open http://localhost/jobportal/.

## Demo accounts

Created by install.php. For local testing only.

| Role | Email | Password |
|---|---|---|
| Admin | admin@jobportal.test | Admin@123 |
| Candidate | candidate@jobportal.test | Candidate@123 |
| Employer | employer1@jobportal.test | Employer@123 |

## Project structure


jobportal/
├── .htaccess              # Clean URLs, blocks PHP in uploads/
├── index.php              # Job listing (also serves the AJAX partial)
├── login.php / register.php / logout.php
├── install.php            # One-time demo seeder (delete after use)
├── api/
│   └── toggle_save.php    # Saved jobs endpoint (JSON)
├── app/                   # Classes (denied to direct web access)
│   ├── Database.php       # PDO singleton
│   ├── Auth.php           # Registration, login, role guard
│   ├── Job.php            # Search and filtering
│   └── helpers.php        # Escaping, CSRF, flash messages, formatting
├── config/config.php      # Settings, session, autoloader
├── database/schema.sql    # MySQL schema
├── includes/              # header, footer, job grid partial
├── assets/css, assets/js  # Custom styles and scripts
└── uploads/               # logos/ and cvs/ (planned)


## Clean URLs

| URL | Maps to |
|---|---|
| /jobs | index.php |
| /login | login.php |
| /register/employer | register.php?role=employer |
| /register/candidate | register.php?role=candidate |
| /employer/<page>, /admin/<page> | employer/<page>.php, admin/<page>.php |

## Database

Tables: users (with role), companies, jobs (with status for approval), applications, saved_jobs.

## Security notes

- Passwords are hashed with password_hash().
- All queries use PDO prepared statements.
- Output is escaped with htmlspecialchars.
- CSRF tokens protect forms and AJAX calls.
- The session ID is regenerated on login.
- PHP execution is blocked inside uploads/.
- Before deploying anywhere public: set a real DB password, delete install.php, and remove the demo accounts.

## Roadmap

1. Employer dashboard: company profile and logo upload (MIME check), post job, applications table with AJAX status dropdown (/api/update_status.php)
2. Candidate apply page with CV upload (PDF only, max 2MB, validated in JS and PHP)
3. Admin job approval page
4. Job detail page

## License

MIT


## Author
Chandrakumar Vishanan - ICT Second Year, ( SLIATE )
