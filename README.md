VaultPHP – Password Manager
A PHP web app for generating and storing passwords securely. Built for a university OOP assignment.
What it does

Register/login with a hashed password
Each user gets their own AES-256 encryption key (generated once, never changes)
Save passwords for websites/apps — stored encrypted in MySQL
Built-in password generator (set exact character counts or percentages)
Change login password without losing your saved passwords

Requirements

PHP 8+
MySQL 8
A web server (Apache or Nginx)

Setup

Import the database schema:

bash   mysql -u root -p < config/schema.sql

Edit config/database.php and set your DB credentials:

php   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_user');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'password_manager');

Point your web server's document root to the password_manager/ folder.
Open http://localhost/register.php and create an account.

Project structure
password_manager/
├── config/
│   ├── database.php       # DB connection (singleton)
│   └── schema.sql         # table definitions
├── classes/
│   ├── Auth.php           # session helpers
│   ├── User.php           # register, login, change password
│   ├── EncryptionService.php
│   ├── PasswordGenerator.php
│   └── SavedPassword.php  # CRUD for saved entries
├── pages/
│   └── nav.php
├── assets/css/
│   └── style.css
├── index.php
├── login.php
├── register.php
├── dashboard.php
├── add_password.php
├── edit_password.php
├── generator.php
├── settings.php
└── logout.php
How the encryption works
On registration a random 32-byte AES key is generated for the user. That key is encrypted using the user's login password and stored in the DB. When the user logs in, the key is decrypted and kept in the session. All saved passwords are encrypted with that key before being written to the DB.
If the user changes their login password, the AES key gets re-wrapped with the new password. The key itself never changes, so all saved passwords remain readable.
Notes

Passwords are never stored in plain text
Login passwords use bcrypt
All DB queries use prepared statements
htmlspecialchars() on all output
