<?php
/**
 * JSN Renewable Energy - Master Database & System Configuration
 * Approved Tagline: A NEW WAY TO POWER
 */

// Prevent direct access
if (!defined('JSN_EXEC')) {
    define('JSN_EXEC', true);
}

// Session Security Configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Database Credentials (Update for production hosting)
define('DB_HOST', 'localhost');
define('DB_NAME', 'jsn_renewable_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// System Constants
define('SITE_NAME', 'JSN Renewable Energy Private Limited');
define('SITE_TAGLINE', 'A NEW WAY TO POWER');
define('SITE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/jns%20renuable');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Notification Settings
define('ADMIN_EMAIL', 'info@jsnrenewable.com');

/**
 * Returns PDO Database Connection Instance
 */
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Return null if database is not installed yet
            return null;
        }
    }
    return $pdo;
}
?>
