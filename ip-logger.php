<?php
/**
 * JSN Renewable Energy - Visitor IP Logger Endpoint
 */

require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$ip = sanitize($data['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
$page = sanitize($data['page'] ?? $_SERVER['REQUEST_URI'] ?? '/');
$userAgent = sanitize($data['userAgent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? '');

$db = getDB();
if ($db) {
    // Create table if not exists
    $db->exec("
        CREATE TABLE IF NOT EXISTS `visitor_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `ip_address` VARCHAR(45) NOT NULL,
            `page_visited` VARCHAR(255) NOT NULL,
            `user_agent` TEXT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $stmt = $db->prepare("INSERT INTO visitor_logs (ip_address, page_visited, user_agent, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$ip, $page, $userAgent]);
}

echo json_encode(['success' => true, 'logged_ip' => $ip]);
?>
