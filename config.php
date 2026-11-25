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
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? 80) == 443);

    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if ($isHttps) {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

// Database Credentials (Configure via environment variables or update for production hosting)
define('DB_HOST', getenv('JSN_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('JSN_DB_NAME') ?: 'jsn_renewable_db');
define('DB_USER', getenv('JSN_DB_USER') ?: 'root');
define('DB_PASS', getenv('JSN_DB_PASS') !== false ? getenv('JSN_DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

// Dynamic Base URL Detection
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')) ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
$appRoot = str_replace('\\', '/', realpath(__DIR__) ?: __DIR__);
$basePath = '';
if ($docRoot && strpos($appRoot, $docRoot) === 0) {
    $basePath = rtrim(substr($appRoot, strlen($docRoot)), '/');
}

// System Constants
define('SITE_NAME', 'JSN Renewable Energy Private Limited');
define('SITE_TAGLINE', 'A new way to power');
define('BASE_PATH', $basePath);
define('SITE_URL', $protocol . '://' . $host . $basePath);
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_REL', 'uploads/');
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
            return null;
        }
    }
    return $pdo;
}
?>
