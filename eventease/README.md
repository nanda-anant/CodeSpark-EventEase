# EventEase — College Event Registration & Check-In (Team CodeSpark, PS-03, Code Carnival 3.0)
Stack: PHP 8 (PDO), MySQL, Bootstrap 5, Chart.js, qrcode.js, html5-qrcode (CDN libs — internet needed for UI libs).
## Run (XAMPP)
1. Copy folder `eventease` to `C:\xampp\htdocs\`. Start Apache + MySQL.
2. phpMyAdmin → Import `database/eventease.sql`.
3. Open http://localhost/<your-folder>/eventease/ — the database and demo data are created automatically on first load (manual import is optional)  (DB user `root`, no password; edit `config/database.php` if different).
4. Admin: admin@eventease.com / Admin@123 (hashed on first login).
## Demo (3–5 min)
1. Login → dashboard (Registrations 1, Check-ins 0).
2. Admin → New Event "AI Innovation Workshop", capacity 100 (a seeded one already exists).
3. Events → Register Anant Nanda → pass with QR + entry code.
4. Admin → Check-in → enter the code → ✓ success.
5. Same code again → ⚠ DUPLICATE CHECK-IN ("already checked in").
6. Dashboard → counts and attendance updated. Seeded sample code: EVT7K92P.
## Integrity
UNIQUE(event_id, participant_id), UNIQUE(entry_code), UNIQUE(checkins.registration_id) → duplicates blocked by the DB itself; capacity checked inside a row-locked transaction. Prepared statements, CSRF tokens, output escaping, session-protected admin.
## Future scope
Edit event, image upload, waitlist, email confirmation, certificates, dark/light toggle.
