<?php
/**
 * JSN Renewable Energy - System Helper Functions & Security Engine
 */

require_once __DIR__ . '/config.php';

/**
 * Sanitize Output for HTML
 */
function sanitize($data) {
    return htmlspecialchars(trim((string)($data ?? '')), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate URL Slug
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', (string)$text);
    if (function_exists('iconv')) {
        $converted = @iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        if ($converted !== false) {
            $text = $converted;
        }
    }
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'project-' . time());
}

/**
 * Format Solar Capacity (kW / MW) accurately
 */
function formatCapacity($kw) {
    $kw = (float)$kw;
    if ($kw >= 1000) {
        return number_format($kw / 1000, 2) . ' MW';
    }
    return number_format($kw, ($kw == floor($kw)) ? 0 : 1) . ' kW';
}

/**
 * Resolve Asset URL safely for root or subdirectories
 */
function resolveAssetPath($path, $prefix = '') {
    $path = trim((string)$path);
    if (empty($path)) {
        return $prefix . 'assets/projects/bhiwani-5mw-1.jpg';
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    $path = ltrim($path, '/');
    if (strpos($path, '../') === 0) {
        $path = substr($path, 3);
    }
    return $prefix . $path;
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
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
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
        header('Location: ' . BASE_PATH . '/admin/index.php');
        exit;
    }
}

/**
 * Check Admin Role
 */
function hasRole($allowedRoles = ['SuperAdmin', 'Admin']) {
    $role = $_SESSION['admin_role'] ?? '';
    return in_array($role, (array)$allowedRoles, true);
}

/**
 * Get Real Client IP Safely (Server-Side)
 */
function getClientIP() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $candidate = trim($parts[0]);
        if (filter_var($candidate, FILTER_VALIDATE_IP)) {
            $ip = $candidate;
        }
    }
    return $ip;
}

/**
 * Mask IP Address for Privacy (supports IPv4 & IPv6)
 */
function maskIP($ip, $reveal = false) {
    if ($reveal) return $ip;
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $parts = explode('.', $ip);
        return $parts[0] . '.' . $parts[1] . '.x.x';
    }
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $parts = explode(':', $ip);
        return ($parts[0] ?? '0') . ':' . ($parts[1] ?? '0') . ':xxxx:xxxx';
    }
    return 'xxx.xxx.x.x';
}

/**
 * Parse User-Agent into Device, Browser & OS
 */
function parseUserAgent($ua) {
    $ua = (string)$ua;
    $device = 'Desktop';
    if (preg_match('/(ipad|tablet|playbook|silk)|(android(?!.*mobile))/i', $ua)) {
        $device = 'Tablet';
    } elseif (preg_match('/(android|bb\d+|meego).+mobile|iphone|ipod|blackberry|opera mini|windows phone/i', $ua)) {
        $device = 'Mobile';
    }

    $browser = 'Other';
    if (stripos($ua, 'Edg') !== false) $browser = 'Edge';
    elseif (stripos($ua, 'OPR') !== false || stripos($ua, 'Opera') !== false) $browser = 'Opera';
    elseif (stripos($ua, 'Chrome') !== false) $browser = 'Chrome';
    elseif (stripos($ua, 'Safari') !== false) $browser = 'Safari';
    elseif (stripos($ua, 'Firefox') !== false) $browser = 'Firefox';

    $os = 'Other';
    if (stripos($ua, 'Windows') !== false) $os = 'Windows';
    elseif (stripos($ua, 'Android') !== false) $os = 'Android';
    elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) $os = 'iOS';
    elseif (stripos($ua, 'Mac OS') !== false) $os = 'macOS';
    elseif (stripos($ua, 'Linux') !== false) $os = 'Linux';

    return ['device' => $device, 'browser' => $browser, 'os' => $os];
}

/**
 * Secure File Upload Handler (stores relative path for domain portability)
 */
function uploadImage($file, $targetSubfolder = '') {
    if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'No file uploaded or upload error occurred.'];
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $maxFileSize = 5 * 1024 * 1024; // 5 MB

    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, $allowedExtensions, true)) {
        return ['success' => false, 'error' => 'Invalid file format. Only JPG, JPEG, PNG, and WEBP are allowed.'];
    }

    if ($file['size'] > $maxFileSize) {
        return ['success' => false, 'error' => 'File size exceeds maximum limit of 5MB.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mimeType, $allowedMimes, true)) {
        return ['success' => false, 'error' => 'Security check failed. File is not a valid image.'];
    }

    $sub = $targetSubfolder ? trim($targetSubfolder, '/') . '/' : '';
    $uploadPath = UPLOAD_DIR . $sub;
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $uniqueName = bin2hex(random_bytes(8)) . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($file['name']));
    $destination = $uploadPath . $uniqueName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $relPath = UPLOAD_REL . $sub . $uniqueName;
        return [
            'success'  => true,
            'filename' => $uniqueName,
            'filepath' => $destination,
            'url'      => $relPath
        ];
    }

    return ['success' => false, 'error' => 'Failed to move uploaded file.'];
}

/**
 * Safely delete an uploaded file inside uploads/
 */
function deleteUploadedFile($relPath) {
    $relPath = ltrim((string)$relPath, '/');
    if (strpos($relPath, 'uploads/') !== 0) {
        return false;
    }
    $fullPath = realpath(__DIR__ . '/' . $relPath);
    $uploadsRoot = realpath(UPLOAD_DIR);
    if ($fullPath && $uploadsRoot && strpos($fullPath, $uploadsRoot) === 0 && is_file($fullPath)) {
        return @unlink($fullPath);
    }
    return false;
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
