WAMP SETUP (PHP + MySQL)

1) Install WAMP Server if you haven't: https://www.wampserver.com/
2) Create folder:
   C:\wamp64\www\medicine-reminder\
   Put ALL files from this zip into that folder (you should see index.php, add_medicine.php, config\ etc.).

3) Start WAMP (green icon). Open phpMyAdmin: http://localhost/phpmyadmin

4) Create database and tables:
   - In phpMyAdmin, go to the SQL tab
   - Paste the entire contents of schema.sql and execute.

5) Configure DB credentials:
   - If your MySQL credentials are not default, edit config/db.php:
     $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS

6) Open the app:
   http://localhost/medicine-reminder/login.php
   - Register a new account
   - Log in
   - Add a medicine with one or more reminder times
   - Go to Dashboard (/) to see today’s list and mark doses as taken

Notes:
- If you want the app at http://localhost/ (without the folder), move files directly into C:\wamp64\www\.
- If you see an "Index of ..." directory listing, you likely opened http://localhost/medicine-reminder without a default index. Open /login.php or /index.php directly, or set DirectoryIndex in Apache.
- Email/push notifications are not included; this app is focused on tracking and reminders you see when you open the dashboard. Mark “taken” to track progress daily.

Security Tips (for a TY BCS project demo):
- Passwords are securely hashed with password_hash().
- Use prepared statements everywhere to prevent SQL injection.
- For production, use HTTPS and set secure cookie flags.
