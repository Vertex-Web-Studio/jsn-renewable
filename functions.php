<?php
/**
 * JSN Renewable Energy - System Helper Functions & Security Engine
 */

require_once __DIR__ . '/config.php';

/**
 * Sanitize User Input
 */
function sanitize($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate URL Slug
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'n-a');
}

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * Check if User is Logged In
 */
function isLoggedIn() {
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require Admin Login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/index.php');
        exit;
    }
}

/**
 * Secure File Upload Handler
 */
function uploadImage($file, $targetSubfolder = '') {
    if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'No file uploaded or upload error occurred.'];
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
    $maxFileSize = 5 * 1024 * 1024; // 5 MB Limit

    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($fileExt, $allowedExtensions)) {
        return ['success' => false, 'error' => 'Invalid file format. Only JPG, PNG, WEBP and SVG allowed.'];
    }

    if ($file['size'] > $maxFileSize) {
        return ['success' => false, 'error' => 'File size exceeds maximum limit of 5MB.'];
    }

    // Verify MIME Type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'text/xml'];
    if (!in_array($mimeType, $allowedMimes)) {
        return ['success' => false, 'error' => 'Security check failed. File is not a valid image.'];
    }

    $uploadPath = UPLOAD_DIR . ($targetSubfolder ? rtrim($targetSubfolder, '/') . '/' : '');
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $uniqueName = bin2hex(random_bytes(8)) . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($file['name']));
    $destination = $uploadPath . $uniqueName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $fileUrl = UPLOAD_URL . ($targetSubfolder ? rtrim($targetSubfolder, '/') . '/' : '') . $uniqueName;
        return [
            'success' => true,
            'filename' => $uniqueName,
            'filepath' => $destination,
            'url' => $fileUrl
        ];
    }

    return ['success' => false, 'error' => 'Failed to move uploaded file.'];
}

/**
 * Save Enquiry to Database
 */
function saveEnquiry($name, $phone, $email, $category, $location, $message) {
    $db = getDB();
    if (!$db) {
        return false;
    }

    $stmt = $db->prepare("
        INSERT INTO enquiries (name, phone, email, project_category, location, message, status, created_at)
        VALUES (:name, :phone, :email, :category, :location, :message, 'New', NOW())
    ");

    return $stmt->execute([
        ':name'     => sanitize($name),
        ':phone'    => sanitize($phone),
        ':email'    => sanitize($email),
        ':category' => sanitize($category),
        ':location' => sanitize($location),
        ':message'  => sanitize($message)
    ]);
}
?>
