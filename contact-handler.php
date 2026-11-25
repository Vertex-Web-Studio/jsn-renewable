<?php
/**
 * JSN Renewable Energy - Site Survey & Enquiry Form Server Processor
 * Validates input and stores enquiries in MySQL Database
 */

require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Honeypot anti-spam check
if (!empty($_POST['website_url'])) {
    echo json_encode(['success' => true, 'message' => 'Thank you for your enquiry.']);
    exit;
}

$name = sanitize($_POST['name'] ?? $_POST['heroName'] ?? $_POST['contactName'] ?? '');
$phone = sanitize($_POST['phone'] ?? $_POST['heroPhone'] ?? $_POST['contactPhone'] ?? '');
$email = sanitize($_POST['email'] ?? $_POST['contactEmail'] ?? '');
$category = sanitize($_POST['projectType'] ?? $_POST['contactSector'] ?? 'General Inquiry');
$location = sanitize($_POST['location'] ?? $_POST['heroLocation'] ?? 'Delhi NCR');
$message = sanitize($_POST['message'] ?? $_POST['contactMessage'] ?? '');

if (empty($name) || empty($phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please provide your full name and phone number.']);
    exit;
}

if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid phone number.']);
    exit;
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

$saved = saveEnquiry($name, $phone, $email, $category, $location, $message);

if ($saved) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for contacting JSN Renewable Energy. Our engineering team will reach out within 24 hours.'
    ]);
} else {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'message' => 'Database is currently unavailable. Please call +91 92890 44454 or email info@jsnrenewable.com directly.'
    ]);
}
?>
