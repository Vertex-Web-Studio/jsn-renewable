<?php
/**
 * JSN Renewable Energy - Server-Side Visitor Analytics Engine
 * Features: Server-side IP capture, session-based deduplication, Browser/OS/Device parsing, 90-day retention
 */

require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

// Session-based deduplication (15-minute window)
if (!isset($_SESSION['visitor_session_id'])) {
    $_SESSION['visitor_session_id'] = bin2hex(random_bytes(16));
    $_SESSION['session_start_time'] = time();
}

$json = file_get_contents('php://input');
$data = json_decode($json, true) ?: [];

$ip = getClientIP();
$page = substr(sanitize($data['page'] ?? $_SERVER['REQUEST_URI'] ?? '/'), 0, 255);
$referrer = substr(sanitize($data['referrer'] ?? $_SERVER['HTTP_REFERER'] ?? ''), 0, 255);
$userAgent = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? $data['userAgent'] ?? ''), 0, 500);
$sessionId = $_SESSION['visitor_session_id'];

$parsedUA = parseUserAgent($userAgent);

$db = getDB();
if ($db) {
    try {
        // Ensure table exists (safe for fresh installs)
        $db->exec("
            CREATE TABLE IF NOT EXISTS `visitor_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `session_id` VARCHAR(64) NOT NULL,
                `ip_address` VARCHAR(45) NOT NULL,
                `page_visited` VARCHAR(255) NOT NULL,
                `referrer` VARCHAR(255) NULL,
                `browser` VARCHAR(50) DEFAULT 'Other',
                `os` VARCHAR(50) DEFAULT 'Other',
                `device_type` VARCHAR(30) DEFAULT 'Desktop',
                `user_agent` TEXT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_visitor_session` (`session_id`),
                INDEX `idx_visitor_created` (`created_at`),
                INDEX `idx_visitor_page` (`page_visited`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Deduplicate: count same session + page only once every 15 minutes
        $checkStmt = $db->prepare("
            SELECT COUNT(*) FROM visitor_logs
            WHERE session_id = ? AND page_visited = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ");
        $checkStmt->execute([$sessionId, $page]);

        if ((int)$checkStmt->fetchColumn() === 0) {
            $stmt = $db->prepare("
                INSERT INTO visitor_logs (session_id, ip_address, page_visited, referrer, browser, os, device_type, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $sessionId,
                $ip,
                $page,
                $referrer ?: null,
                $parsedUA['browser'],
                $parsedUA['os'],
                $parsedUA['device'],
                $userAgent
            ]);
        }

        // Lightweight 90-day data retention cleanup (1% chance per hit)
        if (mt_rand(1, 100) === 1) {
            $db->exec("DELETE FROM visitor_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
        }
    } catch (Exception $e) {
        // Fail silently on analytics error to never impact UX
    }
}

echo json_encode(['success' => true]);
?>
