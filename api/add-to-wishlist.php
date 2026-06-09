<?php
define('GYC_ACCESS', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false], 405);
}

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Please log in to save items', 'redirect' => SITE_URL . '/login.php']);
}

$productId = (int)($_POST['product_id'] ?? 0);
if (!$productId) {
    jsonResponse(['success' => false, 'message' => 'Invalid product'], 400);
}

if (isInWishlist($productId)) {
    removeFromWishlist($productId);
    jsonResponse(['success' => true, 'action' => 'removed', 'message' => 'Removed from wishlist']);
} else {
    addToWishlist($productId);
    jsonResponse(['success' => true, 'action' => 'added', 'message' => 'Saved to wishlist!']);
}
