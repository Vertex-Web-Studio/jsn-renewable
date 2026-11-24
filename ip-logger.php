<?php
/**
 * JSN Renewable Energy - Server-Side Visitor Analytics Engine
 * Features: Session-based deduplication, IP logging, device parsing, trend tracking
 */

require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

// Session-based deduplication (prevent counting every refresh within 15 minutes as new visitor)
if (!isset($_SESSION['visitor_session_id'])) {
    $_SESSION['visitor_session_id'] = bin2hex(random_bytes(16));
    $_SESSION['session_start_time'] = time();
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$ip = sanitize($data['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
$page = sanitize($data['page'] ?? $_SERVER['REQUEST_URI'] ?? '/');
$userAgent = sanitize($data['userAgent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? '');
$sessionId = $_SESSION['visitor_session_id'];

// Parse Device Type
$deviceType = 'Desktop';
if (preg_match('/(android|bb\d+|meego).+mobile|avail|blackberry|emulator|iphone|ipod|sm-b|opera mini|windows phone/i', $userAgent)) {
    $deviceType = 'Mobile';
} elseif (preg_match('/(ipad|tablet|playbook|silk)|(android(?!.*mobile))/i', $userAgent)) {
    $deviceType = 'Tablet';
}

$db = getDB();
if ($db) {
    // Create visitor_logs table with deduplication and analytics fields
    $db->exec("
        CREATE TABLE IF NOT EXISTS `visitor_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `session_id` VARCHAR(64) NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `page_visited` VARCHAR(255) NOT NULL,
            `device_type` VARCHAR(30) DEFAULT 'Desktop',
            `user_agent` TEXT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX(`session_id`),
            INDEX(`created_at`),
            INDEX(`ip_address`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Deduplicate: check if this session hit the same page in the last 10 minutes
    $checkStmt = $db->prepare("
        SELECT COUNT(*) FROM visitor_logs
        WHERE session_id = ? AND page_visited = ? AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
    ");
    $checkStmt->execute([$sessionId, $page]);

    if ($checkStmt->fetchColumn() == 0) {
        $stmt = $db->prepare("INSERT INTO visitor_logs (session_id, ip_address, page_visited, device_type, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$sessionId, $ip, $page, $deviceType, $userAgent]);
    }
}

echo json_encode(['success' => true, 'logged_ip' => $ip, 'session' => $sessionId]);
?>
