# SiteForge – Construction Site Management (XAMPP)
1. Copy this folder to `C:\xampp\htdocs\siteforge`. Start Apache + MySQL.
2. phpMyAdmin → Import `database.sql` (creates `siteforge_cms`).
3. Open `http://localhost/siteforge/setup_users.php` once, then DELETE that file.
4. Login at `http://localhost/siteforge/login.php`:
   admin/Admin@123 · manager/Manager@123 · accountant/Account@123 · supervisor/Super@123 (change these).
DB credentials: `includes/core.php` (default root, no password). Bootstrap/Chart.js load from CDN (needs internet).
