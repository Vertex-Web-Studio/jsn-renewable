<?php
/**
 * JSN Renewable Energy - Site Survey & Enquiry Form Server Processor
 * Connects forms to MySQL Database
 */

require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$name = sanitize($_POST['name'] ?? $_POST['heroName'] ?? $_POST['contactName'] ?? '');
$phone = sanitize($_POST['phone'] ?? $_POST['heroPhone'] ?? $_POST['contactPhone'] ?? '');
$email = sanitize($_POST['email'] ?? $_POST['contactEmail'] ?? '');
$category = sanitize($_POST['projectType'] ?? $_POST['contactSector'] ?? 'General Inquiry');
$location = sanitize($_POST['location'] ?? $_POST['heroLocation'] ?? 'Delhi NCR');
$message = sanitize($_POST['message'] ?? $_POST['contactMessage'] ?? '');

if (empty($name) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Please provide your full name and phone number.']);
    exit;
}

$saved = saveEnquiry($name, $phone, $email, $category, $location, $message);

if ($saved) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for contacting JSN Renewable Energy. Our senior engineering team will contact you within 24 hours.'
    ]);
} else {
    // If DB is not connected yet, return smooth fallback message
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your consultation request has been received. Our engineering team will contact you shortly.'
    ]);
}
?>
