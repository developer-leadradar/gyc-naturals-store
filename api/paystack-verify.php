<?php
define('GYC_ACCESS', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/email-templates.php';

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// ─────────────────────────────────────────────────────────
// GET: Paystack callback after payment — verify & create order
// ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['reference'])) {
    $reference = sanitize($_GET['reference']);
    $type      = sanitize($_GET['type'] ?? 'order');

    // Deposit payment verification (for appointment bookings)
    if ($type === 'deposit' && !empty($_GET['apt'])) {
        $aptNum = sanitize($_GET['apt']);
        redirect(SITE_URL . '/booking-confirmation.php?apt=' . urlencode($aptNum) . '&ref=' . urlencode($reference));
    }

    // Order payment verification
    $payData = verifyPaystackTransaction($reference);
    if (!$payData) {
        redirect(SITE_URL . '/checkout.php?error=payment_failed');
    }

    // Retrieve pending order from session
    $pending = $_SESSION['pending_order'] ?? null;
    if (!$pending) {
        redirect(SITE_URL . '/checkout.php?error=session_expired');
    }

    $orderData     = $pending['order_data'];
    $cartItemsSnap = $pending['cart_items'];

    // Create order
    $orderData['payment_status'] = 'paid';
    $orderData['payment_method'] = 'paystack';
    $orderData['paystack_ref']   = $reference;
    $result = createOrder($orderData);

    if (!$result['success']) {
        redirect(SITE_URL . '/checkout.php?error=order_failed');
    }

    $orderId     = $result['order_id'];
    $orderNumber = $result['order_number'];

    // Add order items and reduce stock
    addOrderItems($orderId, $cartItemsSnap);

    // Send branded confirmation email
    if (!empty($orderData['customer_email'])) {
        $orderForEmail = array_merge($orderData, [
            'order_number' => $orderNumber,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        $emailHtml = emailOrderConfirmation($orderForEmail, $cartItemsSnap);
        sendEmail($orderData['customer_email'], 'Order Confirmed — ' . $orderNumber . ' | GYC Naturals', $emailHtml);
    }

    // Clear cart and session
    clearCart();
    unset($_SESSION['pending_order']);

    redirect(SITE_URL . '/order-details.php?order=' . urlencode($orderNumber));
}

// ─────────────────────────────────────────────────────────
// POST: Checkout form → save to session, return Paystack params
// ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isAjax) header('Content-Type: application/json; charset=utf-8');

    if (!verifyCsrfSilent()) {
        if ($isAjax) { echo json_encode(['success' => false, 'message' => 'Invalid security token.']); exit; }
        redirect(SITE_URL . '/checkout.php?error=csrf');
    }

    $summary = getCartSummary();
    if ($summary['itemCount'] === 0) {
        if ($isAjax) { echo json_encode(['success' => false, 'message' => 'Your cart is empty.']); exit; }
        redirect(SITE_URL . '/cart.php');
    }

    // Sanitize form fields
    $firstName  = trim(sanitize($_POST['shipping_first_name'] ?? ''));
    $lastName   = trim(sanitize($_POST['shipping_last_name']  ?? ''));
    $email      = strtolower(trim(sanitize($_POST['customer_email']  ?? '')));
    $phone      = trim(sanitize($_POST['shipping_phone']      ?? ''));
    $address    = trim(sanitize($_POST['shipping_address']    ?? ''));
    $city       = trim(sanitize($_POST['shipping_city']       ?? ''));
    $state      = trim(sanitize($_POST['shipping_state']      ?? ''));
    $country    = 'Nigeria';
    $notes      = trim(sanitize($_POST['notes']               ?? ''));

    $errors = [];
    if (!$firstName) $errors[] = 'First name required.';
    if (!$lastName)  $errors[] = 'Last name required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
    if (!$phone)     $errors[] = 'Phone number required.';
    if (!$address)   $errors[] = 'Shipping address required.';
    if (!$city)      $errors[] = 'City required.';
    if (!$state)     $errors[] = 'State required.';

    if (!empty($errors)) {
        if ($isAjax) { echo json_encode(['success' => false, 'message' => implode(' ', $errors)]); exit; }
        redirect(SITE_URL . '/checkout.php?error=' . urlencode($errors[0]));
    }

    $subtotal = $summary['subtotal'];
    $shipping = $summary['shipping'];
    $total    = $summary['total'];

    // Build order data (will be saved after payment verification)
    $orderData = [
        'user_id'              => isLoggedIn() ? $_SESSION['user_id'] : null,
        'status'               => 'pending',
        'payment_status'       => 'pending',
        'subtotal'             => $subtotal,
        'tax'                  => 0,
        'shipping'             => $shipping,
        'discount'             => 0,
        'total'                => $total,
        'shipping_first_name'  => $firstName,
        'shipping_last_name'   => $lastName,
        'shipping_address'     => $address,
        'shipping_city'        => $city,
        'shipping_state'       => $state,
        'shipping_country'     => $country,
        'shipping_phone'       => $phone,
        'billing_first_name'   => $firstName,
        'billing_last_name'    => $lastName,
        'billing_address'      => $address,
        'billing_city'         => $city,
        'billing_state'        => $state,
        'billing_country'      => $country,
        'billing_phone'        => $phone,
        'notes'                => $notes,
        'customer_email'       => $email,
    ];

    // Snapshot cart items for order
    $cartItems = array_map(function($item) {
        return [
            'product_id' => $item['product_id'],
            'name'       => $item['name'],
            'price'      => $item['price'],
            'quantity'   => $item['quantity'],
            'bundle_id'  => $item['bundle_id'] ?? null,
        ];
    }, $summary['items']);

    // Save to session
    $_SESSION['pending_order'] = [
        'order_data'  => $orderData,
        'cart_items'  => $cartItems,
    ];

    // Generate Paystack reference
    $reference = 'GYC-ORD-' . strtoupper(substr(md5(uniqid()), 0, 12));

    $paystackData = [
        'public_key' => PAYSTACK_PUBLIC_KEY,
        'email'      => $email,
        'amount'     => (int)($total * 100),
        'currency'   => 'NGN',
        'reference'  => $reference,
        'metadata'   => [
            'customer_name' => $firstName . ' ' . $lastName,
            'phone'         => $phone,
            'custom_fields' => [
                ['display_name' => 'Order Total', 'variable_name' => 'order_total', 'value' => formatPrice($total)],
                ['display_name' => 'Item Count',  'variable_name' => 'item_count',  'value' => $summary['itemCount']],
            ],
        ],
    ];

    if ($isAjax) {
        echo json_encode(['success' => true, 'paystack' => $paystackData]);
    } else {
        // Non-JS fallback: use Paystack redirect API
        $payUrl = generatePaystackLink($email, $total, $reference);
        if ($payUrl) redirect($payUrl);
        else redirect(SITE_URL . '/checkout.php?error=paystack_unavailable');
    }
    exit;
}

// Fallback
redirect(SITE_URL . '/checkout.php');
