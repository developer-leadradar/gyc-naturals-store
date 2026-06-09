<?php
define('GYC_ACCESS', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/email-templates.php';

header('Content-Type: application/json; charset=utf-8');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Verify CSRF
if (!verifyCsrfSilent()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit;
}

// Collect and sanitize
$galleryImageId = (int)($_POST['gallery_image_id'] ?? 0);
$slotId         = (int)($_POST['slot_id'] ?? 0);
$date           = sanitize($_POST['date'] ?? '');
$time           = sanitize($_POST['time'] ?? '');
$customerName   = trim(sanitize($_POST['customer_name'] ?? ''));
$customerPhone  = trim(sanitize($_POST['customer_phone'] ?? ''));
$customerEmail  = strtolower(trim(sanitize($_POST['customer_email'] ?? '')));
$notes          = trim(sanitize($_POST['notes'] ?? ''));

// Validate required fields
$errors = [];

if (empty($customerName) || strlen($customerName) < 2) {
    $errors[] = 'Please enter your full name.';
}
if (empty($customerPhone) || strlen($customerPhone) < 7) {
    $errors[] = 'Please enter a valid phone number.';
}
if ($customerEmail && !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $errors[] = 'Please select a date.';
}
if (strtotime($date) < strtotime('today')) {
    $errors[] = 'Please select a future date.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Fetch style info for deposit calculation
$styleInfo = null;
if ($galleryImageId > 0) {
    $styleInfo = getDB()->fetchOne(
        "SELECT id, title, price_from, price_to FROM gallery_images WHERE id = ? AND is_active = 1",
        [$galleryImageId]
    );
}

// Calculate deposit (30% of price_from, minimum ₦2,000)
$depositAmount = 0;
if ($styleInfo && $styleInfo['price_from']) {
    $depositAmount = round($styleInfo['price_from'] * 0.30);
    if ($depositAmount < 2000) $depositAmount = 2000;
}

// Build data for createAppointment()
$appointmentData = [
    'customer_name'    => $customerName,
    'customer_phone'   => $customerPhone,
    'customer_email'   => $customerEmail,
    'user_id'          => isLoggedIn() ? $_SESSION['user_id'] : null,
    'gallery_image_id' => $galleryImageId ?: null,
    'slot_id'          => $slotId ?: null,
    'requested_date'   => $date,
    'requested_time'   => $time ?: null,
    'customer_notes'   => $notes,
    'duration_estimate'=> $styleInfo ? null : null,
];

$result = createAppointment($appointmentData);

if (!$result['success']) {
    echo json_encode($result);
    exit;
}

$aptNum = $result['appointment_number'];
$aptId  = $result['appointment_id'];

// ── Paystack deposit initiation (only if deposit > 0 and PAYSTACK_PUBLIC_KEY set) ──
$paystackData  = null;
$paystackReady = (PAYSTACK_PUBLIC_KEY !== '' && $depositAmount > 0 && $customerEmail);

if ($paystackReady) {
    // Store deposit intent in session
    $_SESSION['pending_deposit'] = [
        'appointment_id'     => $aptId,
        'appointment_number' => $aptNum,
        'amount_kobo'        => $depositAmount * 100,
        'email'              => $customerEmail,
        'name'               => $customerName,
        'style'              => $styleInfo['title'] ?? 'Appointment',
    ];

    $paystackData = [
        'public_key'   => PAYSTACK_PUBLIC_KEY,
        'email'        => $customerEmail,
        'amount'       => $depositAmount * 100, // Paystack uses kobo
        'currency'     => 'NGN',
        'reference'    => 'GYC-DEP-' . $aptId . '-' . time(),
        'callback_url' => SITE_URL . '/api/paystack-verify.php?type=deposit&apt=' . urlencode($aptNum),
        'metadata'     => [
            'appointment_id'     => $aptId,
            'appointment_number' => $aptNum,
            'customer_name'      => $customerName,
            'style_name'         => $styleInfo['title'] ?? 'Appointment',
        ],
    ];
}

// Send booking confirmation email to customer
if ($customerEmail) {
    $aptEmailData = [
        'appointment_number' => $aptNum,
        'customer_name'      => $customerName,
        'requested_date'     => $date,
        'requested_time'     => $time ?: null,
        'style_name'         => $styleInfo['title'] ?? 'Hair Appointment',
        'customer_notes'     => $notes,
        'deposit_amount'     => $depositAmount,
    ];
    $confirmHtml = emailBookingConfirmation($aptEmailData, false);
    sendEmail($customerEmail, 'Booking Received — Ref #' . $aptNum . ' | GYC Naturals', $confirmHtml);
}

// Build WhatsApp admin notification message
$waPhone   = getSetting('site_whatsapp') ?: SITE_WHATSAPP;
$waMessage = "New Appointment Request:\n"
           . "Ref: $aptNum\n"
           . "Name: $customerName\n"
           . "Phone: $customerPhone\n"
           . "Date: " . date('D jS M Y', strtotime($date))
           . ($time ? " at $time" : '') . "\n"
           . "Style: " . ($styleInfo['title'] ?? 'To be decided') . "\n"
           . "Notes: " . ($notes ?: 'None');

$waUrl = whatsappMessage($waPhone, $waMessage);

// Respond
echo json_encode([
    'success'            => true,
    'appointment_number' => $aptNum,
    'appointment_id'     => $aptId,
    'deposit_amount'     => $depositAmount,
    'paystack'           => $paystackData,
    'whatsapp_url'       => $waUrl,
    'redirect'           => SITE_URL . '/booking-confirmation.php?apt=' . urlencode($aptNum),
]);
