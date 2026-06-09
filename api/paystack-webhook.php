<?php
define('GYC_ACCESS', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Paystack sends webhook as JSON POST
// Verify the signature header before processing

$input    = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
$computed  = hash_hmac('sha512', $input, PAYSTACK_SECRET_KEY);

if (!hash_equals($computed, $sigHeader)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid signature.']);
    exit;
}

$event = json_decode($input, true);

if (!$event || empty($event['event'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload.']);
    exit;
}

$db = getDB();

switch ($event['event']) {

    case 'charge.success':
        $data      = $event['data'];
        $reference = $data['reference'] ?? '';
        $status    = $data['status']    ?? '';
        $amount    = (int)($data['amount'] ?? 0); // in kobo

        if ($status !== 'success' || !$reference) break;

        // Determine if this is a deposit or order payment
        if (strpos($reference, 'GYC-DEP-') === 0) {
            // Appointment deposit — update appointment
            preg_match('/GYC-DEP-(\d+)-/', $reference, $matches);
            $aptId = $matches[1] ?? 0;
            if ($aptId) {
                $db->update('appointments', [
                    'deposit_paid'   => 1,
                    'deposit_amount' => $amount / 100,
                    'paystack_ref'   => $reference,
                    'status'         => 'confirmed',
                    'confirmed_at'   => date('Y-m-d H:i:s'),
                ], 'id = ?', [$aptId]);
            }
        } elseif (strpos($reference, 'GYC-ORD-') === 0) {
            // Order payment — update order
            $order = $db->fetchOne(
                "SELECT * FROM orders WHERE paystack_ref = ?",
                [$reference]
            );
            if ($order) {
                $db->update('orders', [
                    'payment_status' => 'paid',
                    'status'         => 'processing',
                ], 'id = ?', [$order['id']]);
            }
        }
        break;

    case 'refund.processed':
        $data      = $event['data'];
        $reference = $data['transaction']['reference'] ?? '';
        if ($reference) {
            $order = $db->fetchOne("SELECT * FROM orders WHERE paystack_ref = ?", [$reference]);
            if ($order) {
                $db->update('orders', [
                    'payment_status' => 'refunded',
                    'status'         => 'refunded',
                ], 'id = ?', [$order['id']]);
            }
        }
        break;
}

http_response_code(200);
echo json_encode(['received' => true]);
