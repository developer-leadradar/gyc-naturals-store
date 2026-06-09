<?php
define('GYC_ACCESS', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false], 405);
}

$name    = sanitize($_POST['name']    ?? '');
$phone   = sanitize($_POST['phone']   ?? '');
$date    = sanitize($_POST['date']    ?? '');
$styleId = (int)($_POST['gallery_image_id'] ?? 0);

if (!$name || !$phone || !$date) {
    jsonResponse(['success' => false, 'message' => 'Name, phone and date are required'], 400);
}

$result = joinWaitlist([
    'customer_name'     => $name,
    'customer_phone'    => $phone,
    'requested_date'    => $date,
    'gallery_image_id'  => $styleId ?: null,
]);

if ($result) {
    jsonResponse(['success' => true, 'message' => "You're on the waiting list! We'll WhatsApp you at $phone when a slot opens."]);
} else {
    jsonResponse(['success' => false, 'message' => 'Could not add to waiting list. You may already be on it.'], 400);
}
