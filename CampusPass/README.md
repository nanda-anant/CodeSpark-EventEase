# CampusPass — College Event Management Portal

PHP 8+ / MySQL college event registration portal with a separate public participant site and admin portal. Built for local XAMPP use and the Code Carnival 3.0 PS-03 demo.

## Demo administrator login
- **Admin login URL:** `http://localhost/Anant/CampusPass/admin/login.php` (adjust if your folder is directly under `htdocs`)
- **User ID / Email:** `admin@campuspass.local`
- **Password:** `Campus@2026!`
- **Role:** Super Admin

This seeded account is for a local demo only. Change the password after first login. Never use these demo credentials on a public/production server.

## Fresh installation in XAMPP
1. Extract this ZIP. Copy the included `CampusPass` folder to `C:\xampp\htdocs\Anant\CampusPass`. If you don't have the `Anant` folder, use `C:\xampp\htdocs\CampusPass` and adjust the URL.
2. Open XAMPP Control Panel and start **Apache** and **MySQL**.
3. Open `http://localhost/phpmyadmin` and click **Import**.
4. Choose `CampusPass/database/campuspass_portal.sql` and import. The file creates and selects the `campuspass_portal` database itself, so no database needs to be selected first.
5. Confirm `CampusPass/config/database.php` settings. Defaults: host `127.0.0.1`, database `campuspass_portal`, user `root`, blank password (typical local XAMPP defaults).
7. **Participant website:** `http://localhost/Anant/CampusPass/`
8. **Admin portal:** `http://localhost/Anant/CampusPass/admin/login.php`

The SQL intentionally resets tables with the same names in `campuspass_portal`. Import only for a fresh install; back up data before importing over an existing database.

## Included features
- Public homepage, published event listing, search, category filters and event details.
- Participant registration/login, profile, dashboard, event history and tickets.
- Capacity-aware event registration and duplicate registration prevention.
- Unique registration ID, entry code and QR ticket; print / Save as PDF.
- Separate admin login and dashboard, event management, participant/registration views, check-in desk, attendance reports and CSV export.
- Role-aware access, password hashing, PDO prepared statements, CSRF checks and output escaping.
- Responsive light theme.

## Troubleshooting
- **Database connection failed:** make sure MySQL is running and verify `config/database.php`.
- **Unknown database:** import `database/campuspass_portal.sql`.
- **Admin login fails:** verify `admin@campuspass.local` exists in `admin_users` and the SQL import completed.
- **Registration unavailable:** confirm the event is `published`, its event date is today or later, capacity remains, and registration end time has not passed.
- **404:** ensure the folder is under `htdocs` and the URL matches its location.
- **Camera scanning:** use localhost or HTTPS and grant browser camera permission; manual entry code is also available.

## Design refresh
- New violet / amber theme (`assets/css/style.css`): dark gradient hero, glass navbar, rounded cards with hover effects, dark admin sidebar with active-page highlight, restyled forms, tables, tickets and footer.
- New fonts: Plus Jakarta Sans (headings) and Inter (body).

## Form validation (client + server)
Every form now validates on **both** sides. Browser (`assets/js/validation.js`) gives instant inline messages; the server re-checks everything, so it cannot be bypassed.

| Form | Rules |
|---|---|
| Create account | name 2-60 letters, valid email, 10-digit mobile (starts 6-9), strong password (8+ with upper, lower, number, special), confirm password, terms checkbox |
| Login / Admin login | valid email, password required |
| My profile | name, mobile |
| Event registration | rules checkbox required |
| Admin: create / edit event | title, category, description, venue lengths; date not in the past; end time after start; capacity 1-100000 (and not below existing registrations); registration end after start and not after event date; image URL must be http(s) |
| Admin: my profile | name; optional password change needs current password, 12+ char strong password, confirmation |
| Admin: create admin account | name, email, 12+ char strong password, role |
| First-time setup | name, email, strong 12+ char password, confirm |
| Check-in desk | entry code 6-64 letters / numbers / hyphens |
| Search / filter forms | max length; report date range (to >= from) |

Extras: show/hide password button, live password-strength meter, character counters, double-submit protection.

Mobile numbers are stored as the 10-digit number (country code removed).

## Testing note
PHP files were bracket-checked, but the project was **not** executed against a live XAMPP/MySQL here. Please run through `tests/manual-test-checklist.md` after copying it into `htdocs`. Before production, replace demo credentials, secure the database, use HTTPS, and harden server configuration.
