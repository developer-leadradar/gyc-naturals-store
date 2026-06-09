<?php
define('GYC_ACCESS', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');

$date = trim($_GET['date'] ?? '');
if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    jsonResponse(['success' => false, 'message' => 'Invalid date'], 400);
}
if ($date < date('Y-m-d')) {
    jsonResponse(['slots' => [], 'message' => 'Date is in the past']);
}

$slots = getAvailableSlots($date);
jsonResponse(['success' => true, 'date' => $date, 'slots' => $slots]);
