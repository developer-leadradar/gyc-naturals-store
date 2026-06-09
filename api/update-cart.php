<?php
define('GYC_ACCESS', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$action    = $_POST['action']    ?? '';
$productId = (int)($_POST['product_id'] ?? 0);
$qty       = (int)($_POST['qty']       ?? 0);

if (!$productId) {
    jsonResponse(['success' => false, 'message' => 'Invalid product'], 400);
}

if ($action === 'remove' || $qty <= 0) {
    removeFromCart($productId);
} else {
    updateCartQuantity($productId, $qty);
}

$summary = getCartSummary();
jsonResponse([
    'success'    => true,
    'cart_count' => getCartCount(),
    'subtotal'   => formatPrice($summary['subtotal'] ?? 0),
    'total'      => formatPrice($summary['total']    ?? 0),
]);
