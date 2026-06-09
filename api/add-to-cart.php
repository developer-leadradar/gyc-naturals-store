<?php
define('GYC_ACCESS', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$productId = (int)($_POST['product_id'] ?? 0);
$qty       = max(1, (int)($_POST['qty'] ?? 1));

if (!$productId) {
    jsonResponse(['success' => false, 'message' => 'Invalid product'], 400);
}

$product = getProductById($productId);
if (!$product || !$product['is_active']) {
    jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
}
if ($product['stock_quantity'] < $qty) {
    jsonResponse(['success' => false, 'message' => 'Not enough stock'], 400);
}

$result = addToCart($productId, $qty);
if ($result) {
    $count = getCartCount();
    jsonResponse(['success' => true, 'message' => $product['name'] . ' added to bag!', 'cart_count' => $count]);
} else {
    jsonResponse(['success' => false, 'message' => 'Could not add to cart. Please try again.'], 500);
}
